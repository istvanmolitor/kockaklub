<?php

namespace App\Repositories;

use App\Models\Cart;

class CartRepository
{
    public function findByCustomerId(int $customerId): ?Cart
    {
        return Cart::firstWhere('customer_id', $customerId);
    }

    public function findByGuestToken(string $token): ?Cart
    {
        return Cart::firstWhere('guest_token', $token);
    }

    public function firstOrCreateForCustomer(int $customerId): Cart
    {
        return Cart::firstOrCreate(['customer_id' => $customerId]);
    }

    public function createGuest(string $guestToken): Cart
    {
        return Cart::create(['guest_token' => $guestToken]);
    }
}
