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
        Schema::table('dv_import_reconciliation_com_invoices', function (Blueprint $table) {
            $table->integer('disregard_invoice')->default(0)->after('shipping'); 
            $table->string('disregard_type')->nullable()->after('disregard_invoice'); 
            $table->longText('disregard_reason')->nullable()->after('disregard_invoice');
            $table->longText('disregard_comment')->nullable()->after('disregard_reason');
            $table->integer('disregard_comment_visiblity')->default(0)->after('disregard_comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_import_reconciliation_com_invoices', function (Blueprint $table) {
            //
        });
    }
};
