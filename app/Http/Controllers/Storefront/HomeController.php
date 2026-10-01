<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('defaultImage')
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();

        return view('storefront.home', ['products' => $products]);
    }
}
