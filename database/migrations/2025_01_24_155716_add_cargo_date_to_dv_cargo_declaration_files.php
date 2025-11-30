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
        Schema::table('dv_cargo_declaration_files', function (Blueprint $table) {
            $table->date('cargo_date')->nullable()->after('com_invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_cargo_declaration_files', function (Blueprint $table) {
            //
        });
    }
};
