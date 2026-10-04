<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Region;
use App\Models\Site;
use App\Models\StockMovement;
use App\Services\StockService;

function seedPublicStockFor(Product $product, int $quantity): void
{
    $region = Region::factory()->create(['is_public' => true]);

    $movement = StockMovement::factory()->create([
        'type' => StockMovement::TYPE_IN,
        'source_region_id' => null,
        'destination_region_id' => $region->id,
    ]);

    $movement->items()->create([
        'product_id' => $product->id,
        'quantity' => $quantity,
    ]);

    app(StockService::class)->closeMovement($movement);
}

it('subtracts quantity from unreserved orders from the free stock', function () {
    $product = Product::factory()->create();
    seedPublicStockFor($product, 10);

    $order = Order::factory()->create(['site_id' => Site::factory(), 'reserved_at' => null]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 4,
    ]);

    expect($product->freeStock())->toBe(6);
});

it('does not subtract quantity from already reserved orders', function () {
    $product = Product::factory()->create();
    seedPublicStockFor($product, 10);

    $order = Order::factory()->create(['site_id' => Site::factory(), 'reserved_at' => now()]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 4,
    ]);

    expect($product->freeStock())->toBe(10);
});

it('never returns a negative free stock', function () {
    $product = Product::factory()->create();
    seedPublicStockFor($product, 3);

    $order = Order::factory()->create(['site_id' => Site::factory(), 'reserved_at' => null]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    expect($product->freeStock())->toBe(0);
});
