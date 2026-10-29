<?php

declare(strict_types=1);

namespace App\Traits\Models;

use App\Models\ActivityLog as ActivityLogModel;
use App\Observers\ModelObserver;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * If there is a static function in a trait (named boot[TraitName]),
 * it will be executed as the boot() function would on an Eloquent model.
 * Futhermore, event in the Model observer doesn't erase actual events in the model,
 * but adds them.
 *
 * @method static void registerModelEvent(string $event, string $callback)
 */
trait HasActivityLog
{
    /**
     * Perform any actions required after the model boots.
     *
     * Events are registered without calling Model::observe(), which instantiates
     * the model and is forbidden during booting since Laravel 13.
     */
    public static function bootHasActivityLog(): void
    {
        $events = [
            'retrieved',
            'creating',
            'created',
            'updating',
            'updated',
            'saving',
            'saved',
            'deleting',
            'deleted',
            'trashed',
            'restoring',
            'restored',
            'replicating',
            'forceDeleting',
            'forceDeleted',
        ];

        foreach ($events as $event) {
            if (\method_exists(ModelObserver::class, $event)) {
                static::registerModelEvent($event, ModelObserver::class . '@' . $event);
            }
        }
    }

    // * RELATIONS
    /**
     * Get Activities of the Model (has-many relationship).
     *
     * @return HasMany<\App\Models\ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLogModel::class, 'model_id')->with('user');
    }
}
