<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => 'home',
            'title' => fake()->words(3, true),
            'link' => fake()->optional()->url(),
            'description' => fake()->optional()->paragraph(),
            'position' => fake()->randomElement(['hero', 'home_top', 'home_middle', 'home_bottom']),
            'featured_at' => null,
        ];
    }

    /**
     * Indicate that the banner is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured_at' => now(),
        ]);
    }
}
