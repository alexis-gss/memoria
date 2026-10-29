<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActivityLogs\ActivityLogsEventEnum;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Observes Eloquent model events and delegates activity logging to ActivityLog.
 */
class ModelObserver
{
    /**
     * Handle the model "created" event.
     */
    public static function created(Model $model): void
    {
        ActivityLog::addActivity($model, ActivityLogsEventEnum::created);
    }

    /**
     * Handle the model "updated" event.
     */
    public static function updated(Model $model): void
    {
        ActivityLog::addActivity($model, ActivityLogsEventEnum::updated);
    }

    /**
     * Handle the model "duplicated" event.
     */
    public static function duplicated(Model $model): void
    {
        ActivityLog::addActivity($model, ActivityLogsEventEnum::duplicated);
    }

    /**
     * Handle the model "deleted" event.
     */
    public static function deleted(Model $model): void
    {
        ActivityLog::addActivity($model, ActivityLogsEventEnum::deleted);
    }
}
