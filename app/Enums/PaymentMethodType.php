<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case Cod = 'cod';
    case BankTransfer = 'bank_transfer';
    case PayPal = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Utánvét',
            self::BankTransfer => 'Banki átutalás',
            self::PayPal => 'PayPal',
        };
    }
}
