<?php

use App\Models\Product;

it('shows only active and featured products in the featured section', function () {
    $featured = Product::factory()->create(['is_active' => true, 'is_featured' => true, 'name' => 'Kiemelt Termék']);
    Product::factory()->create(['is_active' => false, 'is_featured' => true, 'name' => 'Inaktív Kiemelt Termék']);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Kiemelt termékek');
    $response->assertSee($featured->name);
    $response->assertDontSee('Inaktív Kiemelt Termék');
});

it('shows at most four featured products on the homepage', function () {
    Product::factory()->count(5)->create(['is_active' => true, 'is_featured' => true]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('featuredProducts'))->toHaveCount(4);
});

it('shows at most four new products on the homepage', function () {
    Product::factory()->count(5)->create(['is_active' => true]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Újdonságok');
    expect($response->viewData('newProducts'))->toHaveCount(4);
});

it('never repeats a product across the recommended, featured and new sections', function () {
    Product::factory()->count(4)->create(['is_active' => true, 'is_featured' => true]);
    Product::factory()->count(8)->create(['is_active' => true]);

    $response = $this->get('/');

    $response->assertOk();

    $ids = collect(['recommendedProducts', 'featuredProducts', 'newProducts'])
        ->flatMap(fn (string $key) => $response->viewData($key)->pluck('id'));

    expect($ids)->toHaveCount(12);
    expect($ids->unique())->toHaveCount(12);
});
