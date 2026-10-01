<?php

namespace App\Http\Controllers\Bo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException Bla.
     */
    public function upload(Request $request): \Illuminate\Http\JsonResponse
    {
        // Create the file receiver.
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));
        // Check if the upload is success, throw exception or return response you need.
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }
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
            "done"   => $handler->getPercentageDone(),
            'status' => true
        ]);
    }

    /**
     * Validate the assembled upload and store it as a .webp image.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param mixed                         $uuid
     * @param string|false                  $gameSlug
     * @return \Illuminate\Http\JsonResponse
     */
    protected function store(UploadedFile $file, mixed $uuid, string|false $gameSlug): \Illuminate\Http\JsonResponse
    {
        // Generate a new UUID if none was provided.
        if ($uuid === false) {
            $uuid = Str::uuid();
        }

        // The game slug is required to build the storage path.
        if ($gameSlug === false) {
            abort(422, 'Missing parameter gameSlug');
        }

        // Build the destination folder and create it if it doesn't exist yet.
        $finalPath = Storage::disk("public")->path(sprintf("pictures/%s/", $gameSlug));
        if (!is_dir($finalPath)) {
            mkdir($finalPath, 0755, true);
        }

        // Path of the temporary file assembled from the chunks.
        $tmpPath = $file->getPathname();

        // Check the file is a real image based on its content, not its extension.
        // Fails for corrupted, truncated or non-image files.
        // @phpcs:disabled Generic.PHP.NoSilencedErrors.Discouraged
        if (@getimagesize($tmpPath) === false) {
            unlink($tmpPath);
            return response()->json(['error' => 'Invalid or corrupted image file'], 422);
        }

        // Decode the image with automatic format detection (JPEG, PNG, GIF, WebP…).
        $image = @imagecreatefromstring(file_get_contents($tmpPath));
        // @phpcs:enable
        if ($image === false) {
            unlink($tmpPath);
            return response()->json(['error' => 'Image format not supported'], 422);
        }

        // Convert palette images to true color and preserve PNG transparency.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        // Save as .webp and free the memory used by the image.
        imagewebp($image, $finalPath . $uuid . ".webp", IMG_WEBP_LOSSLESS);
        imagedestroy($image);

        // Remove the temporary assembled file.
        unlink($tmpPath);

        return response()->json([
            'uid' => $uuid,
        ]);
    }
}
