<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2000, 100000);
        $shippingCost = fake()->numberBetween(0, 3000);
        $paymentCost = fake()->numberBetween(0, 1500);

        return [
            'site_id' => Site::factory(),
            'customer_id' => Customer::factory(),
            'order_status_id' => OrderStatus::factory(),
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'shipping_name' => fake()->name(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_country_id' => Country::where('code', 'HU')->value('id'),
            'shipping_city' => fake()->city(),
            'shipping_zip' => fake()->postcode(),
            'shipping_address' => fake()->streetAddress(),
            'billing_name' => fake()->name(),
            'billing_country_id' => Country::where('code', 'HU')->value('id'),
            'billing_city' => fake()->city(),
            'billing_zip' => fake()->postcode(),
            'billing_address' => fake()->streetAddress(),
            'shipping_method_id' => ShippingMethod::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'payment_cost' => $paymentCost,
            'total' => $subtotal + $shippingCost + $paymentCost,
        ];
    }
}
