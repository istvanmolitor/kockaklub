<?php

use App\Models\Product;

it('sorts catalog products by price ascending', function () {
    $cheap = Product::factory()->create(['is_active' => true, 'name' => 'Olcsó Termék', 'price' => 1000]);
    $expensive = Product::factory()->create(['is_active' => true, 'name' => 'Drága Termék', 'price' => 50000]);

    $response = $this->get('/termekek?sort=price_asc');

    $response->assertOk();
    $response->assertSeeInOrder([$cheap->name, $expensive->name]);
});

it('sorts catalog products by price descending', function () {
    $cheap = Product::factory()->create(['is_active' => true, 'name' => 'Olcsó Termék', 'price' => 1000]);
    $expensive = Product::factory()->create(['is_active' => true, 'name' => 'Drága Termék', 'price' => 50000]);

    $response = $this->get('/termekek?sort=price_desc');

    $response->assertOk();
    $response->assertSeeInOrder([$expensive->name, $cheap->name]);
});

it('sorts catalog products by name', function () {
    $zebra = Product::factory()->create(['is_active' => true, 'name' => 'Zebra Termék']);
    $alma = Product::factory()->create(['is_active' => true, 'name' => 'Alma Termék']);

    $response = $this->get('/termekek?sort=name_asc');

    $response->assertOk();
    $response->assertSeeInOrder([$alma->name, $zebra->name]);
});
