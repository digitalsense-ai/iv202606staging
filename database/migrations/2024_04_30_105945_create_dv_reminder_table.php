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
        Schema::create('dv_reminder', function (Blueprint $table) {
            $table->id();            
            $table->bigInteger('vat_reg_main_id')->unsigned();
            $table->bigInteger('action_id')->unsigned();              
            $table->enum('schedule', ['Does not repeat', 'Every second week', 'Each Month', 'Every Year']);
            $table->datetime('start_at');
            $table->string('title');
            $table->text('content');
            $table->integer('status');
            $table->integer('close_status');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('vat_reg_main_id', 'dv_reminder_vat_reg_main_id_fk')
                ->references('id')
                ->on('dv_vat_registration_main')
                ->onDelete('cascade');    

            $table->foreign('action_id', 'dv_reminder_action_id_fk')
                ->references('id')
                ->on('dv_reminder_action_option')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_reminder_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_reminder_updated_by_fk')
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
        Schema::dropIfExists('dv_reminder');
    }
};
