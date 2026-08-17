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
                $table->text('search_save_note')->nullable()->after('manual_input_status');                
                $table->timestamp('search_save_at')->nullable()->after('search_save_note');
                $table->unsignedBigInteger('search_save_by')->nullable()->after('search_save_at');
                $table->string('search_save_status')->nullable()->after('search_save_by');
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
                //     'search_save_note',                    
                //     'search_save_at',
                //     'search_save_by',
                //     'search_save_status',
                // ]);
            });
    }
};
