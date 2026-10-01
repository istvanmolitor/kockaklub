<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $cod = PaymentMethod::updateOrCreate(
            ['name' => 'Utánvét'],
            ['description' => 'Fizetés a csomag átvételekor.', 'is_active' => true]
        );

        $bankTransfer = PaymentMethod::updateOrCreate(
            ['name' => 'Banki átutalás'],
            ['description' => 'Előre utalással.', 'is_active' => true]
        );

        $courier = ShippingMethod::updateOrCreate(
            ['name' => 'Házhozszállítás'],
            ['description' => 'Kiszállítás futárszolgálattal.', 'is_active' => true]
        );
        $courier->paymentMethods()->sync([$cod->id, $bankTransfer->id]);

        $pickup = ShippingMethod::updateOrCreate(
            ['name' => 'Személyes átvétel'],
            ['description' => 'Átvétel üzletünkben.', 'is_active' => true]
        );
        $pickup->paymentMethods()->sync([$bankTransfer->id]);
    }
}
