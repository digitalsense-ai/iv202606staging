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
        Schema::table('dv_reminder', function (Blueprint $table) {
            $table->string('dk_title')->after('content');
            $table->text('dk_content')->after('dk_title');
            $table->string('period')->after('dk_content');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_reminder', function (Blueprint $table) {
            //
        });
    }
};
