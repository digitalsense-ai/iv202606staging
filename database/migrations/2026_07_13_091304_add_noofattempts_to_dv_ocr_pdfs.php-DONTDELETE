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
        Schema::connection(config('database.ocr_connection'))
            ->table('dv_ocr_pdfs', function (Blueprint $table) {
                $table->unsignedTinyInteger('no_of_attempts')->default(0)->after('status');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {       
        Schema::connection(config('database.ocr_connection'))
            ->table('dv_ocr_pdfs', function (Blueprint $table) {
                
            });
    }
};
