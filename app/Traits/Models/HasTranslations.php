<?php

declare(strict_types=1);

namespace App\Traits\Models;

use App\Lib\Helpers\FileStorageHelper;
use App\Lib\Helpers\ToolboxHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations as BaseHasTranslations;

/**
 * @property array<int, string>|null $privatePictures Attributes stored as private files.
 *
 * @phpstan-require-extends Model
 */
trait HasTranslations
{
    use BaseHasTranslations;

    /**
     * Set a given attribute on the model.
     *
     * ! Fix of spatie translatable.
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public function setAttribute($key, $value): mixed
    {
        // phpcs:enable
        if ($this->isTranslatableAttribute($key) && is_array($value)) {
            return $this->setTranslation($key, $this->getLocale(), $value);
        }

        // Pass arrays and untranslatable attributes to the parent method.
        if (! $this->isTranslatableAttribute($key) || is_array($value)) {
            return parent::setAttribute($key, $value);
        }

        // * Store Uploaded file
        if (\is_object($value) and $value instanceof UploadedFile) {
            $storeAsPrivate = (\is_array($this->privatePictures) and \in_array($key, $this->privatePictures)) ? true : false;
            $value          = FileStorageHelper::storeFile($this, $value, true, null, $storeAsPrivate);
        }

        // If the attribute is translatable and not already translated, set a
        // translation for the current app locale.
        return $this->setTranslation($key, $this->getLocale(), $value);
    }

    /**
     * Get the value of an attribute using its mutator for array conversion.
     *
     * ! Fix of spatie translatable.
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
     *
     * @phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
     */
    protected function mutateAttributeForArray($key, $value): mixed
    {
        // phpcs:enable
        if ($this->isTranslatableAttribute($key)) {
            return $this->getTranslation($key, $this->getLocale());
        }

        return parent::mutateAttributeForArray($key, $value);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  string|null  $field
     */
    public function resolveRouteBinding(mixed $value, $field = null): ?Model
    {
        /** @var string */
        $field = $field ?: ((request()->route() ?
            request()->route()->bindingFieldFor(Str::singular($this->getTable())) : null) ?? $this->getRouteKeyName()
        );
        if ($this->isTranslatableAttribute($field)) {
            // Perform the query to find the parameter value in the database.
            return ToolboxHelper::queryColumnWithLocales(
                \config('app.fallback_locale'),
                $this->query(),
                $field,
                \strval($value)
            )->firstOrFail();
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
