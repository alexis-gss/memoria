<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\DeleteUnassociatedPictures;
use Illuminate\Console\Command;

/**
 * Run the DeleteUnassociatedPictures job right now.
 */
class DeleteUnassociatedPicturesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memoria:pictures:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the pictures files and folders not associated to a game (synchronously).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        dispatch_sync(new DeleteUnassociatedPictures());

        $this->info('Unassociated pictures deleted.');

        return self::SUCCESS;
    }
}
