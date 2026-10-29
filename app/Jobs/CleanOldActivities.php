<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;

/**
 * Permanently deletes entries that have been stored for more than 3 months.
 */
class CleanOldActivities implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Delete entries if it has been stored for more than 3 months.
     */
    public function handle(): void
    {
        $dateLimit = Date::now()->subMonths(3);

        ActivityLog::query()
            ->where('created_at', '<', $dateLimit)
            ->cursor()
            ->each(fn ($model) => $model->delete());
    }
}
