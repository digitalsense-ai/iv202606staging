<?php

namespace App\Console\Commands;

use \App\Classes\EFactoClass;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportReconciliationFromEfactoTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'irefacto:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve sale invoices from E-FACTO and store in FTP daily at 1AM';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //return Command::SUCCESS;

        try 
        {
            $eFactoClass =  new EFactoClass();
                       
            $efacto = $eFactoClass->getAllInvoicesLazy();

            $this->info('Sales Invoices has been retrieved from E-FACTO and stored in FTP successfully'); 
            Log::info('Sales Invoices has been retrieved from E-FACTO and stored in FTP successfully');       
        }
        catch (\Exception $e) 
        {
            Log::error('irefacto:task - ' . $e->getMessage());
            return  $e->getMessage();
        }
    }
}
