<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductInterest;
use App\Models\User;
use App\Services\ProductInterestService;

beforeEach(function () {
    $this->service = new ProductInterestService;
});

it('creates and accumulates a score across repeated interactions', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->service->recordView($user, $product);
    $this->service->recordCartAdd($user, $product);
    $this->service->recordOrder($user, $product);

    expect(ProductInterest::query()->count())->toBe(1)
        ->and(ProductInterest::first()->score)->toBe(1 + 5 + 10);
});

it('keeps separate scores per product for the same user', function () {
    $user = User::factory()->create();
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

    $this->service->recordView($user, $productA);
    $this->service->recordCartAdd($user, $productB);

    expect(ProductInterest::where('product_id', $productA->id)->first()->score)->toBe(1)
        ->and(ProductInterest::where('product_id', $productB->id)->first()->score)->toBe(5);
});

it('orders recommendations by score and excludes products already ordered', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

    $highInterest = Product::factory()->create();
    $lowInterest = Product::factory()->create();
    $alreadyOrdered = Product::factory()->create();

    $this->service->recordOrder($user, $highInterest);
    $this->service->recordView($user, $lowInterest);

    $this->service->recordOrder($user, $alreadyOrdered);
    $order = Order::factory()->create(['customer_id' => $customer->id]);
    $order->items()->create([
        'product_id' => $alreadyOrdered->id,
        'product_name' => $alreadyOrdered->name,
        'unit_price' => $alreadyOrdered->price,
        'quantity' => 1,
        'line_total' => $alreadyOrdered->price,
    ]);

    $recommendations = $this->service->recommendationsFor($user);

    expect($recommendations->pluck('id')->all())->toBe([$highInterest->id, $lowInterest->id]);
});

it('excludes inactive products from recommendations', function () {
    $user = User::factory()->create();
    $inactive = Product::factory()->create(['is_active' => false]);

    $this->service->recordView($user, $inactive);

    expect($this->service->recommendationsFor($user))->toBeEmpty();
});
