<?php

declare(strict_types=1);

namespace App\Models;

use App\Lib\Helpers\FileStorageHelper;
use App\Traits\Models\HasActivityLog;
use App\Traits\Models\SchemaOrg;
use App\Traits\Models\Sitemap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Date;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\WebPage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * @property int $id Id.
 * @property string $name Name.
 * @property string $slug Slug of the name.
 * @property string $picture Path of the game's picture.
 * @property string|null $music Path of the game's music.
 * @property \App\Models\Folder $folder_id Folder associated.
 * @property int $igdb_id Id of the game in IGDB.
 * @property int $order Order.
 * @property bool $published Published status.
 * @property \Illuminate\Support\Carbon|null $published_at Published date update.
 * @property-read \Illuminate\Support\Carbon $created_at   Created date.
 * @property-read \Illuminate\Support\Carbon $updated_at   Updated date.
 *
 * @method static void booted() Perform any actions required after the model boots.
 * @method static void setPublishedDate(self $game) Set model's published date.
 * @method static void setOrder(self $game) Set model's order after the last element of the list.
 * @method WebPage toSchemaOrg() Set micro data.
 *
 * @property-read \Illuminate\Support\Collection|\App\Models\ActivityLog[] $activityLogs
 * Get Activities of the Game (morph-to-many relationship).
 * @property-read \App\Models\Folder $folder
 * Get Folder that owns the Game (belongs-to relationship).
 * @property-read \Illuminate\Support\Collection|\App\Models\Tag[] $tags
 * Get Tags of the Game (morph-to-many relationship).
 * @property-read \Illuminate\Support\Collection|\App\Models\Picture[] $pictures
 * Get Pictures of the Game (has-many relationship).
 * @property-read \Illuminate\Support\Collection|\App\Models\Rank[] $rank
 * Get Rank of the Game (belongs-to relationship).
 */
class Game extends Model implements Sitemapable
{
    use HasActivityLog;
    use HasFactory;
    use SchemaOrg;
    use Sitemap;

    /**
     * The attributes that are fillable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'picture',
        'music',
        'folder_id',
        'igdb_id',
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
        'published_at' => 'datetime',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::creating(function (self $game): void {
            self::setOrder($game);
            self::setImage($game);
            self::setMusic($game);
            self::setPublishedDate($game);
            (new Picture())->renameFolderSavedPictures($game, 'default_folder');
        });
        static::created(function (): void {
            static::updateSitemap();
        });
        static::updating(function (self $game): void {
            self::setImage($game);
            self::setMusic($game);
            self::setPublishedDate($game);
            (new Picture())->renameFolderSavedPictures($game, $game->getOriginal('slug'));
        });
        static::updated(function (self $game): void {
            static::updateSitemap();
            FileStorageHelper::removeOldFile($game, 'picture');
            FileStorageHelper::removeOldFile($game, 'music');
        });
        static::deleting(function (self $game): void {
            (new Tag())->removeTags($game);
            (new Picture())->removePictures($game->pictures);
        });
        static::deleted(function (self $game): void {
            static::updateSitemap();
            FileStorageHelper::removeOldFile($game, 'picture');
            FileStorageHelper::removeOldFile($game, 'music');
        });
    }

    // * METHODS

    /**
     * Set model's published date.
     */
    private static function setPublishedDate(self $game): void
    {
        if ($game->published && ! $game->getOriginal('published')) {
            $game->published_at = Date::now();
        } elseif (! $game->published) {
            $game->published_at = null;
        }
    }

    /**
     * Set model's game's picture.
     */
    private static function setImage(self $game): void
    {
        $game->picture = FileStorageHelper::storeFile($game, $game->picture, true);
    }

    /**
     * Set model's game's music.
     */
    private static function setMusic(self $game): void
    {
        $game->music = is_null($game->music) ? null : FileStorageHelper::storeFile($game, $game->music, true);
    }

    /**
     * Set model's order after the last element of the list.
     */
    private static function setOrder(self $game): void
    {
        $game->order = \intval(self::query()->max('order')) + 1;
    }

    /**
     * Set sitemap tag.
     */
    public function toSitemapTag(): Url | string | array
    {
        return $this->toSitemapTagCustom(route('fo.games.show', $this->slug), Url::CHANGE_FREQUENCY_YEARLY, 0.9);
    }

    /**
     * Set micro data.
     */
    public function toSchemaOrg(): WebPage
    {
        return Schema::webPage()
            ->name($this->name)
            ->description(trans('fo_game_description', ['game' => $this->name]))
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
                    ->genre('Game image gallery')
                    ->headline($this->name)
                    ->isPartOf(
                        Schema::creativeWorkSeries()->name($this->folder->name)
                    )
                    ->relatedLink(route('fo.games.show', $this))
                    ->primaryImageOfPage(
                        Schema::imageObject()
                            ->url(asset($this->picture))
                            ->headline('Main picture from the game ' . $this->name)
                    )
                    ->mainEntity(Schema::thing()->name($this->name))
                    ->about(Schema::thing()->name('Pictures from the game ' . $this->name))
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
     * @return BelongsTo<\App\Models\Folder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get Tags of the Game (morph-to-many relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Tag, $this>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Get Pictures of the Game (has-many relationship).
     *
     * @return HasMany<\App\Models\Picture, $this>
     */
    public function pictures(): HasMany
    {
        return $this->hasMany(Picture::class);
    }

    /**
     * Get Rank of the Game (belongs-to relationship).
     */
    public function rank(): ?HasOne
    {
        return $this->hasOne(Rank::class);
    }

    /**
     * Get Visits of the Game (has-many relationship).
     *
     * @return HasMany<\App\Models\Visit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
