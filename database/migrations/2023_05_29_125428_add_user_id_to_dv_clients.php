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
            $table->bigInteger('user_id')->unsigned()->nullable();

            $table->foreign('user_id', 'dv_clients_user_id_fk')
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
        Schema::table('dv_clients', function (Blueprint $table) {
            //
        });
    }
};
