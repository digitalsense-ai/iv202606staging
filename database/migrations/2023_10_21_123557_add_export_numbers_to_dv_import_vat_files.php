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
            $table->decimal('e_fee_number')->after('statistical_number')->nullable();
            $table->decimal('e_statistical_number')->after('e_fee_number')->nullable();
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
