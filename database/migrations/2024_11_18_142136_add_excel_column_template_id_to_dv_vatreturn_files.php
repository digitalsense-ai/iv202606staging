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
            $table->bigInteger('excel_column_template_id')->nullable()->after('vat_reg_id')->unsigned();

            $table->foreign('excel_column_template_id', 'dv_vatreturn_files_excel_column_template_id_fk')
                ->references('id')
                ->on('dv_excel_column_templates')                
                ->onDelete('cascade');
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
