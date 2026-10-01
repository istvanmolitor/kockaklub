<?php

namespace App\Observers;

use App\Models\OrderStatus;

class OrderStatusObserver
{
    public function saved(OrderStatus $status): void
    {
        if ($status->is_default) {
            OrderStatus::whereKeyNot($status->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
