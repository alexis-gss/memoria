<?php

declare(strict_types=1);

namespace App\Traits\Models;

use App\Models\Game;
use App\Models\StaticPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Spatie\Sitemap\Sitemap as SpatieSitemap;
use Spatie\Sitemap\Tags\Url;

trait Sitemap
{
    /**
     * Update sitemap after changes.
     */
    public static function updateSitemap(): void
    {
        SpatieSitemap::create()
            ->add(
                StaticPage::all()
                    ->map(fn (StaticPage $staticPage) => $staticPage->toSitemapTag())
            )
            ->add(
                Game::query()
                    ->where('published', true)
                    ->orderBy('slug', 'asc')
                    ->whereHas('folder', function (Builder $query): void {
                        $query->where('published', true);
                    })
                    ->get()
                    ->map(fn (Game $game) => $game->toSitemapTag())
            )
            ->writeToFile(public_path('sitemap.xml'));
    }

    /**
     * Set sitemap tag.
     */
    public function toSitemapTagCustom(
        string $route,
        string $frequency,
        int | float $priority
    ): Url | string | array {
        return Url::create($route)
            ->setLastModificationDate(Date::create($this->updated_at))
            ->setChangeFrequency($frequency)
            ->setPriority($priority);
    }
}
