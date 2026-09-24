<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dv_vat_registration_main_acc_nos', function (Blueprint $table) {
            $table->boolean('use_base_currency_amount')->default(false)->after('map_column');
        });

        DB::table('dv_client_api')
            ->where('use_base_currency_amount', 1)
            ->whereNotNull('vat_reg_main_id')
            ->pluck('vat_reg_main_id')
            ->each(function ($vatRegMainId) {
                DB::table('dv_vat_registration_main_acc_nos')
                    ->where('vat_reg_main_id', $vatRegMainId)
                    ->update(['use_base_currency_amount' => true]);
            });
    }

    public function down()
    {
        Schema::table('dv_vat_registration_main_acc_nos', function (Blueprint $table) {
            $table->dropColumn('use_base_currency_amount');
        });
    }
};