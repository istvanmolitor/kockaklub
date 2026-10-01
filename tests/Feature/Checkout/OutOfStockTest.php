<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    $this->shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $this->shippingMethod->paymentMethods()->attach($this->paymentMethod);
});

it('refuses to check out a product that is out of stock', function () {
    Mail::fake();

    $product = Product::factory()->create(['price' => 4000, 'stock' => 0]);

    $cart = Cart::create(['guest_token' => 'test-guest-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->withCookie('cart_token', 'test-guest-token')->post('/penztar', [
        'name' => 'Teszt Vásárló',
        'email' => 'nincs.keszlet@example.com',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36301230000',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Pécs',
        'shipping_zip' => '4444',
        'shipping_address' => 'Üres utca 4.',
        'billing_same_as_shipping' => 1,
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    $response->assertSessionHasErrors();

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(0);
});
