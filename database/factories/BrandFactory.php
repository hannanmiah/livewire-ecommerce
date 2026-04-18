<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'featured_at' => null,
        ];
    }

    /**
     * Indicate that the brand is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured_at' => now(),
        ]);
    }
}
