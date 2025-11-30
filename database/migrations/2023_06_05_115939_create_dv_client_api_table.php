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
        Schema::create('dv_client_api', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned();     
            $table->string('api_name');
            $table->string('api_env');
            $table->string('api_base_url');
            $table->string('api_tenant_id')->nullable();
            $table->string('api_client_id');
            $table->string('api_secret_key');
            $table->string('api_company_id')->nullable();
            $table->text('api_token')->nullable();
            $table->datetime('api_token_expire')->nullable();
            $table->integer('status');
            $table->timestamps();

            $table->foreign('client_id', 'dv_client_api_client_id_fk')
                ->references('id')
                ->on('dv_clients')
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
        Schema::dropIfExists('dv_api_details');
    }
};
