<?php

use App\Models\Product;

it('shows related products on the product detail page in the configured order', function () {
    $product = Product::factory()->create();
    $first = Product::factory()->create(['name' => 'Első kapcsolódó termék']);
    $second = Product::factory()->create(['name' => 'Második kapcsolódó termék']);

    $product->relatedProducts()->attach([
        $second->id => ['sort_order' => 0],
        $first->id => ['sort_order' => 1],
    ]);

    $response = $this->get(route('catalog.show', $product));

    $response->assertOk();
    $response->assertSee('Kapcsolódó termékek');
    $response->assertSeeInOrder([$second->name, $first->name]);
});

it('does not show inactive related products', function () {
    $product = Product::factory()->create();
    $inactive = Product::factory()->create(['is_active' => false, 'name' => 'Inaktív kapcsolódó termék']);

    $product->relatedProducts()->attach($inactive->id, ['sort_order' => 0]);

    $response = $this->get(route('catalog.show', $product));

    $response->assertOk();
    $response->assertDontSee('Inaktív kapcsolódó termék');
    $response->assertDontSee('Kapcsolódó termékek');
});
