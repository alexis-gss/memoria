<?php

namespace App\Lib\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 *  * The storage strategy will be using date.
 * The file name may contain sufix timestamp highresolution time (hrtime or microtime) if is duplicate.
 * (function() { return time() . ' ' . Str::of(microtime(true))->slug() . ' ' . hrtime(true); })();
 *
 * posts/                                                 - The model table name
 *   10/                                                  - Year
 *     04/                                                - Month
 *       19/                                              - Day
 *         5f/                                            - First two letters of md5 file
 *           black-cat-under-red-car.png                  - Slugified file name
 *           black-cat-under-red-car-286071690833400.png  - Slugified file name that is duplicated
 *
 * The final path will be storage/modelfiles/posts/10/04/19/5f/black-cat-under-red-car-286071690833400.png
 *
 * ! No limits of file per folder will be handled.
 */
class FileStorageHelper
{
    /**
     * Store a file using optimized storage strategy.
     *
     * You can use filename param to overload file name.
     *
     * @param \Illuminate\Database\Eloquent\Model               $model    The model that will hold the picture.
     * @param \Illuminate\Http\UploadedFile|\SplFileInfo|string $file     The file, will just return itself is string.
     * @param boolean                                           $slugify  Force filename slugification.
     * @param string|null                                       $filename This string may be used to store the file.
     * @param boolean                                           $private  To store the file in a private space.
     * @return string
     * @throws \RuntimeException If file copy fails.
     * @phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     */
    public static function storeFile(
        Model $model,
        $file,
        bool $slugify = false,
        string|null $filename = null,
        bool $private = false
    ): string {
        // phpcs:enable
        $tableName = $model->getTable();
        if (\is_object($file) and $file instanceof \Illuminate\Http\UploadedFile) {
            /** @var \Illuminate\Http\UploadedFile $file */
            $filename = $filename ?: $file->getClientOriginalName();
            $filename = $slugify ? self::slugifyFileName($filename) : $filename;
            $newPath  = self::prepareMoveFile($tableName, $filename, $private);
            $newPath  = str_replace(storage_path('app'), '', $newPath);

            if (!$private) {
                // * Store public file.
                $file->storePubliclyAs(
                    pathinfo($newPath, \PATHINFO_DIRNAME),
                    pathinfo($newPath, \PATHINFO_BASENAME)
                );
                return 'storage' . str_replace('/public', '', $newPath);
            }

            $file->storeAs(
                pathinfo($newPath, \PATHINFO_DIRNAME),
                pathinfo($newPath, \PATHINFO_BASENAME)
            );

            return str_replace('/private/', '', $newPath);
        } //end if
        if (\is_object($file) and $file instanceof \SplFileInfo) {
            /** @var \SplFileInfo $file */
            $filename = $filename ?: $file->getBaseName();
            $filename = $slugify ? self::slugifyFileName($filename) : $filename;
            $newPath  = self::prepareMoveFile($tableName, $filename, $private);
            if (!File::copy($file->getRealPath(), $newPath)) {
                throw new \RuntimeException(sprintf(
                    'Failed to copy file %s to %s',
                    $file->getRealPath(),
                    $newPath
                ));
            }
            return $private ?
                \ltrim(str_replace(\storage_path('app/private'), '/storage/app/private', $newPath), '/') :
                \ltrim(str_replace(\storage_path('app/public'), '/storage', $newPath), '/');
        }
        return strval($file);
    }

