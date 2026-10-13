<?php

use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    Site::factory()->create(['is_main' => true, 'is_pickup_point' => false]);
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    Country::create(['name' => 'Magyarország', 'code' => 'HU', 'sort_order' => 1]);

    config([
        'services.paypal.client_id' => 'test-client-id',
        'services.paypal.client_secret' => 'test-client-secret',
        'services.paypal.mode' => 'sandbox',
    ]);
});

function placePayPalOrder(string $token, PaymentMethod $paymentMethod): TestResponse
{
    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $shippingMethod->paymentMethods()->attach($paymentMethod);

    $product = Product::factory()->create(['price' => 5000]);
    $cart = Cart::create(['guest_token' => $token]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    return test()->withCookie('cart_token', $token)->post('/penztar', [
        'name' => 'Teszt Vásárló',
        'email' => $token.'@example.com',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36301111111',
        'billing_same_as_shipping' => 1,
        'shipping_country_id' => Country::first()->id,
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1000',
        'shipping_address' => 'Teszt utca 1.',
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $paymentMethod->id,
    ]);
}

it('redirects to the PayPal approve URL after placing an order with the PayPal payment method', function () {
    Http::fake([
        '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]),
        '*/v2/checkout/orders' => Http::response([
            'id' => 'PAYPAL-ORDER-ID',
            'status' => 'CREATED',
            'links' => [
                ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-ID', 'method' => 'GET'],
            ],
        ]),
    ]);

    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true, 'type' => PaymentMethodType::PayPal]);

    $response = placePayPalOrder('paypal-token', $paymentMethod);

    $order = Order::first();

    expect($order)->not->toBeNull();

    $response->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-ID');

    expect($order->payment_reference)->toBe('PAYPAL-ORDER-ID')
        ->and($order->payment_status)->toBe(PaymentStatus::Pending);
});

it('marks the order as paid after returning from a completed PayPal payment', function () {
    Http::fake([
        '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]),
        '*/v2/checkout/orders/*/capture' => Http::response(['id' => 'PAYPAL-ORDER-ID', 'status' => 'COMPLETED']),
    ]);

    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true, 'type' => PaymentMethodType::PayPal]);
    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);

    $order = Order::factory()->create([
        'site_id' => Site::first()->id,
        'order_status_id' => OrderStatus::default()->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_status' => PaymentStatus::Pending,
        'payment_reference' => 'PAYPAL-ORDER-ID',
    ]);

    $response = $this->get("/fizetes/paypal/{$order->order_number}/success");

    $response->assertRedirect(route('checkout.confirmation', $order));

    $order->refresh();

    expect($order->isPaid())->toBeTrue()
        ->and($order->paid_at)->not->toBeNull();
});

it('does not call PayPal again for an already-paid order on success', function () {
    Http::fake();

    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true, 'type' => PaymentMethodType::PayPal]);
    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);

    $order = Order::factory()->create([
        'site_id' => Site::first()->id,
        'order_status_id' => OrderStatus::default()->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_status' => PaymentStatus::Paid,
        'paid_at' => now(),
        'payment_reference' => 'PAYPAL-ORDER-ID',
    ]);

    $response = $this->get("/fizetes/paypal/{$order->order_number}/success");

    $response->assertRedirect(route('checkout.confirmation', $order));

    Http::assertNothingSent();
});

it('leaves the order pending when the PayPal payment is cancelled', function () {
    Http::fake();

    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true, 'type' => PaymentMethodType::PayPal]);
    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);

    $order = Order::factory()->create([
        'site_id' => Site::first()->id,
        'order_status_id' => OrderStatus::default()->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_status' => PaymentStatus::Pending,
        'payment_reference' => 'PAYPAL-ORDER-ID',
    ]);

    $response = $this->get("/fizetes/paypal/{$order->order_number}/cancel");

    $response->assertRedirect(route('checkout.confirmation', $order));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Pending);

    Http::assertNothingSent();
});

it('does not call PayPal when checking out with cash on delivery', function () {
    Http::fake();

    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true, 'type' => PaymentMethodType::Cod]);

    $response = placePayPalOrder('cod-token', $paymentMethod);

    $order = Order::first();

    $response->assertRedirect(route('checkout.confirmation', $order));

    Http::assertNothingSent();
});
