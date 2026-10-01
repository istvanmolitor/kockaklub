<?php

namespace App\Repositories;

use App\Models\RegionProductStock;
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
            ->when($excludingMovementId, fn (Builder $query) => $query->where('stock_movements.id', '!=', $excludingMovementId))
            ->sum('stock_movement_items.quantity');
    }

    /**
     * The aggregated quantity of $productId across every public region,
     * read from the region_product_stocks snapshot.
     */
    public function publicStockForProduct(int $productId): int
    {
        return (int) RegionProductStock::query()
            ->join('regions', 'regions.id', '=', 'region_product_stocks.region_id')
            ->where('region_product_stocks.product_id', $productId)
            ->where('regions.is_public', true)
            ->sum('region_product_stocks.quantity');
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
            ->groupBy('stock_movements.source_region_id', 'stock_movement_items.product_id')
            ->get([
                'stock_movements.source_region_id as region_id',
                'stock_movement_items.product_id',
                DB::raw('SUM(stock_movement_items.quantity) as quantity'),
            ]);
    }

    public function upsertRegionProductStockQuantity(int $regionId, int $productId, int $quantity): RegionProductStock
    {
        return RegionProductStock::query()->updateOrCreate(
            ['region_id' => $regionId, 'product_id' => $productId],
            ['quantity' => $quantity],
        );
    }

    /**
     * Zeroes out the quantity of every region_product_stocks row not in
     * $touchedIds, without deleting the row (so configured min/max thresholds
     * survive even if the stock temporarily disappears).
     *
     * @param  array<int, int>  $touchedIds
     */
    public function zeroOutStocksExcept(array $touchedIds): void
    {
        RegionProductStock::query()
            ->whereNotIn('id', $touchedIds ?: [0])
            ->where('quantity', '!=', 0)
            ->update(['quantity' => 0]);
    }
}
