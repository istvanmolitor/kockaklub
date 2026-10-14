<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseStatus;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * Marks $purchase as received: creates the StockMovement (type IN, no
     * destination region yet) and one StockMovementItem per purchase item,
     * then stamps received_at/received_by and the received status on the
     * purchase. The destination region is chosen later, when the created
     * movement is closed (see StockService::closeMovement()).
     */
    public function receive(Purchase $purchase): StockMovement
    {
        if ($purchase->isReceived()) {
            return $purchase->stockMovement;
        }

        if ($purchase->items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'A beérkeztetéshez legalább egy tétel szükséges.',
            ]);
        }

        if ($purchase->paid_amount_default_currency === null) {
            throw ValidationException::withMessages([
                'paid_amount_default_currency' => 'A beérkeztetéshez ki kell tölteni a kifizetett összeget forintban.',
            ]);
        }

        return DB::transaction(function () use ($purchase) {
            $movement = StockMovement::create([
                'type' => StockMovement::TYPE_IN,
                'movement_date' => now()->toDateString(),
                'note' => sprintf('Beszerzés #%d beérkeztetése', $purchase->id),
                'created_by' => Auth::id(),
            ]);

            foreach ($purchase->items as $item) {
                $movement->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ]);
            }

            $purchase->update([
                'received_at' => now(),
                'received_by' => Auth::id(),
                'purchase_status_id' => PurchaseStatus::findByName(PurchaseStatus::RECEIVED)->id,
                'stock_movement_id' => $movement->id,
            ]);

            return $movement;
        });
    }
}
