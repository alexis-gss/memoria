<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Id.
 * @property int $rank Rank of the game.
 * @property \App\Models\Game $game_id Game associated.
 * @property-read \Illuminate\Support\Carbon $created_at   Created date.
 * @property-read \Illuminate\Support\Carbon $updated_at   Updated date.
 *
 * @method static void booted() Perform any actions required after the model boots.
 * @method static void setRank(self $rank) Set model's rank after the last element of the list.
 *
 * @property-read \Illuminate\Support\Collection|Game[] $games
 * Get Games of the Rank (belongs-to relationship).
 */
class Rank extends Model
{
    use HasFactory;

    /**
     * The attributes that are fillable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'game_id',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::creating(function (self $rank): void {
            self::setRank($rank);
        });
    }

    // * METHODS

    /**
     * Set model's rank after the last element of the list.
     */
    private static function setRank(self $rank): void
    {
        $rank->rank = \intval(self::query()->max('rank')) + 1;
    }

    // * RELATIONSHIPS
    /**
     * Get Games of the Rank (belongs-to relationship).
     *
     * @return BelongsTo<\App\Models\Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
