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
        Schema::table('dv_invoice_ocr_pdfs', function (Blueprint $table) {
            $table->longText('azure_url')->nullable()->after('error');

            $table->string('azure_sas_url')->nullable()->after('azure_url');
            $table->timestamp('azure_sas_expiry')->nullable()->after('azure_sas_url');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_invoice_ocr_pdfs', function (Blueprint $table) {
            //
        });
    }
};
