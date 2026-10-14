<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $currencies = [
            ['code' => 'HUF', 'name' => 'Forint', 'is_default' => true, 'sort_order' => 0],
            ['code' => 'EUR', 'name' => 'Euró', 'is_default' => false, 'sort_order' => 1],
            ['code' => 'USD', 'name' => 'Amerikai dollár', 'is_default' => false, 'sort_order' => 2],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
