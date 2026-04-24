<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'price' => fake()->randomFloat(2, 10, 500),
            'sale_price' => $this->faker->randomFloat(2, 10, 500),
            'weight' => fake()->randomFloat(2, 0.1, 10),
        ];
    }

    /**
     * Indicate that the variant is on sale (20% off).
     */
    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? fake()->randomFloat(2, 10, 500);

            return [
                'sale_price' => round($price * 0.8, 2),
            ];
        });
    }
}
