<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);
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
        'shipping_address' => '4444 Pécs, Üres utca 4.',
        'payment_method' => 'cod',
    ]);

    $response->assertSessionHasErrors();

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(0);
});
