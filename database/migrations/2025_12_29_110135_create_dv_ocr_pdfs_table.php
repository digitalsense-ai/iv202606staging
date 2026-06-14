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
        //Schema::table('dv_invoice_ocr_pdfs', function (Blueprint $table) {
        Schema::connection(config('database.ocr_connection'))
            ->create('dv_ocr_pdfs', function (Blueprint $table) {
                $table->id();
                //$table->unsignedBigInteger('client_id');
                $table->bigInteger('client_id')->unsigned()->nullable();
                $table->uuid('batch_id')->index();
                //$table->string('invoice_type')->after('batch_id');
                $table->string('invoice_type');
                $table->string('file_name');
                $table->string('analyzer_id');
                $table->string('status')->default('queued');
                $table->text('operation_url')->nullable();
                $table->json('extracted_data')->nullable();
                $table->text('error')->nullable();
                $table->string('source_environment');
                $table->bigInteger('created_by')->nullable()->unsigned();
                $table->bigInteger('updated_by')->nullable()->unsigned(); 
                $table->timestamps();

                // $table->foreign('client_id', 'dv_ocr_pdfs_client_id_fk')
                //     ->references('id')
                //     ->on('dv_clients')
                //     ->onDelete('cascade');

                // $table->foreign('created_by', 'dv_ocr_pdfs_created_by_fk')
                //     ->references('id')
                //     ->on('users')
                //     ->onDelete('cascade');

                // $table->foreign('updated_by', 'dv_ocr_pdfs_updated_by_fk')
                //     ->references('id')
                //     ->on('users')
                //     ->onDelete('cascade');  
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('database.ocr_connection'))
            ->dropIfExists('dv_ocr_pdfs');
    }
};
