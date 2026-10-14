<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Region;
use App\Models\RegionProductTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegionProductTransfer>
 */
class RegionProductTransferFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_region_id' => Region::factory(),
            'destination_region_id' => Region::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'created_by' => null,
        ];
    }
}
