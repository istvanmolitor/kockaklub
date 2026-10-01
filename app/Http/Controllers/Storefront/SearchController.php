<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->toString();

        $products = $query === ''
            ? new LengthAwarePaginator([], 0, 12)
            : Product::query()
                ->withPublicStock()
                ->with('defaultImage')
                ->where('is_active', true)
                ->where(fn ($products) => $products
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%"))
                ->sortBy($request->string('sort')->toString())
                ->paginate(12)
                ->withQueryString();

        return view('storefront.search.index', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}
