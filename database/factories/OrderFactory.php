<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
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
        $subtotal = fake()->numberBetween(2000, 100000);

        return [
            'customer_id' => Customer::factory(),
            'order_status_id' => OrderStatus::factory(),
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'shipping_name' => fake()->name(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'payment_method' => fake()->randomElement(['cod', 'bank_transfer']),
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }
}
