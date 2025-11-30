<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
//use Illuminate\Support\Facades\Schema;

use Brokenice\LaravelMysqlPartition\Models\Partition;
use Brokenice\LaravelMysqlPartition\Schema\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dv_import_reconciliation_sales_invoices', function (Blueprint $table) {
            //$table->id();

            $table->bigInteger('id');
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->bigInteger('com_invoice_id')->unsigned();            
                    
            $table->string('invoice_no'); 
            $table->date('invoice_date');
            $table->string('doc_status'); 
            $table->string('swiss_declaration_sub_type')->nullable();
            $table->string('country'); 
            $table->string('currency_code');
           
            $table->longText('net_amount')->nullable();   
            $table->longText('vat_amount')->nullable();
            $table->longText('total_amount')->nullable();
            $table->longText('shipping')->nullable();

            $table->integer('credit_note');              

            $table->integer('disregard_invoice')->default(0); 
            $table->longText('disregard_reason')->nullable();
            $table->longText('disregard_comment')->nullable();
            $table->integer('disregard_comment_visiblity')->default(0);

            $table->longText('comment_reason')->nullable();
            $table->longText('comment')->nullable();
            $table->integer('comment_visiblity')->default(0);

            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();

            $table->datetime('saved_at')->nullable();     
            $table->timestamps();
            $table->primary(['id','invoice_date']);

            // $table->foreign('vat_reg_id', 'dv_import_reconciliation_sales_invoices_vat_reg_id_fk')
            //     ->references('id')
            //     ->on('dv_vat_registration')
            //     ->onDelete('cascade');

            // $table->foreign('com_invoice_id', 'dv_import_reconciliation_sales_invoices_com_invoice_id_fk')
            //     ->references('id')
            //     ->on('dv_import_reconciliation_com_invoices')
            //     ->onDelete('cascade');    

            // $table->foreign('created_by', 'dv_import_reconciliation_sales_invoices_created_by_fk')
            //     ->references('id')
            //     ->on('users')
            //     ->onDelete('cascade');

            // $table->foreign('updated_by', 'dv_import_reconciliation_sales_invoices_updated_by_fk')
            //     ->references('id')
            //     ->on('users')
            //     ->onDelete('cascade');        
        });

        // Force autoincrement of one field in composite primary key
        Schema::forceAutoIncrement('dv_import_reconciliation_sales_invoices', 'id');    

        // Make partition by RANGE
        Schema::partitionByRange('dv_import_reconciliation_sales_invoices', 'YEAR(invoice_date)', [
            new Partition('sales_invoice_2023', Partition::RANGE_TYPE, 2024),
            new Partition('sales_invoice_2024', Partition::RANGE_TYPE, 2025),
            new Partition('sales_invoice_2025', Partition::RANGE_TYPE, 2026),
            new Partition('sales_invoice_2026', Partition::RANGE_TYPE, 2027),
            new Partition('sales_invoice_2027', Partition::RANGE_TYPE, 2028),
            new Partition('sales_invoice_2028', Partition::RANGE_TYPE, 2029),
            new Partition('sales_invoice_2029', Partition::RANGE_TYPE, 2030),
            new Partition('sales_invoice_2030', Partition::RANGE_TYPE, 2031),
            new Partition('sales_invoice_2031', Partition::RANGE_TYPE, 2032),
            new Partition('sales_invoice_2032', Partition::RANGE_TYPE, 2033),
            new Partition('sales_invoice_2033', Partition::RANGE_TYPE, 2034),
            new Partition('sales_invoice_2034', Partition::RANGE_TYPE, 2035),
            new Partition('sales_invoice_2035', Partition::RANGE_TYPE, 2036),
            new Partition('sales_invoice_2036', Partition::RANGE_TYPE, 2037),
            new Partition('sales_invoice_2037', Partition::RANGE_TYPE, 2038),
            new Partition('sales_invoice_2038', Partition::RANGE_TYPE, 2039),
            new Partition('sales_invoice_2039', Partition::RANGE_TYPE, 2040),
            new Partition('sales_invoice_2040', Partition::RANGE_TYPE, 2041),
            new Partition('sales_invoice_2041', Partition::RANGE_TYPE, 2042),
            new Partition('sales_invoice_2042', Partition::RANGE_TYPE, 2043),
            new Partition('sales_invoice_2043', Partition::RANGE_TYPE, 2044),
            new Partition('sales_invoice_2044', Partition::RANGE_TYPE, 2045),
            new Partition('sales_invoice_2045', Partition::RANGE_TYPE, 2046),
            new Partition('sales_invoice_2046', Partition::RANGE_TYPE, 2047),
            new Partition('sales_invoice_2047', Partition::RANGE_TYPE, 2048),
            new Partition('sales_invoice_2048', Partition::RANGE_TYPE, 2049),
            new Partition('sales_invoice_2049', Partition::RANGE_TYPE, 2050),
            new Partition('sales_invoice_2050', Partition::RANGE_TYPE, 2051)
        ], true);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dv_import_reconciliation_sales_invoices');
    }
};
