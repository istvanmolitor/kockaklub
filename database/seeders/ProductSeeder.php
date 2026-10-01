<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            return;
        }

        Product::factory()
            ->count(24)
            ->recycle($categories)
            ->create()
            ->each(function (Product $product) {
                $imageCount = fake()->numberBetween(1, 3);

                for ($i = 0; $i < $imageCount; $i++) {
                    $product->images()->create([
                        'path' => 'product-images/placeholder.svg',
                        'alt_text' => $product->name,
                        'sort_order' => $i,
                        'is_default' => $i === 0,
                    ]);
                }
            });
    }
}
