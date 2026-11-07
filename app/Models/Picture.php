<?php

declare(strict_types=1);

namespace App\Models;

use App\Lib\Helpers\PictureHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id Id.
 * @property \App\Models\Game $game_id Game associated.
 * @property string $uuid Uuid.
 * @property string $label Label.
 * @property bool $published Published status.
 * @property-read \Illuminate\Support\Carbon $created_at   Created date.
 * @property-read \Illuminate\Support\Carbon $updated_at   Updated date.
 *
 * @method static void booted() Perform any actions required after the
 *                              model boots.
 * @method static void updatePictures(Game $game, array $request) Differentiate between old and new
 *                                                                pictures, remove oldests and save news.
 * @method static void removePicture(self $model) Delete one specific picture.
 * @method static void removePictures(Collection $pictures) Remove all pictures previously associated.
 *
 * @property-read \App\Models\Game $game
 * Get the Game that owns the Picture (belongs-to relationship).
 * @property-read \Illuminate\Database\Eloquent\Collection|Rating[] $ratings
 * Get Ratings of the Picture (has-many relationship).
 */
class Picture extends Model
{
    use HasFactory;

    /**
     * The relationships that should always be loaded.
     *
     * @var list<string>
     */
    protected $with = ['ratings'];

    /**
     * The attributes that are fillable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'game_id',
        'uuid',
        'label',
        'published',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $picture): void {
            self::removePicture($picture);
        });
    }

    // * METHODS
    /**
     * Differentiate between old and new pictures,
     * remove oldests and save news.
     */
    public static function updatePictures(Game $game, array $request): void
    {
        $picturesAlreadySaved = $game->pictures;
        $newPictures          = collect();
        if (isset($request['uuid'])) {
            foreach ($request['uuid'] as $key => $value) {
                $getPicture = Picture::where('game_id', $game->getKey())
                    ->where('uuid', $request['uuid'][$key])
                    ->first();
                if (is_null($getPicture)) {
                    $picture            = new Picture();
                    $picture->game_id   = $game->getKey();
                    $picture->uuid      = $request['uuid'][$key];
                    $picture->published = true;
                } else {
                    $picture = $getPicture;
                }
                $picture->label = pathinfo($request['label'][$key])['filename'];
                $picture->saveOrFail();
                $newPictures->add($picture);
            } // end foreach
        } // end if
        (new Picture())->removePictures($picturesAlreadySaved->diff($newPictures));
    }

    /**
     * Delete one specific picture.
     */
    private static function removePicture(self $picture): void
    {
        $picture->loadMissing('game');
        PictureHelper::delete($picture->game->slug, $picture->uuid);
    }

    /**
     * Remove all pictures previously associated.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, self>  $pictures
     */
    public static function removePictures(Collection $pictures): void
    {
        $pictures->each(function (self $picture): void {
            self::removePicture($picture);
        });
    }

    // * RELATIONSHIPS
    /**
     * Get the Game that owns the Picture (belongs-to relationship).
     *
     * @return BelongsTo<\App\Models\Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get Ratings of the Picture (has-many relationship).
     *
     * @return HasMany<\App\Models\Rating, $this>
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
