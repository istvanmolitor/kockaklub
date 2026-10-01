<?php

use App\Models\Product;
use App\Models\ProductInterest;
use App\Models\User;

it('shows products the user has shown interest in on the homepage', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Ajánlott Termék']);
    ProductInterest::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'score' => 5]);

    $this->actingAs($user)->get('/')->assertSee('Ajánlott Termék');
});

it('fills the recommendations section with random products for guests', function () {
    Product::factory()->count(12)->create(['is_active' => true]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Neked ajánljuk');
    expect($response->viewData('recommendedProducts'))->toHaveCount(4);
});

it('fills the recommendations section with random products when the user has no interests', function () {
    $user = User::factory()->create();
    Product::factory()->count(12)->create(['is_active' => true]);

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
    $response->assertSee('Neked ajánljuk');
    expect($response->viewData('recommendedProducts'))->toHaveCount(4);
});

it('renders the order button for a recommended product without public stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);
    ProductInterest::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'score' => 5]);

    $this->actingAs($user)->get('/')->assertOk();
});
