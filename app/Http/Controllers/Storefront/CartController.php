<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function show(Request $request): View
    {
        $cart = $this->cartService->currentCart($request);
        $cart->load('items.product.defaultImage');

        return view('storefront.cart.show', ['cart' => $cart]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        abort_unless($product->is_active, 404);

        $this->cartService->add($request, $product, $validated['quantity'] ?? 1);

        return back()->with('status', 'A terméket a kosárhoz adtuk.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $this->cartService->updateQuantity($request, $product, $validated['quantity']);

        return back()->with('status', 'A kosár frissítve.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->cartService->remove($request, $product);

        return back()->with('status', 'A terméket eltávolítottuk a kosárból.');
    }
}
