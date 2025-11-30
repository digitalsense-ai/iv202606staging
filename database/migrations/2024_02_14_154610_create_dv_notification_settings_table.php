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
        Schema::create('dv_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned();           
            $table->enum('file_type', ['pivs', 'documents', 'c79', 'ivf', 'cas', 'dda', 'draft', 'lock']);
            $table->integer('email_notification');
            $table->timestamps();

            $table->foreign('user_id', 'dv_notification_settings_user_id_fk')
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
        Schema::dropIfExists('dv_notification_settings');
    }
};
