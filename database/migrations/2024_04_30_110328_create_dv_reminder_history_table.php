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
        Schema::create('dv_reminder_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reminder_id')->unsigned();
            $table->date('sent_at');
            $table->integer('status');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('reminder_id', 'dv_reminder_history_reminder_id_fk')
                ->references('id')
                ->on('dv_reminder')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_reminder_history_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_reminder_history_updated_by_fk')
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
        Schema::dropIfExists('dv_reminder_history');
    }
};
