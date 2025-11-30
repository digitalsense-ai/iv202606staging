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
        Schema::create('api_call_results', function (Blueprint $table) {
            $table->id();
            //$table->uuid('batch_id')->index();  
            $table->bigInteger('vat_reg_id')->unsigned();
            //$table->string('batch_id')->nullable();  
            $table->integer('page_no')->nullable();  
            $table->integer('total_job')->nullable();  
            $table->string('status')->nullable();  
            //$table->json('account_no_full_datas')->nullable();            
            $table->json('account_no_datas')->nullable();     
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_call_results');
    }
};
