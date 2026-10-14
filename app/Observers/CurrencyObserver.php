<?php

namespace App\Observers;

use App\Models\Currency;

class CurrencyObserver
{
    public function saved(Currency $currency): void
    {
        if ($currency->is_default) {
            Currency::whereKeyNot($currency->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
