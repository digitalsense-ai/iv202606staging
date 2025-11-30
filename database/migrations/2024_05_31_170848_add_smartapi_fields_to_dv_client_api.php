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
            $table->longText('sales_invoice_url')->after('api_base_url')->nullable();
            $table->longText('purchase_invoice_url')->after('sales_invoice_url')->nullable();
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
