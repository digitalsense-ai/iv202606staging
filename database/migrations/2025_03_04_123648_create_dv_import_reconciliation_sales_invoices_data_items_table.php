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
        Schema::create('dv_import_reconciliation_sales_invoices_data_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ir_sales_invoice_data_id')->unsigned();

            $table->string('item_no'); 
            $table->string('item_order_no'); 
            $table->longText('item_name'); 
            $table->longText('item_desc'); 
            $table->integer('base_qty');
            $table->integer('qty');
            $table->string('unit_code'); 
            $table->string('tax_name'); 

            $table->longText('line_amount')->nullable();
            $table->longText('accounting_cost')->nullable();
            $table->longText('tax_amount')->nullable();
            $table->longText('net_amount')->nullable();
            $table->decimal('tax_percent')->nullable();
            $table->longText('price')->nullable();

            $table->string('seller_item_id')->nullable();
            $table->string('seller_item_schema')->nullable();
            $table->string('std_item_id')->nullable();
            $table->string('std_item_schema')->nullable();

            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();

            $table->timestamps();

            $table->foreign('ir_sales_invoice_data_id', 'dv_ir_sales_invoices_data_items_ir_sales_invoice_data_id_fk')
                ->references('id')
                ->on('dv_import_reconciliation_sales_invoices_data')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_ir_sales_invoices_data_items_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_ir_sales_invoices_data_items_updated_by_fk')
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
        Schema::dropIfExists('dv_import_reconciliation_sales_invoices_data_items');
    }
};
