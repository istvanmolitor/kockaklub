<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(private readonly ProductRepository $products) {}

    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->toString();

        $products = $query === ''
            ? new LengthAwarePaginator([], 0, 12)
            : $this->products
                ->paginateSearch($query, $request->string('sort')->toString())
                ->withQueryString();

        return view('storefront.search.index', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}
