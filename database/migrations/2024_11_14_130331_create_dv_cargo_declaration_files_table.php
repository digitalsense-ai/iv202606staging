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
        Schema::create('dv_cargo_declaration_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('import_vat_id')->nullable()->unsigned();            
            $table->string('expo_no')->nullable();
            $table->string('run_no')->nullable();
            $table->string('expo_run_no')->nullable();
            $table->string('email_datetime')->nullable();
            $table->string('email_subject')->nullable();
            $table->string('email_id')->nullable();          
            $table->string('folder_id')->nullable();
            $table->string('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('o_file_name')->nullable();
            $table->integer('status');// 0 - dismissed; 1 - active; 2 - new
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned(); 
            $table->timestamps();

            $table->foreign('import_vat_id', 'dv_cargo_declaration_files_import_vat_id_fk')
                ->references('id')
                ->on('dv_import_vat_files')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_cargo_declaration_files_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_cargo_declaration_files_updated_by_fk')
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
        Schema::dropIfExists('dv_cargo_declaration_files');
    }
};
