<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region_product_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_stock')->nullable();
            $table->unsignedInteger('max_stock')->nullable();
            $table->timestamps();

            $table->unique(['region_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_product_settings');
    }
};
