<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use App\Repositories\CartRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    public const COOKIE_NAME = 'cart_token';

    public function __construct(private readonly CartRepository $carts) {}

    public function existingCart(Request $request): ?Cart
    {
        $customerId = $request->user()?->customer?->id;

        if ($customerId) {
            return $this->carts->findByCustomerId($customerId);
        }

        $token = $request->cookie(self::COOKIE_NAME);

        return $token ? $this->carts->findByGuestToken($token) : null;
    }

    public function currentCart(Request $request): Cart
    {
        $customerId = $request->user()?->customer?->id;

        if ($customerId) {
            return $this->carts->firstOrCreateForCustomer($customerId);
        }

        $token = $request->cookie(self::COOKIE_NAME);

        if ($token) {
            $cart = $this->carts->findByGuestToken($token);

            if ($cart) {
                return $cart;
            }
        }

        $token = (string) Str::uuid();
        $cart = $this->carts->createGuest($token);

        Cookie::queue(Cookie::make(self::COOKIE_NAME, $token, 60 * 24 * 365));

        return $cart;
    }

    public function add(Request $request, Product $product, int $quantity = 1): Cart
    {
        $cart = $this->currentCart($request);

        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->cart_id = $cart->id;
        $item->save();

        return $cart;
    }

    public function updateQuantity(Request $request, Product $product, int $quantity): Cart
    {
        $cart = $this->currentCart($request);

        if ($quantity < 1) {
            $cart->items()->where('product_id', $product->id)->delete();
        } else {
            $cart->items()->updateOrCreate(['product_id' => $product->id], ['quantity' => $quantity]);
        }

        return $cart;
    }

    public function remove(Request $request, Product $product): Cart
    {
        $cart = $this->currentCart($request);

        $cart->items()->where('product_id', $product->id)->delete();

        return $cart;
    }

    public function mergeGuestCartIntoCustomer(Request $request, Customer $customer): void
    {
        $token = $request->cookie(self::COOKIE_NAME);

        if (! $token) {
            return;
        }

        $guestCart = $this->carts->findByGuestToken($token);

        if (! $guestCart) {
            Cookie::queue(Cookie::forget(self::COOKIE_NAME));

            return;
        }

        $customerCart = $this->carts->firstOrCreateForCustomer($customer->id);

        foreach ($guestCart->items as $guestItem) {
            $existing = $customerCart->items()->firstWhere('product_id', $guestItem->product_id);

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $guestItem->quantity]);
            } else {
                $customerCart->items()->create([
                    'product_id' => $guestItem->product_id,
                    'quantity' => $guestItem->quantity,
                ]);
            }
        }

        $guestCart->delete();

        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    public function moveCustomerCartToSession(Customer $customer): void
    {
        $customerCart = $this->carts->findByCustomerId($customer->id);

        if (! $customerCart || $customerCart->items->isEmpty()) {
            return;
        }

        $token = (string) Str::uuid();
        $guestCart = $this->carts->createGuest($token);

        foreach ($customerCart->items as $item) {
            $guestCart->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ]);
        }

        $customerCart->items()->delete();
        $customerCart->delete();

        Cookie::queue(Cookie::make(self::COOKIE_NAME, $token, 60 * 24 * 365));
    }
}
