<?php

use App\Models\Product;

it('stays orderable without stock when not discontinued', function () {
    $product = Product::factory()->make(['is_discontinued' => false]);

    expect($product->isOrderable(0))->toBeTrue();
    expect($product->isOrderable(5))->toBeTrue();
});

it('stops being orderable without stock when discontinued', function () {
    $product = Product::factory()->make(['is_discontinued' => true]);

    expect($product->isOrderable(0))->toBeFalse();
    expect($product->isOrderable(5))->toBeTrue();
});
