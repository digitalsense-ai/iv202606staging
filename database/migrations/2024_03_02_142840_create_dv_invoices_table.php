<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
//use Illuminate\Support\Facades\Schema;

use Brokenice\LaravelMysqlPartition\Models\Partition;
use Brokenice\LaravelMysqlPartition\Schema\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dv_invoices', function (Blueprint $table) {
            //$table->id();
            $table->bigInteger('id');
            $table->bigInteger('vat_reg_id')->unsigned();
            $table->enum('invoice_type', ['sale', 'purchase']);  
            $table->longText('invoice_id')->nullable();
            $table->enum('tax_code', [
                //NO
                'DPGS', 'DPGS_CN','IMPTS', 'IMPTS_CN','IMPTGD', 
                'IMPTGD_CN','IMPTGND', 'IMPTGND_CN','DSGS', 
                'DSGS_CN','EXG', 'EXG_CN','EXS', 'EXS_CN',
                //UK
                'DPGRC', 'DPGRC_CN', 'DPSRC', 'DPSRC_CN', 
                'DSSRC', 'DSSRC_CN', 'DSGRC', 'DSGRC_CN'
            ])->nullable();
            $table->date('invoice_date');
            $table->string('invoice_no');   
            $table->string('currency_code');   
            $table->longText('total_net');
            $table->decimal('vat_rate');
            $table->longText('total_vat');
            $table->longText('total_gross');
            $table->string('local_currency_code')->nullable(); 
            $table->decimal('exchange_rate')->nullable(); 
            $table->longText('local_total_net')->nullable(); 
            $table->longText('local_total_vat')->nullable();
            $table->longText('local_total_gross')->nullable();
            $table->string('n')->nullable(); 
            $table->string('o')->nullable(); 
            $table->string('p')->nullable(); 
            $table->string('q')->nullable(); 
            $table->string('c_name'); 
            $table->string('c_vat_no')->nullable(); 
            $table->string('c_street')->nullable(); 
            $table->string('c_house_no')->nullable(); 
            $table->string('c_city')->nullable(); 
            $table->string('c_postcode')->nullable(); 
            $table->string('c_country')->nullable(); 
            $table->bigInteger('created_by')->nullable()->unsigned();
            $table->bigInteger('updated_by')->nullable()->unsigned();
            $table->timestamps();
            $table->primary(['id','invoice_date']);
        });

        // Force autoincrement of one field in composite primary key
        Schema::forceAutoIncrement('dv_invoices', 'id');    

        // Make partition by RANGE
        Schema::partitionByRange('dv_invoices', 'YEAR(invoice_date)', [
            new Partition('invoice_2023', Partition::RANGE_TYPE, 2024),
            new Partition('invoice_2024', Partition::RANGE_TYPE, 2025),
            new Partition('invoice_2025', Partition::RANGE_TYPE, 2026),
            new Partition('invoice_2026', Partition::RANGE_TYPE, 2027),
            new Partition('invoice_2027', Partition::RANGE_TYPE, 2028),
            new Partition('invoice_2028', Partition::RANGE_TYPE, 2029),
            new Partition('invoice_2029', Partition::RANGE_TYPE, 2030),
            new Partition('invoice_2030', Partition::RANGE_TYPE, 2031),
            new Partition('invoice_2031', Partition::RANGE_TYPE, 2032),
            new Partition('invoice_2032', Partition::RANGE_TYPE, 2033),
            new Partition('invoice_2033', Partition::RANGE_TYPE, 2034),
            new Partition('invoice_2034', Partition::RANGE_TYPE, 2035),
            new Partition('invoice_2035', Partition::RANGE_TYPE, 2036),
            new Partition('invoice_2036', Partition::RANGE_TYPE, 2037),
            new Partition('invoice_2037', Partition::RANGE_TYPE, 2038),
            new Partition('invoice_2038', Partition::RANGE_TYPE, 2039),
            new Partition('invoice_2039', Partition::RANGE_TYPE, 2040),
            new Partition('invoice_2040', Partition::RANGE_TYPE, 2041),
            new Partition('invoice_2041', Partition::RANGE_TYPE, 2042),
            new Partition('invoice_2042', Partition::RANGE_TYPE, 2043),
            new Partition('invoice_2043', Partition::RANGE_TYPE, 2044),
            new Partition('invoice_2044', Partition::RANGE_TYPE, 2045),
            new Partition('invoice_2045', Partition::RANGE_TYPE, 2046),
            new Partition('invoice_2046', Partition::RANGE_TYPE, 2047),
            new Partition('invoice_2047', Partition::RANGE_TYPE, 2048),
            new Partition('invoice_2048', Partition::RANGE_TYPE, 2049),
            new Partition('invoice_2049', Partition::RANGE_TYPE, 2050),
            new Partition('invoice_2050', Partition::RANGE_TYPE, 2051)
        ], true);
            
        // DB::statement("ALTER TABLE `dv_invoices` 
        //     PARTITION BY RANGE(YEAR(invoice_date))
        //     (
        //         PARTITION invoice_2023 VALUES LESS THAN (2024),
        //         PARTITION invoice_2024 VALUES LESS THAN (2025),
        //         PARTITION invoice_2025 VALUES LESS THAN (2026),    
        //         PARTITION invoice_2026 VALUES LESS THAN (2027),
        //         PARTITION invoice_2027 VALUES LESS THAN (2028),
        //         PARTITION invoice_2028 VALUES LESS THAN (2029),
        //         PARTITION invoice_2029 VALUES LESS THAN (2030),
        //         PARTITION invoice_2030 VALUES LESS THAN (2031),
        //         PARTITION invoice_2031 VALUES LESS THAN (2032),
        //         PARTITION invoice_2032 VALUES LESS THAN (2033),
        //         PARTITION invoice_2033 VALUES LESS THAN (2034),
        //         PARTITION invoice_2034 VALUES LESS THAN (2035),
        //         PARTITION invoice_2035 VALUES LESS THAN (2036),
        //         PARTITION invoice_2036 VALUES LESS THAN (2037),
        //         PARTITION invoice_2037 VALUES LESS THAN (2038),
        //         PARTITION invoice_2038 VALUES LESS THAN (2039),
        //         PARTITION invoice_2039 VALUES LESS THAN (2040),
        //         PARTITION invoice_2040 VALUES LESS THAN (2041),
        //         PARTITION invoice_2041 VALUES LESS THAN (2042),
        //         PARTITION invoice_2042 VALUES LESS THAN (2043),
        //         PARTITION invoice_2043 VALUES LESS THAN (2044),
        //         PARTITION invoice_2044 VALUES LESS THAN (2045),
        //         PARTITION invoice_2045 VALUES LESS THAN (2046),
        //         PARTITION invoice_2046 VALUES LESS THAN (2047),
        //         PARTITION invoice_2047 VALUES LESS THAN (2048), 
        //         PARTITION future VALUES LESS THAN (MAXVALUE)
        //     );
        // ");

