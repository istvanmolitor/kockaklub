<?php

use App\Models\Product;

it('automatically makes the first uploaded image the default', function () {
    $product = Product::factory()->create();

    $image = $product->images()->create([
        'path' => 'product-images/one.jpg',
        'sort_order' => 0,
    ]);

    expect($image->fresh()->is_default)->toBeTrue();
});

it('unsets the previous default when another image is marked as default', function () {
    $product = Product::factory()->create();

    $first = $product->images()->create(['path' => 'product-images/one.jpg', 'sort_order' => 0]);
    $second = $product->images()->create(['path' => 'product-images/two.jpg', 'sort_order' => 1]);

    expect($first->fresh()->is_default)->toBeTrue()
        ->and($second->fresh()->is_default)->toBeFalse();

    $second->update(['is_default' => true]);

    expect($first->fresh()->is_default)->toBeFalse()
        ->and($second->fresh()->is_default)->toBeTrue();
});

it('promotes the next lowest sort order image to default when the default is deleted', function () {
    $product = Product::factory()->create();

    $first = $product->images()->create(['path' => 'product-images/one.jpg', 'sort_order' => 0]);
    $second = $product->images()->create(['path' => 'product-images/two.jpg', 'sort_order' => 1]);

    $first->delete();

    expect($second->fresh()->is_default)->toBeTrue();
});
