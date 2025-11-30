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
        Schema::create('dv_vatreturn_notes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->string('type')->comment('specific - Specific Notes; general - General Notes');            
            $table->text('notes');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('client_id', 'dv_vatreturn_notes_client_id_fk')
                ->references('id')
                ->on('dv_clients')
                ->onDelete('cascade');
                
            $table->foreign('vat_reg_id', 'dv_vatreturn_notes_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_vatreturn_notes_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_vatreturn_notes_updated_by_fk')
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
        Schema::dropIfExists('dv_vatreturn_notes');
    }
};
