<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Exceptions\PictureUploadException;
use App\Http\Controllers\Controller;
use App\Lib\Helpers\PictureHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
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
     * Store the assembled upload (original + thumbnail) through the PictureHelper.
     */
    protected function store(UploadedFile $file, mixed $uuid, mixed $gameSlug): JsonResponse
    {
        // Path of the temporary file assembled from the chunks.
        $tmpPath = $file->getPathname();

        try {
            $uuid = PictureHelper::resolveUuid($uuid);
            PictureHelper::store($tmpPath, $gameSlug, $uuid);
        } catch (PictureUploadException $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->status());
        } finally {
            // Always remove the temporary assembled file, on success or failure.
            File::delete($tmpPath);
        }

        return response()->json(['uid' => $uuid]);
    }
}
