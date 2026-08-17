<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_quote_addons', function (Blueprint $table) {
            $table->string('country_code', 10)->nullable()->after('enabled');
            $table->string('country_name')->nullable()->after('country_code');
            $table->text('description')->nullable()->after('country_name');
            $table->decimal('standard_price', 12, 2)->nullable()->after('description');
            $table->string('interval')->nullable()->after('standard_price');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('crm_quote_addons', function (Blueprint $table) {
            $table->dropColumn([
                'country_code',
                'country_name',
                'description',
                'standard_price',
                'interval',
                'discount_amount',
            ]);
        });
    }
};
