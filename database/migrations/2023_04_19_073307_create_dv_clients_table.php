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
        Schema::create('dv_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('lrep_fname');
            $table->string('lrep_sname');
            $table->string('lrep_email')->unique();
            $table->string('lrep_position');
            $table->string('off_houseno');
            $table->string('off_street');
            $table->string('off_officeno');
            $table->string('off_city');
            $table->string('off_postcode');
            $table->string('off_country');
            $table->string('telephone');
            $table->string('vatno');            
            $table->string('email')->unique();
            $table->string('short_desc', 2048);
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
        Schema::dropIfExists('dv_clients');
    }
};
