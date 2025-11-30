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
        Schema::create('dv_files_email_note', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->enum('file_type', ['pivs', 'document', 'c79', 'cas', 'dda', 'ivf', 'draft', 'lock']);
            $table->bigInteger('file_id')->nullable()->unsigned();
            $table->longText('email_note');
            $table->bigInteger('created_by')->nullable()->unsigned();            
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->unsigned();

            $table->foreign('vat_reg_id', 'dv_files_email_note_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_files_email_note_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_files_email_note_updated_by_fk')
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
        Schema::dropIfExists('dv_files_email_note');
    }
};
