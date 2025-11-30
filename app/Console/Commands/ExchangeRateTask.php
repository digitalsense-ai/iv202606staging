<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use \App\Classes\CommonClass;
use Illuminate\Support\Facades\Log;

class ExchangeRateTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchangerate:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the exchange rate from the RSS feed and store it in our database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try 
        {      
            $commonClass =  new CommonClass();
            $result = $commonClass->insertExchangeRates('xml');    

            if($result == 'success')
            {
                $this->info('Todays exchange rate has been stored successfully');   
                Log::info('Todays exchange rate has been stored successfully');
            }
            else
            {
                $this->error('Error in fetching the todays exchange rate.' . $result);
                Log::error('Error in fetching the todays exchange rate.' . $result);          
            }
        }
        catch (Exception $e) 
        {
            Log::error('exchangerate:task - ' . $e->getMessage());
            return  $e->getMessage();
        }
    }
}
