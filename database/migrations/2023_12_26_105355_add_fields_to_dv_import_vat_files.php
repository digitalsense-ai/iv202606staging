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
        Schema::table('dv_import_vat_files', function (Blueprint $table) {            
            $table->longText('adjustment_no')->after('e_statistical_number')->comment('Justering')->nullable();
            $table->longText('invoice_total')->after('adjustment_no')->comment('Fakturasum')->nullable();
            $table->tinyInteger('send_email')->after('invoice_total')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dv_import_vat_files', function (Blueprint $table) {
            //
        });
    }
};
