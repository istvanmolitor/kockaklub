<?php

namespace App\Enums;

enum ShippingFulfillmentType: string
{
    case Courier = 'courier';
    case SitePickup = 'site_pickup';
    case ParcelLocker = 'parcel_locker';

    public function label(): string
    {
        return match ($this) {
            self::Courier => 'Futár',
            self::SitePickup => 'Telephelyi átvétel',
            self::ParcelLocker => 'Csomagautomata',
        };
    }
}
