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
            $table->bigInteger('vat_reg_main_id')->unsigned()->after('client_id')->nullable();

            $table->foreign('vat_reg_main_id', 'dv_client_api_vat_reg_main_id_fk')
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
        Schema::table('dv_client_api', function (Blueprint $table) {
            //
        });
    }
};
