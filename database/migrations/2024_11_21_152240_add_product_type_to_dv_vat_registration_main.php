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
        Schema::table('dv_vat_registration_main', function (Blueprint $table) {
            $table->tinyInteger('product_type')->after('vat_reg_type')->comment('1 - NUF VAT Return; 2 - Import Reconciliation; 3 - NUF VAT Return & Import Reconciliation; 4 - VOEC VAT Return;5 - VOEC VAT Return & Import Reconciliation');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_vat_registration_main', function (Blueprint $table) {
            //
        });
    }
};
