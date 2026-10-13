<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        return $this->renderCatalog($request, null);
    }

    /**
     * Resolves the flat, UNAS-style slug (shared by products and categories)
     * registered as the site-wide catch-all route.
     */
    public function showBySlug(Request $request, string $slug): View
    {
        if ($product = $this->products->findBySlug($slug)) {
            return $this->show($request, $product);
        }

        if ($category = $this->categories->findBySlug($slug)) {
            return $this->renderCatalog($request, $category);
        }

        abort(404);
    }

    private function renderCatalog(Request $request, ?Category $category): View
    {
        $products = $this->products
            ->paginateActiveCatalog($category, $request->string('sort')->toString())
            ->withQueryString();

        $categories = $this->categories->activeTree();

        return view('storefront.catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $category?->slug ?? '',
            'category' => $category,
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
