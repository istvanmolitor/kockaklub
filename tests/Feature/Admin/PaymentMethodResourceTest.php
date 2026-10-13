<?php

use App\Enums\PaymentMethodType;
use App\Filament\Resources\PaymentMethods\Pages\CreatePaymentMethod;
use App\Filament\Resources\PaymentMethods\Pages\EditPaymentMethod;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('allows an admin to create a payment method with shipping methods', function () {
    $shippingMethod = ShippingMethod::factory()->create();

    livewire(CreatePaymentMethod::class)
        ->fillForm([
            'name' => 'Banki átutalás',
            'description' => 'Előre utalással.',
            'shippingMethods' => [$shippingMethod->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $paymentMethod = PaymentMethod::where('name', 'Banki átutalás')->first();

    expect($paymentMethod)->not->toBeNull();
    expect($paymentMethod->shippingMethods()->pluck('shipping_methods.id')->all())->toBe([$shippingMethod->id]);
});

it('allows an admin to edit the shipping methods attached to a payment method', function () {
    $paymentMethod = PaymentMethod::factory()->create();
    $shippingMethods = ShippingMethod::factory()->count(2)->create();

    livewire(EditPaymentMethod::class, ['record' => $paymentMethod->getRouteKey()])
        ->fillForm([
            'shippingMethods' => $shippingMethods->pluck('id')->all(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($paymentMethod->shippingMethods()->pluck('shipping_methods.id')->all())
        ->toEqualCanonicalizing($shippingMethods->pluck('id')->all());
});

it('allows an admin to deactivate a payment method', function () {
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

    livewire(EditPaymentMethod::class, ['record' => $paymentMethod->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($paymentMethod->fresh()->is_active)->toBeFalse();
});

it('allows an admin to create a payment method with a type', function () {
    livewire(CreatePaymentMethod::class)
        ->fillForm([
            'name' => 'PayPal',
            'description' => 'Fizetés PayPal fiókkal vagy bankkártyával.',
            'type' => PaymentMethodType::PayPal->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $paymentMethod = PaymentMethod::where('name', 'PayPal')->first();

    expect($paymentMethod)->not->toBeNull()
        ->and($paymentMethod->type)->toBe(PaymentMethodType::PayPal);
});

it('allows an admin to change the type of an existing payment method', function () {
    $paymentMethod = PaymentMethod::factory()->create(['type' => PaymentMethodType::Cod]);

    livewire(EditPaymentMethod::class, ['record' => $paymentMethod->getRouteKey()])
        ->fillForm(['type' => PaymentMethodType::BankTransfer->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($paymentMethod->fresh()->type)->toBe(PaymentMethodType::BankTransfer);
});

it('unsets the previous default payment method when a new one is marked default', function () {
    $previousDefault = PaymentMethod::factory()->create(['is_default' => true]);
    $paymentMethod = PaymentMethod::factory()->create(['is_default' => false]);

    livewire(EditPaymentMethod::class, ['record' => $paymentMethod->getRouteKey()])
        ->fillForm(['is_default' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($paymentMethod->fresh()->is_default)->toBeTrue()
        ->and($previousDefault->fresh()->is_default)->toBeFalse();
});
