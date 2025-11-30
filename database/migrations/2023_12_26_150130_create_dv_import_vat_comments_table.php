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
        Schema::create('dv_import_vat_comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('import_vat_id')->unsigned();            
            $table->longText('comment');
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned(); 
            $table->timestamps();

            $table->foreign('import_vat_id', 'dv_import_vat_comments_import_vat_id_fk')
                ->references('id')
                ->on('dv_import_vat_files')
                ->onDelete('cascade');

            $table->foreign('created_by', 'dv_import_vat_comments_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_import_vat_comments_updated_by_fk')
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
        Schema::dropIfExists('dv_import_vat_comments');
    }
};
