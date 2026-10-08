<?php

namespace Database\Factories;

use App\Models\DeliveryPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryPoint>
 */
class DeliveryPointFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'foxpost',
            'reference_id' => fake()->unique()->numerify('####'),
            'name' => fake()->company(),
            'zip' => fake()->postcode(),
            'city' => fake()->city(),
            'address' => fake()->streetAddress(),
            'lat' => fake()->latitude(),
            'lng' => fake()->longitude(),
            'raw_payload' => [],
        ];
    }
}
