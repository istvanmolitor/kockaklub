<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Content;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $products = Product::query()
                ->with('defaultImage')
                ->where('is_active', true)
                ->get();

            $categories = Category::query()->where('is_active', true)->get();

            $contents = Content::query()->get();

            return view('sitemap', [
                'products' => $products,
                'categories' => $categories,
                'contents' => $contents,
            ])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
