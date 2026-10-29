<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Tag>
 */
final class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name      = fake()->unique()->word();
        $published = fake()->boolean(75);

        return [
            'name'         => $name,
            'slug'         => Str::of($name)->slug(),
            'published'    => $published,
            'published_at' => ($published) ? now() : null,
            'order'        => 1,
        ];
    }
}
