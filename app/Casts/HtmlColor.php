<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom cast Html Color.
 *
 * @implements CastsAttributes<string|null, string|int|null>
 */
class HtmlColor implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @phpcs:disable
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        // phpcs:enable
        if ($value === null || $value === '') {
            return null;
        }

        return '#' . \str_pad(\dechex(\intval($value)), 6, '0', \STR_PAD_LEFT);
    }

    /**
     * Prepare the given value for storage.
     *
     * @phpcs:disable
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        // phpcs:enable
        if ($value === null || $value === '') {
            return null;
        }

        return \intval(\hexdec(\ltrim(\strval($value), '#')));
    }
}
