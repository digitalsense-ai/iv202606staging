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
                $table->text('manual_note')->nullable()->after('error');
                $table->boolean('force_submitted')->default(false)->after('manual_note');
                $table->timestamp('manual_input_at')->nullable()->after('force_submitted');
                $table->unsignedBigInteger('manual_input_by')->nullable()->after('manual_input_at');
                $table->string('manual_input_status')->nullable()->after('manual_input_by');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('database.ocr_connection'))
            ->table('dv_ocr_pdfs', function (Blueprint $table) {
                $table->dropColumn([
                    'manual_note',
                    'force_submitted',
                    'manual_input_at',
                    'manual_input_by',
                    'manual_input_status',
                ]);
            });
    }
};
