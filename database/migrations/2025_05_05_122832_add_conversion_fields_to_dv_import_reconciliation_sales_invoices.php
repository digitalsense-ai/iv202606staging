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
        Schema::table('dv_import_reconciliation_sales_invoices', function (Blueprint $table) {
            $table->string('convert_currency_code')->nullable()->after('credit_note'); 
            $table->decimal('exchange_rate')->nullable()->after('convert_currency_code'); 
            $table->longText('convert_net_amount')->nullable()->after('exchange_rate'); 
            $table->longText('convert_vat_amount')->nullable()->after('convert_net_amount'); 
            $table->longText('convert_total_amount')->nullable()->after('convert_vat_amount'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_import_reconciliation_sales_invoices', function (Blueprint $table) {
            //
        });
    }
};
