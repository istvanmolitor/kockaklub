<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Függőben', 'slug' => 'pending', 'color' => 'gray', 'sort_order' => 0, 'is_default' => true, 'is_final' => false],
            ['name' => 'Feldolgozás alatt', 'slug' => 'processing', 'color' => 'info', 'sort_order' => 1, 'is_default' => false, 'is_final' => false],
            ['name' => 'Teljesítve', 'slug' => 'completed', 'color' => 'success', 'sort_order' => 2, 'is_default' => false, 'is_final' => true],
            ['name' => 'Törölve', 'slug' => 'cancelled', 'color' => 'danger', 'sort_order' => 3, 'is_default' => false, 'is_final' => true],
        ];

        foreach ($statuses as $status) {
            OrderStatus::updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
