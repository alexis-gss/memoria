<?php

namespace App\Models;

use App\Casts\HtmlColor;
use App\Lib\Helpers\ToolboxHelper;
use App\Traits\Models\HasTranslations;
use App\Traits\Models\Sitemap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LaravelActivityLogs\Traits\HasActivityLog;

/**
 * @property integer                         $id           Id.
 * @property string                          $slug         Slug of the name.
 * @property string                          $name         Name.
 * @property \App\Casts\HtmlColor            $color        Color.
 * @property integer                         $order        Order.
 * @property boolean                         $mandatory    Mandatory status.
 * @property boolean                         $published    Published status.
 * @property \Illuminate\Support\Carbon|null $published_at Published date update.
 * @property-read \Illuminate\Support\Carbon $created_at   Created date.
 * @property-read \Illuminate\Support\Carbon $updated_at   Updated date.
 *
 * @method static void booted()                       Perform any actions required after the model boots.
 * @method static void setPublishedDate(self $folder) Set model's published date.
 * @method static void setOrder(self $folder)         Set model's order after the last element of the list.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelActivityLogs\Models\ActivityLog[] $activityLogs
 * Get Activities of the Folder (morph-to-many relationship).
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $games
 * Get Games of the Folder (has-many relationship).
 */
class Folder extends Model
{
    use HasActivityLog;
    use HasFactory;
    use HasTranslations;
    use Sitemap;

    /**
     * The attributes that are fillable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'color',
        'order',
        'mandatory',
        'published',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'color'        => HtmlColor::class,
        'mandatory'    => 'boolean',
        'published'    => 'boolean',
        'published_at' => 'datetime'
    ];

    /**
     * Translatable fields.
     *
     * @var array
     */
    public $translatable = [
        'name',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (self $folder) {
            self::setSlug($folder);
            self::setOrder($folder);
            self::setPublishedDate($folder);
            self::setDefaultTranslation($folder);
        });
        static::created(function () {
            static::updateSitemap();
        });
        static::updating(function (self $folder) {
            self::setSlug($folder);
            self::setPublishedDate($folder);
            self::setDefaultTranslation($folder);
        });
        static::updated(function () {
            static::updateSitemap();
        });
        static::deleted(function () {
            static::updateSitemap();
        });
    }

    // * METHODS

    /**
     * Set model's slug.
     *
     * @param self $folder
     * @return void
     * @throws \Illuminate\Validation\ValidationException If a folder name already exists.
     */
    private static function setSlug(self $folder): void
    {
        $table = (new self())->getTable();
        $slug  = Str::slug($folder->mandatory && $folder->getTranslation('name', config('app.fallback_locale')) ?
            $folder->getTranslation('name', config('app.fallback_locale')) :
            $folder->name);
        ToolboxHelper::assertFieldIsUnique($table, 'name', $folder->name, $folder->getKey());
        ToolboxHelper::assertFieldIsUnique($table, 'slug', $slug, $folder->getKey());
        $folder->slug = $slug;
    }

    /**
     * Set model's default translation.
     *
     * @param self $folder
     *
     * @return void
     */
    private static function setDefaultTranslation(self $folder): void
    {
        if (!$folder->mandatory) {
            if (config('app.locale') !== config('app.fallback_locale')) {
                $folder->setTranslation('name', config('app.fallback_locale'), $folder->name);
            }
            $locales          = config('app.locales');
            $fallbelLocaleKey = array_search(config('app.fallback_locale'), config('app.locales'));
            unset($locales[$fallbelLocaleKey]);
            foreach ($locales as $locale) {
                $folder->forgetTranslation('name', $locale);
            }
        }
    }

    /**
     * Set model's published date.
     *
     * @param self $folder
     *
     * @return void
     */
    private static function setPublishedDate(self $folder): void
    {
        $folder->published_at = ($folder->published) ? now() : null;
    }

    /**
     * Set model's order after the last element of the list.
     *
     * @param self $folder
     * @return void
     */
    private static function setOrder(self $folder): void
    {
        $folder->order = \intval(self::query()->max('order')) + 1;
    }

    // * RELATIONSHIPS

    /**
     * Get Games of the Folder (has-many relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function games(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Game::class);
    }
}
