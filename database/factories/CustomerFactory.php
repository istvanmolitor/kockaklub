<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'shipping_name' => fake()->name(),
            'shipping_country' => 'Magyarország',
            'shipping_city' => fake()->city(),
            'shipping_zip' => fake()->postcode(),
            'shipping_address' => fake()->streetAddress(),
            'billing_name' => fake()->name(),
            'billing_country' => 'Magyarország',
            'billing_city' => fake()->city(),
            'billing_zip' => fake()->postcode(),
            'billing_address' => fake()->streetAddress(),
        ];
    }
}
