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
        Schema::create('dv_excel_column_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            //$table->integer('header_row');
            $table->json('columns');
            $table->integer('status');
            $table->unsignedInteger('version')->default(1);
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('created_by', 'dv_excel_column_templates_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_excel_column_templates_updated_by_fk')
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
        Schema::dropIfExists('dv_excel_column_templates');
    }
};
