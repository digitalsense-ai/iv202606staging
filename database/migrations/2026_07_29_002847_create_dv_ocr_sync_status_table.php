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
        ->create('dv_ocr_sync_status', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('ocr_pdf_id');
            $table->enum('environment', ['local', 'staging', 'live']);

            //$table->unsignedBigInteger('client_id')->nullable();

            $table->boolean('sync_status')->default(false);
            $table->boolean('is_locked')->default(false);

            $table->timestamp('locked_at')->nullable();
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->unique(['ocr_pdf_id', 'environment']);

            $table->index(['environment', 'sync_status', 'is_locked']);
            $table->index('ocr_pdf_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {        
        // Schema::connection(config('database.ocr_connection'))
        //     ->dropIfExists('dv_ocr_sync_status');
    }
};
