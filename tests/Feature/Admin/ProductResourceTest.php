<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

it('allows an admin to create a product through the admin panel', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin);

    livewire(CreateProduct::class)
        ->fillForm([
            'category_id' => $category->id,
            'name' => 'Teszt termék',
            'slug' => 'teszt-termek',
            'price' => 12990,
            'stock' => 10,
            'sku' => 'TT-0001',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::where('slug', 'teszt-termek')->exists())->toBeTrue();
});

it('does not allow a non-admin user to access the admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user);

    $this->get('/admin')->assertForbidden();
});
