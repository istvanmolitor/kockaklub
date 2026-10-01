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
            ['description' => 'Fizetés a csomag átvételekor.', 'cost' => 390, 'is_active' => true]
        );

        $bankTransfer = PaymentMethod::updateOrCreate(
            ['name' => 'Banki átutalás'],
            ['description' => 'Előre utalással.', 'cost' => 0, 'is_active' => true]
        );

        $courier = ShippingMethod::updateOrCreate(
            ['name' => 'Házhozszállítás'],
            ['description' => 'Kiszállítás futárszolgálattal.', 'cost' => 1490, 'is_active' => true]
        );
        $courier->paymentMethods()->sync([$cod->id, $bankTransfer->id]);

        $pickup = ShippingMethod::updateOrCreate(
            ['name' => 'Személyes átvétel'],
            ['description' => 'Átvétel üzletünkben.', 'cost' => 0, 'is_active' => true]
        );
        $pickup->paymentMethods()->sync([$bankTransfer->id]);
    }
}
