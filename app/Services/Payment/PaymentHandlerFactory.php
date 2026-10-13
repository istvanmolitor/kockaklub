<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;

class PaymentHandlerFactory
{
    public static function make(PaymentMethod $paymentMethod): PaymentHandler
    {
        return match ($paymentMethod->type) {
            PaymentMethodType::Cod => new CashOnDeliveryPaymentHandler,
            PaymentMethodType::BankTransfer => new BankTransferPaymentHandler,
            PaymentMethodType::PayPal => new PayPalPaymentHandler(new PayPalClient),
        };
    }
}
