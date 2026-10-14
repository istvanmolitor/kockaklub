<?php

namespace App\Observers;

use App\Models\PurchaseItem;

class PurchaseItemObserver
{
    public function saved(PurchaseItem $item): void
    {
        $item->purchase?->recalculateItemUnitCosts();
    }

    public function deleted(PurchaseItem $item): void
    {
        $item->purchase?->recalculateItemUnitCosts();
    }
}
