<?php

use App\Models\Cart;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);

    $this->shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);
    $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $this->shippingMethod->paymentMethods()->attach($this->paymentMethod);
});

it('links an existing guest customer to a new account instead of duplicating it', function () {
    Mail::fake();
    Event::fake();

    $product = Product::factory()->create(['price' => 3000, 'stock' => 10]);

    $cart = Cart::create(['guest_token' => 'guest-register-token']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $this->withCookie('cart_token', 'guest-register-token')->post('/penztar', [
        'name' => 'Ismétlődő Ügyfél',
        'email' => 'ismetlodo@example.com',
        'shipping_name' => 'Ismétlődő Ügyfél',
        'shipping_phone' => '+36309998888',
        'shipping_address' => '3333 Szeged, Teszt tér 3.',
        'shipping_method_id' => $this->shippingMethod->id,
        'payment_method_id' => $this->paymentMethod->id,
    ]);

    expect(Customer::count())->toBe(1);

    $this->post('/regisztracio', [
        'name' => 'Ismétlődő Ügyfél',
        'email' => 'ismetlodo@example.com',
        'password' => 'masikjelszo123',
        'password_confirmation' => 'masikjelszo123',
    ]);

    expect(Customer::count())->toBe(1);

    $customer = Customer::where('email', 'ismetlodo@example.com')->first();
    $user = User::where('email', 'ismetlodo@example.com')->first();

    expect($customer->user_id)->toBe($user->id);
});
