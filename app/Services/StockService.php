<?php

namespace App\Services;

use App\Models\RegionProductStock;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Current quantity of $productId inside $regionId, derived from the stock
     * movement history (incoming movements into the region minus outgoing
     * movements out of the region).
     */
    public function quantityInRegion(int $productId, int $regionId, ?int $excludingMovementId = null): int
    {
        $incoming = DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->where('stock_movement_items.product_id', $productId)
            ->where('stock_movements.destination_region_id', $regionId)
            ->when($excludingMovementId, fn (Builder $query) => $query->where('stock_movements.id', '!=', $excludingMovementId))
            ->sum('stock_movement_items.quantity');

        $outgoing = DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->where('stock_movement_items.product_id', $productId)
            ->where('stock_movements.source_region_id', $regionId)
            ->when($excludingMovementId, fn (Builder $query) => $query->where('stock_movements.id', '!=', $excludingMovementId))
            ->sum('stock_movement_items.quantity');

        return (int) $incoming - (int) $outgoing;
    }

    /**
     * The aggregated quantity of $productId across every public region,
     * read from the region_product_stocks snapshot (see rebuildRegionProductStocks()).
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
     * for use with Product::query()->addSelect(['public_stock' => StockService::publicStockSubquery()]).
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
     * Recomputes the `quantity` column of every region_product_stocks row from the
     * stock movement history (incoming minus outgoing per region+product), without
     * touching the admin-managed `min_stock`/`max_stock` thresholds. Pairs with no
     * movement history left are zeroed out rather than deleted, so a configured
     * threshold survives even if the stock temporarily disappears.
     */
    public function rebuildRegionProductStocks(): void
    {
        $totals = [];

        $incoming = DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->whereNotNull('stock_movements.destination_region_id')
            ->groupBy('stock_movements.destination_region_id', 'stock_movement_items.product_id')
            ->get([
                'stock_movements.destination_region_id as region_id',
                'stock_movement_items.product_id',
                DB::raw('SUM(stock_movement_items.quantity) as quantity'),
            ]);

        $outgoing = DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->whereNotNull('stock_movements.source_region_id')
            ->groupBy('stock_movements.source_region_id', 'stock_movement_items.product_id')
            ->get([
                'stock_movements.source_region_id as region_id',
                'stock_movement_items.product_id',
                DB::raw('SUM(stock_movement_items.quantity) as quantity'),
            ]);

        foreach ($incoming as $row) {
            $totals[$row->region_id][$row->product_id] = ($totals[$row->region_id][$row->product_id] ?? 0) + (int) $row->quantity;
        }

        foreach ($outgoing as $row) {
            $totals[$row->region_id][$row->product_id] = ($totals[$row->region_id][$row->product_id] ?? 0) - (int) $row->quantity;
        }

        DB::transaction(function () use ($totals) {
            $touchedIds = [];

            foreach ($totals as $regionId => $products) {
                foreach ($products as $productId => $quantity) {
                    $stock = RegionProductStock::query()->updateOrCreate(
                        ['region_id' => $regionId, 'product_id' => $productId],
                        ['quantity' => $quantity],
                    );

                    $touchedIds[] = $stock->id;
                }
            }

            RegionProductStock::query()
                ->whereNotIn('id', $touchedIds ?: [0])
                ->where('quantity', '!=', 0)
                ->update(['quantity' => 0]);
        });
    }

    /**
     * Throws if posting $quantity out of $regionId for $productId would push the
     * region's stock for that product below zero.
     */
    public function assertCanRemove(int $productId, int $regionId, int $quantity, ?int $excludingMovementId = null): void
    {
        $available = $this->quantityInRegion($productId, $regionId, $excludingMovementId);

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'A kiválasztott régióban nincs elég készlet (elérhető: %d db, szükséges: %d db).',
                    $available,
                    $quantity
                ),
            ]);
        }
    }
}
