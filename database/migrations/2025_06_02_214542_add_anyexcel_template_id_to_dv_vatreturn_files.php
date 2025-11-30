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
        Schema::table('dv_vatreturn_files', function (Blueprint $table) {
            $table->bigInteger('anyexcel_template_id')->nullable()->after('excel_column_template_id')->unsigned();

            $table->foreign('anyexcel_template_id', 'dv_vatreturn_files_anyexcel_template_id_fk')
                ->references('id')
                ->on('dv_anyexcel_templates')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_vatreturn_files', function (Blueprint $table) {
            //
        });
    }
};
