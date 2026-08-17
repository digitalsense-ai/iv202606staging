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
                $table->string('manual_input_environment')->nullable()->after('manual_input_status');
                $table->string('search_save_environment')->nullable()->after('search_save_status');                
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('database.ocr_connection'))
            ->table('dv_ocr_pdfs', function (Blueprint $table) {
                // $table->dropColumn([
                //     'manual_input_environment',                    
                //     'search_save_environment'
                // ]);
            });
    }
};
