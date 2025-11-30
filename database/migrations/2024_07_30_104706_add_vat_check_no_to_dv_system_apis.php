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
        Schema::table('dv_system_apis', function (Blueprint $table) {
            $table->integer('vat_start_no')->after('status')->nullable();
            $table->json('vat_dead_nos')->after('vat_start_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dv_system_apis', function (Blueprint $table) {
            //
        });
    }
};
