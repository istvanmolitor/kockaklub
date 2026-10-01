<?php

namespace App\Services;

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
     * derived from the stock movement history.
     */
    public function publicStockForProduct(int $productId): int
    {
        $incoming = DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->join('regions', 'regions.id', '=', 'stock_movements.destination_region_id')
            ->where('stock_movement_items.product_id', $productId)
            ->where('regions.is_public', true)
            ->sum('stock_movement_items.quantity');

        $outgoing = DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->join('regions', 'regions.id', '=', 'stock_movements.source_region_id')
            ->where('stock_movement_items.product_id', $productId)
            ->where('regions.is_public', true)
            ->sum('stock_movement_items.quantity');

        return (int) $incoming - (int) $outgoing;
    }

    /**
     * Correlated subquery expression for "sum of public-region stock" per product,
     * for use with Product::query()->addSelect(['public_stock' => StockService::publicStockSubquery()]).
     * Correlates to the outer `products.id` column via whereColumn.
     */
    public static function publicStockSubquery(): Builder
    {
        return DB::table('stock_movement_items')
            ->join('stock_movements', 'stock_movements.id', '=', 'stock_movement_items.stock_movement_id')
            ->leftJoin('regions as destination_regions', 'destination_regions.id', '=', 'stock_movements.destination_region_id')
            ->leftJoin('regions as source_regions', 'source_regions.id', '=', 'stock_movements.source_region_id')
            ->whereColumn('stock_movement_items.product_id', 'products.id')
            ->selectRaw(
                'COALESCE(SUM('.
                'CASE WHEN destination_regions.is_public = 1 THEN stock_movement_items.quantity ELSE 0 END - '.
                'CASE WHEN source_regions.is_public = 1 THEN stock_movement_items.quantity ELSE 0 END'.
                '), 0)'
            );
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
