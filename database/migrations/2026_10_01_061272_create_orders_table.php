<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_status_id')->constrained()->restrictOnDelete();
            $table->foreignId('shipping_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->foreignId('shipping_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_zip')->nullable();
            $table->text('shipping_address');
            $table->string('billing_name')->nullable();
            $table->foreignId('billing_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('billing_city')->nullable();
            $table->string('billing_zip')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_tax_number')->nullable();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('shipping_cost')->default(0);
            $table->unsignedInteger('payment_cost')->default(0);
            $table->unsignedInteger('total');
            $table->string('invoice_number')->nullable();
            $table->timestamp('invoiced_at')->nullable();
            $table->string('invoice_pdf_path')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
