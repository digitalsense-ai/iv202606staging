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
        Schema::table('dv_vat_registration', function (Blueprint $table) {
            $table->bigInteger('excel_column_template_id')->nullable()->after('next_dda_date')->unsigned();

            $table->foreign('excel_column_template_id', 'dv_vat_registration_excel_column_template_id_fk')
                ->references('id')
                ->on('dv_excel_column_templates')                
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
        Schema::table('dv_vat_registration', function (Blueprint $table) {
            //
        });
    }
};
