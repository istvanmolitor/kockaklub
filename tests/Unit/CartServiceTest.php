<?php

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->service = new CartService;
});

it('adds a product to a new guest cart and increments quantity on repeat add', function () {
    $product = Product::factory()->create(['price' => 1000]);
    $request = Request::create('/kosar/hozzaadas/'.$product->id, 'POST');

    $cart = $this->service->add($request, $product, 2);
    expect($cart->items()->where('product_id', $product->id)->first()->quantity)->toBe(2);

    // Simulate the cookie that would have been queued on the first call.
    $token = $cart->guest_token;
    $request2 = Request::create('/kosar/hozzaadas/'.$product->id, 'POST');
    $request2->cookies->set(CartService::COOKIE_NAME, $token);

    $this->service->add($request2, $product, 3);

    expect($cart->items()->where('product_id', $product->id)->first()->quantity)->toBe(5);
});

it('updates and removes cart item quantities', function () {
    $product = Product::factory()->create();
    $request = Request::create('/', 'GET');

    $cart = $this->service->add($request, $product, 1);
    $token = $cart->guest_token;

    $request2 = Request::create('/', 'GET');
    $request2->cookies->set(CartService::COOKIE_NAME, $token);

    $this->service->updateQuantity($request2, $product, 4);
    expect($cart->items()->where('product_id', $product->id)->first()->quantity)->toBe(4);

    $this->service->updateQuantity($request2, $product, 0);
    expect($cart->items()->where('product_id', $product->id)->exists())->toBeFalse();
});

it('computes the cart total from its items', function () {
    $productA = Product::factory()->create(['price' => 1500]);
    $productB = Product::factory()->create(['price' => 2500]);
    $request = Request::create('/', 'GET');

    $cart = $this->service->add($request, $productA, 2);
    $token = $cart->guest_token;

    $request2 = Request::create('/', 'GET');
    $request2->cookies->set(CartService::COOKIE_NAME, $token);
    $this->service->add($request2, $productB, 1);

    $total = $cart->fresh()->items->sum(fn ($item) => $item->lineTotal());

    expect($total)->toBe(2 * 1500 + 2500);
});

it('merges a guest cart into the customer cart and deletes the guest cart', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();
    $request = Request::create('/', 'GET');

    $guestCart = $this->service->add($request, $product, 2);
    $token = $guestCart->guest_token;

    $request2 = Request::create('/', 'GET');
    $request2->cookies->set(CartService::COOKIE_NAME, $token);

    $this->service->mergeGuestCartIntoCustomer($request2, $customer);

    expect(Cart::find($guestCart->id))->toBeNull();

    $customerCart = Cart::where('customer_id', $customer->id)->first();
    expect($customerCart->items()->where('product_id', $product->id)->first()->quantity)->toBe(2);
});
