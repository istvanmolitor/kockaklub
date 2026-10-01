<?php

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
