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
        Schema::create('dv_import_reconciliation_com_invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
           
            $table->string('relation_match_no')->nullable();
            $table->bigInteger('doc_id')->unsigned()->nullable();
            
            $table->string('invoice_no'); 
            $table->date('invoice_date')->nullable(); 
            $table->string('doc_status'); 
            $table->string('swiss_declaration_sub_type')->nullable();
            $table->string('country'); 
            $table->string('currency_code');
           
            $table->longText('net_amount')->nullable();   
            $table->longText('vat_amount')->nullable();
            $table->longText('total_amount')->nullable();
            $table->longText('shipping')->nullable();
            //$table->integer('credit_note');  

            // $table->integer('disregard_invoice')->default(0); 
            // $table->longText('disregard_reason')->nullable();
            // $table->longText('disregard_comment')->nullable();
            // $table->integer('disregard_type')->default(0);

            $table->longText('comment_reason')->nullable();
            $table->longText('comment')->nullable();
            $table->integer('comment_visiblity')->default(0);

            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();

            $table->datetime('saved_at')->nullable();     
            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_import_reconciliation_com_invoices_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_import_reconciliation_com_invoices_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_import_reconciliation_com_invoices_updated_by_fk')
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
        Schema::dropIfExists('dv_import_reconciliation_com_invoices');
    }
};
