<?php

use App\Enums\ShippingFulfillmentType;
use App\Models\Cart;
use App\Models\Country;
use App\Models\DeliveryPoint;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\Site;

beforeEach(function () {
    Site::factory()->create(['is_main' => true, 'is_pickup_point' => false]);
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    $this->country = Country::create(['name' => 'Magyarország', 'code' => 'HU', 'sort_order' => 1]);
    $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
});

function placeOrder(string $token, array $overrides = []): \Illuminate\Testing\TestResponse
{
    $product = Product::factory()->create(['price' => 5000]);
    $cart = Cart::create(['guest_token' => $token]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    return test()->withCookie('cart_token', $token)->post('/penztar', array_merge([
        'name' => 'Teszt Vásárló',
        'email' => $token.'@example.com',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36301111111',
        'billing_same_as_shipping' => 1,
    ], $overrides));
}

it('creates an order with a site delivery point when picking up at a pickup-enabled site', function () {
    $pickupSite = Site::factory()->create(['is_pickup_point' => true, 'is_main' => false]);

    $shippingMethod = ShippingMethod::factory()->create([
        'is_active' => true,
        'fulfillment_type' => ShippingFulfillmentType::SitePickup,
    ]);
    $shippingMethod->paymentMethods()->attach($this->paymentMethod);

    $response = placeOrder('site-pickup-token', [
        'site_id' => $pickupSite->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull();

    $response->assertRedirect(route('checkout.confirmation', $order));

    expect($order->deliveryPoint)->not->toBeNull()
        ->and($order->deliveryPoint->type)->toBe('site')
        ->and($order->deliveryPoint->reference_id)->toBe((string) $pickupSite->id)
        ->and($order->shipping_city)->toBe($pickupSite->city)
        ->and($order->shipping_zip)->toBe($pickupSite->zip);
});

it('rejects a site pickup order when the chosen site is not pickup-enabled', function () {
    $nonPickupSite = Site::factory()->create(['is_pickup_point' => false, 'is_main' => false]);

    $shippingMethod = ShippingMethod::factory()->create([
        'is_active' => true,
        'fulfillment_type' => ShippingFulfillmentType::SitePickup,
    ]);
    $shippingMethod->paymentMethods()->attach($this->paymentMethod);

    $response = placeOrder('non-pickup-token', [
        'site_id' => $nonPickupSite->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $response->assertSessionHasErrors('site_id');
    expect(Order::count())->toBe(0);
});

it('creates an order with a foxpost delivery point when a parcel locker payload is submitted', function () {
    $shippingMethod = ShippingMethod::factory()->create([
        'is_active' => true,
        'fulfillment_type' => ShippingFulfillmentType::ParcelLocker,
        'locker_provider' => 'foxpost',
    ]);
    $shippingMethod->paymentMethods()->attach($this->paymentMethod);

    $payload = json_encode([
        'place_id' => 'FP123',
        'name' => 'Foxpost Teszt Automata',
        'zip' => '1012',
        'city' => 'Budapest',
        'street' => 'Teszt körút 5.',
    ]);

    $response = placeOrder('foxpost-token', [
        'delivery_point_payload' => $payload,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull();

    $response->assertRedirect(route('checkout.confirmation', $order));

    expect($order->deliveryPoint)->not->toBeNull()
        ->and($order->deliveryPoint->type)->toBe('foxpost')
        ->and($order->deliveryPoint->reference_id)->toBe('FP123')
        ->and($order->shipping_city)->toBe('Budapest')
        ->and($order->shipping_zip)->toBe('1012');

    expect(DeliveryPoint::count())->toBe(1);
});

it('rejects a parcel locker order when no locker was selected', function () {
    $shippingMethod = ShippingMethod::factory()->create([
        'is_active' => true,
        'fulfillment_type' => ShippingFulfillmentType::ParcelLocker,
        'locker_provider' => 'foxpost',
    ]);
    $shippingMethod->paymentMethods()->attach($this->paymentMethod);

    $response = placeOrder('foxpost-missing-token', [
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $response->assertSessionHasErrors('delivery_point_payload');
    expect(Order::count())->toBe(0);
});

it('still requires the full address for the courier fulfillment type', function () {
    $shippingMethod = ShippingMethod::factory()->create([
        'is_active' => true,
        'fulfillment_type' => ShippingFulfillmentType::Courier,
    ]);
    $shippingMethod->paymentMethods()->attach($this->paymentMethod);

    $response = placeOrder('courier-token', [
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $response->assertSessionHasErrors(['shipping_country_id', 'shipping_city', 'shipping_zip', 'shipping_address']);
    expect(Order::count())->toBe(0);
});

it('creates a courier order with the submitted address and no delivery point', function () {
    $shippingMethod = ShippingMethod::factory()->create([
        'is_active' => true,
        'fulfillment_type' => ShippingFulfillmentType::Courier,
    ]);
    $shippingMethod->paymentMethods()->attach($this->paymentMethod);

    $response = placeOrder('courier-full-token', [
        'shipping_method_id' => $shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
        'shipping_country_id' => $this->country->id,
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1000',
        'shipping_address' => 'Teszt utca 1.',
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull();

    $response->assertRedirect(route('checkout.confirmation', $order));

    expect($order->delivery_point_id)->toBeNull()
        ->and($order->shipping_country_id)->toBe($this->country->id)
        ->and($order->shipping_address)->toBe('Teszt utca 1.');
});
