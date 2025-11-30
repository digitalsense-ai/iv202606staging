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
        Schema::table('dv_vat_registration_main_acc_nos', function (Blueprint $table) {
            $table->integer('is_reverse')->default(0)->after('acc_name');
            $table->string('map_column')->nullable()->after('is_reverse')->comment('net_sales - Net Sales; vat_sales - Output VAT (on Sales); net_purchases - Net Purchases; vat_purchases - Output VAT (on Purchases)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_vat_registration_main_acc_nos', function (Blueprint $table) {
            //
        });
    }
};
