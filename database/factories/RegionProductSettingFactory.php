<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Region;
use App\Models\RegionProductSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegionProductSetting>
 */
class RegionProductSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'product_id' => Product::factory(),
            'min_stock' => null,
            'max_stock' => null,
        ];
    }
}
