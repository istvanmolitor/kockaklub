<?php

namespace App\Repositories;

use App\Models\OrderItem;
use Illuminate\Support\Collection;

class OrderRepository
{
    /**
     * Product ids the given customer has already ordered.
     *
     * @return Collection<int, int>
     */
    public function productIdsPurchasedByCustomer(int $customerId): Collection
    {
        return OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('customer_id', $customerId))
            ->pluck('product_id');
    }
}
