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
        Schema::create('crm_contract_templates', function (Blueprint $table) {
            $table->id();
            //$table->enum('language', ['en', 'da']);
            $table->integer('clause_number');
            $table->string('title')->nullable();            
            $table->longText('content');        
            $table->string('dk_title')->nullable();    
            $table->longText('dk_content');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('created_by', 'crm_contract_templates_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'crm_contract_templates_updated_by_fk')
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
        Schema::dropIfExists('crm_contract_templates');
    }
};
