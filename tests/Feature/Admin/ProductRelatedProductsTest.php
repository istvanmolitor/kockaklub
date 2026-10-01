<?php

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

it('allows an admin to attach related products and set their order', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $product = Product::factory()->create(['category_id' => $category->id]);
    $first = Product::factory()->create(['category_id' => $category->id, 'name' => 'Első kapcsolódó']);
    $second = Product::factory()->create(['category_id' => $category->id, 'name' => 'Második kapcsolódó']);

    $this->actingAs($admin);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'relatedProductPivots' => [
                ['related_product_id' => $second->id],
                ['related_product_id' => $first->id],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->relatedProducts()->pluck('products.id')->all())
        ->toEqual([$second->id, $first->id]);
});

it('does not allow a product to be related to itself', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $product = Product::factory()->create(['category_id' => $category->id]);

    $this->actingAs($admin);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'relatedProductPivots' => [
                ['related_product_id' => $product->id],
            ],
        ])
        ->call('save')
        ->assertHasFormErrors();
});
