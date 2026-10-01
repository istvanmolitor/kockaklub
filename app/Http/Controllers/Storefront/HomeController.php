<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductInterestService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly ProductInterestService $productInterestService) {}

    public function index(Request $request): View
    {
        $products = Product::query()
            ->withPublicStock()
            ->with('defaultImage')
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();

        $recommendedProducts = $request->user()
            ? $this->productInterestService->recommendationsFor($request->user())
            : collect();

        return view('storefront.home', [
            'products' => $products,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }
}
