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
        Schema::table('dv_vat_registration', function (Blueprint $table) {
            $table->integer('status_import_re')->after('status');
            $table->integer('is_disregard_import_re')->default(0)->after('is_disregard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_vat_registration', function (Blueprint $table) {
            //
        });
    }
};
