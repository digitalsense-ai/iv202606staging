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
            $table->string('lope_no')->nullable()->after('invoice_date');
        });            
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
