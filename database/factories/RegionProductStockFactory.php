<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Region;
use App\Models\RegionProductStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegionProductStock>
 */
class RegionProductStockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'product_id' => Product::factory(),
            'quantity' => 0,
            'min_stock' => null,
            'max_stock' => null,
        ];
    }
}
