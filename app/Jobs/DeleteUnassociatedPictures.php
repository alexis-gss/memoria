<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Lib\Helpers\PictureHelper;
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
     * Execute the job.
     */
    public function handle(): void
    {
        // Delete all failed/uncompleted chunks.
        Storage::deleteDirectory('chunks');

        // Every game slug is expected, even without picture, with the uuids of its pictures.
        $expected = Game::query()
            ->pluck('slug')
            ->mapWithKeys(fn (string $slug): array => [$slug => []])
            ->all();

        Picture::query()
            ->without('ratings')
            ->with('game:id,slug')
            ->get()
            ->each(function (Picture $picture) use (&$expected): void {
                $expected[$picture->game->slug][] = $picture->uuid;
            });

        PictureHelper::purgeOrphans($expected);
    }
}
