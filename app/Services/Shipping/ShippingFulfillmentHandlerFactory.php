<?php

namespace App\Services\Shipping;

use App\Enums\ShippingFulfillmentType;
use App\Models\ShippingMethod;
use RuntimeException;

class ShippingFulfillmentHandlerFactory
{
    public static function make(ShippingMethod $shippingMethod): ShippingFulfillmentHandler
    {
        return match ($shippingMethod->fulfillment_type) {
            ShippingFulfillmentType::Courier => new CourierFulfillmentHandler,
            ShippingFulfillmentType::SitePickup => new SitePickupFulfillmentHandler,
            ShippingFulfillmentType::ParcelLocker => match ($shippingMethod->locker_provider) {
                'foxpost' => new FoxpostFulfillmentHandler,
                default => throw new RuntimeException("Unsupported locker provider: {$shippingMethod->locker_provider}"),
            },
        };
    }
}
