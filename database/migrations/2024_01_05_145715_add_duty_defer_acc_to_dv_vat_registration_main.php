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
        Schema::table('dv_vat_registration_main', function (Blueprint $table) {
            $table->tinyInteger('duty_defer_acc')->after('cash_acc_stmt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dv_vat_registration_main', function (Blueprint $table) {
            //
        });
    }
};
