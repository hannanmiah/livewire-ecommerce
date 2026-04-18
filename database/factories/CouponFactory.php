<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('COUPON-####??'),
            'type' => 'fixed',
            'value' => fake()->randomFloat(2, 5, 50),
            'min_order_amount' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'expires_at' => null,
        ];
    }

    /**
     * Indicate that the coupon is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the coupon is a percentage-based discount.
     */
    public function percent(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percent',
            'value' => fake()->randomFloat(2, 5, 30),
        ]);
    }
}
