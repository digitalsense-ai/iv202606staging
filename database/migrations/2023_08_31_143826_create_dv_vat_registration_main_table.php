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
        Schema::create('dv_vat_registration_main', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned();
            $table->string('country');
            $table->date('service_start');
            $table->date('turnover_date');
            $table->string('general_periods');   
            $table->integer('status');  
            $table->timestamps();

            $table->foreign('client_id', 'dv_vat_registration_client_id_fk')
                ->references('id')
                ->on('dv_clients')
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
        Schema::dropIfExists('dv_vat_registration_main');
    }
};
