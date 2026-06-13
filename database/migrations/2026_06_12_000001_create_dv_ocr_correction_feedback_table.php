<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dv_ocr_correction_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->string('field_name')->index();
            $table->text('original_value')->nullable();
            $table->text('corrected_value')->nullable();
            $table->string('original_value_hash', 64)->nullable()->index();
            $table->string('layout_fingerprint', 128)->nullable()->index();
            $table->timestamps();

            $table->index(['client_id', 'field_name']);
            $table->index(['field_name', 'original_value_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dv_ocr_correction_feedback');
    }
};
