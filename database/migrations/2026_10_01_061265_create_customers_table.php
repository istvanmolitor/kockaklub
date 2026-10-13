<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('shipping_name')->nullable();
            $table->foreignId('shipping_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_zip')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('billing_name')->nullable();
            $table->foreignId('billing_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('billing_city')->nullable();
            $table->string('billing_zip')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_tax_number')->nullable();
            $table->boolean('is_buyer')->default(false);
            $table->boolean('is_seller')->default(false);
            $table->foreignId('default_shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
            $table->foreignId('default_payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
