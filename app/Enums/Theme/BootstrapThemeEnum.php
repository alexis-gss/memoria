<?php

declare(strict_types=1);

namespace App\Enums\Theme;

use App\Traits\Enums\BaseEnum;

enum BootstrapThemeEnum: int
{
    use BaseEnum;

    case light = 1;
    case dark  = 2;
    case beige = 3;

    /**
     * Optionnal labels definition.
     *
     * @phpstan-ignore-next-line
     */
    private const LABELS = [
        self::light->name => 'enums.bootstrap-theme.light',
        self::dark->name  => 'enums.bootstrap-theme.dark',
        self::beige->name => 'enums.bootstrap-theme.beige',
    ];
}
