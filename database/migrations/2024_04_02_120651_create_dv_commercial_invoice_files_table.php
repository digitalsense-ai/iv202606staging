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
        Schema::create('dv_commercial_invoice_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->string('folder_id')->nullable();
            $table->string('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable(); 
            $table->longText('sale_invoice_nos')->nullable();       
            $table->integer('invoice_count')->default(0);
            $table->decimal('invoice_amount')->default(0.00);
            $table->decimal('invoice_shipping')->default(0.00);
            $table->decimal('invoice_handling_fee')->default(0.00);
            $table->decimal('invoice_total')->default(0.00);
            $table->integer('status');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_commercial_invoice_files_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_commercial_invoice_files_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_commercial_invoice_files_updated_by_fk')
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
        Schema::dropIfExists('dv_commercial_invoice_files');
    }
};
