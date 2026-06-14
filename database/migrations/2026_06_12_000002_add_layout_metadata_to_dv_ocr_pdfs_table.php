<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('database.ocr_connection'))
            ->table('dv_ocr_pdfs', function (Blueprint $table) {
                if (!Schema::hasColumn('dv_ocr_pdfs', 'layout_metadata')) {
                    $table->json('layout_metadata')->nullable()->after('end_pageno');
                }
            });
    }

    public function down(): void
    {
        Schema::connection(config('database.ocr_connection'))
            ->table('dv_ocr_pdfs', function (Blueprint $table) {
                if (Schema::hasColumn('dv_ocr_pdfs', 'layout_metadata')) {
                    $table->dropColumn('layout_metadata');
                }
            });
    }
};