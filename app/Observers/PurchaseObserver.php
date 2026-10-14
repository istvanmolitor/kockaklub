<?php

namespace App\Observers;

use App\Models\Purchase;

class PurchaseObserver
{
    public function saved(Purchase $purchase): void
    {
        $purchase->recalculateItemUnitCosts();
    }
}
