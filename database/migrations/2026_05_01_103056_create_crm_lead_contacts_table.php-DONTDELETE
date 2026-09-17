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
        Schema::create('crm_lead_contacts', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('lead_id')->unsigned();
            $table->string('role');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');            
            $table->string('designation')->nullable();
            $table->string('lang');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('lead_id', 'crm_lead_contacts_lead_id_fk')
                ->references('id')
                ->on('crm_leads')
                ->onDelete('cascade');

            $table->foreign('created_by', 'crm_lead_contacts_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'crm_lead_contacts_updated_by_fk')
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
        Schema::dropIfExists('crm_lead_contacts');
    }
};
