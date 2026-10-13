<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PayPalClient
{
    private readonly string $clientId;

    private readonly string $clientSecret;

    private readonly string $baseUrl;

    public function __construct()
    {
        $this->clientId = (string) config('services.paypal.client_id');
        $this->clientSecret = (string) config('services.paypal.client_secret');
        $this->baseUrl = config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * @return array<string, mixed>
     */
    public function createOrder(Order $order): array
    {
        return Http::withToken($this->fetchAccessToken())
            ->acceptJson()
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $order->order_number,
                    'amount' => [
                        'currency_code' => 'HUF',
                        'value' => (string) $order->total,
                    ],
                ]],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('payment.paypal.success', $order),
                    'cancel_url' => route('payment.paypal.cancel', $order),
                ],
            ])
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function captureOrder(string $paypalOrderId): array
    {
        return Http::withToken($this->fetchAccessToken())
            ->acceptJson()
            ->post("{$this->baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture")
            ->throw()
            ->json();
    }

    private function fetchAccessToken(): string
    {
        return Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials'])
            ->throw()
            ->json('access_token');
    }
}
