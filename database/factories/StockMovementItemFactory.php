<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovementItem>
 */
class StockMovementItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_movement_id' => StockMovement::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 50),
        ];
    }
}
