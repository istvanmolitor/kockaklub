<?php

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Models\Customer;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('fills the shipping and billing fields from the selected customer profile', function () {
    $customer = Customer::factory()->create([
        'phone' => '06701112233',
        'shipping_name' => 'Teszt Vásárló',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1051',
        'shipping_address' => 'Profilból töltött utca 9.',
        'billing_name' => 'Teszt Vásárló Kft.',
        'billing_country' => 'Magyarország',
        'billing_city' => 'Budapest',
        'billing_zip' => '1051',
        'billing_address' => 'Számlázási utca 9.',
        'billing_tax_number' => '12345678-1-42',
    ]);

    livewire(CreateOrder::class)
        ->set('data.customer_id', $customer->id)
        ->assertSchemaStateSet([
            'shipping_name' => 'Teszt Vásárló',
            'shipping_phone' => '06701112233',
            'shipping_country' => 'Magyarország',
            'shipping_city' => 'Budapest',
            'shipping_zip' => '1051',
            'shipping_address' => 'Profilból töltött utca 9.',
            'billing_name' => 'Teszt Vásárló Kft.',
            'billing_country' => 'Magyarország',
            'billing_city' => 'Budapest',
            'billing_zip' => '1051',
            'billing_address' => 'Számlázási utca 9.',
            'billing_tax_number' => '12345678-1-42',
        ]);
});
