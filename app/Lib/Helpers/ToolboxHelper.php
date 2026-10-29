<?php

declare(strict_types=1);

namespace App\Lib\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
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
     */
    public static function mbReplace(mixed $search, mixed $replace, mixed $subject, int &$count = 0): mixed
    {
        if (! is_array($search) && is_array($replace)) {
            return false;
        }
        if (is_array($subject)) {
            // Call mb_replace for each single string in $subject .
            foreach ($subject as &$string) {
                $string = &self::mbReplace($search, $replace, $string, $count);
            }
        } elseif (is_array($search)) {
            if (! is_array($replace)) {
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
            $count   = \intval(\count($parts) - 1);
            $subject = implode($replace, $parts);
        } // end if

        return $subject;
    }

    /**
     * Get a request field validated against an enum, or the default value.
     *
     * @param  class-string<\BackedEnum>  $enumPath
     */
    public static function getValidatedEnum(int | string $default, string $field, string $enumPath): int | string
    {
        try {
            /** @var int|string */
            return Validator::make(
                [$field => \request()->input($field)],
                [$field => new Enum($enumPath)]
            )->validated()[$field];
        } catch (ValidationException) {
            return $default;
        }
    }

    /**
     * Create a custom pagination from collection.
     */
    public static function customPaginate(
        Collection $items,
        int $perPage,
        array $options,
        ?int $page = null
    ): LengthAwarePaginator {
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
     * @param  array<int|string, mixed>  $array1
     * @param  array<int|string, mixed>  $array2
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

    // @phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
    public static function validatePicture(
        string $attribute,
        string | UploadedFile | null $value,
        callable $fail,
        object $sizes
    ): void {
        // @phpcs:enable
        if ($value === null || $value === '') {
            return;
        }

        if ($value instanceof UploadedFile) {
            if (! $value->isValid()) {
                $fail(trans(':attribute n\'a pas pu être envoyé correctement', [
                    'attribute' => $attribute,
                ]));

                return;
            }
            $fileFullPath = $value->getRealPath();
            $mimeType     = $value->getMimeType();
        } else {
            if (! ($useStorage = Storage::exists($value)) and ! File::exists($value)) {
                $fail(trans(':attribute n\'existe pas dans le système de fichier', [
                    'attribute' => $attribute,
                ]));

                return;
            }
            $fileFullPath = self::getUploadedFileFullPath($value, $useStorage);
            $mimeType     = self::getUploadedFileMimeType($value, $useStorage);
        }

        if (! \in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'], true)) {
            $fail(trans(':attribute doit être une image de type \'image/jpeg,image/jpg,image/png,image/gif\'', [
                'attribute' => $attribute,
            ]));
        }

        if (! $fileFullPath || ! ($dimensions = \getimagesize($fileFullPath))) {
            $fail(trans(':attribute impossible de valider les dimensions', [
                'attribute' => $attribute,
            ]));

            return;
        }

        $width  = intval($dimensions[0]);
        $height = intval($dimensions[1]);

        $xMessage = $sizes->minWidth === $sizes->maxWidth
            ? trans(":attribute la largeur doit être de {$sizes->minWidth}px")
            : trans(":attribute la largeur doit être comprise entre {$sizes->minWidth}px et {$sizes->maxWidth}px");

        $yMessage = $sizes->minHeight === $sizes->maxHeight
            ? trans(":attribute la hauteur doit être de {$sizes->minHeight}px")
            : trans(":attribute la hauteur doit être comprise entre {$sizes->minHeight}px et {$sizes->maxHeight}px");

        if ($width < $sizes->minWidth || $width > $sizes->maxWidth) {
            $fail($xMessage);
        }
        if ($height < $sizes->minHeight || $height > $sizes->maxHeight) {
            $fail($yMessage);
        }
    }

    /**
     * Get the file full path.
     */
    private static function getUploadedFileFullPath(string $value, bool $useStorage): string
    {
        $disk = Storage::disk();

        return ! $useStorage ? (realpath($value) ?: $value) : $disk->path($value);
    }

    /**
     * Get the file mimetype.
     */
    private static function getUploadedFileMimeType(string $value, bool $useStorage): string | false
    {
        return ! $useStorage ? File::mimeType($value) : Storage::mimeType($value);
    }

    /**
     * Dynamic model query.
     */
    public static function queryColumnWithLocales(
        string $defaultLocale,
        Builder $query,
        string $column,
        string $value
    ): Builder {
        $currentLocale = app()->currentLocale();
        $locales       = collect(config('app.locales'))
            ->mapWithKeys(fn (string $locale) => [$locale => $locale]);
        if (! $locales->has($defaultLocale)) {
            // * Remove default locale.
            $locales->pull($defaultLocale);
        }
        if (! $locales->has($currentLocale)) {
            // * Remove current locale.
            $locales->pull($currentLocale);
        }

        return $query->where(function (Builder $query) use ($defaultLocale, $currentLocale, $locales, $column, $value): void {
            // * Query default locale
            $query->whereRaw("JSON_EXTRACT({$column}, '$.{$defaultLocale}') = '{$value}'");
            if ($defaultLocale !== $currentLocale) {
                // * Query current locale
                $query->whereRaw("JSON_EXTRACT({$column}, '$.{$currentLocale}') = '{$value}'");
            }
            // * Query any other locale
            $locales->each(fn ($locale) => $query->orWhereRaw(
                "JSON_EXTRACT({$column}, '$.{$locale}') = '{$value}'"
            ));
        });
    }

    /**
     * Asserts that the field is unique
     *
     * @throws ValidationException If the field is not unique.
     */
    public static function assertFieldIsUnique(
        string $table,
        string $field,
        string | int | float | bool | null | array $value,
        ?int $id = null,
        ?callable $query = null
    ): void {
        if (! $query) {
            Validator::make([$field => $value], [
                self::mbReplace('.', '\.', $field) => Rule::unique($table, $field)->ignore($id),
            ], [
                self::mbReplace('.', '\.', "{$field}.unique") => trans('crud.libs.toolbox.assertFieldIsUnique', [
                    'attribute' => $field,
                    'value'     => $value,
                ]),
            ])->validate();

            return;
        }
        Validator::make([$field => $value], [
            $field => Rule::unique($table)->where($query)->ignore($id),
        ], [
            $field . '.unique' => trans(
                'crud.libs.toolbox.assertFieldIsUnique',
                ['attribute' => $field, 'value' => $value]
            ),
        ])->validate();
    }
}
