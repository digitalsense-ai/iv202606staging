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
        Schema::table('dv_clients', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_clients_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_clients_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');  
        });

        Schema::table('dv_receipts', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_receipts_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_receipts_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');  
        }); 

        Schema::table('dv_submitting_fields', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_submitting_fields_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_submitting_fields_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');  
        });

        Schema::table('dv_vat_registration_main', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_vat_registration_main_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_vat_registration_main_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');  
        }); 

        Schema::table('dv_vat_registration', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_vat_registration_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_vat_registration_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');  
        });

        Schema::table('dv_users', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_users_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_users_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');  
        });

        Schema::table('dv_vatreturn_comment_files', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_vatreturn_comment_files_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_vatreturn_comment_files_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');  
        });

        Schema::table('dv_vatreturn_files', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_vatreturn_files_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_vatreturn_files_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');  
        });

        Schema::table('dv_vat_returns', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->unsigned()->after('created_at');
            $table->bigInteger('updated_by')->nullable()->unsigned()->after('updated_at');

            $table->foreign('created_by', 'dv_vat_returns_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'dv_vat_returns_updated_by_fk')
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
        //
    }
};
