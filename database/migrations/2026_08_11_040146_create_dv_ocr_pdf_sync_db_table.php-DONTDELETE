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
        Schema::connection(config('database.ocr_connection'))
            ->create('dv_ocr_pdf_sync_db', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ocr_pdf_id');

                $table->string('invoice_type');

                $table->string('client_no');
                $table->string('client_name');

                $table->string('invoice_no');
                $table->string('invoice_date');

                $table->boolean('credit_note')->default(false);

                $table->string('currency');
                $table->string('net_amount');
                $table->string('vat_rate')->nullable();
                $table->string('vat_amount')->nullable();
                $table->string('total_amount')->nullable();
                $table->string('calc_net_amount')->nullable();
                $table->string('additional_amount')->nullable();
                $table->string('variance')->nullable();
                $table->string('adjustment_amount')->nullable();

                $table->string('exchange_currency')->nullable();
                $table->string('exchange_net_amount')->nullable();
                $table->string('exchange_rate')->nullable();
                $table->string('exchange_vat_amount')->nullable();
                $table->string('exchange_total_amount')->nullable();
                
                $table->json('related_sales_invoices')->nullable();

                $table->text('note')->nullable();
                
                $table->string('created_by_environment')->nullable();
                $table->bigInteger('created_by')->nullable()->unsigned();
                $table->string('updated_by_environment')->nullable();
                $table->bigInteger('updated_by')->nullable()->unsigned(); 

                $table->timestamps();

                $table->unique('ocr_pdf_id');
            });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::connection(config('database.ocr_connection'))
        //     ->dropIfExists('dv_ocr_pdf_sync_db');        
    }
};
