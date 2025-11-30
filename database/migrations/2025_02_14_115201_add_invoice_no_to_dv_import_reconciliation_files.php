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
        Schema::table('dv_import_reconciliation_files', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->after('o_file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_import_reconciliation_files', function (Blueprint $table) {
            //
        });
    }
};
