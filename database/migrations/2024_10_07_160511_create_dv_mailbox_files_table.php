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
        Schema::create('dv_mailbox_files', function (Blueprint $table) {
            $table->id();           
            $table->enum('file_type', ['pivs', 'document', 'c79', 'cas', 'dda', 'ivf', 'receipt', 'client', 'ci', 'comment', 'vatreturn'])->nullable();
            $table->bigInteger('vat_reg_main_id')->unsigned();
            $table->string('email_datetime');
            $table->string('email_subject');
            $table->string('email_id');            
            $table->string('folder_id')->nullable();
            $table->string('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('o_file_name')->nullable();
            $table->integer('status');// 0 - dismissed; 1 - active; 2 - new
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('vat_reg_main_id', 'dv_mailbox_files_vat_reg_main_id_fk')
                ->references('id')
                ->on('dv_vat_registration_main')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_mailbox_files_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_mailbox_files_updated_by_fk')
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
        Schema::dropIfExists('dv_mailbox_files');
    }
};
