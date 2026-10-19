<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'country_id' => Country::where('code', 'HU')->value('id'),
            'city' => fake()->city(),
            'zip' => fake()->postcode(),
            'address' => fake()->streetAddress(),
            'is_active' => true,
            'is_main' => false,
            'is_pickup_point' => true,
        ];
    }
}
