<?php

namespace Database\Seeders;

use App\Enums\PaymentMethodType;
use App\Enums\ShippingFulfillmentType;
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
            ['description' => 'Fizetés a csomag átvételekor.', 'cost' => 390, 'is_active' => true, 'type' => PaymentMethodType::Cod]
        );

        $bankTransfer = PaymentMethod::updateOrCreate(
            ['name' => 'Banki átutalás'],
            ['description' => 'Előre utalással.', 'cost' => 0, 'is_active' => true, 'is_default' => true, 'type' => PaymentMethodType::BankTransfer]
        );

        $paypal = PaymentMethod::updateOrCreate(
            ['name' => 'PayPal'],
            ['description' => 'Fizetés PayPal fiókkal vagy bankkártyával.', 'cost' => 0, 'is_active' => true, 'type' => PaymentMethodType::PayPal]
        );

        $courier = ShippingMethod::updateOrCreate(
            ['name' => 'Házhozszállítás'],
            [
                'description' => 'Kiszállítás futárszolgálattal.',
                'cost' => 1490,
                'is_active' => true,
                'is_default' => true,
                'fulfillment_type' => ShippingFulfillmentType::Courier,
            ]
        );
        $courier->paymentMethods()->sync([$cod->id, $bankTransfer->id, $paypal->id]);

        $pickup = ShippingMethod::updateOrCreate(
            ['name' => 'Személyes átvétel'],
            [
                'description' => 'Átvétel üzletünkben.',
                'cost' => 0,
                'is_active' => true,
                'fulfillment_type' => ShippingFulfillmentType::SitePickup,
            ]
        );
        $pickup->paymentMethods()->sync([$bankTransfer->id, $paypal->id]);

        $foxpost = ShippingMethod::updateOrCreate(
            ['name' => 'Foxpost csomagautomata'],
            [
                'description' => 'Átvétel a kiválasztott Foxpost csomagautomatából.',
                'cost' => 990,
                'is_active' => true,
                'fulfillment_type' => ShippingFulfillmentType::ParcelLocker,
                'locker_provider' => 'foxpost',
            ]
        );
        $foxpost->paymentMethods()->sync([$cod->id, $bankTransfer->id, $paypal->id]);
    }
}
