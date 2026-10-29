<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Pages\StaticPageTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StaticPage>
 */
final class StaticPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seo_title'       => fake()->sentence(),
            'seo_description' => fake()->sentence(),
            'title'           => fake()->words(fake()->numberBetween(1, 2), true),
            'type'            => \collect(StaticPageTypeEnum::toArray())->random()->value,
            'order'           => 1,
        ];
    }
}
