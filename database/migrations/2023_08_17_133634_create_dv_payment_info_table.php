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
        Schema::create('dv_payment_info', function (Blueprint $table) {
            $table->id();
            $table->string('countrycode');
            $table->string('bankname');
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->string('postcode');
            $table->string('sortcode');
            $table->string('accountno');
            $table->string('accountname');
            $table->string('paymentref');
            $table->string('bic');
            $table->string('iban');           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dv_payment_info');
    }
};
