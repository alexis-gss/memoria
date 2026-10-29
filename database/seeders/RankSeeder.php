<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Rank::factory(1)->make()->each(function (Rank $rankModel, int $key): void {
            $rankModel->rank = $key + 1;
            $rankModel->saveQuietly();
        });
    }
}
