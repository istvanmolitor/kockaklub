<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    public function created(Order $order): void
    {
        $order->statusLogs()->create([
            'order_status_id' => $order->order_status_id,
            'previous_order_status_id' => null,
            'user_id' => Auth::id(),
        ]);
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status_id')) {
            return;
        }

        $order->statusLogs()->create([
            'order_status_id' => $order->order_status_id,
            'previous_order_status_id' => $order->getOriginal('order_status_id'),
            'user_id' => Auth::id(),
        ]);
    }
}
