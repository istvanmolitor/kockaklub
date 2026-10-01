<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('phone');
            $table->string('shipping_country')->nullable()->after('shipping_name');
            $table->string('shipping_city')->nullable()->after('shipping_country');
            $table->string('shipping_zip')->nullable()->after('shipping_city');
            $table->string('shipping_address')->nullable()->after('shipping_zip');

            $table->string('billing_name')->nullable()->after('shipping_address');
            $table->string('billing_country')->nullable()->after('billing_name');
            $table->string('billing_city')->nullable()->after('billing_country');
            $table->string('billing_zip')->nullable()->after('billing_city');
            $table->string('billing_address')->nullable()->after('billing_zip');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_country',
                'shipping_city',
                'shipping_zip',
                'shipping_address',
                'billing_name',
                'billing_country',
                'billing_city',
                'billing_zip',
                'billing_address',
            ]);
        });
    }
};
