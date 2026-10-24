<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Mail\PaymentUpdateMail;
use App\Models\Order;
use App\Models\OrderPayment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PaymentStatusService
{
    public function recordPayment(
        Order $order,
        int $amount,
        ?CarbonInterface $receivedAt = null,
        ?string $reference = null,
        ?string $note = null,
    ): OrderPayment {
        $payment = $this->createPayment($order, $amount, $receivedAt, $reference, $note);

        $this->notify($order, payment: $payment);

        return $payment;
    }

    public function setStatus(Order $order, PaymentStatus $status): Order
    {
        $previousStatus = $this->applyStatus($order, $status);

        if ($previousStatus !== null) {
            $this->notify($order, previousStatus: $previousStatus);
        }

        return $order;
    }

    public function recordPaymentAndSetStatus(
        Order $order,
        int $amount,
        PaymentStatus $status,
        ?CarbonInterface $receivedAt = null,
        ?string $reference = null,
        ?string $note = null,
    ): Order {
        $payment = $this->createPayment($order, $amount, $receivedAt, $reference, $note);
        $previousStatus = $this->applyStatus($order, $status);

        $this->notify($order, payment: $payment, previousStatus: $previousStatus);

        return $order;
    }

    private function createPayment(
        Order $order,
        int $amount,
        ?CarbonInterface $receivedAt,
        ?string $reference,
        ?string $note,
    ): OrderPayment {
        return $order->payments()->create([
            'amount' => $amount,
            'received_at' => $receivedAt ?? now(),
            'reference' => $reference,
            'note' => $note,
            'user_id' => Auth::id(),
        ]);
    }

    private function applyStatus(Order $order, PaymentStatus $status): ?PaymentStatus
    {
        if ($order->payment_status === $status) {
            return null;
        }

        $previousStatus = $order->payment_status;

        $order->update([
            'payment_status' => $status,
            'paid_at' => $status === PaymentStatus::Paid ? now() : null,
        ]);

        return $previousStatus;
    }

    private function notify(Order $order, ?OrderPayment $payment = null, ?PaymentStatus $previousStatus = null): void
    {
        Mail::to($order->customer->email)->queue(new PaymentUpdateMail($order, $payment, $previousStatus));
    }
}
