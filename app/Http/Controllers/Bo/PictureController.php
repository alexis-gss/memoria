<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Exceptions\PictureUploadException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class PictureController extends Controller
{
    /**
     * Upload multiple chunks of the image.
     *
     * @throws UploadMissingFileException Bla.
     */
    public function upload(Request $request): JsonResponse
    {
        // Create the file receiver.
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        // Check if the upload is success, throw exception or return response you need.
        throw_if($receiver->isUploaded() === false, UploadMissingFileException::class);
        // Receive the file.
        $save = $receiver->receive();
        // Check if the upload has finished (in chunk mode it will send smaller files).
        if ($save->isFinished()) {
            // Save the file and return any response you need, current example uses `move` function.
            // If you are not using move, you need to manually delete the file: unlink($save->getFile()->getPathname()).
            return $this->store(
                $save->getFile(),
                $request->input('uuid', false),
                $request->input('gameSlug', false)
            );
        }
        // We are in chunk mode, lets send the current progress.
        /** @var \Pion\Laravel\ChunkUpload\Handler\AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            'done'   => $handler->getPercentageDone(),
            'status' => true,
        ]);
    }

    /**
     * Validate the assembled upload and store it as a .webp image.
     */
    protected function store(UploadedFile $file, mixed $uuid, string | false $gameSlug): JsonResponse
    {
        // Path of the temporary file assembled from the chunks.
        $tmpPath = $file->getPathname();

        try {
            $uuid      = $this->resolveUuid($uuid);
            $gameSlug  = $this->resolveGameSlug($gameSlug);
            $image     = $this->decodeImage($tmpPath);
            $directory = $this->prepareDirectory($gameSlug);

            $this->saveAsWebp($image, sprintf('%s/%s.webp', $directory, $uuid));
        } catch (PictureUploadException $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->status());
        } finally {
            // Always remove the temporary assembled file, on success or failure.
            File::delete($tmpPath);
        }

        return response()->json(['uid' => $uuid]);
    }

    /**
     * Return a valid uuid, generating one if none was provided.
     *
     * @throws PictureUploadException
     */
    private function resolveUuid(mixed $uuid): string
    {
        if ($uuid === false) {
            return Str::uuid()->toString();
        }

        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            throw new PictureUploadException('Invalid uuid');
        }

        return $uuid;
    }

    /**
     * Check the game slug, as it ends up in a file path.
     *
     * @throws PictureUploadException
     */
    private function resolveGameSlug(string | false $gameSlug): string
    {
        if ($gameSlug === false) {
            throw new PictureUploadException('Missing parameter gameSlug');
        }

        if (preg_match('/^[a-z0-9-]+$/', $gameSlug) !== 1) {
            throw new PictureUploadException('Invalid gameSlug');
        }

        return $gameSlug;
    }

    /**
     * Create the destination folder if it doesn't exist yet and return its path.
     *
     * @throws PictureUploadException
     */
    private function prepareDirectory(string $gameSlug): string
    {
        $directory = Storage::disk('public')->path('pictures/' . $gameSlug);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new PictureUploadException('Unable to create directory', 500);
        }

        return $directory;
    }

    /**
     * Check the file is a real image based on its content, then decode it
     * (JPEG, PNG, GIF, WebP…).
     *
     * @throws PictureUploadException
     */
    private function decodeImage(string $path): \GdImage
    {
        // @phpcs:disabled Generic.PHP.NoSilencedErrors.Discouraged
        if (@getimagesize($path) === false) {
            throw new PictureUploadException('Invalid or corrupted image file');
        }

        $image = @imagecreatefromstring((string) file_get_contents($path));
        // @phpcs:enable

        if ($image === false) {
            throw new PictureUploadException('Image format not supported');
        }

        return $image;
    }

    /**
     * Convert the image to a lossless .webp file at the given path.
     *
     * @throws PictureUploadException
     */
    private function saveAsWebp(\GdImage $image, string $target): void
    {
        // Convert palette images to true color and preserve PNG transparency.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        if (imagewebp($image, $target, IMG_WEBP_LOSSLESS) === false) {
            throw new PictureUploadException('Unable to save image', 500);
        }
    }
}
