<?php

declare(strict_types=1);

namespace App\Lib\Helpers;

use App\Exceptions\PictureUploadException;
use GdImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Single entry point for everything related to the pictures files.
 *
 * Layout on the public disk:
 *   pictures/{game-slug}/{uuid}.webp           original, lossless
 *   pictures/{game-slug}/thumbnails/{uuid}.webp    grid thumbnail, lossy
 */
final class PictureHelper
{
    public const DIRECTORY = 'pictures';

    public const THUMBS_DIRECTORY = 'thumbnails';

    public const THUMB_WIDTH = 800;

    public const THUMB_QUALITY = 80;

    // * PATHS

    /**
     * Path of a picture, relative to the public disk.
     */
    public static function relativePath(string $gameSlug, string $uuid, bool $thumbnail = false): string
    {
        return $thumbnail
            ? sprintf('%s/%s/%s/%s.webp', self::DIRECTORY, $gameSlug, self::THUMBS_DIRECTORY, $uuid)
            : sprintf('%s/%s/%s.webp', self::DIRECTORY, $gameSlug, $uuid);
    }

    /**
     * Absolute path of a picture on the server.
     */
    public static function absolutePath(string $gameSlug, string $uuid, bool $thumbnail = false): string
    {
        return Storage::disk('public')->path(self::relativePath($gameSlug, $uuid, $thumbnail));
    }

    // * VALIDATION

    /**
     * Return a valid uuid, generating one if none was provided.
     *
     * @throws PictureUploadException
     */
    public static function resolveUuid(mixed $uuid): string
    {
        if ($uuid === false || $uuid === null) {
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
    public static function assertValidSlug(mixed $gameSlug): string
    {
        if ($gameSlug === false || $gameSlug === null) {
            throw new PictureUploadException('Missing parameter game slug');
        }

        if (! is_string($gameSlug) || preg_match('/^[a-z0-9-]+$/', $gameSlug) !== 1) {
            throw new PictureUploadException('Invalid game slug');
        }

        return $gameSlug;
    }

    // * STORE

    /**
     * Validate an uploaded image, then save the lossless original and its thumbnail.
     *
     * @throws PictureUploadException
     */
    public static function store(string $sourcePath, mixed $gameSlug, string $uuid): void
    {
        $gameSlug = self::assertValidSlug($gameSlug);
        $image    = self::decode($sourcePath);

        $original = self::absolutePath($gameSlug, $uuid);
        self::prepareDirectory(dirname($original));

        self::saveOriginal($image, $original);

        // A missing thumbnail is not fatal: the front falls back on the original.
        if (! self::saveThumbnail($image, self::absolutePath($gameSlug, $uuid, true))) {
            Log::warning('Picture thumbnail not generated.', ['gameSlug' => $gameSlug, 'uuid' => $uuid]);
        }
    }

    /**
     * Generate the thumbnail of an already saved picture (used for the existing ones).
     */
    public static function createThumbnail(string $gameSlug, string $uuid, bool $force = false): bool
    {
        $source = self::absolutePath($gameSlug, $uuid);
        $target = self::absolutePath($gameSlug, $uuid, true);

        if (! $force && is_file($target)) {
            return true;
        }

        if (! is_file($source)) {
            return false;
        }

        // @phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        $image = @imagecreatefromwebp($source);

        return $image !== false && self::saveThumbnail($image, $target);
    }

    // * DELETE / RENAME

    /**
     * Delete the original and the thumbnail of a picture.
     */
    public static function delete(string $gameSlug, string $uuid): void
    {
        $disk = Storage::disk('public');
        $disk->delete([
            self::relativePath($gameSlug, $uuid),
            self::relativePath($gameSlug, $uuid, true),
        ]);
    }

    /**
     * Rename the folder of a game (thumbnails follow, they live inside it).
     */
    public static function renameDirectory(string $oldSlug, string $newSlug): void
    {
        if ($oldSlug === $newSlug) {
            return;
        }

        $old = Storage::disk('public')->path(self::DIRECTORY . '/' . $oldSlug);
        $new = Storage::disk('public')->path(self::DIRECTORY . '/' . $newSlug);

        if (is_dir($old) && ! file_exists($new)) {
            rename($old, $new);
        }
    }

    /**
     * Delete every file or folder which is not expected.
     *
     * @param  array<string, array<int, string>>  $expected  Uuids of the pictures, indexed by game slug.
     * @return int Number of deleted items.
     */
    public static function purgeOrphans(array $expected): int
    {
        $disk    = Storage::disk('public');
        $deleted = 0;

        foreach ($disk->directories(self::DIRECTORY) as $directory) {
            $slug = basename($directory);

            if (! array_key_exists($slug, $expected)) {
                $disk->deleteDirectory($directory);
                $deleted++;

                continue;
            }

            $uuids = array_flip($expected[$slug]);
            foreach ([$directory, $directory . '/' . self::THUMBS_DIRECTORY] as $folder) {
                foreach ($disk->files($folder) as $file) {
                    if (! isset($uuids[pathinfo($file, PATHINFO_FILENAME)])) {
                        $disk->delete($file);
                        $deleted++;
                    }
                }
            }
        }

        return $deleted;
    }

    // * INTERNALS

    /**
     * Check the file is a real image based on its content, then decode it (JPEG, PNG, GIF, WebP…).
     *
     * @throws PictureUploadException
     */
    private static function decode(string $path): GdImage
    {
        // @phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        if (@getimagesize($path) === false) {
            throw new PictureUploadException('Invalid or corrupted image file');
        }

        $image = @imagecreatefromstring((string) file_get_contents($path));
        // @phpcs:enable

        if ($image === false) {
            throw new PictureUploadException('Image format not supported');
        }

        // Convert palette images to true color and preserve PNG transparency.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * @throws PictureUploadException
     */
    private static function prepareDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new PictureUploadException('Unable to create directory', 500);
        }
    }

    /**
     * @throws PictureUploadException
     */
    private static function saveOriginal(GdImage $image, string $target): void
    {
        if (imagewebp($image, $target, IMG_WEBP_LOSSLESS) === false) {
            throw new PictureUploadException('Unable to save image', 500);
        }
    }

    /**
     * Save a lossy thumbnail. Never throws: returns false on failure.
     */
    private static function saveThumbnail(GdImage $image, string $target): bool
    {
        $directory = dirname($target);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return false;
        }

        $thumb = imagesx($image) > self::THUMB_WIDTH
            ? imagescale($image, self::THUMB_WIDTH, -1, IMG_BICUBIC)
            : $image;

        if ($thumb === false) {
            return false;
        }

        imagealphablending($thumb, true);
        imagesavealpha($thumb, true);

        return imagewebp($thumb, $target, self::THUMB_QUALITY);
    }
}
