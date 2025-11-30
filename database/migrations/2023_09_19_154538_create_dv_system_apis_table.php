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
        Schema::create('dv_system_apis', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('system_id')->unsigned();
            $table->string('api_name');
            $table->string('api_env');
            $table->string('api_base_url');
            $table->string('api_tenant_id')->nullable();
            $table->string('api_client_id');
            $table->string('api_secret_key');           
            $table->text('api_token')->nullable();
            $table->datetime('api_token_expire')->nullable();
            $table->text('access_token')->nullable();
            $table->string('api_user_id')->nullable();
            $table->string('one_drive_root_id')->nullable();
            $table->integer('status');
            $table->timestamps();

            $table->foreign('system_id', 'dv_system_apis_system_id_fk')
                ->references('id')
                ->on('dv_system')
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
        Schema::dropIfExists('dv_system_apis');
    }
};
