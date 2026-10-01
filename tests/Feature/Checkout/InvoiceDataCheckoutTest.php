<?php

use App\Models\Cart;
use App\Models\Customer;
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

    $this->shippingMethod = ShippingMethod::factory()->create(['is_active' => true, 'cost' => 0]);
    $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true, 'cost' => 0]);
    $this->shippingMethod->paymentMethods()->attach($this->paymentMethod);
});

it('snapshots the product vat rate on the order item and stores the billing tax number', function () {
    Mail::fake();

    $product = Product::factory()->create(['price' => 5000, 'vat_rate' => 5]);

    $cart = Cart::create(['guest_token' => 'invoice-checkout-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $this->withCookie('cart_token', 'invoice-checkout-token')->post('/penztar', [
        'name' => 'Cég Kft.',
        'email' => 'ceges@example.com',
        'shipping_name' => 'Cég Kft.',
        'shipping_phone' => '+36301234567',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1111',
        'shipping_address' => 'Teszt utca 1.',
        'billing_same_as_shipping' => 1,
        'billing_tax_number' => '12345678-1-42',
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->billing_tax_number)->toBe('12345678-1-42')
        ->and($order->items->first()->vat_rate)->toBe(5);

    $customer = Customer::where('email', 'ceges@example.com')->first();

    expect($customer->billing_tax_number)->toBe('12345678-1-42');
});

it('allows checkout without a billing tax number', function () {
    Mail::fake();

    $product = Product::factory()->create(['price' => 5000, 'vat_rate' => 27]);

    $cart = Cart::create(['guest_token' => 'invoice-checkout-token-2']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->withCookie('cart_token', 'invoice-checkout-token-2')->post('/penztar', [
        'name' => 'Magán Vásárló',
        'email' => 'magan@example.com',
        'shipping_name' => 'Magán Vásárló',
        'shipping_phone' => '+36301234567',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1111',
        'shipping_address' => 'Teszt utca 1.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $order = Order::first();

    $response->assertRedirect(route('checkout.confirmation', $order));

    expect($order->billing_tax_number)->toBeNull()
        ->and($order->items->first()->vat_rate)->toBe(27);
});
