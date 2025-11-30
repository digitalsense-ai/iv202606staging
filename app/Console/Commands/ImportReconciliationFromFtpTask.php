<?php

namespace App\Console\Commands;

use App\Models\VATRegistration;

use \App\Classes\CommonClass;
use \App\Classes\FtpClass;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportReconciliationFromFtpTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'irftp:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and store sale invoices from FTP daily at 1AM';

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
            $ftpClass = new FtpClass();
            
            $authUser = $commonClass->getAuthUser(1); 
            $system = $commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();

            $vatregs = VATRegistration::with(['vatregmain','client'])                                                            
                                    ->whereHas('vatregmain', function ($subquery) {
                                        $subquery->where('product_type', 2)
                                            ->orWhere('product_type', 3)
                                            ->orWhere('product_type', 5); 
                                    })  
                                    ->whereHas('client', function ($subquery) {                                        
                                        $subquery->where('client_name', 'LIKE', '%aubo%');
                                        $subquery->orWhere('client_name', 'LIKE', '%beck%');
                                        $subquery->orWhere('client_name', 'LIKE', '%geisler%');
                                        $subquery->orWhere('client_name', 'LIKE', '%noscomed%');
                                        $subquery->orWhere('client_name', 'LIKE', '%rexholm%');
                                        $subquery->orWhere('client_name', 'LIKE', '%villy%');
                                    })                                    
                                    ->get();
            
            $unique_client_ids = [];           
            foreach($vatregs as $key => $vatreg)
            {   
                $client_id = $vatreg->client->id;
                $client_name = $vatreg->client->client_name;

                if(!in_array($client_id, $unique_client_ids, true)) 
                {               
                    array_push($unique_client_ids, $client_id);

                    $refresh = true;
                    $from = 'cron';
                    $which_folder = 'main';
                                       
                    /* -- READ XML FILE FROM FTP -- */
                    $ftpdata = $ftpClass->getImportReconciliationFilesFromFtp($vatreg, $authUser, $which_folder); 
                    /* --end READ XML FILE FROM FTP -- */
                    
                    /* -- READ XML FILE FROM E-FACTO -- */
                    if (stripos(strtolower($client_name), "noscomed") !== false ||
                        stripos(strtolower($client_name), "rexholm") !== false)
                    { 
                      $efacto_ftp_data = $ftpClass->getImportReconciliationFilesFromFtp($vatreg, $authUser, $which_folder, true);

                      if($ftpdata)
                        $ftpdata = array_merge($ftpdata, $efacto_ftp_data);                          
                    }
                    /* --end READ XML FILE FROM E-FACTO -- */    
                } //run  one time for ech client
            }

            $this->info('Sales Invoices has been retrieved successfully from FTP');  
            Log::info('Sales Invoices has been retrieved successfully from FTP');          
        }
        catch (\Exception $e) 
        {
            Log::error('irftp:task - ' . $e->getMessage());
            return  $e->getMessage();
        }
    }
}
