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
        Schema::create('dv_system_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('system_id')->unsigned();
            $table->string('file_type');
            $table->string('file_id');
            $table->string('file_name');
            $table->string('file_size');
            $table->timestamps();

            $table->foreign('system_id', 'dv_system_files_system_id_fk')
                ->references('id')
                ->on('dv_system')
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
        Schema::dropIfExists('dv_system_files');
    }
};
