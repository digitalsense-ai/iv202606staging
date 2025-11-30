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
        Schema::create('dv_vatreturn_comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->string('subject');
            $table->text('comment');
            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_vatreturn_comments_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
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
        Schema::dropIfExists('dv_vatreturn_comments');
    }
};
