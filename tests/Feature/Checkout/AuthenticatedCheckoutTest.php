<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);
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
        'shipping_address' => '2222 Debrecen, Fő utca 2.',
        'payment_method' => 'bank_transfer',
    ]);

    $order = Order::first();

    expect($order->customer_id)->toBe($customer->id)
        ->and(Customer::count())->toBe(1);
});
