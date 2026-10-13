<?php

namespace App\Services\Payment;

use App\Models\Order;

class PayPalPaymentHandler implements PaymentHandler
{
    public function __construct(private readonly PayPalClient $client) {}

    public function rules(): array
    {
        return [];
    }

    public function initiate(Order $order): ?string
    {
        $response = $this->client->createOrder($order);

        $order->update(['payment_reference' => $response['id']]);

        $approveLink = collect($response['links'] ?? [])->firstWhere('rel', 'approve');

        return $approveLink['href'] ?? null;
    }
}
