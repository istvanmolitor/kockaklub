<?php

namespace App\Repositories;

use App\Models\PurchaseStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockRepository
{
    /**
     * Total quantity moved into $regionId for $productId, from the stock
     * movement history.
     */
    public function quantityMovedIn(int $productId, int $regionId, ?int $excludingMovementId = null): int
    {
        return (int) DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->where('stock_movement_items.product_id', $productId)
            ->where('stock_movements.destination_region_id', $regionId)
            ->whereNotNull('stock_movements.closed_at')
            ->when($excludingMovementId, fn (Builder $query) => $query->where('stock_movements.id', '!=', $excludingMovementId))
            ->sum('stock_movement_items.quantity');
    }

    /**
     * Total quantity moved out of $regionId for $productId, from the stock
     * movement history.
     */
    public function quantityMovedOut(int $productId, int $regionId, ?int $excludingMovementId = null): int
    {
        return (int) DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->where('stock_movement_items.product_id', $productId)
            ->where('stock_movements.source_region_id', $regionId)
            ->whereNotNull('stock_movements.closed_at')
            ->when($excludingMovementId, fn (Builder $query) => $query->where('stock_movements.id', '!=', $excludingMovementId))
            ->sum('stock_movement_items.quantity');
    }

    /**
     * The current quantity snapshot for $productId inside $regionId, read
     * from the region_product_stocks snapshot.
     */
    public function quantityFor(int $regionId, int $productId): int
    {
        return (int) DB::table('region_product_stocks')
            ->where('region_id', $regionId)
            ->where('product_id', $productId)
            ->value('quantity');
    }

    /**
     * Adds $delta (negative to subtract) to the region_product_stocks
     * snapshot for $regionId/$productId, creating the row if it doesn't
     * exist yet. Locks the row for the duration of the transaction so
     * concurrent adjustments don't race.
     */
    public function adjustStock(int $regionId, int $productId, int $delta): void
    {
        DB::transaction(function () use ($regionId, $productId, $delta) {
            $row = DB::table('region_product_stocks')
                ->where('region_id', $regionId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if ($row) {
                DB::table('region_product_stocks')
                    ->where('region_id', $regionId)
                    ->where('product_id', $productId)
                    ->update(['quantity' => $row->quantity + $delta]);
            } else {
                DB::table('region_product_stocks')->insert([
                    'region_id' => $regionId,
                    'product_id' => $productId,
                    'quantity' => $delta,
                ]);
            }
        });
    }

    /**
     * Raw region_product_stocks rows (quantity > 0) for any of $regionIds.
     *
     * @param  array<int, int>  $regionIds
     * @return Collection<int, object>
     */
    public function quantitiesForRegions(array $regionIds): Collection
    {
        return DB::table('region_product_stocks')
            ->whereIn('region_id', $regionIds)
            ->where('quantity', '>', 0)
            ->get(['region_id', 'product_id', 'quantity']);
    }

    /**
     * The aggregated quantity of $productId across every public region,
     * read from the region_product_stocks snapshot.
     */
    public function publicStockForProduct(int $productId): int
    {
        return (int) DB::table('region_product_stocks')
            ->join('regions', 'regions.id', '=', 'region_product_stocks.region_id')
            ->where('region_product_stocks.product_id', $productId)
            ->where('regions.is_public', true)
            ->sum('region_product_stocks.quantity');
    }

    /**
     * Public regions of $siteId that currently hold stock of $productId, ordered
     * by quantity ascending (smallest box first) — the depletion order used when
     * fulfilling an order.
     *
     * @return Collection<int, object>
     */
    public function publicRegionStocksForProduct(int $productId, int $siteId): Collection
    {
        return DB::table('region_product_stocks')
            ->join('regions', 'regions.id', '=', 'region_product_stocks.region_id')
            ->where('region_product_stocks.product_id', $productId)
            ->where('regions.site_id', $siteId)
            ->where('regions.is_public', true)
            ->where('region_product_stocks.quantity', '>', 0)
            ->orderBy('region_product_stocks.quantity')
            ->get([
                'regions.id as region_id',
                'regions.name as region_name',
                'region_product_stocks.quantity',
            ]);
    }

    /**
     * Total quantity of $productId sitting on orders that have not been
     * reserved yet (orders.reserved_at is null) — stock already promised
     * but not yet taken off the shelf.
     */
    public function pendingReservationQuantityForProduct(int $productId): int
    {
        return (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
            ->whereNull('orders.reserved_at')
            ->sum('order_items.quantity');
    }

    /**
     * Correlated subquery expression for "sum of public-region stock" per product,
     * for use with Product::query()->addSelect(['public_stock' => StockRepository::publicStockSubquery()]).
     * Correlates to the outer `products.id` column via whereColumn.
     */
    public static function publicStockSubquery(): Builder
    {
        return DB::table('region_product_stocks')
            ->join('regions', 'regions.id', '=', 'region_product_stocks.region_id')
            ->whereColumn('region_product_stocks.product_id', 'products.id')
            ->where('regions.is_public', true)
            ->selectRaw('COALESCE(SUM(region_product_stocks.quantity), 0)');
    }

    /**
     * Correlated subquery expression for "quantity sitting on unreserved orders"
     * per product, for use with
     * Product::query()->addSelect(['pending_reservation' => StockRepository::pendingReservationSubquery()]).
     * Correlates to the outer `products.id` column via whereColumn.
     */
    public static function pendingReservationSubquery(): Builder
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereColumn('order_items.product_id', 'products.id')
            ->whereNull('orders.reserved_at')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');
    }

    /**
     * Correlated subquery expression for "quantity sitting on open purchases"
     * (ordered but not yet received or cancelled) per product, for use with
     * Product::query()->addSelect(['pending_purchase' => StockRepository::pendingPurchaseSubquery()]).
     * Correlates to the outer `products.id` column via whereColumn.
     */
    public static function pendingPurchaseSubquery(): Builder
    {
        return DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('purchase_statuses', 'purchase_statuses.id', '=', 'purchases.purchase_status_id')
            ->whereColumn('purchase_items.product_id', 'products.id')
            ->where('purchase_statuses.name', PurchaseStatus::ORDERED)
            ->selectRaw('COALESCE(SUM(purchase_items.quantity), 0)');
    }

    /**
     * Per region+product incoming quantity totals, from the stock movement
     * history (rows: region_id, product_id, quantity).
     *
     * @return Collection<int, object>
     */
    public function incomingMovementTotals(): Collection
    {
        return DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->whereNotNull('stock_movements.destination_region_id')
            ->whereNotNull('stock_movements.closed_at')
            ->groupBy('stock_movements.destination_region_id', 'stock_movement_items.product_id')
            ->get([
                'stock_movements.destination_region_id as region_id',
                'stock_movement_items.product_id',
                DB::raw('SUM(stock_movement_items.quantity) as quantity'),
            ]);
    }

    /**
     * Per region+product outgoing quantity totals, from the stock movement
     * history (rows: region_id, product_id, quantity).
     *
     * @return Collection<int, object>
     */
    public function outgoingMovementTotals(): Collection
    {
        return DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->whereNotNull('stock_movements.source_region_id')
            ->whereNotNull('stock_movements.closed_at')
            ->groupBy('stock_movements.source_region_id', 'stock_movement_items.product_id')
            ->get([
                'stock_movements.source_region_id as region_id',
                'stock_movement_items.product_id',
                DB::raw('SUM(stock_movement_items.quantity) as quantity'),
            ]);
    }

    /**
     * Replaces the entire region_product_stocks snapshot with $rows. The table
     * is a pure cache of the stock movement history, so it is safe to wipe and
     * reinsert wholesale on every rebuild.
     *
     * @param  array<int, array{region_id: int, product_id: int, quantity: int}>  $rows
     */
    public function replaceRegionProductStocks(array $rows): void
    {
        DB::transaction(function () use ($rows) {
            DB::table('region_product_stocks')->delete();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('region_product_stocks')->insert($chunk);
            }
        });
    }
}
