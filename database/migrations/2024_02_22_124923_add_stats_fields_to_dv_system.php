<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dv_system', function (Blueprint $table) {            
            $table->string('stats_file_id')->nullable()->after('status');
            $table->string('stats_file_name')->nullable()->after('stats_file_id');
            $table->string('stats_file_size')->nullable()->after('stats_file_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dv_system', function (Blueprint $table) {
            //
        });
    }
};
