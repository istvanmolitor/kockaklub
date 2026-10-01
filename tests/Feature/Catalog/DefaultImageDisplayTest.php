<?php

use App\Models\Product;

it('shows the default image thumbnail on the catalog page', function () {
    $product = Product::factory()->create(['is_active' => true, 'name' => 'Katalógus Teszt Termék']);

    $product->images()->create(['path' => 'product-images/first.jpg', 'sort_order' => 0]);
    $product->images()->create(['path' => 'product-images/second.jpg', 'sort_order' => 1]);

    $response = $this->get('/termekek');

    $response->assertOk();
    $response->assertSee('storage/product-images/first.jpg', false);
    $response->assertDontSee('storage/product-images/second.jpg', false);
});

it('falls back to the placeholder image when a product has no images', function () {
    Product::factory()->create(['is_active' => true, 'name' => 'Kép Nélküli Termék']);

    $response = $this->get('/termekek');

    $response->assertOk();
    $response->assertSee('product-placeholder.svg', false);
});
