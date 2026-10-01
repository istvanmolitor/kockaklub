<?php

use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Site::factory()->create(['is_main' => true]);
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    $this->shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $this->shippingMethod->paymentMethods()->attach($this->paymentMethod);
});

it('fills the customer address details from the first order when they were empty', function () {
    Mail::fake();

    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'email' => $user->email,
        'shipping_name' => null,
        'shipping_country' => null,
        'shipping_city' => null,
        'shipping_zip' => null,
        'shipping_address' => null,
        'billing_name' => null,
        'billing_country' => null,
        'billing_city' => null,
        'billing_zip' => null,
        'billing_address' => null,
    ]);

    $product = Product::factory()->create(['price' => 1000]);

    $this->actingAs($user);
    $this->post("/kosar/hozzaadas/{$product->id}", ['quantity' => 1]);

    $this->post('/penztar', [
        'shipping_name' => 'Első Rendelő',
        'shipping_phone' => '+36301230000',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1111',
        'shipping_address' => 'Első utca 1.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $customer->refresh();

    expect($customer->shipping_city)->toBe('Budapest')
        ->and($customer->shipping_address)->toBe('Első utca 1.')
        ->and($customer->billing_city)->toBe('Budapest')
        ->and($customer->billing_address)->toBe('Első utca 1.');
});

it('does not overwrite already filled in customer address details on a later order', function () {
    Mail::fake();

    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'email' => $user->email,
        'shipping_city' => 'Eredeti Város',
        'billing_city' => 'Eredeti Város',
    ]);

    $product = Product::factory()->create(['price' => 1000]);

    $this->actingAs($user);
    $this->post("/kosar/hozzaadas/{$product->id}", ['quantity' => 1]);

    $this->post('/penztar', [
        'shipping_name' => 'Más Rendelő',
        'shipping_phone' => '+36301230000',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Másik Város',
        'shipping_zip' => '2222',
        'shipping_address' => 'Másik utca 2.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    expect($customer->fresh()->shipping_city)->toBe('Eredeti Város');
});
