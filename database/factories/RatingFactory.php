<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Picture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Rating>
 */
final class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid'       => fake()->uuid(),
            'picture_id' => fake()->randomElement(Picture::pluck('id'))
                ?? Picture::factory()->createQuietly(['published' => true]),
        ];
    }
}
