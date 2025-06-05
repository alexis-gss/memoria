<?php

namespace App\Enums\Pages;

use App\Traits\Enums\BaseEnum;

enum StaticPageTypeEnum: int
{
    use BaseEnum;

    case home    = 1;
    case ranking = 2;

    /**
     * Optionnal labels definition.
     *
     * @phpstan-ignore-next-line
     */
    private const LABELS = [
        self::home->name    => 'fo_home_title',
        self::ranking->name => 'fo_ranking_title',
    ];

    /**
     * Optionnal labels definition.
     */
    private const ROUTES = [
        self::home->name    => 'fo.games.index',
        self::ranking->name => 'fo.ranks.index',
    ];

    /**
     * Get Class.
     *
     * @return string
     */
    public function routeName(): string
    {
        return self::ROUTES[$this->name];
    }
}
