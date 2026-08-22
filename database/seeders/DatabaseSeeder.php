<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Molitor\Address\database\seeders\AddressSeeder;
use Molitor\Currency\Database\Seeders\CurrencySeeder;
use Molitor\Customer\database\seeders\CustomerSeeder;
use Molitor\Language\Database\Seeders\LanguageSeeder;
use Molitor\Media\Database\Seeders\MediaSeeder;
use Molitor\Order\database\seeders\OrderSeeder;
use Molitor\Product\Database\Seeders\ProductSeeder;
use Molitor\Setting\database\seeders\SettingSeeder;
use Molitor\Stock\database\seeders\StockSeeder;
use Molitor\User\Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            UserSeeder::class,
            LanguageSeeder::class,
            CurrencySeeder::class,
            AddressSeeder::class,
            MediaSeeder::class,
            SettingSeeder::class,
            StockSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
