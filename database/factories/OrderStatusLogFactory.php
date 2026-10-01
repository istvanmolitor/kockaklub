<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusLog>
 */
class OrderStatusLogFactory extends Factory
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
            'order_status_id' => OrderStatus::factory(),
            'previous_order_status_id' => null,
            'user_id' => null,
        ];
    }
}
