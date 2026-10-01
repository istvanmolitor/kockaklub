<?php

use App\Models\Product;

it('finds active products matching the search term by name', function () {
    $match = Product::factory()->create(['is_active' => true, 'name' => 'Rubik Kocka']);
    Product::factory()->create(['is_active' => true, 'name' => 'Sakk Készlet']);

    $response = $this->get('/kereses?q=Rubik');

    $response->assertOk();
    $response->assertSee($match->name);
    $response->assertDontSee('Sakk Készlet');
});

it('does not show inactive products in search results', function () {
    Product::factory()->create(['is_active' => false, 'name' => 'Rubik Kocka Inaktív']);

    $response = $this->get('/kereses?q=Rubik');

    $response->assertOk();
    $response->assertDontSee('Rubik Kocka Inaktív');
});

it('shows a hint when no search term is given', function () {
    $response = $this->get('/kereses');

    $response->assertOk();
    $response->assertSee('Adj meg egy keresési kifejezést.');
});

it('sorts search results by price', function () {
    $cheap = Product::factory()->create(['is_active' => true, 'name' => 'Rubik Olcsó', 'price' => 1000]);
    $expensive = Product::factory()->create(['is_active' => true, 'name' => 'Rubik Drága', 'price' => 50000]);

    $response = $this->get('/kereses?q=Rubik&sort=price_desc');

    $response->assertOk();
    $response->assertSeeInOrder([$expensive->name, $cheap->name]);
});
