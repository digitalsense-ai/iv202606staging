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
        Schema::table('dv_users', function (Blueprint $table) {
            $table->integer('is_compliance')->after('status');
            $table->integer('compliance_status')->after('is_compliance');
            $table->string('compliance_firstname')->after('compliance_status')->nullable();
            $table->string('compliance_lastname')->after('compliance_firstname')->nullable();
            $table->string('compliance_designation')->after('compliance_lastname')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dv_users', function (Blueprint $table) {
            //
        });
    }
};