    /**
     * Destroy the file associate with the attribute.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $attribute
     * @return void
     */
    public static function removeFile(Model $model, string $attribute): void
    {
        $path = \ltrim(\urldecode($model->{$attribute}), '/');
        if (\strpos($path, 'storage') !== 0) {
            return;
        }
        $path = ToolboxHelper::mbReplace('storage/', '', $path);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Destroy the file associate with the attribute old value (before updated to DB).
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $attribute
     * @param boolean                             $onlyIfUpdated
     * @return void
     */
    public static function removeOldFile(Model $model, string $attribute, bool $onlyIfUpdated = true): void
    {
        // * Ignore If removal has to be done only if the field has changed and it actually hasnt.
        if ($onlyIfUpdated and $model->{$attribute} === $model->getOriginal($attribute)) {
            return;
        }
        $originalPath = \ltrim(\urldecode($model->getOriginal($attribute)), '/');
        if (\strpos($originalPath, 'storage') !== 0) {
            return;
        }
        $originalPath = ToolboxHelper::mbReplace('storage/', '', $originalPath);
        if (Storage::disk('public')->exists($originalPath)) {
            Storage::disk('public')->delete($originalPath);
        }
    }

    /**
     * Remove all old files that are stored in public that
     * are not present anymore on new array files
     *
     * @param string[] $oldFiles The old array files.
     * @param string[] $newFiles The new array files.
     * @return void
     */
    public static function removeOldFiles(array $oldFiles, array $newFiles): void
    {
        $oldFiles      = \collect(self::onlyUploadedFilesWithFileStorageHelper($oldFiles));
        $filesToKeep   = \collect(self::onlyUploadedFilesWithFileStorageHelper($newFiles));
        $filesToRemove = $oldFiles->diff($filesToKeep);
        $filesToRemove->each(function (string $publicStorageFilePath) {
            $path = ToolboxHelper::mbReplace('storage/', '', \ltrim(\urldecode($publicStorageFilePath), '/'));
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    /**
     * Filter array to get only uploaded files that exists
     * on disk 'public' (using helper storage strategy).
     *
     * @param array $urls
     * @return array
     */
    public static function onlyUploadedFilesWithFileStorageHelper(array $urls): array
    {
        return \collect($urls)->unique()
            // * Filter to get only /uploads and using app url or empty
            ->filter(function (string $url) {
                $path = \parse_url($url, \PHP_URL_PATH);
                return \strpos(\ltrim($path, '/'), 'storage/modelfiles') === 0;
            })->filter(function (string $url) {
                $path = \parse_url($url, \PHP_URL_PATH);
                return Storage::disk('public')
                    ->exists(ToolboxHelper::mbReplace('storage/', '', \ltrim(\urldecode($path), '/')));
            })->all();
    }

    /**
     * Prepare storage path and return
     * the file full path.
     *
     * @param string  $tableName Model table name.
     * @param string  $filename  The basename for the file.
     * @param boolean $private   May the file be stored in a private dir.
     * @return string
     */
    private static function prepareMoveFile(string $tableName, string $filename, bool $private = false): string
    {
        // * Security filter
        $filename = \htmlspecialchars($filename);
        // * Security filter + beautifier
        $filename = self::sanitizeFileName($filename);
        // * Slugify filename
        $ext      = \pathinfo($filename, \PATHINFO_EXTENSION);
        $filename = Str::slug(\pathinfo($filename, \PATHINFO_FILENAME)) .
            (\strlen($ext) ? ".{$ext}" : '');

        $folderPath = self::getStoragePath(
            $private ? 'app/private/modelfiles' : 'app/public/modelfiles',
            $tableName,
            $filename
        );
        $filename   = self::getFileUniqueName($folderPath, $filename);
        return "{$folderPath}/{$filename}";
    }

    /**
     * Create sub folders to store files using pattern
     * storage/modelfiles/posts/10/04/19/5f
     * year/month/day/md5 first 2 letters.
     *
     * @param string $storage_dir
     * @param string $table
     * @param string $filename
     * @return string
     */
    private static function getStoragePath(string $storage_dir, string $table, string $filename): string
    {
        $filenameHash = \md5($filename);
        $pathFolder   = \storage_path(sprintf(
            '%s/%s/%s/%s/%s/%s',
            $storage_dir,
            $table,
            gmdate('y'),
            gmdate('m'),
            gmdate('d'),
            substr($filenameHash, 0, 2)
        ));
        if (!File::exists($pathFolder)) {
            File::makeDirectory($pathFolder, 0755, true);
        }
        return $pathFolder;
    }

    /**
     * Get unique filename if the path is already occupied.
     *
     * @param string $folderPath
     * @param string $filename
     * @return string
     * @throws \RuntimeException If a unique filename cannot be obtained.
     */
    private static function getFileUniqueName(string $folderPath, string $filename): string
    {
        $attempts    = 0;
        $maxAttempts = 20;
        while (File::exists("{$folderPath}/{$filename}")) {
            if ($attempts++ >= $maxAttempts) {
                throw new \RuntimeException("Failed to has a unique filename for {$folderPath}/{$filename}");
            }
            $filename = pathinfo($filename, \PATHINFO_FILENAME) .
                \uniqid('-') . '.' .
                pathinfo($filename, \PATHINFO_EXTENSION);
        }
        return $filename;
    }

    /**
     * Slugify the filename.
     *
     * @param string $filename
     * @return string
     */
    public static function slugifyFileName(string $filename): string
    {
        return Str::of(pathinfo($filename, \PATHINFO_FILENAME))->slug() .
            '.' .
            pathinfo($filename, \PATHINFO_EXTENSION);
    }

    /**
     * Sanitize File name for uploaded files.
     *
     * @param string  $filename The original uploaded client file name.
     * @param boolean $beautify Beautify the filename.
     * @return string The sanitized file name.
     */
    public static function sanitizeFileName(string $filename, bool $beautify = true): string
    {
        // * Sanitize filename
        // phpcs:disable Generic.Files.LineLength.TooLong
        $filename = preg_replace(
            '~
            [<>:"/\\\|?*]|           # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
            [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
            [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
            [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
            [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
            ~x',
            '-',
            $filename
        );
        // phpcs:enable
        // * Avoids '.' '..' or '.hiddenFiles' .
        $filename = Str::of($filename)->ltrim('.-');
        // * Optional beautification
        if ($beautify) {
            $filename = self::beautifyFilename($filename);
        }
        // * Maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
        /** @var string */
        $extension = pathinfo($filename, \PATHINFO_EXTENSION);
        /** @var string */
        $filename = pathinfo($filename, \PATHINFO_FILENAME);

        $encoding = mb_detect_encoding($filename);
        $encoding = $encoding ? $encoding : null;
        return \mb_strcut(
            $filename,
            0,
            255 - (!empty($extension) ? strlen($extension) + 1 : 0),
            $encoding
        ) . (!empty($extension) ? ".$extension" : '');
    }

    /**
     * Beautify a filename.
     *
     * @param string $filename
     * @return string
     */
    private static function beautifyFilename(string $filename): string
    {
        // Reduce consecutive characters.
        $filename = preg_replace([
            // * "file   name.zip" becomes 'file-name.zip'
            '/ +/',
            // * "file___name.zip" becomes 'file-name.zip'
            '/_+/',
            // * "file---name.zip" becomes 'file-name.zip'
            '/-+/'
        ], '-', $filename);
        $filename = preg_replace([
            // * "file--.--.-.--name.zip" becomes "file.name.zip"
            '/-*\.-*/',
            // * "file...name..zip" becomes "file.name.zip"
            '/\.{2,}/'
        ], '.', $filename);
        // Lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625 .
        $filename = mb_strtolower($filename, mb_detect_encoding($filename));
        // * ".file-name.-" becomes "file-name"
        $filename = trim($filename, '.-');
        return $filename;
    }
}
