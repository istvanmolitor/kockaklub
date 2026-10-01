<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('defaultImage')
            ->where('is_active', true)
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('storefront.catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $request->string('category')->toString(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('images', 'category');

        return view('storefront.catalog.show', [
            'product' => $product,
        ]);
    }
}
