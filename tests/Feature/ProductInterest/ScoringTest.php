<?php

use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductInterest;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    $this->shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $this->shippingMethod->paymentMethods()->attach($this->paymentMethod);
});

it('awards one point for viewing a product page while logged in', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->get("/termekek/{$product->slug}");

    expect(ProductInterest::first()->score)->toBe(1);
});

it('does not record a score for guests viewing a product page', function () {
    $product = Product::factory()->create();

    $this->get("/termekek/{$product->slug}");

    expect(ProductInterest::count())->toBe(0);
});

it('awards five points for adding a product to the cart while logged in', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->post("/kosar/hozzaadas/{$product->id}", ['quantity' => 1]);

    expect(ProductInterest::first()->score)->toBe(5);
});

it('awards ten points per product when an order is placed while logged in', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'user@example.com']);
    Customer::factory()->create(['user_id' => $user->id, 'email' => $user->email]);
    $product = Product::factory()->create();

    $this->actingAs($user)->post("/kosar/hozzaadas/{$product->id}", ['quantity' => 1]);

    $this->post('/penztar', [
        'shipping_name' => 'Teszt Elek',
        'shipping_phone' => '+36301112222',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Debrecen',
        'shipping_zip' => '2222',
        'shipping_address' => 'Fő utca 2.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    expect(ProductInterest::first()->score)->toBe(5 + 10);
});
