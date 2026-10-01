<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_country')->nullable()->after('shipping_phone');
            $table->string('shipping_city')->nullable()->after('shipping_country');
            $table->string('shipping_zip')->nullable()->after('shipping_city');

            $table->string('billing_name')->nullable()->after('shipping_address');
            $table->string('billing_country')->nullable()->after('billing_name');
            $table->string('billing_city')->nullable()->after('billing_country');
            $table->string('billing_zip')->nullable()->after('billing_city');
            $table->string('billing_address')->nullable()->after('billing_zip');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_country',
                'shipping_city',
                'shipping_zip',
                'billing_name',
                'billing_country',
                'billing_city',
                'billing_zip',
                'billing_address',
            ]);
        });
    }
};
