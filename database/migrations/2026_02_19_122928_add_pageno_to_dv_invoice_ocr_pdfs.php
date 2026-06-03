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
            $table->integer('start_pageno')->nullable()->after('file_name');
            $table->integer('end_pageno')->nullable()->after('start_pageno');
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
