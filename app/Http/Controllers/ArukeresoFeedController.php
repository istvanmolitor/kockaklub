<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShippingMethod;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ArukeresoFeedController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('arukereso.xml', now()->addHour(), function () {
            $products = Product::query()
                ->withPublicStock()
                ->with(['category', 'defaultImage', 'images', 'barcodes'])
                ->where('is_active', true)
                ->get()
                ->filter(fn (Product $product) => $product->isOrderable($product->public_stock));

            $brandsByProductId = DB::table('attribute_value_product')
                ->join('product_attribute_values', 'product_attribute_values.id', '=', 'attribute_value_product.product_attribute_value_id')
                ->join('product_attributes', 'product_attributes.id', '=', 'product_attribute_values.product_attribute_id')
                ->where('product_attributes.name', 'Márka')
                ->pluck('product_attribute_values.value', 'attribute_value_product.product_id');

            $deliveryCost = ShippingMethod::query()->where('is_active', true)->max('cost') ?? 0;

            return view('feeds.arukereso', [
                'products' => $products,
                'brandsByProductId' => $brandsByProductId,
                'deliveryCost' => $deliveryCost,
            ])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
