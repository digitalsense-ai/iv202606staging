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
        Schema::create('dv_team_user_client', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('team_user_id')->unsigned();
            $table->bigInteger('client_id')->unsigned();            
            $table->timestamps();

            $table->foreign('team_user_id', 'dv_team_user_client_team_user_id_fk')
                ->references('id')
                ->on('dv_team_users')
                ->onDelete('cascade');

            $table->foreign('client_id', 'dv_team_user_client_client_id_fk')
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
        Schema::dropIfExists('dv_team_user_client');
    }
};
