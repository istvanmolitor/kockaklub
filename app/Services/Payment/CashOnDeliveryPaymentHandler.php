<?php

namespace App\Services\Payment;

use App\Models\Order;

class CashOnDeliveryPaymentHandler implements PaymentHandler
{
    public function rules(): array
    {
        return [];
    }

    public function initiate(Order $order): ?string
    {
        return null;
    }
}
