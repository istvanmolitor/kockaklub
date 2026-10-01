<?php

use App\Mail\OrderConfirmationMail;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);
});

it('allows a guest to check out and creates an order with a linked guest customer', function () {
    Mail::fake();

    $product = Product::factory()->create(['price' => 5000, 'stock' => 10]);

    $cart = Cart::create(['guest_token' => 'guest-checkout-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

    $response = $this->withCookie('cart_token', 'guest-checkout-token')->post('/penztar', [
        'name' => 'Vendég Vásárló',
        'email' => 'vendeg@example.com',
        'shipping_name' => 'Vendég Vásárló',
        'shipping_phone' => '+36301234567',
        'shipping_address' => '1111 Budapest, Teszt utca 1.',
        'payment_method' => 'cod',
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->total)->toBe(10000)
        ->and($order->items)->toHaveCount(1);

    $response->assertRedirect(route('checkout.confirmation', $order));

    $customer = Customer::where('email', 'vendeg@example.com')->first();

    expect($customer)->not->toBeNull()
        ->and($customer->user_id)->toBeNull()
        ->and($order->customer_id)->toBe($customer->id);

    expect($product->fresh()->stock)->toBe(8);

    Mail::assertQueued(OrderConfirmationMail::class);
});
