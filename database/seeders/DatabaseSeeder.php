<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ActivityLogSeeder::class,
            TagSeeder::class,
            FolderSeeder::class,
            GameSeeder::class,
            PictureSeeder::class,
            RankSeeder::class,
            RatingSeeder::class,
            UserSeeder::class,
            StaticPageSeeder::class,
        ]);
    }
}
