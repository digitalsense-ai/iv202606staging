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
        Schema::create('dv_company_team_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('team_user_id')->unsigned();
            $table->timestamps();

            $table->foreign('company_id', 'dv_company_team_user_company_id_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('team_user_id', 'dv_company_team_user_team_user_id_fk')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('dv_company_team_user');
    }
};
