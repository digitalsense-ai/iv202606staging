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
            $table->renameColumn('gb_vat', 'vat_no');

            $table->longText('zaz_no')->after('eori_no')->nullable();
            $table->longText('steuer_no')->after('zaz_no')->nullable();
            $table->longText('cvr_no')->after('steuer_no')->nullable();
            $table->longText('omz_no')->after('cvr_no')->nullable();
            $table->longText('nip_no')->after('omz_no')->nullable();
            $table->longText('fo_no')->after('nip_no')->nullable();
            $table->longText('siret_no')->after('fo_no')->nullable();
            $table->longText('nif_no')->after('siret_no')->nullable();
            $table->longText('nipc_no')->after('nif_no')->nullable();            
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
