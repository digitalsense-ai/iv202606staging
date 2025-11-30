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
        Schema::create('dv_submitting_fields', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->string('period_key');
            $table->longText('box_1');
            $table->longText('box_2');
            $table->longText('box_3');
            $table->longText('box_4');
            $table->longText('box_5');
            $table->longText('box_6');
            $table->longText('box_7');
            $table->longText('box_8');
            $table->longText('box_9');
            $table->integer('status');
            $table->date('processing_date')->nullable();
            $table->string('payment_indicator')->nullable();
            $table->integer('form_bundle_number')->nullable();
            $table->string('charge_ref_number')->nullable();
            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_submitting_fields_vat_reg_id_fk')
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
        Schema::dropIfExists('dv_submitting_fields');
    }
};
