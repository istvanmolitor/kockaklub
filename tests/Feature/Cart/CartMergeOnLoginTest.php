<?php

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('merges the guest cart into the customer cart on login and removes the guest cart', function () {
    $user = User::factory()->create(['password' => Hash::make('jelszo1234')]);
    $customer = Customer::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

    $customerCart = Cart::create(['customer_id' => $customer->id]);
    $customerCart->items()->create(['product_id' => $productA->id, 'quantity' => 1]);

    $guestCart = Cart::create(['guest_token' => 'guest-login-token']);
    $guestCart->items()->create(['product_id' => $productA->id, 'quantity' => 2]);
    $guestCart->items()->create(['product_id' => $productB->id, 'quantity' => 1]);

    $this->withCookie('cart_token', 'guest-login-token')->post('/bejelentkezes', [
        'email' => $user->email,
        'password' => 'jelszo1234',
    ]);

    expect(Cart::find($guestCart->id))->toBeNull();

    $customerCart->refresh();

    expect($customerCart->items()->where('product_id', $productA->id)->first()->quantity)->toBe(3)
        ->and($customerCart->items()->where('product_id', $productB->id)->first()->quantity)->toBe(1);
});
