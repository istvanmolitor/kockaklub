<?php

namespace Database\Seeders;

use App\Models\PurchaseStatus;
use Illuminate\Database\Seeder;

class PurchaseStatusSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => PurchaseStatus::ORDERED, 'description' => 'Megrendelve és kifizetve a beszállítónál, áru még nem érkezett'],
            ['name' => PurchaseStatus::RECEIVED, 'description' => 'Beérkezett'],
            ['name' => PurchaseStatus::CANCELLED, 'description' => 'Sztornózva'],
        ];

        foreach ($statuses as $status) {
            PurchaseStatus::updateOrCreate(['name' => $status['name']], $status);
        }
    }
}
