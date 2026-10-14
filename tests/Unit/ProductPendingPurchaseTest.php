<?php

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseStatus;
use App\Models\Site;

function createPurchase(PurchaseStatus $status): Purchase
{
    return Purchase::create([
        'customer_id' => Customer::factory()->create()->id,
        'destination_site_id' => Site::factory()->create()->id,
        'currency_id' => Currency::query()->firstOrCreate(['code' => 'HUF'], ['name' => 'Forint'])->id,
        'ordered_at' => now(),
        'purchase_status_id' => $status->id,
    ]);
}

it('counts quantity from ordered purchases as pending purchase', function () {
    $product = Product::factory()->create();
    $ordered = PurchaseStatus::firstOrCreate(['name' => PurchaseStatus::ORDERED]);

    $purchase = createPurchase($ordered);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'unit_price' => 10,
    ]);

    $result = Product::query()->withPendingPurchase()->find($product->id);

    expect((int) $result->pending_purchase)->toBe(5);
});

it('excludes received and cancelled purchases from pending purchase', function () {
    $product = Product::factory()->create();
    $received = PurchaseStatus::firstOrCreate(['name' => PurchaseStatus::RECEIVED]);
    $cancelled = PurchaseStatus::firstOrCreate(['name' => PurchaseStatus::CANCELLED]);

    foreach ([$received, $cancelled] as $status) {
        $purchase = createPurchase($status);
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 7,
            'unit_price' => 10,
        ]);
    }

    $result = Product::query()->withPendingPurchase()->find($product->id);

    expect((int) $result->pending_purchase)->toBe(0);
});
