<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Id.
 * @property string $uuid IP of the customer.
 * @property \App\Models\Picture $picture_id Picture associated.
 * @property-read \Illuminate\Support\Carbon $created_at Created date.
 * @property-read \Illuminate\Support\Carbon $updated_at Updated date.
 * @property-read \App\Models\Picture $picture Get the Picture that owns the Rating (belongs-to relationship).
 */
class Rating extends Model
{
    use HasFactory;

    /**
     * The attributes that are fillable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'picture_id',
    ];

    // * RELATIONSHIPS
    /**
     * Get the Picture that owns the Rating (belongs-to relationship).
     *
     * @return BelongsTo<\App\Models\Picture, $this>
     */
    public function picture(): BelongsTo
    {
        return $this->belongsTo(Picture::class);
    }
}
