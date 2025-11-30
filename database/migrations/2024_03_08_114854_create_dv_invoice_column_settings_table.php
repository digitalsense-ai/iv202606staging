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
        Schema::create('dv_invoice_column_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned();           
            // $table->enum('column_name', ['Tax code', 'Invoice Date', 'Invoice Number', 'Currency Code', 'Total NET (invoice currency)', 'VAT rate', 'Total VAT (invoice currency)', 'Total GROSS (invoice currency)', 'Local currency code', 'Exchange rate', 'Total NET (local currency)', 'Total VAT (local currency)', 'Total GROSS (local currency)', 'N', 'O', 'P', 'Q', 'Name', 'VAT number (if applicable)', 'Street', 'House and office no.', 'City', 'Postal code', 'Country code', 'PDF']);
            $table->enum('column_name', ['taxcode', 'invoicedate', 'invoiceno', 'currencycode', 'totalnet', 'vatrate', 'totalvat', 'totalgross', 'localcurrencycode', 'exchangerate', 'localtotalnet', 'localtotalvat', 'localtotalgross', 'n', 'o', 'p', 'q', 'cname', 'cvatno', 'cstreet', 'chouseofficeno', 'ccity', 'cpostalcode', 'ccountrycode', 'pdf', 'accno']);
            $table->integer('status');
            $table->timestamps();

            $table->foreign('user_id', 'dv_invoice_column_settings_user_id_fk')
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
        Schema::dropIfExists('dv_invoice_column_settings');
    }
};
