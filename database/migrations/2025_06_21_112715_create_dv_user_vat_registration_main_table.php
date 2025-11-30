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
        Schema::create('dv_user_vat_registration_main', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('vat_reg_main_id')->unsigned();
            $table->timestamps();

            $table->foreign('user_id', 'dv_user_vat_registration_main_user_id_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('vat_reg_main_id', 'dv_user_vat_registration_main_vat_reg_main_id_fk')
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
        Schema::dropIfExists('dv_user_vat_registration_main');
    }
};
