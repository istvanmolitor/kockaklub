<?php

namespace App\Services;

use App\Repositories\StockRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function __construct(private readonly StockRepository $stock) {}

    /**
     * Current quantity of $productId inside $regionId, derived from the stock
     * movement history (incoming movements into the region minus outgoing
     * movements out of the region).
     */
    public function quantityInRegion(int $productId, int $regionId, ?int $excludingMovementId = null): int
    {
        $incoming = $this->stock->quantityMovedIn($productId, $regionId, $excludingMovementId);
        $outgoing = $this->stock->quantityMovedOut($productId, $regionId, $excludingMovementId);

        return $incoming - $outgoing;
    }

    /**
     * The aggregated quantity of $productId across every public region, read
     * from the region_product_stocks snapshot (see rebuildRegionProductStocks()).
     */
    public function publicStockForProduct(int $productId): int
    {
        return $this->stock->publicStockForProduct($productId);
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

        foreach ($this->stock->incomingMovementTotals() as $row) {
            $totals[$row->region_id][$row->product_id] = ($totals[$row->region_id][$row->product_id] ?? 0) + (int) $row->quantity;
        }

        foreach ($this->stock->outgoingMovementTotals() as $row) {
            $totals[$row->region_id][$row->product_id] = ($totals[$row->region_id][$row->product_id] ?? 0) - (int) $row->quantity;
        }

        DB::transaction(function () use ($totals) {
            $touchedIds = [];

            foreach ($totals as $regionId => $products) {
                foreach ($products as $productId => $quantity) {
                    $stock = $this->stock->upsertRegionProductStockQuantity($regionId, $productId, $quantity);

                    $touchedIds[] = $stock->id;
                }
            }

            $this->stock->zeroOutStocksExcept($touchedIds);
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
