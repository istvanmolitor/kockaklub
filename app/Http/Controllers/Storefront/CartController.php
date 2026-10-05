<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\ProductInterestService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

        $freeStockByProductId = $cart->items
            ->mapWithKeys(fn ($item) => [$item->product_id => $this->stockService->freeStockForProduct($item->product_id)]);

        return view('storefront.cart.show', [
            'cart' => $cart,
            'freeStockByProductId' => $freeStockByProductId,
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        abort_unless($product->is_active, 404);

        $quantity = $validated['quantity'] ?? 1;

        if ($product->is_discontinued) {
            $freeStock = $this->stockService->freeStockForProduct($product->id);
            $alreadyInCart = $this->cartService->currentCart($request)->items()
                ->where('product_id', $product->id)->value('quantity') ?? 0;

            if ($alreadyInCart + $quantity > $freeStock) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Ez a termék kifutó, legfeljebb %d db tehető belőle a kosárba.',
                        max(0, $freeStock - $alreadyInCart)
                    ),
                ]);
            }
        }

        $this->cartService->add($request, $product, $quantity);

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

        if ($product->is_discontinued) {
            $freeStock = $this->stockService->freeStockForProduct($product->id);

            if ($validated['quantity'] > $freeStock) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Ez a termék kifutó, legfeljebb %d db tehető belőle a kosárba.',
                        $freeStock
                    ),
                ]);
            }
        }

        $this->cartService->updateQuantity($request, $product, $validated['quantity']);

        return back()->with('status', 'A kosár frissítve.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->cartService->remove($request, $product);

        return back()->with('status', 'A terméket eltávolítottuk a kosárból.');
    }
}
