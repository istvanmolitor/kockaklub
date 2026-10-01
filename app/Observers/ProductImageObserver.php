<?php

namespace App\Observers;

use App\Models\ProductImage;

class ProductImageObserver
{
    public function creating(ProductImage $image): void
    {
        if (! $image->product->images()->exists()) {
            $image->is_default = true;
        }
    }

    public function saved(ProductImage $image): void
    {
        if ($image->is_default) {
            $image->product->images()
                ->whereKeyNot($image->id)
                ->update(['is_default' => false]);
        }
    }

    public function deleted(ProductImage $image): void
    {
        if (! $image->is_default) {
            return;
        }

        $next = $image->product->images()->orderBy('sort_order')->first();

        if ($next) {
            $next->update(['is_default' => true]);
        }
    }
}
