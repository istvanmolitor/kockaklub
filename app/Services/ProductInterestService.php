<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductInterest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProductInterestService
{
    public const VIEW_SCORE = 1;

    public const CART_SCORE = 5;

    public const ORDER_SCORE = 10;

    public function recordView(User $user, Product $product): void
    {
        $this->addScore($user, $product, self::VIEW_SCORE);
    }

    public function recordCartAdd(User $user, Product $product): void
    {
        $this->addScore($user, $product, self::CART_SCORE);
    }

    public function recordOrder(User $user, Product $product): void
    {
        $this->addScore($user, $product, self::ORDER_SCORE);
    }

    /**
     * Products the given user has shown interest in, ordered by score (highest
     * first), excluding anything they have already ordered.
     *
     * @return Collection<int, Product>
     */
    public function recommendationsFor(User $user, int $limit = 8): Collection
    {
        $customerId = $user->customer?->id;

        $orderedProductIds = $customerId
            ? OrderItem::query()->whereHas('order', fn ($query) => $query->where('customer_id', $customerId))->pluck('product_id')
            : collect();

        return Product::query()
            ->join('product_interests', 'product_interests.product_id', '=', 'products.id')
            ->where('product_interests.user_id', $user->id)
            ->where('products.is_active', true)
            ->when($orderedProductIds->isNotEmpty(), fn ($query) => $query->whereNotIn('products.id', $orderedProductIds))
            ->withPublicStock()
            ->with('defaultImage')
            ->orderByDesc('product_interests.score')
            ->select('products.*')
            ->limit($limit)
            ->get();
    }

    private function addScore(User $user, Product $product, int $points): void
    {
        $interest = ProductInterest::query()->firstOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            ['score' => 0]
        );

        $interest->increment('score', $points);
    }
}