//         DB::statement("CREATE TABLE `dv_invoices` (
//   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
//   `vat_reg_id` bigint unsigned NOT NULL,
//   `invoice_type` enum('sale','purchase') COLLATE utf8mb4_unicode_ci NOT NULL,
//   `invoice_id` longtext COLLATE utf8mb4_unicode_ci,
//   `tax_code` enum('DPGS','DPGS_CN','IMPTS','IMPTS_CN','IMPTGD','IMPTGD_CN','IMPTGND','IMPTGND_CN','DSGS','DSGS_CN','EXG','EXG_CN','EXS','EXS_CN','DPGRC','DPGRC_CN','DPSRC','DPSRC_CN','DSSRC','DSSRC_CN','DSGRC','DSGRC_CN') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `invoice_date` date NOT NULL,
//   `invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
//   `currency_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
//   `total_net` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
//   `vat_rate` decimal(8,2) NOT NULL,
//   `total_vat` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
//   `total_gross` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
//   `local_currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `exchange_rate` decimal(8,2) DEFAULT NULL,
//   `local_total_net` longtext COLLATE utf8mb4_unicode_ci,
//   `local_total_vat` longtext COLLATE utf8mb4_unicode_ci,
//   `local_total_gross` longtext COLLATE utf8mb4_unicode_ci,
//   `n` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `o` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `p` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `q` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `c_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
//   `c_vat_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `c_street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `c_house_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `c_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `c_postcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `c_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//   `created_by` bigint unsigned DEFAULT NULL,
//   `updated_by` bigint unsigned DEFAULT NULL,
//   `created_at` timestamp NULL DEFAULT NULL,
//   `updated_at` timestamp NULL DEFAULT NULL,
//   PRIMARY KEY (`id`,`invoice_date`),
//   KEY `dv_invoices_vat_reg_id_fk` (`vat_reg_id`),
//   KEY `dv_invoices_created_by_fk` (`created_by`),
//   KEY `dv_invoices_updated_by_fk` (`updated_by`)  
// ) ENGINE=InnoDB AUTO_INCREMENT=24745 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
// PARTITION BY RANGE(YEAR(invoice_date))
// (
//     PARTITION invoice_2023 VALUES LESS THAN (2024),
//     PARTITION invoice_2024 VALUES LESS THAN (2025),
//     PARTITION invoice_2025 VALUES LESS THAN (2026),    
//     PARTITION invoice_2026 VALUES LESS THAN (2027),
//     PARTITION invoice_2027 VALUES LESS THAN (2028),
//     PARTITION invoice_2028 VALUES LESS THAN (2029),
//     PARTITION invoice_2029 VALUES LESS THAN (2030),
//     PARTITION invoice_2030 VALUES LESS THAN (2031),
//     PARTITION invoice_2031 VALUES LESS THAN (2032),
//     PARTITION invoice_2032 VALUES LESS THAN (2033),
//     PARTITION invoice_2033 VALUES LESS THAN (2034),
//     PARTITION invoice_2034 VALUES LESS THAN (2035),
//     PARTITION invoice_2035 VALUES LESS THAN (2036),
//     PARTITION invoice_2036 VALUES LESS THAN (2037),
//     PARTITION invoice_2037 VALUES LESS THAN (2038),
//     PARTITION invoice_2038 VALUES LESS THAN (2039),
//     PARTITION invoice_2039 VALUES LESS THAN (2040),
//     PARTITION invoice_2040 VALUES LESS THAN (2041),
//     PARTITION invoice_2041 VALUES LESS THAN (2042),
//     PARTITION invoice_2042 VALUES LESS THAN (2043),
//     PARTITION invoice_2043 VALUES LESS THAN (2044),
//     PARTITION invoice_2044 VALUES LESS THAN (2045),
//     PARTITION invoice_2045 VALUES LESS THAN (2046),
//     PARTITION invoice_2046 VALUES LESS THAN (2047),
//     PARTITION invoice_2047 VALUES LESS THAN (2048), 
//     PARTITION future VALUES LESS THAN (MAXVALUE)
// );
// ");       

        // Schema::create('dv_invoices', function (Blueprint $table) {
        //     $table->id();
        //     $table->bigInteger('vat_reg_id')->unsigned();
        //     $table->enum('invoice_type', ['sale', 'purchase']);  
        //     $table->longText('invoice_id')->nullable();
        //     $table->enum('tax_code', [
        //         //NO
        //         'DPGS', 'DPGS_CN','IMPTS', 'IMPTS_CN','IMPTGD', 
        //         'IMPTGD_CN','IMPTGND', 'IMPTGND_CN','DSGS', 
        //         'DSGS_CN','EXG', 'EXG_CN','EXS', 'EXS_CN',
        //         //UK
        //         'DPGRC', 'DPGRC_CN', 'DPSRC', 'DPSRC_CN', 
        //         'DSSRC', 'DSSRC_CN', 'DSGRC', 'DSGRC_CN'
        //     ])->nullable();
        //     $table->date('invoice_date');
        //     $table->string('invoice_no');   
        //     $table->string('currency_code');   
        //     $table->longText('total_net');
        //     $table->decimal('vat_rate');
        //     $table->longText('total_vat');
        //     $table->longText('total_gross');
        //     $table->string('local_currency_code')->nullable(); 
        //     $table->decimal('exchange_rate')->nullable(); 
        //     $table->longText('local_total_net')->nullable(); 
        //     $table->longText('local_total_vat')->nullable();
        //     $table->longText('local_total_gross')->nullable();
        //     $table->string('n')->nullable(); 
        //     $table->string('o')->nullable(); 
        //     $table->string('p')->nullable(); 
        //     $table->string('q')->nullable(); 
        //     $table->string('c_name'); 
        //     $table->string('c_vat_no')->nullable(); 
        //     $table->string('c_street')->nullable(); 
        //     $table->string('c_house_no')->nullable(); 
        //     $table->string('c_city')->nullable(); 
        //     $table->string('c_postcode')->nullable(); 
        //     $table->string('c_country')->nullable(); 
        //     $table->bigInteger('created_by')->nullable()->unsigned();
        //     $table->bigInteger('updated_by')->nullable()->unsigned();
        //     $table->timestamps();
        //     //$table->primary(['id','invoice_date']);

        //     // Force autoincrement of one field in composite primary key
        //     //Schema::forceAutoIncrement('dv_invoices', 'id');

        //     // $table->foreign('vat_reg_id', 'dv_invoices_vat_reg_id_fk')
        //     //     ->references('id')
        //     //     ->on('dv_vat_registration')
        //     //     ->onDelete('cascade');

        //     // $table->foreign('created_by', 'dv_invoices_created_by_fk')
        //     //     ->references('id')
        //     //     ->on('users')
        //     //     ->onDelete('cascade');

        //     // $table->foreign('updated_by', 'dv_invoices_updated_by_fk')
        //     //     ->references('id')
        //     //     ->on('users')
        //     //     ->onDelete('cascade');  

        //     // Schema::partitionByRange('dv_invoices', 'YEAR(invoice_date)', [
        //     //     new Partition('invoice_2023', Partition::RANGE_TYPE, 2023),
        //     //     new Partition('invoice_2024', Partition::RANGE_TYPE, 2024),
        //     //     new Partition('invoice_2025', Partition::RANGE_TYPE, 2025),
        //     //     new Partition('invoice_2026', Partition::RANGE_TYPE, 2026),
        //     //     new Partition('invoice_2027', Partition::RANGE_TYPE, 2027),
        //     //     new Partition('invoice_2028', Partition::RANGE_TYPE, 2028),
        //     //     new Partition('invoice_2029', Partition::RANGE_TYPE, 2029),
        //     //     new Partition('invoice_2030', Partition::RANGE_TYPE, 2030),
        //     //     new Partition('invoice_2031', Partition::RANGE_TYPE, 2031),
        //     //     new Partition('invoice_2032', Partition::RANGE_TYPE, 2032),
        //     //     new Partition('invoice_2033', Partition::RANGE_TYPE, 2033),
        //     //     new Partition('invoice_2034', Partition::RANGE_TYPE, 2034),
        //     //     new Partition('invoice_2035', Partition::RANGE_TYPE, 2035),
        //     //     new Partition('invoice_2036', Partition::RANGE_TYPE, 2036),
        //     //     new Partition('invoice_2037', Partition::RANGE_TYPE, 2037),
        //     //     new Partition('invoice_2038', Partition::RANGE_TYPE, 2038),
        //     //     new Partition('invoice_2039', Partition::RANGE_TYPE, 2039),
        //     //     new Partition('invoice_2040', Partition::RANGE_TYPE, 2040),
        //     //     new Partition('invoice_2041', Partition::RANGE_TYPE, 2041),
        //     //     new Partition('invoice_2042', Partition::RANGE_TYPE, 2042),
        //     //     new Partition('invoice_2043', Partition::RANGE_TYPE, 2043),
        //     //     new Partition('invoice_2044', Partition::RANGE_TYPE, 2044),
        //     //     new Partition('invoice_2045', Partition::RANGE_TYPE, 2045),
        //     //     new Partition('invoice_2046', Partition::RANGE_TYPE, 2046),
        //     //     new Partition('invoice_2047', Partition::RANGE_TYPE, 2047)
        //     // ], true);    
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dv_invoices');
    }
};
