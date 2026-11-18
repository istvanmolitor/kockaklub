<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY vat_rate TINYINT UNSIGNED NOT NULL DEFAULT 0');

        Product::query()->update(['vat_rate' => 0]);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY vat_rate TINYINT UNSIGNED NOT NULL DEFAULT 27');
    }
};
