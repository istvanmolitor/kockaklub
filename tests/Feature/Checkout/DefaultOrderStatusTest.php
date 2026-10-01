<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Mail;

it('assigns the default order status to a newly created order', function () {
    Mail::fake();

    OrderStatus::factory()->create(['slug' => 'processing', 'is_default' => false]);
    $pending = OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $shippingMethod->paymentMethods()->attach($paymentMethod);

    $product = Product::factory()->create(['price' => 2500]);

    $cart = Cart::create(['guest_token' => 'status-default-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $this->withCookie('cart_token', 'status-default-token')->post('/penztar', [
        'name' => 'Teszt Vásárló',
        'email' => 'statusz@example.com',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36301239999',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Győr',
        'shipping_zip' => '5555',
        'shipping_address' => 'Alap utca 5.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $paymentMethod->id,
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->order_status_id)->toBe($pending->id);
});
