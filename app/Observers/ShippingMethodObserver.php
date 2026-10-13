<?php

namespace App\Observers;

use App\Models\ShippingMethod;

class ShippingMethodObserver
{
    public function saved(ShippingMethod $method): void
    {
        if ($method->is_default) {
            ShippingMethod::whereKeyNot($method->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
