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
        Schema::create('dv_email_notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->string('message_id');
            $table->string('subject');
            $table->string('name');
            $table->string('email');
            $table->enum('status', ['delivered', 'opened', 'clicked', 'bounced','sent','pending','complaint'])->default('pending');
            $table->timestamp('delivered_on')->nullable();
            $table->timestamp('opened_on')->nullable();
            $table->timestamp('clicked_on')->nullable();
            $table->timestamp('bounced_on')->nullable();
            $table->timestamp('sent_on')->nullable();
            $table->timestamp('complaint_on')->nullable();  
            $table->bigInteger('sent_by')->nullable()->unsigned();          
            $table->timestamps();
            
            $table->foreign('vat_reg_id', 'dv_email_notifications_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');

            $table->foreign('sent_by', 'dv_email_notifications_sent_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');   

            $table->unique(["vat_reg_id", "message_id"], 'dv_email_notifications_vat_reg_id_message_id_unique');     
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dv_email_notifications');
    }
};
