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
        Schema::create('dv_importreconciliation_anyexcel_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->bigInteger('anyexcel_template_id')->nullable()->unsigned();
            $table->string('folder_id')->nullable();
            $table->string('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('o_file_id')->nullable();
            $table->string('o_file_name')->nullable();
            $table->string('o_file_size')->nullable();
            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_importreconciliation_anyexcel_files_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');

            $table->foreign('anyexcel_template_id', 'dv_importreconciliation_anyexcel_files_anyexcel_template_id_fk')
                ->references('id')
                ->on('dv_anyexcel_templates')                
                ->onDelete('cascade');    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dv_importreconciliation_anyexcel_files');
    }
};
