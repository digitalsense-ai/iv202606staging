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
                //sync_db
                // -------
                // 0 = Pending
                // 1 = Successfully synced
                // 2 = Processing
                // 3 = Permanent/business failure
                $table->unsignedTinyInteger('sync_db')->default(0);
                $table->string('sync_db_remarks')->nullable();
                $table->timestamp('sync_started_at')->nullable()->after('sync_db');
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
                //     'sync_db'
                // ]);
            });       
    }
};
