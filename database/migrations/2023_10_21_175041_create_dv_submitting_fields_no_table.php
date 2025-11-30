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
        Schema::create('dv_submitting_fields_no', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();            
            $table->longText('box_3')->comment('Sales and withdrawals of goods and services (standard rate)')->nullable();
            $table->longText('box_31')->comment('Sales and withdrawals of goods and services (medium rate)')->nullable();
            $table->longText('box_33')->comment('Sales and withdrawals of goods and services (low rate)')->nullable();
           
            $table->longText('box_5')->comment('Sales and withdrawals of goods and services exempt from value added tax (zero-rate)')->nullable();
            $table->longText('box_6')->comment('Sales and withdrawals of goods and services outside the scope of the Value Added Tax Act')->nullable();

            $table->longText('box_52')->comment('Sales of goods and services exempt from value added tax to other countries (zero-rate)')->nullable();

            $table->longText('box_1')->comment('Purchases of goods and services with deductions (standard rate)')->nullable();
            $table->longText('box_11')->comment('Purchases of goods and services with deductions (medium rate)')->nullable();
            $table->longText('box_13')->comment('Purchases of goods and services with deductions (low rate)')->nullable();

            $table->longText('box_32')->comment('Sales of fish and other marine wildilfe resources (11,11 %)')->nullable();
            $table->longText('box_12')->comment('Purchases of fish and other marine wildilfe resources (11,11 %)')->nullable();

            $table->longText('box_51')->comment('Sales of emission allowances and gold to businesses/self-employed persons')->nullable();
            $table->longText('box_91')->comment('Purchases of emission allowances and gold with deductions (standard rate)')->nullable();
            $table->longText('box_92')->comment('Purchases of emission allowances and gold without deductions entitlement (standard rate)')->nullable();

            $table->longText('box_86')->comment('Purchases of services from abroad with deductions (standard rate)')->nullable();
            $table->longText('box_87')->comment('Purchases of services from abroad without deductions entitlement (standard rate)')->nullable();
            $table->longText('box_88')->comment('Purchases of services from abroad with deductions (low rate)')->nullable();
            $table->longText('box_89')->comment('Purchases of services from abroad without deductions entitlement (low rate)')->nullable();

            $table->longText('box_81')->comment('Purchases of goods from abroad with deductions (standard rate)')->nullable();
            $table->longText('box_14')->comment('Deductions on purchases of goods from abroad, value added tax paid upon import (standard rate)')->nullable();
            $table->longText('box_82')->comment('Purchases of goods from abroad without deduction entitlement (standard rate)')->nullable();
            $table->longText('box_15')->comment('Deductions on purchases of goods from abroad, value added tax paid upon import (medium rate)')->nullable();
            $table->longText('box_83')->comment('Purchases of goods from abroad with deductions (medium rate)')->nullable();
            $table->longText('box_84')->comment('Purchases of goods from abroad without deduction entitlement (medium rate)')->nullable();
            $table->longText('box_85')->comment('Purchases of goods from abroad with a zero-rate')->nullable();

            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_submitting_fields_no_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
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
        Schema::dropIfExists('dv_submitting_fields_no');
    }
};
