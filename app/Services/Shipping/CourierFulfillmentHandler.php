<?php

namespace App\Services\Shipping;

use Illuminate\Validation\Rule;

class CourierFulfillmentHandler implements ShippingFulfillmentHandler
{
    public function rules(): array
    {
        return [
            'shipping_country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_zip' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:2000'],
        ];
    }

    public function resolve(array $validated): array
    {
        return [
            'delivery_point' => null,
            'shipping' => [
                'shipping_country_id' => $validated['shipping_country_id'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_zip' => $validated['shipping_zip'],
                'shipping_address' => $validated['shipping_address'],
            ],
        ];
    }
}
