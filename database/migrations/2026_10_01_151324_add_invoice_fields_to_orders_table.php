<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('total');
            $table->timestamp('invoiced_at')->nullable()->after('invoice_number');
            $table->string('invoice_pdf_path')->nullable()->after('invoiced_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoiced_at', 'invoice_pdf_path']);
        });
    }
};
