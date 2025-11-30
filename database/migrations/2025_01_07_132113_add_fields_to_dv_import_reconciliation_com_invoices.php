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
        Schema::table('dv_import_reconciliation_com_invoices', function (Blueprint $table) {
            $table->string('data_from')->nullable()->after('vat_reg_id');
            $table->string('invoice_no')->nullable()->change();
            $table->bigInteger('rematch_com_invoice_id')->unsigned()->nullable()->after('invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_import_reconciliation_com_invoices', function (Blueprint $table) {
            //
        });
    }
};
