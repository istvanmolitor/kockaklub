<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    $this->shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $this->shippingMethod->paymentMethods()->attach($this->paymentMethod);
});

it('links an order to the logged in customer without creating a duplicate', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'user@example.com']);
    $customer = Customer::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

    $product = Product::factory()->create(['price' => 7500, 'stock' => 5]);

    $this->actingAs($user);
    $this->post("/kosar/hozzaadas/{$product->id}", ['quantity' => 1]);

    $this->post('/penztar', [
        'shipping_name' => $customer->name,
        'shipping_phone' => '+36301112222',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Debrecen',
        'shipping_zip' => '2222',
        'shipping_address' => 'Fő utca 2.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $order = Order::first();

    expect($order->customer_id)->toBe($customer->id)
        ->and(Customer::count())->toBe(1);
});
