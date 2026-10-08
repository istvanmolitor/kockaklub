<?php

namespace App\Services\Shipping;

use App\Models\DeliveryPoint;
use Illuminate\Validation\ValidationException;

class FoxpostFulfillmentHandler implements ShippingFulfillmentHandler
{
    public function rules(): array
    {
        return [
            'delivery_point_payload' => ['required', 'string'],
        ];
    }

    public function resolve(array $validated): array
    {
        $payload = json_decode($validated['delivery_point_payload'], true);

        if (! is_array($payload) || blank($payload['place_id'] ?? null) || blank($payload['name'] ?? null)) {
            throw ValidationException::withMessages([
                'delivery_point_payload' => 'Válassz ki egy csomagautomatát a térképen.',
            ]);
        }

        $deliveryPoint = DeliveryPoint::create([
            'type' => 'foxpost',
            'reference_id' => (string) $payload['place_id'],
            'name' => $payload['name'],
            'zip' => $payload['zip'] ?? null,
            'city' => $payload['city'] ?? null,
            'address' => $payload['address'] ?? $payload['street'] ?? null,
            'lat' => $payload['geolat'] ?? null,
            'lng' => $payload['geolng'] ?? null,
            'raw_payload' => $payload,
        ]);

        return [
            'delivery_point' => $deliveryPoint,
            'shipping' => [
                'shipping_country_id' => null,
                'shipping_city' => $deliveryPoint->city,
                'shipping_zip' => $deliveryPoint->zip,
                'shipping_address' => trim($deliveryPoint->name.' ('.$deliveryPoint->address.')'),
            ],
        ];
    }
}
