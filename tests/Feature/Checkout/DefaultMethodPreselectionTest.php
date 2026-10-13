<?php

use App\Models\Cart;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Site::factory()->create(['is_main' => true, 'is_pickup_point' => false]);
    OrderStatus::factory()->create(['slug' => 'pending', 'is_default' => true]);
    Country::create(['name' => 'Magyarország', 'code' => 'HU', 'sort_order' => 1]);
});

function addGuestCartItem(string $token): void
{
    $product = Product::factory()->create(['price' => 5000]);
    $cart = Cart::create(['guest_token' => $token]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);
}

it('preselects the first available shipping and payment method when no default is set', function () {
    $shippingA = ShippingMethod::factory()->create(['is_active' => true, 'name' => 'A szállítás']);
    $shippingB = ShippingMethod::factory()->create(['is_active' => true, 'name' => 'B szállítás']);
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $shippingA->paymentMethods()->attach($paymentMethod);
    $shippingB->paymentMethods()->attach($paymentMethod);

    addGuestCartItem('preselect-first-token');
    $response = $this->withCookie('cart_token', 'preselect-first-token')->get('/penztar');

    expect($response->viewData('defaultShippingMethodId'))->toBe($shippingA->id)
        ->and($response->viewData('defaultPaymentMethodId'))->toBe($paymentMethod->id);
});

it('preselects the admin-configured global default shipping and payment method', function () {
    $shippingA = ShippingMethod::factory()->create(['is_active' => true, 'is_default' => false, 'name' => 'A szállítás']);
    $shippingB = ShippingMethod::factory()->create(['is_active' => true, 'is_default' => true, 'name' => 'B szállítás']);
    $paymentX = PaymentMethod::factory()->create(['is_active' => true, 'is_default' => true]);
    $paymentY = PaymentMethod::factory()->create(['is_active' => true, 'is_default' => false]);
    $shippingA->paymentMethods()->attach($paymentY);
    $shippingB->paymentMethods()->attach([$paymentX->id, $paymentY->id]);

    addGuestCartItem('preselect-global-default-token');
    $response = $this->withCookie('cart_token', 'preselect-global-default-token')->get('/penztar');

    expect($response->viewData('defaultShippingMethodId'))->toBe($shippingB->id)
        ->and($response->viewData('defaultPaymentMethodId'))->toBe($paymentX->id);
});

it('preselects the logged in customer\'s saved preference over the global default', function () {
    $shippingA = ShippingMethod::factory()->create(['is_active' => true, 'is_default' => false, 'name' => 'A szállítás']);
    $shippingB = ShippingMethod::factory()->create(['is_active' => true, 'is_default' => true, 'name' => 'B szállítás']);
    $paymentX = PaymentMethod::factory()->create(['is_active' => true, 'is_default' => true]);
    $paymentY = PaymentMethod::factory()->create(['is_active' => true, 'is_default' => false]);
    $shippingA->paymentMethods()->attach($paymentY);
    $shippingB->paymentMethods()->attach([$paymentX->id, $paymentY->id]);

    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'email' => $user->email,
        'default_shipping_method_id' => $shippingA->id,
        'default_payment_method_id' => $paymentY->id,
    ]);

    $product = Product::factory()->create(['price' => 5000]);
    Cart::create(['customer_id' => $customer->id])->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->actingAs($user)->get('/penztar');

    expect($response->viewData('defaultShippingMethodId'))->toBe($shippingA->id)
        ->and($response->viewData('defaultPaymentMethodId'))->toBe($paymentY->id);
});

it('overwrites the customer\'s saved default with the most recently chosen methods on every order', function () {
    Mail::fake();

    $shippingA = ShippingMethod::factory()->create(['is_active' => true]);
    $shippingB = ShippingMethod::factory()->create(['is_active' => true]);
    $paymentX = PaymentMethod::factory()->create(['is_active' => true]);
    $paymentY = PaymentMethod::factory()->create(['is_active' => true]);
    $shippingA->paymentMethods()->attach($paymentX);
    $shippingB->paymentMethods()->attach($paymentY);

    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'email' => $user->email,
        'default_shipping_method_id' => $shippingA->id,
        'default_payment_method_id' => $paymentX->id,
    ]);

    $product = Product::factory()->create(['price' => 5000]);
    Cart::create(['customer_id' => $customer->id])->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $this->actingAs($user)->post('/penztar', [
        'shipping_name' => 'Teszt Vásárló',
        'shipping_phone' => '+36301111111',
        'billing_same_as_shipping' => 1,
        'shipping_country_id' => Country::first()->id,
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1000',
        'shipping_address' => 'Teszt utca 1.',
        'shipping_method_id' => $shippingB->id,
        'payment_method_id' => $paymentY->id,
    ]);

    expect(Order::count())->toBe(1);

    $customer->refresh();

    expect($customer->default_shipping_method_id)->toBe($shippingB->id)
        ->and($customer->default_payment_method_id)->toBe($paymentY->id);
});
