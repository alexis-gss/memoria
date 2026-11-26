<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Lib\Helpers\PictureHelper;
use App\Models\Picture;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GeneratePictureThumbnailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memoria:pictures:thumbnails
        {--force : Regenerate the thumbnails which already exist}
        {--folder= : Only process the pictures of the game with this slug}
        {--img= : Only process the picture with this uuid}';

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
        $force  = (bool) $this->option('force');
        $folder = $this->stringOption('folder');
        $img    = $this->stringOption('img');

        $query = Picture::query()
            ->without('ratings')
            ->with('game:id,slug')
            ->when($folder !== null, fn (Builder $q) => $q->whereHas(
                'game',
                fn (Builder $game) => $game->where('slug', $folder)
            ))
            ->when($img !== null, fn (Builder $q) => $q->where('uuid', $img));

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->warn('No picture matches the given criteria.');

            return ($folder !== null || $img !== null) ? self::FAILURE : self::SUCCESS;
        }

        $this->info(sprintf(
            'Processing %d picture(s)%s%s%s',
            $total,
            $folder !== null ? " [folder: {$folder}]" : '',
            $img    !== null ? " [img: {$img}]" : '',
            $force ? ' [force]' : ''
        ));

        $done   = 0;
        $failed = [];
        $bar    = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById(100, function (Collection $pictures) use (&$done, &$failed, $force, $bar): void {
            foreach ($pictures as $picture) {
                $slug = $picture->game->slug;

                try {
                    $success = PictureHelper::createThumbnail($slug, $picture->uuid, $force);
                } catch (\Throwable $e) {
                    $success = false;
                    report($e);
                }

                $success ? $done++ : $failed[] = "{$slug}/{$picture->uuid}";
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info('Done: ' . $done . ', Failed: ' . count($failed));

        if ($failed !== []) {
            $this->error('Failed pictures:');
            $this->line(' - ' . implode(PHP_EOL . ' - ', $failed));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Get a trimmed string option, or null when empty/missing.
     */
    private function stringOption(string $name): ?string
    {
        $value = $this->option($name);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
