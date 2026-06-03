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
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->string('cvr_number')->nullable();
            $table->string('company_name');
            $table->string('company_address');
            $table->string('company_postcode');
            $table->string('company_city');  
            $table->string('company_country');   
            $table->string('company_telephone');
            $table->string('company_email');
            $table->string('company_website');
            $table->string('company_desc');
            $table->string('company_employees')->nullable();
            $table->string('financial_year')->nullable();
            $table->decimal('revenue', 15, 2)->nullable();
            $table->enum('rating', ['good','medium','poor']);
            $table->json('potential_countries');
            $table->json('potential_products');
            $table->date('lead_date');
            $table->enum('status',['new', 'rejected', 'converted', 'reminder'])->default('new');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();           
            $table->timestamps();

            $table->foreign('created_by', 'crm_leads_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'crm_leads_updated_by_fk')
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
        Schema::dropIfExists('crm_leads');
    }
};
