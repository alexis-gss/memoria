<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Rank>
 */
final class RankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => fake()->randomElement(Game::pluck('id'))
                ?? Game::factory()->createQuietly(['published' => true]),
            'rank'    => 1,
        ];
    }
}
