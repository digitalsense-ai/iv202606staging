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
        Schema::table('dv_import_reconciliation_sales_invoices_data', function (Blueprint $table) {
            $table->longText('allowance_charge')->nullable()->after('payment_penalty_date');
            $table->string('allowance_charge_currency_code')->nullable()->after('allowance_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_import_reconciliation_sales_invoices_data', function (Blueprint $table) {
            //
        });
    }
};
