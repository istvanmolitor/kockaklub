<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Repositories\StockRepository;
use Illuminate\Support\Facades\Auth;
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
     * Recomputes the entire region_product_stocks snapshot from the stock movement
     * history (incoming minus outgoing per region+product). The table is a pure
     * cache, so it is wiped and reinserted wholesale on every rebuild.
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

        $rows = [];

        foreach ($totals as $regionId => $products) {
            foreach ($products as $productId => $quantity) {
                $rows[] = ['region_id' => $regionId, 'product_id' => $productId, 'quantity' => $quantity];
            }
        }

        $this->stock->replaceRegionProductStocks($rows);
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

    /**
     * Closes $movement: validates there is enough stock for OUT/TRANSFER
     * movements, stamps closed_at/closed_by, and recomputes the
     * region_product_stocks snapshot so the movement finally affects stock.
     */
    public function closeMovement(StockMovement $movement): void
    {
        if ($movement->isClosed()) {
            return;
        }

        if (in_array($movement->type, [StockMovement::TYPE_OUT, StockMovement::TYPE_TRANSFER], true)) {
            $quantitiesByProduct = $movement->items
                ->groupBy('product_id')
                ->map(fn ($items) => $items->sum('quantity'));

            foreach ($quantitiesByProduct as $productId => $quantity) {
                $this->assertCanRemove((int) $productId, $movement->source_region_id, (int) $quantity);
            }
        }

        DB::transaction(function () use ($movement) {
            $movement->update([
                'closed_at' => now(),
                'closed_by' => Auth::id(),
            ]);

            $this->rebuildRegionProductStocks();
        });
    }
}
