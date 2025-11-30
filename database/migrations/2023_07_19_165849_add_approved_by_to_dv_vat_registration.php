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
        Schema::table('dv_vat_registration', function (Blueprint $table) {            
            $table->bigInteger('email_by')->nullable()->unsigned();
            $table->timestamp('email_at')->nullable();
            
            $table->bigInteger('approved_by')->nullable()->unsigned();
            $table->timestamp('approved_at')->nullable();

            $table->bigInteger('receipt_by')->nullable()->unsigned();
            $table->timestamp('receipt_at')->nullable();

            $table->bigInteger('locked_by')->nullable()->unsigned();
            $table->timestamp('locked_at')->nullable();

            $table->foreign('email_by', 'dv_vat_registration_email_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('approved_by', 'dv_vat_registration_approved_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
                
            $table->foreign('receipt_by', 'dv_vat_registration_receipt_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
                
            $table->foreign('locked_by', 'dv_vat_registration_locked_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dv_vat_registration', function (Blueprint $table) {
            //
        });
    }
};
