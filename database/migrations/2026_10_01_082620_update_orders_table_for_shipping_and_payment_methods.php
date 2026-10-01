<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->foreignId('shipping_method_id')->after('order_status_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->after('shipping_method_id')->constrained()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_method_id');
            $table->dropConstrainedForeignId('payment_method_id');
            $table->enum('payment_method', ['cod', 'bank_transfer'])->after('order_status_id');
        });
    }
};
