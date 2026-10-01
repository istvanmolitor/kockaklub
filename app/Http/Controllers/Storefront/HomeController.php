<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\ProductInterestService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const SECTION_SIZE = 4;

    public function __construct(
        private readonly ProductInterestService $productInterestService,
        private readonly ProductRepository $products,
    ) {}

    public function index(Request $request): View
    {
        // Phase 1: pick each section's own products, each excluding IDs already
        // claimed by a higher-priority section (recommended > featured > new).
        $recommendedProducts = $request->user()
            ? $this->productInterestService->recommendationsFor($request->user(), self::SECTION_SIZE)
            : collect();
        $usedIds = $recommendedProducts->pluck('id')->all();

        $featuredProducts = $this->products->featuredExcluding($usedIds, self::SECTION_SIZE);
        $usedIds = array_merge($usedIds, $featuredProducts->pluck('id')->all());

        $newProducts = $this->products->newestExcluding($usedIds, self::SECTION_SIZE);
        $usedIds = array_merge($usedIds, $newProducts->pluck('id')->all());

        // Phase 2: top up any section that is short with random active products,
        // still never repeating a product already used elsewhere on the page.
        $recommendedProducts = $this->fillWithRandomProducts($recommendedProducts, $usedIds);
        $usedIds = array_merge($usedIds, $recommendedProducts->pluck('id')->all());

        $featuredProducts = $this->fillWithRandomProducts($featuredProducts, $usedIds);
        $usedIds = array_merge($usedIds, $featuredProducts->pluck('id')->all());

        $newProducts = $this->fillWithRandomProducts($newProducts, $usedIds);

        return view('storefront.home', [
            'newProducts' => $newProducts,
            'featuredProducts' => $featuredProducts,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }

    /**
     * Tops up a product section to SECTION_SIZE with random active products,
     * never picking a product already used in another section on the page.
     *
     * @param  Collection<int, Product>  $products
     * @param  array<int, int>  $usedIds  product IDs already claimed on the page
     * @return Collection<int, Product>
     */
    private function fillWithRandomProducts(Collection $products, array $usedIds): Collection
    {
        $remaining = self::SECTION_SIZE - $products->count();

        if ($remaining <= 0) {
            return $products;
        }

        $filler = $this->products->randomActiveExcluding($usedIds, $remaining);

        return $products->concat($filler);
    }
}
