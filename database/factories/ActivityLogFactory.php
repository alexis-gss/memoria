<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ActivityLogs\ActivityLogsEventEnum;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating ActivityLog model instances with randomized data.
 *
 * @extends Factory<ActivityLog>
 */
final class ActivityLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ActivityLog>
     */
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $boolean = random_int(0, 1);

        return [
            'user_id'      => null,
            'is_anonymous' => $boolean,
            'is_console'   => ! $boolean,
            'model_class'  => User::class,
            'model_id'     => 1,
            'event'        => \collect(ActivityLogsEventEnum::toArray())->random()->value,
            'data'         => [],
            'created_at'   => now(),
        ];
    }
}
