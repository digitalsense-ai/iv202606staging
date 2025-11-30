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
        Schema::table('dv_clients', function (Blueprint $table) {
            $table->string('api_name')->nullable();
            $table->string('api_env')->nullable();
            $table->string('api_base_url')->nullable();
            $table->string('api_tenant_id')->nullable();
            $table->string('api_client_id')->nullable();
            $table->string('api_secret_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dv_clients', function (Blueprint $table) {
            //
        });
    }
};
