<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Repositories\StockRepository;
use Illuminate\Support\Collection;
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
     * How much of $productId can still be promised to a new order: the public
     * stock minus what's already sitting on orders that exist but have not
     * been reserved (picked off the shelf) yet.
     */
    public function freeStockForProduct(int $productId): int
    {
        $available = $this->publicStockForProduct($productId) - $this->stock->pendingReservationQuantityForProduct($productId);

        return max(0, $available);
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

    /**
     * For each item of $order, works out which public region(s) of the
     * order's site the quantity would be taken from if it were reserved now
     * — smallest-stocked region first — and how much (if any) can't be
     * covered by public stock at all.
     *
     * @return Collection<int, array{item: OrderItem, allocations: array<int, array{region_id: int, region_name: string, quantity: int}>, shortfall: int}>
     */
    public function planReservation(Order $order): Collection
    {
        return $order->items->map(function (OrderItem $item) use ($order) {
            $remaining = $item->quantity;
            $allocations = [];

            foreach ($this->stock->publicRegionStocksForProduct($item->product_id, $order->site_id) as $regionStock) {
                if ($remaining <= 0) {
                    break;
                }

                $quantity = min($remaining, (int) $regionStock->quantity);

                $allocations[] = [
                    'region_id' => (int) $regionStock->region_id,
                    'region_name' => $regionStock->region_name,
                    'quantity' => $quantity,
                ];

                $remaining -= $quantity;
            }

            return [
                'item' => $item,
                'allocations' => $allocations,
                'shortfall' => $remaining,
            ];
        });
    }

    /**
     * Picks $order's items off the shelf: deducts the quantities from the
     * public regions of the order's site (smallest region first, per
     * planReservation()) via closed StockMovements, and stamps reserved_at.
     * Does nothing if the order was already reserved. Throws if the order's
     * site doesn't have enough public stock for one or more items — in that
     * case nothing is deducted.
     */
    public function reserveOrder(Order $order): void
    {
        if ($order->isReserved()) {
            return;
        }

        $plan = $this->planReservation($order);

        $shortages = $plan->filter(fn (array $row) => $row['shortfall'] > 0);

        if ($shortages->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => $shortages
                    ->map(fn (array $row) => sprintf(
                        '%s: nincs elég publikus készlet (hiányzik %d db).',
                        $row['item']->product_name,
                        $row['shortfall']
                    ))
                    ->all(),
            ]);
        }

        DB::transaction(function () use ($order, $plan) {
            $quantitiesByRegion = [];

            foreach ($plan as $row) {
                foreach ($row['allocations'] as $allocation) {
                    $productId = $row['item']->product_id;
                    $quantitiesByRegion[$allocation['region_id']][$productId] =
                        ($quantitiesByRegion[$allocation['region_id']][$productId] ?? 0) + $allocation['quantity'];
                }
            }

            foreach ($quantitiesByRegion as $regionId => $quantitiesByProduct) {
                $movement = StockMovement::create([
                    'type' => StockMovement::TYPE_OUT,
                    'source_region_id' => $regionId,
                    'movement_date' => now(),
                    'note' => "Rendelés: {$order->order_number}",
                    'created_by' => Auth::id(),
                    'closed_by' => Auth::id(),
                    'closed_at' => now(),
                ]);

                foreach ($quantitiesByProduct as $productId => $quantity) {
                    $movement->items()->create([
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    ]);
                }
            }

            $this->rebuildRegionProductStocks();

            $order->update(['reserved_at' => now()]);
        });
    }
}
