<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentHandler
{
    /**
     * Extra validation rules for the fields this payment type needs,
     * on top of the base StoreOrderRequest rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array;

    /**
     * Called once, right after the order has been committed to the database.
     * Returns an off-site redirect URL if the browser must be sent away to
     * complete payment, or null if there is nothing further to do.
     */
    public function initiate(Order $order): ?string;
}
