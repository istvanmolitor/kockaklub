<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPriceLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPriceLog>
 */
class ProductPriceLogFactory extends Factory
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
            'price' => $this->faker->numberBetween(1000, 50000),
            'previous_price' => null,
            'user_id' => null,
        ];
    }
}
