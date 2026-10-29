<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Folder>
 */
final class FolderFactory extends Factory
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
            'color'        => fake()->unique()->safeHexColor(),
            'mandatory'    => fake()->boolean(20),
            'published'    => $published,
            'published_at' => ($published) ? now() : null,
            'order'        => 1,
        ];
    }
}
