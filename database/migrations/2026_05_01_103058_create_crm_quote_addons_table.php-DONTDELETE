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
        Schema::create('crm_quote_addons', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('quote_id')->unsigned();            
            $table->string('addon_name');
            $table->boolean('enabled')->default(false);
            $table->decimal('price', 12,2)->default(0);
            $table->timestamps();

            // $table->foreign('lead_id', 'crm_quote_addons_lead_id_fk')
            //     ->references('id')
            //     ->on('crm_leads')
            //     ->onDelete('cascade');

            $table->foreign('quote_id', 'crm_quote_addons_quote_id_fk')
                ->references('id')
                ->on('crm_quotes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_quote_addons');
    }
};
