<?php

use App\Enums\ShippingFulfillmentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->string('fulfillment_type')->default(ShippingFulfillmentType::Courier->value)->after('cost');
            $table->string('locker_provider')->nullable()->after('fulfillment_type');
        });

        DB::table('shipping_methods')
            ->where('name', 'Személyes átvétel')
            ->update(['fulfillment_type' => ShippingFulfillmentType::SitePickup->value]);
    }

    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_type', 'locker_provider']);
        });
    }
};
