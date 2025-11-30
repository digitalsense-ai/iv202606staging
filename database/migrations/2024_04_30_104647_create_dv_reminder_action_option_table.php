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
        Schema::create('dv_reminder_action_option', function (Blueprint $table) {
            $table->id();
            $table->enum('action_name', ['No data in folder', 'Upload missed', 'Pivs not uploaded', 'Cash Account Statement not uploaded', 'Duty Deferment Account not uploaded']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dv_reminder_action_option');
    }
};
