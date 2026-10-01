<?php

use App\Models\Product;

function orderButtonHtml(\Illuminate\Testing\TestResponse $response): string
{
    preg_match('/<form method="POST" action="[^"]*\/kosar\/hozzaadas\/.*?<\/form>/s', $response->getContent(), $matches);

    return $matches[0] ?? '';
}

it('allows ordering a regular product even when it has no stock', function () {
    $product = Product::factory()->create(['is_active' => true, 'is_discontinued' => false]);

    $response = $this->get(route('catalog.show', $product));

    $response->assertOk();
    $response->assertSee('Előrendelem');
    expect(orderButtonHtml($response))->not->toMatch('/(^|\s)disabled(\s|>)/');
});

it('blocks ordering a discontinued product once it has no stock', function () {
    $product = Product::factory()->create(['is_active' => true, 'is_discontinued' => true]);

    $response = $this->get(route('catalog.show', $product));

    $response->assertOk();
    $response->assertSee('Elfogyott');
    expect(orderButtonHtml($response))->toMatch('/(^|\s)disabled(\s|>)/');
});
