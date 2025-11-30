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
        Schema::table('dv_import_vat_files', function (Blueprint $table) {
            //$table->integer('disregard_declaration')->default(0)->after('send_email'); 
            //$table->longText('disregard_reason')->nullable()->after('disregard_declaration'); 
            //$table->longText('disregard_comment')->nullable()->after('disregard_reason'); 

            $table->longText('comment_reason')->nullable()->after('send_email');
            $table->longText('comment')->nullable()->after('comment_reason');
            $table->integer('comment_visiblity')->default(0)->after('comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dv_import_vat_files', function (Blueprint $table) {
            //
        });
    }
};
