<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(['card', 'bkash', 'bank', 'cash']),
            'amount' => fake()->randomFloat(2, 20, 500),
            'status' => 'pending',
            'transaction_id' => null,
            'paid_at' => null,
        ];
    }
}
