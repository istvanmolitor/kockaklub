<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\ProductInterestService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly StockService $stockService,
        private readonly ProductInterestService $productInterestService,
    ) {}

    public function show(Request $request): View
    {
        $cart = $this->cartService->currentCart($request);
        $cart->load('items.product.defaultImage');

        $publicStockByProductId = $cart->items
            ->mapWithKeys(fn ($item) => [$item->product_id => $this->stockService->publicStockForProduct($item->product_id)]);

        return view('storefront.cart.show', [
            'cart' => $cart,
            'publicStockByProductId' => $publicStockByProductId,
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        abort_unless($product->is_active, 404);

        $this->cartService->add($request, $product, $validated['quantity'] ?? 1);

        if ($request->user()) {
            $this->productInterestService->recordCartAdd($request->user(), $product);
        }

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
