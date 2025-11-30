<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dv_client_cvr', function (Blueprint $table) {
            $table->integer('is_compliance')->after('person_address');
            $table->integer('compliance_status')->after('is_compliance');
            $table->string('compliance_firstname')->after('compliance_status')->nullable();
            $table->string('compliance_lastname')->after('compliance_firstname')->nullable();
            $table->string('compliance_designation')->after('compliance_lastname')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_client_cvr', function (Blueprint $table) {
            //
        });
    }
};
