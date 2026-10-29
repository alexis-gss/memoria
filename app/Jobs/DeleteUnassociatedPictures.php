<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Game;
use App\Models\Picture;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class DeleteUnassociatedPictures implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * All directories of pictures from storage.
     *
     * @var array<string>
     */
    private $storageDirectories = [];

    /**
     * All pictures saved from storage.
     *
     * @var array<string>
     */
    private $storagePictures = [];

    /**
     * All games slug saved from database.
     *
     * @var array<string>
     */
    private $gamesSlug = [];

    /**
     * All pictures saved from database.
     *
     * @var \Illuminate\Support\Collection
     */
    private $pictures;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->storageDirectories = Storage::disk('public')->allDirectories('pictures');
        $this->storagePictures    = Storage::disk('public')->allFiles('pictures');
        $this->gamesSlug          = Game::query()->get()->pluck('slug')->toArray();
        $this->pictures           = Picture::query()->get();
        $this->handle();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->deleteChunks();
        $this->deleteUnassociatedDirectories();
        $this->deleteUnassociatedPictures();
    }

    /**
     * Delete all failed/uncompleted chunks.
     */
    public function deleteChunks(): void
    {
        Storage::deleteDirectory('chunks');
    }

    /**
     * Delete all directories which have not equivalent to a game slug in the database.
     */
    public function deleteUnassociatedDirectories(): void
    {
        foreach ($this->storageDirectories as $directory) {
            $directoryExplode = explode('/', $directory);
            if (! in_array($directoryExplode[count($directoryExplode) - 1], $this->gamesSlug)) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        }
    }

    /**
     * Delete all pictures which have not equivalent to a picture uuid in the database.
     */
    public function deleteUnassociatedPictures(): void
    {
        foreach ($this->storagePictures as $picture) {
            if ($this->pictures->where('uuid', explode('.', explode('/', $picture)[2])[0])->isEmpty()) {
                Storage::disk('public')->delete($picture);
            }
        }
    }
}
