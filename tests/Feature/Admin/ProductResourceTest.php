<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
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
            'sku' => 'TT-0001',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::where('slug', 'teszt-termek')->exists())->toBeTrue();
});

it('allows an admin to assign single and multi-select attribute values to a product', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $brand = ProductAttribute::factory()->create(['name' => 'Márka', 'allow_multiple' => false]);
    $brandValue = $brand->values()->create(['value' => 'Acme']);

    $color = ProductAttribute::factory()->create(['name' => 'Szín', 'allow_multiple' => true]);
    $red = $color->values()->create(['value' => 'Piros']);
    $blue = $color->values()->create(['value' => 'Kék']);

    $this->actingAs($admin);

    livewire(CreateProduct::class)
        ->fillForm([
            'category_id' => $category->id,
            'name' => 'Attribútumos termék',
            'slug' => 'attributumos-termek',
            'price' => 9990,
            'is_active' => true,
            'attribute_values' => [
                $brand->id => $brandValue->id,
                $color->id => [$red->id, $blue->id],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'attributumos-termek')->firstOrFail();

    expect($product->attributeValues()->pluck('product_attribute_values.id')->sort()->values()->all())
        ->toEqual(collect([$brandValue->id, $red->id, $blue->id])->sort()->values()->all());

    $green = $color->values()->create(['value' => 'Zöld']);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'attribute_values' => [
                $brand->id => $brandValue->id,
                $color->id => [$green->id],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->attributeValues()->pluck('product_attribute_values.id')->sort()->values()->all())
        ->toEqual(collect([$brandValue->id, $green->id])->sort()->values()->all());
});

it('does not allow a non-admin user to access the admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user);

    $this->get('/admin')->assertForbidden();
});
