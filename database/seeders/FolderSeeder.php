<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Folder;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Folder::factory(12)->make()->each(function (Folder $folderModel, int $key): void {
            if ($folderModel->mandatory) {
                $locales          = config('app.locales');
                $fallbelLocaleKey = array_search(config('app.fallback_locale'), config('app.locales'));
                unset($locales[$fallbelLocaleKey]);
                foreach ($locales as $locale) {
                    $folderModel->setTranslation('name', $locale, fake()->unique()->word());
                }
            }
            $folderModel->order = $key + 1;
            $folderModel->saveQuietly();
        });
    }
}
