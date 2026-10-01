<?php

use App\Models\Product;
use App\Models\User;

it('logs the initial price when a product is created', function () {
    $product = Product::factory()->create(['price' => 9990]);

    expect($product->priceLogs)->toHaveCount(1);

    $log = $product->priceLogs->first();

    expect($log->price)->toBe(9990)
        ->and($log->previous_price)->toBeNull()
        ->and($log->user_id)->toBeNull();
});

it('logs who changed the price and what it changed from and to', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['price' => 9990]);

    $this->actingAs($admin);

    $product->update(['price' => 12990]);

    expect($product->priceLogs)->toHaveCount(2);

    $log = $product->priceLogs->first();

    expect($log->price)->toBe(12990)
        ->and($log->previous_price)->toBe(9990)
        ->and($log->user_id)->toBe($admin->id);
});

it('does not log when the product is saved without the price changing', function () {
    $product = Product::factory()->create(['price' => 9990]);

    $product->update(['name' => 'Új név']);

    expect($product->priceLogs)->toHaveCount(1);
});
