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
        Schema::create('dv_vatcontrol_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->bigInteger('anyexcel_template_id')->nullable()->unsigned();
            $table->string('folder_id')->nullable();
            $table->string('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_vatcontrol_files_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');

            $table->foreign('anyexcel_template_id', 'dv_vatcontrol_files_anyexcel_template_id_fk')
                ->references('id')
                ->on('dv_anyexcel_templates')
                ->onUpdate('cascade'); 

            $table->foreign('created_by', 'dv_vatcontrol_files_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_vatcontrol_files_updated_by_fk')
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
        Schema::dropIfExists('dv_vatcontrol_files');
    }
};
