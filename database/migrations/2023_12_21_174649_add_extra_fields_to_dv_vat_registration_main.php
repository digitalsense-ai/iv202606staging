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
            //For GB
            $table->longText('gb_vat')->after('cash_acc_stmt')->nullable();
            $table->longText('eori_no')->after('gb_vat')->nullable();
            $table->longText('cash_account_no')->after('eori_no')->nullable();

            //For NO
            $table->longText('mva_no')->after('cash_account_no')->nullable();
            $table->longText('org_no')->after('mva_no')->nullable();
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
