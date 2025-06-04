<?php

namespace App\Models;

use App\Enums\Pages\StaticPageTypeEnum;
use App\Traits\Models\HasTranslations;
use App\Traits\Models\SchemaOrg;
use App\Traits\Models\Sitemap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelActivityLogs\Traits\ActivityLog;
use Spatie\SchemaOrg\Schema;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * @property \App\Enums\Pages\StaticPageTypeEnum $type            Page type.
 * @property string                              $seo_title       Page seo title.
 * @property string                              $seo_description Page seo description.
 * @property string                              $title           Page title.
 * @property integer                             $order           Page order.
 * @property-read \Illuminate\Support\Carbon     $created_at      Created date.
 * @property-read \Illuminate\Support\Carbon     $updated_at      Updated date.
 *
 * @method static void booted()                    Perform any actions required after the model boots.
 * @method \Spatie\SchemaOrg\WebPage toSchemaOrg() Set micro data.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelActivityLogs\Models\ActivityLog[] $activityLogs
 * Get Activities of the Static page (morph-to-many relationship).
 */
class StaticPage extends Model implements Sitemapable
{
    use ActivityLog;
    use HasFactory;
    use HasTranslations;
    use SchemaOrg;
    use Sitemap;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'seo_title',
        'seo_description',
        'title',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => StaticPageTypeEnum::class,
    ];

    /**
     * Translatable fields.
     *
     * @var array
     */
    public $translatable = [
        'seo_title',
        'seo_description',
        'title',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function () {
            throw new \RuntimeException(trans('crud.messages.cannot_event_on_model', [
                'event' => trans('crud.actions.create'),
                'model' => trans_choice('models.static_page', 1)
            ]));
        });
        static::updated(function () {
            static::updateSitemap();
        });
        static::deleting(function () {
            throw new \RuntimeException(trans('crud.messages.cannot_event_on_model', [
                'event' => trans('crud.actions.delete'),
                'model' => trans_choice('models.static_page', 1)
            ]));
        });
    }

    /**
     * Set sitemap tag.
     *
     * @return \Spatie\Sitemap\Tags\Url|string|array
     */
    public function toSitemapTag(): \Spatie\Sitemap\Tags\Url|string|array
    {
        return $this->toSitemapTagCustom(route($this->type->routeName()), Url::CHANGE_FREQUENCY_MONTHLY, 1);
    }

    /**
     * Set micro data.
     *
     * @return \Spatie\SchemaOrg\WebPage
     */
    public function toSchemaOrg(): \Spatie\SchemaOrg\WebPage
    {
        return Schema::webPage()
            ->name($this->seo_title)
            ->description($this->seo_description)
            ->headline($this->title)
            ->inLanguage(config('app.locale'))
            ->keywords(['page'])
            ->dateCreated($this->created_at)
            ->dateModified($this->updated_at)
            ->isAccessibleForFree(true)
            ->relatedLink(route($this->type->routeName()))
            ->mainEntityOfPage(route($this->type->routeName()))
            ->publisher($this->getPersonSchema())
            ->reviewedBy($this->getPersonSchema())
            ->author($this->getPersonSchema())
            ->creator($this->getPersonSchema())
            ->isPartOf($this->getWebsiteSchema());
    }
}
