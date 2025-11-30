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
        Schema::create('dv_client_cvr', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned();
            $table->string('organization_name')->nullable();
            $table->string('organization_no')->nullable();
            $table->string('person_name')->nullable();
            $table->string('person_designation')->nullable();
            $table->string('person_address')->nullable();
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('client_id', 'dv_client_cvr_client_id_fk')
                ->references('id')
                ->on('dv_clients')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_client_cvr_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_client_cvr_updated_by_fk')
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
        Schema::dropIfExists('dv_client_cvr');
    }
};
