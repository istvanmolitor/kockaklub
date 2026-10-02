<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function paginateActiveCatalog(?Category $category, ?string $sort, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->withPublicStock()
            ->with('defaultImage')
            ->where('is_active', true)
            ->when($category, fn ($query) => $query->whereIn('category_id', $category->selfAndDescendantIds()))
            ->sortBy($sort)
            ->paginate($perPage);
    }

    public function paginateSearch(string $search, ?string $sort, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->withPublicStock()
            ->with('defaultImage')
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->sortBy($sort)
            ->paginate($perPage);
    }

    public function loadStorefrontShowRelations(Product $product): Product
    {
        $product->load('images', 'category', 'attributeValues.attribute');
        $product->load(['relatedProducts' => fn ($query) => $query
            ->where('is_active', true)
            ->withPublicStock()
            ->with('defaultImage')]);

        return $product;
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Product>
     */
    public function featuredExcluding(array $excludeIds, int $limit): Collection
    {
        return Product::query()
            ->withPublicStock()
            ->with('defaultImage')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->whereNotIn('id', $excludeIds)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Product>
     */
    public function newestExcluding(array $excludeIds, int $limit): Collection
    {
        return Product::query()
            ->withPublicStock()
            ->with('defaultImage')
            ->where('is_active', true)
            ->whereNotIn('id', $excludeIds)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Product>
     */
    public function randomActiveExcluding(array $excludeIds, int $limit): Collection
    {
        if ($limit <= 0) {
            return new Collection;
        }

        return Product::query()
            ->withPublicStock()
            ->with('defaultImage')
            ->where('is_active', true)
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Product names keyed by id, for Filament select options.
     *
     * @return array<int, string>
     */
    public function pluckNamesForSelect(): array
    {
        return Product::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
