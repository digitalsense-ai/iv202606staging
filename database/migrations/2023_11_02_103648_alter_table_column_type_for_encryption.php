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
        Schema::table('dv_client_api', function (Blueprint $table) {
            $table->longText('api_tenant_id')->change();
            $table->longText('api_client_id')->change();
            $table->longText('api_secret_key')->change();
            $table->longText('api_company_id')->change();
            $table->longText('api_token')->change();
            $table->longText('api_token_expire')->change();
        });

        Schema::table('dv_client_api_vat_acc_no', function (Blueprint $table) {
            $table->longText('sales_vat_ac_no')->change();
            $table->longText('purchase_vat_ac_no')->change();
            $table->longText('vat_payable_ac_no')->change();
            $table->longText('total_vat_ac_no')->change();            
        });

        Schema::table('dv_documents', function (Blueprint $table) {
            $table->longText('doc_numbers')->change();                
        });
        
        Schema::table('dv_import_vat_files', function (Blueprint $table) {
            $table->longText('fee_number')->change();
            $table->longText('statistical_number')->change();
            $table->longText('e_fee_number')->change();
            $table->longText('e_statistical_number')->change();            
        });

        Schema::table('dv_pivs_files', function (Blueprint $table) {
            $table->longText('month_total')->change();                
        });

        Schema::table('dv_submitting_fields', function (Blueprint $table) {
            $table->longText('box_1')->change();
            $table->longText('box_2')->change();
            $table->longText('box_3')->change();
            $table->longText('box_4')->change();            
            $table->longText('box_5')->change();
            $table->longText('box_6')->change();
            $table->longText('box_7')->change();
            $table->longText('box_8')->change();   
            $table->longText('box_9')->change();   
            $table->longText('processing_date')->change();
            $table->longText('payment_indicator')->change();
            $table->longText('form_bundle_number')->change();
            $table->longText('charge_ref_number')->change(); 
        });

        Schema::table('dv_submitting_fields_no', function (Blueprint $table) {
            $table->longText('box_3')->change();
            $table->longText('box_31')->change();
            $table->longText('box_33')->change();
            $table->longText('box_5')->change();
            $table->longText('box_6')->change();

            $table->longText('box_52')->change();

            $table->longText('box_1')->change();   
            $table->longText('box_11')->change();   
            $table->longText('box_13')->change();   

            $table->longText('box_32')->change();   
            $table->longText('box_12')->change(); 

            $table->longText('box_51')->change();
            $table->longText('box_91')->change();   
            $table->longText('box_92')->change(); 

            $table->longText('box_86')->change(); 
            $table->longText('box_87')->change(); 
            $table->longText('box_88')->change(); 
            $table->longText('box_89')->change(); 

            $table->longText('box_81')->change(); 
            $table->longText('box_14')->change(); 
            $table->longText('box_82')->change(); 
            $table->longText('box_15')->change(); 
            $table->longText('box_83')->change();
            $table->longText('box_84')->change();
            $table->longText('box_85')->change();            
        });

        Schema::table('dv_system_apis', function (Blueprint $table) {
            $table->longText('api_tenant_id')->change();
            $table->longText('api_client_id')->change();
            $table->longText('api_secret_key')->change();            
            $table->longText('api_token')->change();
            $table->longText('api_token_expire')->change();

            $table->longText('access_token')->change();
            $table->longText('api_user_id')->change();
            $table->longText('one_drive_root_id')->change();          
        });

        Schema::table('dv_vat_returns', function (Blueprint $table) {
            $table->longText('vat_amount')->change();
            $table->longText('net_amount')->change();                
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
