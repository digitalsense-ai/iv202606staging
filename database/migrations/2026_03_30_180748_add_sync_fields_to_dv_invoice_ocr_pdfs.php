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
            $table->unsignedTinyInteger('sync_status')->default(0)->after('status');
            $table->boolean('is_deleted')->default(false)->after('sync_status');
            $table->text('deleted_reason')->nullable()->after('is_deleted');
            $table->boolean('is_locked')->default(false)->after('deleted_reason');

            $table->timestamp('polling_locked_at')->nullable()->after('azure_sas_expiry')->index();  
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
