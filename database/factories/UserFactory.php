<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Users\RoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $published = fake()->boolean(75);

        return [
            'first_name'        => fake()->firstName(),
            'last_name'         => fake()->lastName(),
            'email'             => fake()->unique()->email(),
            'email_verified_at' => now(),
            'picture'           => '',
            'password'          => fake()->password(),
            'remember_token'    => Str::random(10),
            'role'              => \collect(RoleEnum::toArray())->random()->value,
            'published'         => $published,
            'published_at'      => ($published) ? now() : null,
            'order'             => 1,
        ];
    }
}
