<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    public const COOKIE_NAME = 'cart_token';

    public function currentCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['customer_id' => $request->user()->customer->id]);
        }

        $token = $request->cookie(self::COOKIE_NAME);

        if ($token) {
            $cart = Cart::firstWhere('guest_token', $token);

            if ($cart) {
                return $cart;
            }
        }

        $token = (string) Str::uuid();
        $cart = Cart::create(['guest_token' => $token]);

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

        $guestCart = Cart::firstWhere('guest_token', $token);

        if (! $guestCart) {
            Cookie::queue(Cookie::forget(self::COOKIE_NAME));

            return;
        }

        $customerCart = Cart::firstOrCreate(['customer_id' => $customer->id]);

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
}
