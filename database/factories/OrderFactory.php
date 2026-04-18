<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 500);

        return [
            'user_id' => User::factory(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'shipping_status' => 'pending',
            'subtotal' => $subtotal,
            'shipping_fee' => 0,
            'discount' => 0,
            'total' => $subtotal,
        ];
    }
}
