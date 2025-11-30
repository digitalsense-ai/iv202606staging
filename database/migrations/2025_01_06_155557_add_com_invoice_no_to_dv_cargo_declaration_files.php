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
            $table->longText('ivf_com_invoice_nos')->nullable()->after('import_vat_id');
            $table->longText('ivf_com_invoice_dates')->nullable()->after('ivf_com_invoice_nos');
            $table->longText('cargo_com_invoice_nos')->nullable()->after('ivf_com_invoice_dates');
            $table->longText('cargo_com_invoice_dates')->nullable()->after('cargo_com_invoice_nos');
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
