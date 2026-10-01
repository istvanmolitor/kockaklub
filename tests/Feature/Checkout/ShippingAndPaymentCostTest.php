<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\Site;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Site::factory()->create(['is_main' => true]);
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);
});

it('adds the shipping and payment method costs to the order total', function () {
    Mail::fake();

    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true, 'cost' => 1490]);
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true, 'cost' => 390]);
    $shippingMethod->paymentMethods()->attach($paymentMethod);

    $product = Product::factory()->create(['price' => 5000]);

    $cart = Cart::create(['guest_token' => 'cost-checkout-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

    $this->withCookie('cart_token', 'cost-checkout-token')->post('/penztar', [
        'name' => 'Vendég Vásárló',
        'email' => 'koltseg@example.com',
        'shipping_name' => 'Vendég Vásárló',
        'shipping_phone' => '+36301234567',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1111',
        'shipping_address' => 'Teszt utca 1.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $paymentMethod->id,
    ]);

    $order = Order::first();

    expect($order->subtotal)->toBe(10000)
        ->and($order->shipping_cost)->toBe(1490)
        ->and($order->payment_cost)->toBe(390)
        ->and($order->total)->toBe(11880);
});
