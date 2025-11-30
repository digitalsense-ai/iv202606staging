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
        Schema::create('dv_vat_registration_main_cas_dda_months', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_main_id')->unsigned(); 
            $table->string('month_year');
            $table->timestamps();

            $table->foreign('vat_reg_main_id', 'dv_vat_registration_main_cas_dda_months_vat_reg_main_id_fk')
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
        Schema::dropIfExists('dv_vat_registration_main_cas_dda_months');
    }
};
