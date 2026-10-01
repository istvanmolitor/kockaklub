<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductInterestService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly ProductInterestService $productInterestService,
    ) {}

    public function index(Request $request): View
    {
        $category = Category::query()->where('slug', $request->string('category'))->first();

        $products = Product::query()
            ->withPublicStock()
            ->with('defaultImage')
            ->where('is_active', true)
            ->when($category, fn ($query) => $query->whereIn('category_id', $category->selfAndDescendantIds()))
            ->sortBy($request->string('sort')->toString())
            ->paginate(12)
            ->withQueryString();

        $categories = Category::tree(Category::query()->where('is_active', true)->orderBy('name')->get());

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

        $product->load('images', 'category');
        $product->load(['relatedProducts' => fn ($query) => $query
            ->where('is_active', true)
            ->withPublicStock()
            ->with('defaultImage')]);

        return view('storefront.catalog.show', [
            'product' => $product,
            'publicStock' => $this->stockService->publicStockForProduct($product->id),
        ]);
    }
}
