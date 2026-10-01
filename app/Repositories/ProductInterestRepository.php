<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductInterest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ProductInterestRepository
{
    public function incrementScore(int $userId, int $productId, int $points): void
    {
        $interest = ProductInterest::query()->firstOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            ['score' => 0]
        );

        $interest->increment('score', $points);
    }

    /**
     * Active products the given user has shown interest in, ordered by score
     * (highest first), excluding the given product ids.
     *
     * @param  array<int, int>|SupportCollection<int, int>  $excludeProductIds
     * @return Collection<int, Product>
     */
    public function recommendedProductsFor(int $userId, array|SupportCollection $excludeProductIds, int $limit): Collection
    {
        $excludeProductIds = collect($excludeProductIds);

        return Product::query()
            ->join('product_interests', 'product_interests.product_id', '=', 'products.id')
            ->where('product_interests.user_id', $userId)
            ->where('products.is_active', true)
            ->when($excludeProductIds->isNotEmpty(), fn ($query) => $query->whereNotIn('products.id', $excludeProductIds))
            ->select('products.*')
            ->withPublicStock()
            ->with('defaultImage')
            ->orderByDesc('product_interests.score')
            ->limit($limit)
            ->get();
    }
}
