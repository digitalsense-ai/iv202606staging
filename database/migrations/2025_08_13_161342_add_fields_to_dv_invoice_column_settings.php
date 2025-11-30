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
        Schema::table('dv_invoice_column_settings', function (Blueprint $table) {            
            $table->bigInteger('client_id')->unsigned()->after('user_id');
            $table->bigInteger('vat_reg_main_id')->unsigned()->after('client_id');

            $table->foreign('client_id', 'dv_invoice_column_settings_client_id_fk')
                ->references('id')
                ->on('dv_clients')
                ->onDelete('cascade');
                
            $table->foreign('vat_reg_main_id', 'dv_invoice_column_settings_vat_reg_main_id_fk')
                ->references('id')
                ->on('dv_vat_registration_main')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_invoice_column_settings', function (Blueprint $table) {
            //
        });
    }
};
