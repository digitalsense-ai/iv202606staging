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
        Schema::create('crm_quotes', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('lead_id')->unsigned();
            //$table->integer('version')->default(1);
            $table->string('version', 255)->default('1');
            $table->unsignedBigInteger('parent_quote_id')->nullable();
            $table->unsignedBigInteger('root_quote_id')->nullable();
            $table->string('package');
            $table->decimal('base_price', 12,2)->default(0);
            $table->decimal('registration_price', 12,2)->default(0);
            $table->enum('status',['active','negotiation','approved','rejected'])
                  ->default('active');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned(); 
            $table->timestamps();

            $table->foreign('lead_id', 'crm_quotes_lead_id_fk')
                ->references('id')
                ->on('crm_leads')
                ->onDelete('cascade');

            $table->foreign('created_by', 'crm_quotes_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'crm_quotes_updated_by_fk')
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
        Schema::dropIfExists('crm_quotes');
    }
};
