<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    public function created(Product $product): void
    {
        $product->priceLogs()->create([
            'price' => $product->price,
            'previous_price' => null,
            'user_id' => Auth::id(),
        ]);
    }

    public function updated(Product $product): void
    {
        if (! $product->wasChanged('price')) {
            return;
        }

        $product->priceLogs()->create([
            'price' => $product->price,
            'previous_price' => $product->getOriginal('price'),
            'user_id' => Auth::id(),
        ]);
    }
}
