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
            $table->longText('uk_gateway_userid')->after('org_no')->nullable();
            $table->longText('uk_gateway_password')->after('uk_gateway_userid')->nullable();
            $table->longText('cds_gateway_userid')->after('uk_gateway_password')->nullable();
            $table->longText('cds_gateway_password')->after('cds_gateway_userid')->nullable();
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
