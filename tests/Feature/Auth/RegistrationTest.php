<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Event;

it('registers a user with a linked customer and no admin access', function () {
    Event::fake();

    $response = $this->post('/regisztracio', [
        'name' => 'Kovács Anna',
        'email' => 'anna@example.com',
        'password' => 'titkosjelszo123',
        'password_confirmation' => 'titkosjelszo123',
    ]);

    $response->assertRedirect(route('verification.notice'));

    $user = User::where('email', 'anna@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_admin)->toBeFalse();

    $customer = Customer::where('email', 'anna@example.com')->first();

    expect($customer)->not->toBeNull()
        ->and($customer->user_id)->toBe($user->id);

    $this->actingAs($user);
    $this->get('/admin')->assertForbidden();
});
