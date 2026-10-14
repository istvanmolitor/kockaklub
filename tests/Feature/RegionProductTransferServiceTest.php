<?php

use App\Models\Product;
use App\Models\Region;
use App\Models\RegionProductTransfer;
use App\Models\Site;
use App\Services\RegionProductTransferService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

function seedRegionStock(int $regionId, int $productId, int $quantity): void
{
    DB::table('region_product_stocks')->insert([
        'region_id' => $regionId,
        'product_id' => $productId,
        'quantity' => $quantity,
    ]);
}

it('moves stock from the source region into the destination region and logs the transfer', function () {
    $site = Site::factory()->create();
    $source = Region::factory()->create(['site_id' => $site->id]);
    $destination = Region::factory()->create(['site_id' => $site->id]);
    $product = Product::factory()->create();

    seedRegionStock($source->id, $product->id, 10);

    $transfer = app(RegionProductTransferService::class)->transfer($source, $destination, $product->id, 4);

    expect(DB::table('region_product_stocks')->where('region_id', $source->id)->where('product_id', $product->id)->value('quantity'))->toBe(6)
        ->and(DB::table('region_product_stocks')->where('region_id', $destination->id)->where('product_id', $product->id)->value('quantity'))->toBe(4)
        ->and($transfer)->toBeInstanceOf(RegionProductTransfer::class)
        ->and($transfer->source_region_id)->toBe($source->id)
        ->and($transfer->destination_region_id)->toBe($destination->id)
        ->and($transfer->quantity)->toBe(4);
});

it('rejects a transfer that would exceed the source region stock', function () {
    $site = Site::factory()->create();
    $source = Region::factory()->create(['site_id' => $site->id]);
    $destination = Region::factory()->create(['site_id' => $site->id]);
    $product = Product::factory()->create();

    seedRegionStock($source->id, $product->id, 3);

    app(RegionProductTransferService::class)->transfer($source, $destination, $product->id, 5);
})->throws(ValidationException::class);

it('rejects a transfer between regions of different sites', function () {
    $source = Region::factory()->create(['site_id' => Site::factory()]);
    $destination = Region::factory()->create(['site_id' => Site::factory()]);
    $product = Product::factory()->create();

    seedRegionStock($source->id, $product->id, 10);

    app(RegionProductTransferService::class)->transfer($source, $destination, $product->id, 1);
})->throws(ValidationException::class);

it('rejects a transfer where the source and destination region are the same', function () {
    $site = Site::factory()->create();
    $region = Region::factory()->create(['site_id' => $site->id]);
    $product = Product::factory()->create();

    seedRegionStock($region->id, $product->id, 10);

    app(RegionProductTransferService::class)->transfer($region, $region, $product->id, 1);
})->throws(ValidationException::class);
