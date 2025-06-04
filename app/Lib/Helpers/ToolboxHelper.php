<?php

namespace App\Lib\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class ToolboxHelper
{
    /**
     * Mutltibyte string replace.
     *
     * @param mixed   $search
     * @param mixed   $replace
     * @param mixed   $subject
     * @param integer $count
     * @return mixed
     */
    public static function mbReplace(mixed $search, mixed $replace, mixed $subject, int &$count = 0): mixed
    {
        if (!is_array($search) && is_array($replace)) {
            return false;
        }
        if (is_array($subject)) {
            // Call mb_replace for each single string in $subject .
            foreach ($subject as &$string) {
                $string = &self::mbReplace($search, $replace, $string, $count);
            }
        } elseif (is_array($search)) {
            if (!is_array($replace)) {
                foreach ($search as &$string) {
                    $subject = self::mbReplace($string, $replace, $subject, $count);
                }
            } else {
                $n = max(count($search), count($replace));
                while ($n--) {
                    $subject = self::mbReplace(current($search), current($replace), $subject, $count);
                    next($search);
                    next($replace);
                }
            }
        } else {
            $parts   = mb_split(preg_quote($search), $subject);
            $count   = count($parts) - 1;
            $subject = implode($replace, $parts);
        } //end if
        return $subject;
    }

    /**
     * Get validated pagination from request.
     *
     * @param integer $default
     * @param string  $field
     * @param string  $enumPath
     * @return integer
     */
    public static function getValidatedEnum(int $default, string $field, string $enumPath): int
    {
        try {
            return \intval(Validator::make(
                [$field => \request()->get($field)],
                [$field => new Enum($enumPath)]
            )->validated()[$field]);
        } catch (ValidationException $e) {
            return $default;
        }
    }

    /**
     * Create a custom pagination from collection.
     *
     * @param \Illuminate\Support\Collection $items
     * @param integer                        $perPage
     * @param array                          $options
     * @param integer|null                   $page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function customPaginate(
        Collection $items,
        int $perPage,
        array $options,
        int|null $page = null
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $page   = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $result = new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            $options
        );
        return $result;
    }

    /**
     * Array merge recursive distinct.
     *
     * @param array<int|string, mixed> $array1
     * @param array<int|string, mixed> $array2
     *
     * @return array<int|string, mixed>
     */
    public static function arrayMergeRecursiveDistinct(array &$array1, array &$array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Validate picture.
     *
     * @param string      $attribute
     * @param string|null $value
     * @param callable    $fail
     * @param object      $sizes
     * @return void
     */
    public static function validatePicture(string $attribute, string|null $value, callable $fail, object $sizes): void
    {
        if (!($useStorage = Storage::exists($value)) and !File::exists($value)) {
            $fail(trans(':attribute n\'existe pas dans le système de fichier', [
                'attribute' => $attribute
            ]));
            return;
        }
        $fileFullPath = self::getUploadedFileFullPath($value, $useStorage);
        if (
            !\in_array(self::getUploadedFileMimeType($value, $useStorage), [
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif'
            ])
        ) {
            $fail(trans(':attribute doit être une image de type \'image/jpeg,image/jpg,image/png,image/gif\'', [
                'attribute' => $attribute
            ]));
        }
        if (!($dimensions = \getimagesize($fileFullPath))) {
            $fail(trans(':attribute impossible de valider les dimensions', [
                'attribute' => $attribute
            ]));
        } else {
            $width  = intval($dimensions[0]);
            $height = intval($dimensions[1]);

            $xMessage = trans(
                ":attribute la largeur doit être comprise entre {$sizes->minWidth}px et {$sizes->maxWidth}px"
            );
            if ($sizes->minWidth === $sizes->maxWidth) {
                $xMessage = trans(":attribute la largeur doit être de {$sizes->minWidth}px");
            }
            $yMessage = trans(
                ":attribute la hauteur doit être comprise entre {$sizes->minWidth}px et {$sizes->maxWidth}px"
            );
            if ($sizes->minHeight === $sizes->maxHeight) {
                $yMessage = trans(":attribute la hauteur doit être de {$sizes->minHeight}px");
            }

            if (($width < $sizes->minWidth) or ($width > $sizes->maxWidth)) {
                $fail($xMessage);
            }
            if (($height < $sizes->minHeight) or ($height > $sizes->maxHeight)) {
                $fail($yMessage);
            }
        } //end if
    }

    /**
     * Get the file full path.
     *
     * @param string  $value
     * @param boolean $useStorage
     * @return string
     */
    private static function getUploadedFileFullPath(string $value, bool $useStorage): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk();
        return !$useStorage ? (realpath($value) ?: $value) : $disk->path($value);
    }

    /**
     * Get the file mimetype.
     *
     * @param string  $value
     * @param boolean $useStorage
     * @return string|false
     */
    private static function getUploadedFileMimeType(string $value, bool $useStorage): string|false
    {
        return !$useStorage ? File::mimeType($value) : Storage::mimeType($value);
    }

    /**
     * Dynamic model query.
     *
     * @param string                                $defaultLocale
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $column
     * @param string                                $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function queryColumnWithLocales(
        string $defaultLocale,
        Builder $query,
        string $column,
        string $value
    ): \Illuminate\Database\Eloquent\Builder {
        $currentLocale = app()->currentLocale();
        $locales       = collect(config('app.locales'))
            ->mapWithKeys(fn (string $locale) => [$locale => $locale]);
        if (!$locales->has($defaultLocale)) {
            // * Remove default locale.
            $locales->pull($defaultLocale);
        }
        if (!$locales->has($currentLocale)) {
            // * Remove current locale.
            $locales->pull($currentLocale);
        }

        return $query->where(function (Builder $query) use ($defaultLocale, $currentLocale, $locales, $column, $value) {
            // * Query default locale
            $query->whereRaw("JSON_EXTRACT({$column}, '$.{$defaultLocale}') = '{$value}'");
            if ($defaultLocale !== $currentLocale) {
                // * Query current locale
                $query->whereRaw("JSON_EXTRACT({$column}, '$.{$currentLocale}') = '{$value}'");
            }
            // * Query any other locale
            $locales->each(fn($locale) => $query->orWhereRaw(
                "JSON_EXTRACT({$column}, '$.{$locale}') = '{$value}'"
            ));
        });
    }

    /**
     * Asserts that the field is unique
     *
     * @param string                                  $table
     * @param string                                  $field
     * @param string|integer|float|boolean|null|array $value
     * @param integer|null                            $id
     * @param callable|null                           $query
     * @return void
     * @throws \Illuminate\Validation\ValidationException If the field is not unique.
     */
    public static function assertFieldIsUnique(
        string $table,
        string $field,
        string|int|float|bool|null|array $value,
        ?int $id = null,
        ?callable $query = null
    ): void {
        if (!$query) {
            Validator::make([$field => $value], [
                self::mbReplace('.', '\.', $field) => Rule::unique($table, $field)->ignore($id),
            ], [
                self::mbReplace('.', '\.', "{$field}.unique")
                => trans('crud.libs.toolbox.assertFieldIsUnique', ['attribute' => $field, 'value' => $value])
            ])->validate();
            return;
        }
        Validator::make([$field => $value], [
            $field => Rule::unique($table)->where($query)->ignore($id),
        ], [
            $field . '.unique' => trans(
                'crud.libs.toolbox.assertFieldIsUnique',
                ['attribute' => $field, 'value' => $value]
            )
        ])->validate();
    }
}
