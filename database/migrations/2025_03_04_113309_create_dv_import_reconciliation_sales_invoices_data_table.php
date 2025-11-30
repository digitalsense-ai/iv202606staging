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
        Schema::create('dv_import_reconciliation_sales_invoices_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ir_file_id')->unsigned();

            $table->string('invoice_no'); 
            $table->date('invoice_date');
            $table->string('order_no')->nullable(); 
            $table->string('currency_code');
            $table->longText('note')->nullable();

            $table->string('sender_name')->nullable(); 
            $table->string('sender_street')->nullable(); 
            $table->string('sender_houseno')->nullable();
            $table->string('sender_city')->nullable(); 
            $table->string('sender_postcode')->nullable(); 
            $table->string('sender_countrycode')->nullable(); 
            $table->string('sender_vatno')->nullable(); 
            $table->string('sender_email')->nullable(); 
            $table->string('sender_website')->nullable(); 
            $table->string('sender_endpoint')->nullable(); 
            $table->string('sender_contact_name')->nullable();
            $table->string('sender_contact_email')->nullable();
            $table->string('sender_contact_telephone')->nullable();

            $table->string('buyer_name'); 
            $table->string('buyer_street'); 
            $table->string('buyer_houseno')->nullable(); 
            $table->string('buyer_city'); 
            $table->string('buyer_postcode'); 
            $table->string('buyer_countrycode'); 
            $table->string('buyer_vatno')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_website')->nullable();
            $table->string('buyer_endpoint')->nullable();
            $table->string('buyer_contact_name'); 
            $table->string('buyer_contact_email')->nullable();
            $table->string('buyer_contact_telephone')->nullable();

            $table->date('delivery_date')->nullable();           
            $table->string('delivery_street')->nullable(); 
            $table->string('delivery_houseno')->nullable();
            $table->string('delivery_city')->nullable(); 
            $table->string('delivery_postcode')->nullable(); 
            $table->string('delivery_countrycode')->nullable(); 

            $table->string('payment_id')->nullable(); 
            $table->string('payment_branch_id')->nullable(); 
            $table->date('payment_due_date')->nullable(); 
            $table->string('payment_institute_name')->nullable();
            $table->string('payment_type_id')->nullable();
            $table->string('payment_note')->nullable();
            $table->longText('payment_discount_percent')->nullable();
            $table->longText('payment_amount')->nullable();
            $table->string('payment_currency_code')->nullable();
            $table->date('payment_settlement_date')->nullable(); 
            $table->date('payment_penalty_date')->nullable(); 

            $table->longText('tax_total_amount')->nullable();
            $table->string('tax_total_amount_currency_code')->nullable();
            $table->longText('tax_total_net_amount')->nullable();
            $table->string('tax_total_net_amount_currency_code')->nullable();
            $table->decimal('tax_total_percent')->nullable();
            $table->string('tax_total_name')->nullable();

            $table->longText('total_line_amount')->nullable();
            $table->string('total_line_currency_code')->nullable();
            $table->longText('total_tax_excl_amount')->nullable();
            $table->string('total_tax_excl_currency_code')->nullable();
            $table->longText('total_tax_incl_amount')->nullable();
            $table->string('total_tax_incl_currency_code')->nullable();
            $table->longText('total_payable_amount')->nullable();
            $table->string('total_payable_currency_code')->nullable();

            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();

            $table->timestamps();

            $table->foreign('ir_file_id', 'dv_import_reconciliation_sales_invoices_data_ir_file_id_fk')
                ->references('id')
                ->on('dv_import_reconciliation_files')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_import_reconciliation_sales_invoices_data_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_import_reconciliation_sales_invoices_data_updated_by_fk')
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
        Schema::dropIfExists('dv_import_reconciliation_sales_invoices_data');
    }
};
