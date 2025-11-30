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
        Schema::table('dv_email_notifications', function (Blueprint $table) {            
            $table->bigInteger('reminder_action_id')->unsigned()->after('status')->nullable();

            $table->foreign('reminder_action_id', 'dv_email_notifications_reminder_action_id_fk')
                ->references('id')
                ->on('dv_reminder_action_option')
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
        Schema::table('dv_email_notifications', function (Blueprint $table) {
            //
        });
    }
};
