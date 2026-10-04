<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Region;
use App\Models\Site;
use App\Models\StockMovement;
use App\Services\StockService;

function seedCatalogPublicStock(Product $product, int $quantity): void
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

it('shows the free stock instead of the raw public stock on the product page', function () {
    $product = Product::factory()->create(['is_active' => true, 'is_discontinued' => false]);
    seedCatalogPublicStock($product, 10);

    $order = Order::factory()->create(['site_id' => Site::factory(), 'reserved_at' => null]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 4,
    ]);

    $response = $this->get(route('catalog.show', $product));

    $response->assertOk();
    $response->assertSee('Raktáron (6 db)');
    $response->assertDontSee('Raktáron (10 db)');
});
