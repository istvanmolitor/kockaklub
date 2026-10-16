<?php

use App\Models\Setting;

it('shows the maintenance page on the storefront when maintenance mode is enabled', function () {
    Setting::set('maintenance_mode', '1');
    Setting::set('maintenance_message', 'Teszt üzenet karbantartáshoz.');

    $response = $this->get('/');

    $response->assertStatus(503);
    $response->assertSee('Karbantartás alatt');
    $response->assertSee('Teszt üzenet karbantartáshoz.');
});

it('serves the storefront normally when maintenance mode is disabled', function () {
    Setting::set('maintenance_mode', '0');

    $response = $this->get('/');

    $response->assertOk();
});

it('keeps the admin login page accessible while maintenance mode is enabled', function () {
    Setting::set('maintenance_mode', '1');

    $response = $this->get('/admin/login');

    $response->assertOk();
});

it('does not intercept the livewire update endpoint while maintenance mode is enabled', function () {
    Setting::set('maintenance_mode', '1');

    $response = $this->post('/livewire/update');

    $response->assertStatus(404);
});
