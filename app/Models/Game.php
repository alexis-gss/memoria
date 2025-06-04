<?php

namespace App\Models;

use App\Lib\Helpers\FileStorageHelper;
use App\Traits\Models\SchemaOrg;
use App\Traits\Models\Sitemap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use LaravelActivityLogs\Traits\ActivityLog;
use Spatie\SchemaOrg\Schema;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * @property integer                         $id           Id.
 * @property string                          $name         Name.
 * @property string                          $slug         Slug of the name.
 * @property string                          $picture      Path of the game's picture.
 * @property \App\Models\Folder              $folder_id    Folder associated.
 * @property integer                         $akora_id     Id of the game in akora.
 * @property integer                         $order        Order.
 * @property boolean                         $published    Published status.
 * @property \Illuminate\Support\Carbon|null $published_at Published date update.
 * @property-read \Illuminate\Support\Carbon $created_at   Created date.
 * @property-read \Illuminate\Support\Carbon $updated_at   Updated date.
 *
 * @method static void booted()                     Perform any actions required after the model boots.
 * @method static void setPublishedDate(self $game) Set model's published date.
 * @method static void setOrder(self $game)         Set model's order after the last element of the list.
 * @method \Spatie\SchemaOrg\WebPage toSchemaOrg()  Set micro data.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelActivityLogs\Models\ActivityLog[] $activityLogs
 * Get Activities of the Game (morph-to-many relationship).
 * @property-read \App\Models\Folder $folder
 * Get Folder that owns the Game (belongs-to relationship).
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * Get Tags of the Game (morph-to-many relationship).
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Picture[] $pictures
 * Get Pictures of the Game (has-many relationship).
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Rank[] $rank
 * Get Rank of the Game (belongs-to relationship).
 */
class Game extends Model implements Sitemapable
{
    use ActivityLog;
    use HasFactory;
    use SchemaOrg;
    use Sitemap;

    /**
     * The attributes that are fillable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'picture',
        'folder_id',
        'akora_id',
        'order',
        'published',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published'    => 'boolean',
        'published_at' => 'datetime'
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (self $game) {
            self::setOrder($game);
            self::setImage($game);
            self::setPublishedDate($game);
            (new Picture())->renameFolderSavedPictures($game, "default_folder");
        });
        static::created(function () {
            static::updateSitemap();
        });
        static::updating(function (self $game) {
            self::setImage($game);
            self::setPublishedDate($game);
            (new Picture())->renameFolderSavedPictures($game, $game->getOriginal('slug'));
        });
        static::updated(function (self $game) {
            static::updateSitemap();
            FileStorageHelper::removeOldFile($game, 'picture');
        });
        static::deleting(function (self $game) {
            (new Tag())->removeTags($game);
            (new Picture())->removePictures($game->pictures);
        });
        static::deleted(function (self $game) {
            static::updateSitemap();
            FileStorageHelper::removeOldFile($game, 'picture');
        });
    }

    // * METHODS

    /**
     * Set model's published date.
     *
     * @param self $game
     * @return void
     */
    private static function setPublishedDate(self $game): void
    {
        if ($game->published && !$game->getOriginal('published')) {
            $game->published_at = Carbon::now();
        } elseif (!$game->published) {
            $game->published_at = null;
        }
    }

    /**
     * Set model's game's picture.
     *
     * @param self $game
     * @return void
     */
    private static function setImage(self $game): void
    {
        $game->picture = FileStorageHelper::storeFile($game, $game->picture, true);
    }

    /**
     * Set model's order after the last element of the list.
     *
     * @param self $game
     * @return void
     */
    private static function setOrder(self $game): void
    {
        $game->order = \intval(self::query()->max('order')) + 1;
    }

    /**
     * Set sitemap tag.
     *
     * @return \Spatie\Sitemap\Tags\Url|string|array
     */
    public function toSitemapTag(): \Spatie\Sitemap\Tags\Url|string|array
    {
        return $this->toSitemapTagCustom(route('fo.games.show', $this->slug), Url::CHANGE_FREQUENCY_YEARLY, 0.9);
    }

    /**
     * Set micro data.
     *
     * @return \Spatie\SchemaOrg\WebPage
     */
    public function toSchemaOrg(): \Spatie\SchemaOrg\WebPage
    {
        return Schema::webPage()
            ->name($this->name)
            ->description(trans('fo_description', ['game' => $this->name]))
            ->headline($this->name)
            ->inLanguage(config('app.locale'))
            ->keywords(['page'])
            ->dateCreated($this->created_at)
            ->dateModified($this->updated_at)
            ->isAccessibleForFree(true)
            ->relatedLink(route('fo.games.show', $this))
            ->mainEntityOfPage(route('fo.games.show', $this))
            ->publisher($this->getPersonSchema())
            ->reviewedBy($this->getPersonSchema())
            ->creator($this->getPersonSchema())
            ->author($this->getPersonSchema())
            ->isPartOf($this->getWebsiteSchema())
            ->mainEntity(
                Schema::imageGallery()
                    ->inLanguage(config('app.locale'))
                    ->datePublished($this->published_at)
                    ->isAccessibleForFree(true)
                    ->genre("Game image gallery")
                    ->headline($this->name)
                    ->isPartOf(
                        Schema::creativeWorkSeries()->name($this->folder->name)
                    )
                    ->relatedLink(route('fo.games.show', $this))
                    ->significantLink(sprintf('%s/%s', config('app.akora_url'), $this->akora_id))
                    ->primaryImageOfPage(
                        Schema::imageObject()
                            ->url(asset($this->picture))
                            ->headline("Main picture from the game " . $this->name)
                    )
                    ->mainEntity(Schema::thing()->name($this->name))
                    ->about(Schema::thing()->name("Pictures from the game " . $this->name))
                    ->reviewedBy($this->getPersonSchema())
                    ->editor($this->getPersonSchema())
                    ->publisher($this->getPersonSchema())
                    ->creator($this->getPersonSchema())
                    ->author($this->getPersonSchema())
            );
    }

    // * RELATIONSHIPS

    /**
     * Get Folder that owns the Game (belongs-to relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function folder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get Tags of the Game (morph-to-many relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Get Pictures of the Game (has-many relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pictures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Picture::class);
    }

    /**
     * Get Rank of the Game (belongs-to relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|null
     */
    public function rank(): \Illuminate\Database\Eloquent\Relations\HasOne|null
    {
        return $this->hasOne(Rank::class);
    }

    /**
     * Get Visits of the Game (has-many relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
