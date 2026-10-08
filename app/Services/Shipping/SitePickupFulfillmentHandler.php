<?php

namespace App\Services\Shipping;

use App\Models\DeliveryPoint;
use App\Models\Site;
use Illuminate\Validation\Rule;

class SitePickupFulfillmentHandler implements ShippingFulfillmentHandler
{
    public function rules(): array
    {
        return [
            'site_id' => [
                'required',
                'integer',
                Rule::exists('sites', 'id')->where('is_active', true)->where('is_pickup_point', true),
            ],
        ];
    }

    public function resolve(array $validated): array
    {
        $site = Site::findOrFail($validated['site_id']);

        $deliveryPoint = DeliveryPoint::create([
            'type' => 'site',
            'reference_id' => (string) $site->id,
            'name' => $site->name,
            'zip' => $site->zip,
            'city' => $site->city,
            'address' => $site->address,
            'raw_payload' => $site->only(['id', 'name', 'country', 'city', 'zip', 'address']),
        ]);

        return [
            'delivery_point' => $deliveryPoint,
            'shipping' => [
                'shipping_country_id' => null,
                'shipping_city' => $site->city,
                'shipping_zip' => $site->zip,
                'shipping_address' => trim($site->name.' — '.$site->address, ' —'),
            ],
        ];
    }
}
