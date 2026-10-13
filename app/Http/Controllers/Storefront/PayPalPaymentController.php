<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PayPalClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class PayPalPaymentController extends Controller
{
    public function __construct(private readonly PayPalClient $client) {}

    public function success(Order $order): RedirectResponse
    {
        if ($order->isPaid()) {
            return redirect()->route('checkout.confirmation', $order);
        }

        if (blank($order->payment_reference)) {
            return redirect()->route('checkout.confirmation', $order)
                ->with('status', 'Nem található folyamatban lévő PayPal fizetés ehhez a rendeléshez.');
        }

        try {
            $response = $this->client->captureOrder($order->payment_reference);
        } catch (\Throwable $exception) {
            Log::error('PayPal capture failed.', [
                'order_id' => $order->id,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->route('checkout.confirmation', $order)
                ->with('status', 'A PayPal fizetés jóváhagyása sikertelen volt. Próbáld újra, vagy válassz másik fizetési módot.');
        }

        if (($response['status'] ?? null) === 'COMPLETED') {
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);
        }

        return redirect()->route('checkout.confirmation', $order);
    }

    public function cancel(Order $order): RedirectResponse
    {
        return redirect()->route('checkout.confirmation', $order)
            ->with('status', 'A PayPal fizetést megszakítottad. A rendelésed rögzítettük, fizetésre vár.');
    }
}
