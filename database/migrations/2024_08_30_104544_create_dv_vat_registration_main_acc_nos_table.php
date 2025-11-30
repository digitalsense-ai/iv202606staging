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
        Schema::create('dv_vat_registration_main_acc_nos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_main_id')->unsigned(); 
            $table->string('acc_type');
            $table->string('acc_name');
            $table->string('acc_no');
            $table->timestamps();

            $table->foreign('vat_reg_main_id', 'dv_vat_registration_main_acc_nos_vat_reg_main_id_fk')
                ->references('id')
                ->on('dv_vat_registration_main')
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
        Schema::dropIfExists('dv_vat_registration_main_acc_nos');
    }
};
