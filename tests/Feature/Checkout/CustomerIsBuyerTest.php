<?php

use App\Enums\ShippingFulfillmentType;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Customer;
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
    $this->shippingMethod = ShippingMethod::factory()->create([
        'is_active' => true,
        'fulfillment_type' => ShippingFulfillmentType::Courier,
    ]);
    $this->shippingMethod->paymentMethods()->attach($this->paymentMethod);
});

it('marks the customer as a buyer once they place their first order', function () {
    $token = 'is-buyer-token';
    $product = Product::factory()->create(['price' => 5000]);
    $cart = Cart::create(['guest_token' => $token]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    test()->withCookie('cart_token', $token)->post('/penztar', [
        'name' => 'Teszt Vásárló',
        'email' => 'is-buyer@example.com',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36301111111',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
        'shipping_country_id' => $this->country->id,
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1000',
        'shipping_address' => 'Teszt utca 1.',
    ]);

    $customer = Customer::where('email', 'is-buyer@example.com')->first();

    expect($customer)->not->toBeNull()
        ->and($customer->is_buyer)->toBeTrue()
        ->and($customer->is_seller)->toBeFalse();
});

it('defaults new customers to not being a buyer or a seller', function () {
    $customer = Customer::factory()->create();

    expect($customer->is_buyer)->toBeFalse()
        ->and($customer->is_seller)->toBeFalse();
});
