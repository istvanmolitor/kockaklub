<?php

use App\Filament\Resources\Sites\Pages\OrganizeRegions;
use App\Models\Product;
use App\Models\Region;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('transfers the full source quantity to the destination region through the page', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $site = Site::factory()->create();
    $source = Region::factory()->create(['site_id' => $site->id]);
    $destination = Region::factory()->create(['site_id' => $site->id]);
    $product = Product::factory()->create();

    DB::table('region_product_stocks')->insert([
        'region_id' => $source->id,
        'product_id' => $product->id,
        'quantity' => 8,
    ]);

    Livewire::actingAs($admin)
        ->test(OrganizeRegions::class, ['record' => $site->getRouteKey()])
        ->set('sourceRegionId', $source->id)
        ->set('destinationRegionId', $destination->id)
        ->call('transferToDestination', $product->id);

    expect(DB::table('region_product_stocks')->where('region_id', $source->id)->where('product_id', $product->id)->value('quantity'))->toBe(0)
        ->and(DB::table('region_product_stocks')->where('region_id', $destination->id)->where('product_id', $product->id)->value('quantity'))->toBe(8)
        ->and(DB::table('region_product_transfers')->where('product_id', $product->id)->where('created_by', $admin->id)->where('quantity', 8)->exists())->toBeTrue();
});

it('transfers only the quantity entered between the two arrows', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $site = Site::factory()->create();
    $source = Region::factory()->create(['site_id' => $site->id]);
    $destination = Region::factory()->create(['site_id' => $site->id]);
    $product = Product::factory()->create();

    DB::table('region_product_stocks')->insert([
        'region_id' => $source->id,
        'product_id' => $product->id,
        'quantity' => 8,
    ]);

    Livewire::actingAs($admin)
        ->test(OrganizeRegions::class, ['record' => $site->getRouteKey()])
        ->set('sourceRegionId', $source->id)
        ->set('destinationRegionId', $destination->id)
        ->set("quantities.{$product->id}", 3)
        ->call('transferToDestination', $product->id);

    expect(DB::table('region_product_stocks')->where('region_id', $source->id)->where('product_id', $product->id)->value('quantity'))->toBe(5)
        ->and(DB::table('region_product_stocks')->where('region_id', $destination->id)->where('product_id', $product->id)->value('quantity'))->toBe(3)
        ->and(DB::table('region_product_transfers')->where('product_id', $product->id)->where('created_by', $admin->id)->where('quantity', 3)->exists())->toBeTrue();
});

it('lists a product that only exists in one of the two regions with a zero quantity on the other side', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $site = Site::factory()->create();
    $source = Region::factory()->create(['site_id' => $site->id]);
    $destination = Region::factory()->create(['site_id' => $site->id]);
    $product = Product::factory()->create();

    DB::table('region_product_stocks')->insert([
        'region_id' => $destination->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $component = Livewire::actingAs($admin)
        ->test(OrganizeRegions::class, ['record' => $site->getRouteKey()])
        ->set('sourceRegionId', $source->id)
        ->set('destinationRegionId', $destination->id);

    $row = $component->instance()->getRows()->firstWhere('product_id', $product->id);

    expect($row->source_quantity)->toBe(0)
        ->and($row->destination_quantity)->toBe(3);
});
