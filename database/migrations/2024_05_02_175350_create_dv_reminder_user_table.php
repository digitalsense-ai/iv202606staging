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
        Schema::create('dv_reminder_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reminder_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->timestamps();

            $table->foreign('reminder_id', 'dv_reminder_user_reminder_id_fk')
                ->references('id')
                ->on('dv_reminder')
                ->onDelete('cascade');

            $table->foreign('user_id', 'dv_reminder_user_user_id_fk')
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
        Schema::dropIfExists('dv_reminder_user');
    }
};
