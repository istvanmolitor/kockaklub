<?php

namespace Database\Seeders;

use App\Models\PurchaseCostType;
use Illuminate\Database\Seeder;

class PurchaseCostTypeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Vám', 'sort_order' => 0],
            ['name' => 'Import ÁFA', 'sort_order' => 1],
            ['name' => 'Szállítási költség', 'sort_order' => 2],
            ['name' => 'Egyéb', 'sort_order' => 3],
        ];

        foreach ($types as $type) {
            PurchaseCostType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
