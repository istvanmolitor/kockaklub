<?php

use App\Filament\Resources\ProductAttributes\Pages\CreateProductAttribute;
use App\Models\ProductAttribute;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('allows an admin to create a product attribute with values', function () {
    livewire(CreateProductAttribute::class)
        ->fillForm([
            'name' => 'Szín',
            'slug' => 'szin',
            'allow_multiple' => true,
            'values' => [
                ['value' => 'Piros'],
                ['value' => 'Kék'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $attribute = ProductAttribute::where('slug', 'szin')->first();

    expect($attribute)->not->toBeNull();
    expect($attribute->allow_multiple)->toBeTrue();
    expect($attribute->values()->pluck('value')->all())->toEqualCanonicalizing(['Piros', 'Kék']);
});
