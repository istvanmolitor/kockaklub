<?php

namespace Database\Factories;

use App\Models\Region;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => StockMovement::TYPE_IN,
            'source_region_id' => null,
            'destination_region_id' => Region::factory(),
            'movement_date' => now(),
            'note' => null,
        ];
    }
}
