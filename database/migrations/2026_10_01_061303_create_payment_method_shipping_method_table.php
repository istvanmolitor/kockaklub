<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method_shipping_method', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shipping_method_id', 'payment_method_id'], 'payment_method_shipping_method_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_shipping_method');
    }
};
