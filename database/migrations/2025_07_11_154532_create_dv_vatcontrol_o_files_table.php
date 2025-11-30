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
        Schema::create('dv_vatcontrol_o_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vatcontrol_file_id')->unsigned();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->string('folder_id')->nullable();
            $table->string('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('o_file_name')->nullable();
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('vatcontrol_file_id', 'dv_vatcontrol_o_files_vatcontrol_file_id_fk')
                ->references('id')
                ->on('dv_vatcontrol_files')
                ->onDelete('cascade');

            $table->foreign('vat_reg_id', 'dv_vatcontrol_o_files_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');    

            $table->foreign('created_by', 'dv_vatcontrol_o_files_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_vatcontrol_o_files_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dv_vatcontrol_o_files');
    }
};
