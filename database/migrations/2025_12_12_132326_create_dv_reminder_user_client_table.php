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
        Schema::create('dv_reminder_user_client', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reminder_user_id')->unsigned();
            $table->bigInteger('client_id')->unsigned();
            $table->timestamps();

            $table->foreign('reminder_user_id', 'dv_reminder_user_client_reminder_user_id_fk')
                ->references('id')
                ->on('dv_reminder_user')
                ->onDelete('cascade');

            $table->foreign('client_id', 'dv_reminder_user_client_client_id_fk')
                ->references('id')
                ->on('dv_clients')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dv_reminder_user_client');
    }
};
