<?php

namespace App\Services\Shipping;

interface ShippingFulfillmentHandler
{
    /**
     * Extra validation rules for the fields this fulfillment type needs,
     * on top of the base StoreOrderRequest rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array;

    /**
     * Resolve the delivery point (if any) and the shipping_* fields to persist on the order.
     *
     * @param  array<string, mixed>  $validated
     * @return array{delivery_point: ?\App\Models\DeliveryPoint, shipping: array<string, mixed>}
     */
    public function resolve(array $validated): array;
}
