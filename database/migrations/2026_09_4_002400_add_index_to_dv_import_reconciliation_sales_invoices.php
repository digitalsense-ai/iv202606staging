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
            $table->index(
                ['vat_reg_id', 'com_invoice_id', 'ocr_pdf_id'],
                'idx_sales_vat_com_ocr'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_import_reconciliation_sales_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_sales_vat_com_ocr');
        });
    }
};
