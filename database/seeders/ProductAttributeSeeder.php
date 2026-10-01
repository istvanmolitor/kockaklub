<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $attributes = [
            'Márka' => ['GAN', 'MoYu', 'QiYi', 'YJ', 'X-Man', 'Rubik\'s'],
            'Szín' => ['Fekete', 'Fehér', 'Stickerless', 'Többszínű'],
            'Matrica' => ['Eredeti matrica', 'Matrica nélkül', 'Egyedi matrica'],
        ];

        foreach ($attributes as $name => $values) {
            $attribute = ProductAttribute::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'allow_multiple' => false]
            );

            foreach ($values as $value) {
                $attribute->values()->updateOrCreate(['value' => $value]);
            }
        }
    }
}
