<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivityLog;
use Illuminate\Database\Seeder;

/**
 * Seeds the activity_logs table.
 */
class ActivityLogSeeder extends Seeder
{
    /** @var string Associated table to this seeder. */
    public static string $table = 'activity_logs';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ActivityLog::factory(10)->createQuietly();
    }
}
