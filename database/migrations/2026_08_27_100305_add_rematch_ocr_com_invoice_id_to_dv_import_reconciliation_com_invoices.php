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
            $table->bigInteger('rematch_ocr_com_invoice_id')->unsigned()->nullable()->after('rematch_com_invoice_id');
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
