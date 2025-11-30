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
        Schema::table('dv_client_files', function (Blueprint $table) {
            $table->string('file_for')->default('other')->after('client_id')->comment('profile_pic - Profile Photo; cover_pic - Cover Picture; other - Other');
            $table->string('subject')->nullable()->after('file_for');
            $table->integer('is_locked')->default(0)->after('subject');
            $table->string('o_file_name')->nullable()->after('file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_client_files', function (Blueprint $table) {
            //
        });
    }
};
