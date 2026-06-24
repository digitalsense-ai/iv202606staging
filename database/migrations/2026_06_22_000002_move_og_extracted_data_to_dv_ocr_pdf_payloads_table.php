<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = config('database.ocr_connection', 'ocr');

        Schema::connection($connection)->create('dv_ocr_pdf_payloads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ocr_pdf_id')->unique();
            $table->longText('og_extracted_data')->nullable();
            $table->timestamps();
        });

        if (!Schema::connection($connection)->hasColumn('dv_ocr_pdfs', 'og_extracted_data')) {
            return;
        }

        DB::connection($connection)
            ->table('dv_ocr_pdfs')
            ->select(['id', 'og_extracted_data'])
            ->whereNotNull('og_extracted_data')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($connection) {
                foreach ($rows as $row) {
                    DB::connection($connection)
                        ->table('dv_ocr_pdf_payloads')
                        ->updateOrInsert(
                            ['ocr_pdf_id' => $row->id],
                            [
                                'og_extracted_data' => $row->og_extracted_data,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                }
            });

        Schema::connection($connection)->table('dv_ocr_pdfs', function (Blueprint $table) {
            $table->dropColumn('og_extracted_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('database.ocr_connection', 'ocr');

        if (!Schema::connection($connection)->hasColumn('dv_ocr_pdfs', 'og_extracted_data')) {
            Schema::connection($connection)->table('dv_ocr_pdfs', function (Blueprint $table) {
                $table->longText('og_extracted_data')->nullable();
            });
        }

        if (Schema::connection($connection)->hasTable('dv_ocr_pdf_payloads')) {
            DB::connection($connection)
                ->table('dv_ocr_pdf_payloads')
                ->select(['ocr_pdf_id', 'og_extracted_data'])
                ->whereNotNull('og_extracted_data')
                ->orderBy('ocr_pdf_id')
                ->chunkById(200, function ($rows) use ($connection) {
                    foreach ($rows as $row) {
                        DB::connection($connection)
                            ->table('dv_ocr_pdfs')
                            ->where('id', $row->ocr_pdf_id)
                            ->update(['og_extracted_data' => $row->og_extracted_data]);
                    }
                }, 'ocr_pdf_id');

            Schema::connection($connection)->dropIfExists('dv_ocr_pdf_payloads');
        }
    }
};
