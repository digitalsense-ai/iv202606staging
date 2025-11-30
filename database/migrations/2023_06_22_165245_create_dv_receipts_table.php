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
        Schema::create('dv_receipts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->string('folder_id');
            $table->string('file_id');
            $table->string('file_name');
            $table->string('file_size');
            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_receipts_vat_reg_id_fk')
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
        Schema::dropIfExists('dv_receipts');
    }
};
