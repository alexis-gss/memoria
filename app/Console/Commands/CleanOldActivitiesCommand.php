<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\CleanOldActivities;
use Illuminate\Console\Command;

/**
 * Run the CleanOldActivities job right now.
 */
class CleanOldActivitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memoria:activities:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the activities stored for more than 3 months (synchronously).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        dispatch_sync(new CleanOldActivities());

        $this->info('Old activities cleaned.');

        return self::SUCCESS;
    }
}
