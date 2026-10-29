<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::factory(12)->make()->each(function (Tag $tagModel, int $key): void {
            $tagModel->order = $key + 1;
            $tagModel->saveQuietly();
        });
    }
}
