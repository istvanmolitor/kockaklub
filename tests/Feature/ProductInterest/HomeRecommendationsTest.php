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

it('does not show a recommendations section for guests', function () {
    $this->get('/')->assertDontSee('Neked ajánljuk');
});

it('does not show a recommendations section when the user has no interests', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertDontSee('Neked ajánljuk');
});
