<?php

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

it('moves the customer cart into a new session cart on logout and removes the customer cart', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

    $customerCart = Cart::create(['customer_id' => $customer->id]);
    $customerCart->items()->create(['product_id' => $productA->id, 'quantity' => 2]);
    $customerCart->items()->create(['product_id' => $productB->id, 'quantity' => 1]);

    $this->actingAs($user)->post('/kijelentkezes');

    expect(Cart::find($customerCart->id))->toBeNull();

    $guestCart = Cart::whereNotNull('guest_token')->first();

    expect($guestCart)->not->toBeNull()
        ->and($guestCart->items()->where('product_id', $productA->id)->first()->quantity)->toBe(2)
        ->and($guestCart->items()->where('product_id', $productB->id)->first()->quantity)->toBe(1);
});

it('does not create a session cart on logout when the customer cart was empty', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

    $cartCountBefore = Cart::count();

    $this->actingAs($user)->post('/kijelentkezes');

    expect(Cart::count())->toBe($cartCountBefore);
});
