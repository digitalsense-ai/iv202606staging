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
        Schema::table('dv_anyexcel_templates', function (Blueprint $table) {
            $table->bigInteger('client_id')->unsigned()->after('id')->nullable();

            $table->foreign('client_id', 'dv_anyexcel_templates_client_id_fk')
                ->references('id')
                ->on('dv_clients')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_anyexcel_templates', function (Blueprint $table) {
            //
        });
    }
};
