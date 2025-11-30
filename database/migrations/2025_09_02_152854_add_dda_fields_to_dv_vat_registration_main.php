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
        Schema::table('dv_vat_registration_main', function (Blueprint $table) {
            $table->longText('dda_acc_no')->after('duty_defer_acc')->nullable();
            $table->longText('dda_acc_limit')->after('dda_acc_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_vat_registration_main', function (Blueprint $table) {
            //
        });
    }
};
