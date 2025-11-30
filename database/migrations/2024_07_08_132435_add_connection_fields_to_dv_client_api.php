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
        Schema::table('dv_client_api', function (Blueprint $table) {
            $table->string('connection_name')->after('vat_reg_main_id')->nullable();
            $table->integer('connection_status')->after('connection_name')->nullable();
            $table->longText('connection_remarks')->after('connection_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dv_client_api', function (Blueprint $table) {
            //
        });
    }
};
