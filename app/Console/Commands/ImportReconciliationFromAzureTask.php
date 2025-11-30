<?php

namespace App\Console\Commands;

use App\Models\VATRegistration;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ImportReconciliationFromAzureTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'irazure:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and store new com. and sale invoices hourly';

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
            $commonClass =  new CommonClass();
            $apiClass =  new ApiClass();
            
            $authUser = $commonClass->getAuthUser(1); 
            $system = $commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();

            $vatregs = VATRegistration::with(['vatregmain',
                                      'importreconciliationcominvoices' => function($query) {                                       
                                        $query
                                            ->where('data_from', '!=', 'ivf')
                                            ->where('data_from', '!=', 'ftp')
                                            ->orderBy('last_modified_at', 'desc')                                         
                                            ->get();
                                      }
                                    ])
                                    ->withCount('importreconciliationcominvoices')
                                    ->withCount('importreconciliationsalesinvoices')                                    
                                    ->whereHas('vatregmain', function ($subquery) {
                                        $subquery->where('product_type', 2)
                                            ->orWhere('product_type', 3)
                                            ->orWhere('product_type', 5)
                                            ; 
                                    })     
                                    ->get();
             
            $noInvoices = $vatregs;
               
            $currentDate = Carbon::now();        
            $ninetyDaysAgo = $currentDate->copy()->subDays(90);   
            foreach($noInvoices as $key => $noInvoice)
            {
                $startDate = Carbon::parse($noInvoice->service_start);
                $endDate = Carbon::parse($apiClass->getEndDateLazy($noInvoice)); 

                if (
                    ($startDate->lt($currentDate) && $endDate->gte($ninetyDaysAgo)) || // Period overlaps the last 90 days
                    ($startDate->lte($ninetyDaysAgo) && $endDate->gte($ninetyDaysAgo)) // Period starts before 90 days ago and ends after 90 days ago
                ) {                 
                    $from = 'cron';
                    $data = $commonClass->loadImportReconciliationDatasFromAzureDb($authUser, $noInvoice, $from);  
                }          
            }

            $this->info('Com. & Sales Invoices has been retrieved successfully from AZURE');    
            Log::info('Com. & Sales Invoices has been retrieved successfully from AZURE');  
        }
        catch (\Exception $e) 
        {
            Log::error('irazure:task - ' . $e->getMessage());
            return  $e->getMessage();
        }
    }
}
