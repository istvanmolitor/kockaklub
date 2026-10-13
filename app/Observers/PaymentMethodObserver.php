<?php

namespace App\Observers;

use App\Models\PaymentMethod;

class PaymentMethodObserver
{
    public function saved(PaymentMethod $method): void
    {
        if ($method->is_default) {
            PaymentMethod::whereKeyNot($method->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
