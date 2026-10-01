<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;

beforeEach(function () {
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);
});

it('does not show an inactive shipping method on the checkout page', function () {
    $activeShippingMethod = ShippingMethod::factory()->create(['is_active' => true, 'name' => 'Házhozszállítás']);
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $activeShippingMethod->paymentMethods()->attach($paymentMethod);

    $inactiveShippingMethod = ShippingMethod::factory()->create(['is_active' => false, 'name' => 'Inaktív szállítás']);
    $inactiveShippingMethod->paymentMethods()->attach($paymentMethod);

    $product = Product::factory()->create(['price' => 2000, 'stock' => 5]);
    $cart = Cart::create(['guest_token' => 'inactive-methods-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->withCookie('cart_token', 'inactive-methods-token')->get('/penztar');

    $response->assertSee('Házhozszállítás');
    $response->assertDontSee('Inaktív szállítás');
});

it('rejects an order placed with an inactive payment method', function () {
    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $inactivePaymentMethod = PaymentMethod::factory()->create(['is_active' => false]);
    $shippingMethod->paymentMethods()->attach($inactivePaymentMethod);

    $product = Product::factory()->create(['price' => 2000, 'stock' => 5]);
    $cart = Cart::create(['guest_token' => 'inactive-payment-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->withCookie('cart_token', 'inactive-payment-token')->post('/penztar', [
        'name' => 'Teszt Vásárló',
        'email' => 'inaktiv@example.com',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36301111111',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1000',
        'shipping_address' => 'Teszt utca 1.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $inactivePaymentMethod->id,
    ]);

    $response->assertSessionHasErrors('payment_method_id');
    expect(Order::count())->toBe(0);
});

it('rejects a payment method that is not linked to the selected shipping method', function () {
    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $unrelatedPaymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

    $product = Product::factory()->create(['price' => 2000, 'stock' => 5]);
    $cart = Cart::create(['guest_token' => 'mismatched-methods-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->withCookie('cart_token', 'mismatched-methods-token')->post('/penztar', [
        'name' => 'Teszt Vásárló',
        'email' => 'mismatch@example.com',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36302222222',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1000',
        'shipping_address' => 'Teszt utca 2.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $unrelatedPaymentMethod->id,
    ]);

    $response->assertSessionHasErrors('payment_method_id');
    expect(Order::count())->toBe(0);
});
