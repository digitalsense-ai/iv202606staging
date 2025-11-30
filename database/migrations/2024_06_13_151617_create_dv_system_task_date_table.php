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
        Schema::create('dv_system_task_date', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('system_id')->unsigned();
            $table->string('task_name');
            $table->integer('task_date');            
            $table->integer('status');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('system_id', 'dv_system_task_date_system_id_fk')
                ->references('id')
                ->on('dv_system')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_system_task_date_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_system_task_date_updated_by_fk')
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
        Schema::dropIfExists('dv_system_task_date');
    }
};
