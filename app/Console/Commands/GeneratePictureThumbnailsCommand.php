<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Lib\Helpers\PictureHelper;
use App\Models\Picture;
use Illuminate\Console\Command;

class GeneratePictureThumbnailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memoria:pictures:thumbnails {--force : Regenerate the thumbnails which already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the grid thumbnails of the already saved pictures.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $done   = 0;
        $failed = 0;
        $force  = (bool) $this->option('force');

        Picture::query()
            ->without('ratings')
            ->with('game:id,slug')
            ->chunkById(100, function ($pictures) use (&$done, &$failed, $force): void {
                foreach ($pictures as $picture) {
                    PictureHelper::createThumbnail($picture->game->slug, $picture->uuid, $force)
                        ? $done++
                        : $failed++;
                }
            });

        $this->info("Done: {$done}, failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
