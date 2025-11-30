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
        Schema::create('dv_client_api_vat_acc_no', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned(); 
            $table->bigInteger('client_api_id')->unsigned();  
            $table->string('country');    
            $table->string('sales_vat_ac_no');    
            $table->string('purchase_vat_ac_no');    
            $table->string('total_vat_ac_no');    
            $table->timestamps();

            $table->foreign('client_id', 'dv_client_api_vat_acc_no_client_id_fk')
                ->references('id')
                ->on('dv_clients')
                ->onDelete('cascade');

            $table->foreign('client_api_id', 'dv_client_api_vat_acc_no_client_api_id_fk')
                ->references('id')
                ->on('dv_client_api')
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
        Schema::dropIfExists('dv_client_api_vat_acc_no');
    }
};
