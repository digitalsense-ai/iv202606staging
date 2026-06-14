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

                $table->json('og_extracted_data')->nullable()->after('extracted_data');

                // Used for duplicate detection (type-scoped or global hashing)
                $table->char('duplicate_hash', 64)
                    ->nullable()
                    ->after('og_extracted_data');

                // Validation lifecycle state
                $table->string('validation_status', 30)
                    ->default('not_yet_validated')
                    ->after('duplicate_hash');

                $table->timestamp('validated_at')->nullable();
                $table->timestamp('duplicate_marked_at')->nullable();

                // Optional but recommended: error tracking consistency
                $table->text('duplicate_message')
                    ->nullable()
                    ->after('validation_status');
                    //->change();
            });

        // Indexes (IMPORTANT for performance)
        Schema::connection(config('database.ocr_connection'))
            ->table('dv_ocr_pdfs', function (Blueprint $table) {
                $table->index('duplicate_hash');
                $table->index('validation_status');
                $table->index(['invoice_type', 'duplicate_hash']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('database.ocr_connection'))
            ->table('dv_ocr_pdfs', function (Blueprint $table) {
                $table->dropIndex(['duplicate_hash']);
                $table->dropIndex(['validation_status']);
                $table->dropIndex(['invoice_type', 'duplicate_hash']);

                $table->dropColumn('duplicate_hash');
                $table->dropColumn('validation_status');
            });
    }
};
