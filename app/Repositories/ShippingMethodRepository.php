<?php

namespace App\Repositories;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Collection;

class ShippingMethodRepository
{
    /**
     * Active shipping methods that have at least one active payment method,
     * for the checkout step where a customer must pick both.
     *
     * @return Collection<int, ShippingMethod>
     */
    public function activeWithAvailablePaymentMethods(): Collection
    {
        return ShippingMethod::query()
            ->where('is_active', true)
            ->with(['paymentMethods' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->filter(fn (ShippingMethod $shippingMethod) => $shippingMethod->paymentMethods->isNotEmpty())
            ->values();
    }
}
