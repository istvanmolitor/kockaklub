<?php

use App\Models\Customer;
use App\Models\User;

it('lets a user without a customer record fill in their details for the first time', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->patch('/fiokom', [
        'name' => 'Teszt Elek',
        'phone' => '+36301112222',
        'shipping_name' => 'Teszt Elek',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Budapest',
        'shipping_zip' => '1111',
        'shipping_address' => 'Teszt utca 1.',
        'billing_name' => 'Teszt Elek',
        'billing_country' => 'Magyarország',
        'billing_city' => 'Budapest',
        'billing_zip' => '1111',
        'billing_address' => 'Teszt utca 1.',
    ]);

    $response->assertRedirect(route('account.show'));

    $customer = Customer::where('user_id', $user->id)->first();

    expect($customer)->not->toBeNull()
        ->and($customer->email)->toBe($user->email)
        ->and($customer->shipping_city)->toBe('Budapest')
        ->and($customer->billing_zip)->toBe('1111');
});

it('lets a user update their existing customer details', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $customer = Customer::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

    $this->actingAs($user)->patch('/fiokom', [
        'name' => $customer->name,
        'shipping_name' => 'Új Név',
        'shipping_country' => 'Magyarország',
        'shipping_city' => 'Szeged',
        'shipping_zip' => '6720',
        'shipping_address' => 'Új utca 2.',
        'billing_name' => 'Új Név',
        'billing_country' => 'Magyarország',
        'billing_city' => 'Szeged',
        'billing_zip' => '6720',
        'billing_address' => 'Új utca 2.',
    ]);

    expect($customer->fresh()->shipping_city)->toBe('Szeged');
});
