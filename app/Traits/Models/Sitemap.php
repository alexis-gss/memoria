<?php

namespace App\Traits\Models;

use App\Models\Game;
use App\Models\StaticPage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Sitemap as SpatieSitemap;

trait Sitemap
{
    /**
     * Update sitemap after changes.
     *
     * @return void
     */
    public static function updateSitemap(): void
    {
        SpatieSitemap::create()
            ->add(
                StaticPage::all()
                    ->map(fn(StaticPage $staticPage) => $staticPage->toSitemapTag())
            )
            ->add(
                Game::query()
                    ->where('published', true)
                    ->orderBy('slug', 'ASC')
                    ->whereHas('folder', function (Builder $query) {
                        $query->where('published', true);
                    })
                    ->get()
                    ->map(fn(Game $game) => $game->toSitemapTag())
            )
            ->writeToFile(public_path('sitemap.xml'));
    }

    /**
     * Set sitemap tag.
     *
     * @param string        $route
     * @param string        $frequency
     * @param integer|float $priority
     * @return \Spatie\Sitemap\Tags\Url|string|array
     */
    public function toSitemapTagCustom(
        string $route,
        string $frequency,
        int|float $priority
    ): \Spatie\Sitemap\Tags\Url|string|array {
        return Url::create($route)
            ->setLastModificationDate(Carbon::create($this->updated_at))
            ->setChangeFrequency($frequency)
            ->setPriority($priority);
    }
}
