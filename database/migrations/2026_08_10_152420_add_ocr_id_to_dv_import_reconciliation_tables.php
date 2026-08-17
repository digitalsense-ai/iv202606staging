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
            $table->unsignedBigInteger('ocr_pdf_id')->nullable()->after('data_from');
        });

        Schema::table('dv_import_reconciliation_sales_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('ocr_pdf_id')->nullable()->after('com_invoice_id');
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

        Schema::table('dv_import_reconciliation_sales_invoices', function (Blueprint $table) {
            //
        });
    }
};
