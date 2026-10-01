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
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    $this->shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $this->shippingMethod->paymentMethods()->attach($this->paymentMethod);
});

it('assigns a newly placed order to the main site', function () {
    Mail::fake();

    Site::factory()->create(['is_main' => false]);
    $mainSite = Site::factory()->create(['is_main' => true]);

    $product = Product::factory()->create(['price' => 2000]);

    $cart = Cart::create(['guest_token' => 'main-site-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $this->withCookie('cart_token', 'main-site-token')->post('/penztar', [
        'name' => 'Teszt Vásárló',
        'email' => 'fotelephely@example.com',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36301111222',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1000',
        'shipping_address' => 'Teszt utca 1.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->site_id)->toBe($mainSite->id);
});

it('does not allow checkout when no main site is configured', function () {
    $product = Product::factory()->create(['price' => 2000]);

    $cart = Cart::create(['guest_token' => 'no-main-site-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->withCookie('cart_token', 'no-main-site-token')->get('/penztar');

    $response->assertRedirect(route('cart.show'));

    expect(Order::count())->toBe(0);
});
