<?php

use App\Filament\Resources\Orders\Pages\ReserveOrderItems;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Region;
use App\Models\Site;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

function seedPublicStock(Region $region, Product $product, int $quantity): void
{
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

function makeOrderWithItem(Site $site, Product $product, int $quantity): Order
{
    $order = Order::factory()->create([
        'site_id' => $site->id,
        'customer_id' => Customer::factory(),
        'order_status_id' => OrderStatus::factory(),
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => $quantity,
    ]);

    return $order;
}

it('reserves order items from the smallest public region first', function () {
    $site = Site::factory()->create();
    $small = Region::factory()->create(['site_id' => $site->id, 'name' => 'Kicsi', 'is_public' => true]);
    $big = Region::factory()->create(['site_id' => $site->id, 'name' => 'Nagy', 'is_public' => true]);
    $warehouse = Region::factory()->create(['site_id' => $site->id, 'name' => 'Raktár', 'is_public' => false]);
    $product = Product::factory()->create();

    seedPublicStock($small, $product, 3);
    seedPublicStock($big, $product, 10);
    seedPublicStock($warehouse, $product, 100);

    $order = makeOrderWithItem($site, $product, 5);

    livewire(ReserveOrderItems::class, ['record' => $order->getKey()])
        ->callAction('reserve')
        ->assertNotified();

    expect($order->fresh())
        ->isReserved()->toBeTrue();

    $stock = fn (Region $region) => DB::table('region_product_stocks')
        ->where('region_id', $region->id)
        ->where('product_id', $product->id)
        ->value('quantity');

    expect($stock($small))->toBe(0)
        ->and($stock($big))->toBe(8)
        ->and($stock($warehouse))->toBe(100);

    expect(StockMovement::query()->where('type', StockMovement::TYPE_OUT)->count())->toBe(2);
});

it('does not reserve or touch stock when public stock is insufficient', function () {
    $site = Site::factory()->create();
    $small = Region::factory()->create(['site_id' => $site->id, 'name' => 'Kicsi', 'is_public' => true]);
    $warehouse = Region::factory()->create(['site_id' => $site->id, 'name' => 'Raktár', 'is_public' => false]);
    $product = Product::factory()->create();

    seedPublicStock($small, $product, 3);
    seedPublicStock($warehouse, $product, 100);

    $order = makeOrderWithItem($site, $product, 20);

    livewire(ReserveOrderItems::class, ['record' => $order->getKey()])
        ->callAction('reserve')
        ->assertNotified();

    expect($order->fresh()->isReserved())->toBeFalse();

    expect(DB::table('region_product_stocks')->where('region_id', $small->id)->value('quantity'))->toBe(3);
    expect(StockMovement::query()->where('type', StockMovement::TYPE_OUT)->count())->toBe(0);
});
