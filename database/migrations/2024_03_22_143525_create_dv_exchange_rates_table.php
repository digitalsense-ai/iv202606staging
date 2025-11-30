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
        Schema::create('dv_exchange_rates', function (Blueprint $table) {
            //$table->id();
            $table->bigInteger('id');
            $table->string('main_currency_code');
            $table->integer('per_unit');  
            $table->string('currency_code');  
            $table->date('exchange_date');
            $table->decimal('exchange_rate');             
            $table->timestamps();
            $table->primary(['id','exchange_date']);
        });

        // Force autoincrement of one field in composite primary key
        Schema::forceAutoIncrement('dv_exchange_rates', 'id'); 

        // Make partition by RANGE
        Schema::partitionByRange('dv_exchange_rates', 'YEAR(exchange_date)', [
            new Partition('exchange_2023', Partition::RANGE_TYPE, 2024),
            new Partition('exchange_2024', Partition::RANGE_TYPE, 2025),
            new Partition('exchange_2025', Partition::RANGE_TYPE, 2026),
            new Partition('exchange_2026', Partition::RANGE_TYPE, 2027),
            new Partition('exchange_2027', Partition::RANGE_TYPE, 2028),
            new Partition('exchange_2028', Partition::RANGE_TYPE, 2029),
            new Partition('exchange_2029', Partition::RANGE_TYPE, 2030),
            new Partition('exchange_2030', Partition::RANGE_TYPE, 2031),
            new Partition('exchange_2031', Partition::RANGE_TYPE, 2032),
            new Partition('exchange_2032', Partition::RANGE_TYPE, 2033),
            new Partition('exchange_2033', Partition::RANGE_TYPE, 2034),
            new Partition('exchange_2034', Partition::RANGE_TYPE, 2035),
            new Partition('exchange_2035', Partition::RANGE_TYPE, 2036),
            new Partition('exchange_2036', Partition::RANGE_TYPE, 2037),
            new Partition('exchange_2037', Partition::RANGE_TYPE, 2038),
            new Partition('exchange_2038', Partition::RANGE_TYPE, 2039),
            new Partition('exchange_2039', Partition::RANGE_TYPE, 2040),
            new Partition('exchange_2040', Partition::RANGE_TYPE, 2041),
            new Partition('exchange_2041', Partition::RANGE_TYPE, 2042),
            new Partition('exchange_2042', Partition::RANGE_TYPE, 2043),
            new Partition('exchange_2043', Partition::RANGE_TYPE, 2044),
            new Partition('exchange_2044', Partition::RANGE_TYPE, 2045),
            new Partition('exchange_2045', Partition::RANGE_TYPE, 2046),
            new Partition('exchange_2046', Partition::RANGE_TYPE, 2047),
            new Partition('exchange_2047', Partition::RANGE_TYPE, 2048),
            new Partition('exchange_2048', Partition::RANGE_TYPE, 2049),
            new Partition('exchange_2049', Partition::RANGE_TYPE, 2050),
            new Partition('exchange_2050', Partition::RANGE_TYPE, 2051)
        ], true); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dv_exchange_rates');
    }
};
