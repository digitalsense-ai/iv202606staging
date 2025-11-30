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
        Schema::create('dv_client_qa', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned();
            $table->string('country');

            $table->date('est_date')->nullable();
            $table->longtext('est_name')->nullable();
            $table->longtext('est_warehouse_address')->nullable();
            $table->longtext('est_warehouse')->nullable();
            $table->longtext('est_new_warehouse')->nullable();
            $table->longtext('est_showroom')->nullable();
            $table->longtext('est_branch')->nullable();
            $table->longtext('est_office')->nullable();
            $table->longtext('est_office_employee')->nullable();
            $table->longtext('est_emp_authority')->nullable();
            $table->longtext('est_emp_role')->nullable();
            $table->longtext('est_emp_type')->nullable();
            $table->longtext('est_emp_stay')->nullable();
            $table->longtext('est_agent')->nullable();
            $table->longtext('est_invoice')->nullable();
            $table->longtext('est_subcontractor')->nullable();
            $table->longtext('est_goods_value')->nullable();
            $table->longtext('est_services_value')->nullable();
            $table->longtext('est_industry_regulation')->nullable();
            $table->longtext('est_cost_element')->nullable();

            $table->longtext('gs_desc')->nullable();
            $table->longtext('gs_value')->nullable();
            $table->longtext('gs_annual_turnover')->nullable();
            $table->longtext('gs_internal_consumption')->nullable();
            $table->longtext('gs_sell')->nullable();
            $table->longtext('gs_sell_value')->nullable();
            $table->longtext('gs_free_sample')->nullable();
            $table->longtext('gs_influencer')->nullable();
            $table->longtext('gs_vat_exempt')->nullable();
            $table->longtext('gs_vat_exempt_turnover')->nullable();
            $table->longtext('gs_service')->nullable();
            $table->longtext('gs_service_value')->nullable();
            $table->longtext('gs_event')->nullable();
            $table->longtext('gs_market')->nullable();
            $table->longtext('gs_real_estate')->nullable();

            $table->longtext('eu_acquisition_turnover')->nullable();
            $table->longtext('eu_reg_export_turnover')->nullable();
            $table->longtext('eu_import_turnover')->nullable();
            $table->longtext('eu_export_turnover')->nullable();
            $table->longtext('eu_export_owner')->nullable();

            $table->longtext('ie_import_turnover')->nullable();
            $table->longtext('ie_export_turnover')->nullable();
            $table->longtext('ie_export_owner')->nullable();

            $table->longtext('about_vat_countries')->nullable();
            $table->longtext('about_warehouse_countries')->nullable();
            $table->longtext('about_sell_countries')->nullable();
            $table->longtext('about_originate_countries')->nullable();
            $table->longtext('about_suppliers')->nullable();
            $table->longtext('about_freight')->nullable();
            $table->longtext('about_bank_details')->nullable();
            $table->longtext('about_erp')->nullable();
            $table->longtext('about_erp_contact')->nullable();
            $table->longtext('about_main_contact')->nullable();
            $table->longtext('about_cvr_contact')->nullable();
            $table->longtext('about_invoice_email')->nullable();
            $table->longtext('about_invoice_contact')->nullable();
            $table->longtext('about_scan_contact')->nullable();           

            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('client_id', 'dv_client_qa_client_id_fk')
                ->references('id')
                ->on('dv_clients')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_client_qa_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_client_qa_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');     
        });

        Schema::create('dv_client_qa_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('qa_id')->unsigned();
            $table->string('file_type')->comment('name - Director Name; address - Director Address');
            $table->string('folder_id')->nullable();
            $table->string('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('o_file_name')->nullable();
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('qa_id', 'dv_client_qa_files_qa_id_fk')
                ->references('id')
                ->on('dv_client_qa')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_client_qa_files_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_client_qa_files_updated_by_fk')
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
        Schema::dropIfExists('dv_client_qa');
        Schema::dropIfExists('dv_client_qa_files');
    }
};
