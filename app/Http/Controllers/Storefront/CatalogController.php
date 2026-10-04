<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Services\ProductInterestService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly ProductInterestService $productInterestService,
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
    ) {}

    public function index(Request $request): View
    {
        $category = $this->categories->findBySlug($request->string('category')->toString());

        $products = $this->products
            ->paginateActiveCatalog($category, $request->string('sort')->toString())
            ->withQueryString();

        $categories = $this->categories->activeTree();

        return view('storefront.catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $request->string('category')->toString(),
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        if ($request->user()) {
            $this->productInterestService->recordView($request->user(), $product);
        }

        $this->products->loadStorefrontShowRelations($product);

        return view('storefront.catalog.show', [
            'product' => $product,
            'freeStock' => $this->stockService->freeStockForProduct($product->id),
        ]);
    }
}
