<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dv_user_vat_registration', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->timestamps();

            $table->foreign('user_id', 'dv_user_vat_registration_user_id_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('vat_reg_id', 'dv_user_vat_registration_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dv_user_vat_registration');
    }
};
