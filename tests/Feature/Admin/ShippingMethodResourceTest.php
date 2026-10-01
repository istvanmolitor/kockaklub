<?php

use App\Filament\Resources\ShippingMethods\Pages\CreateShippingMethod;
use App\Filament\Resources\ShippingMethods\Pages\EditShippingMethod;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('allows an admin to create a shipping method with payment methods', function () {
    $paymentMethod = PaymentMethod::factory()->create();

    livewire(CreateShippingMethod::class)
        ->fillForm([
            'name' => 'Házhozszállítás',
            'description' => 'Futárszolgálattal.',
            'paymentMethods' => [$paymentMethod->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $shippingMethod = ShippingMethod::where('name', 'Házhozszállítás')->first();

    expect($shippingMethod)->not->toBeNull();
    expect($shippingMethod->paymentMethods()->pluck('payment_methods.id')->all())->toBe([$paymentMethod->id]);
});

it('allows an admin to edit the payment methods attached to a shipping method', function () {
    $shippingMethod = ShippingMethod::factory()->create();
    $paymentMethods = PaymentMethod::factory()->count(2)->create();

    livewire(EditShippingMethod::class, ['record' => $shippingMethod->getRouteKey()])
        ->fillForm([
            'paymentMethods' => $paymentMethods->pluck('id')->all(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($shippingMethod->paymentMethods()->pluck('payment_methods.id')->all())
        ->toEqualCanonicalizing($paymentMethods->pluck('id')->all());
});

it('allows an admin to deactivate a shipping method', function () {
    $shippingMethod = ShippingMethod::factory()->create(['is_active' => true]);

    livewire(EditShippingMethod::class, ['record' => $shippingMethod->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($shippingMethod->fresh()->is_active)->toBeFalse();
});
