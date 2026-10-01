<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ProductInterestRepository;
use Illuminate\Database\Eloquent\Collection;

class ProductInterestService
{
    public const VIEW_SCORE = 1;

    public const CART_SCORE = 5;

    public const ORDER_SCORE = 10;

    public function __construct(
        private readonly ProductInterestRepository $productInterests,
        private readonly OrderRepository $orders,
    ) {}

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
            ? $this->orders->productIdsPurchasedByCustomer($customerId)
            : collect();

        return $this->productInterests->recommendedProductsFor($user->id, $orderedProductIds, $limit);
    }

    private function addScore(User $user, Product $product, int $points): void
    {
        $this->productInterests->incrementScore($user->id, $product->id, $points);
    }
}
