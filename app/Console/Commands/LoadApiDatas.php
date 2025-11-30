<?php

namespace App\Console\Commands;

use App\Models\VATRegistration;

use \App\Classes\CommonClass;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoadApiDatas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apidatas:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and store new invoices daily at 1AM';

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
           
            $authUser = $commonClass->getAuthUser(1); 
            $system = $commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();

            $vatregs = VATRegistration::withCount('invoices')->get();

            $noInvoices = $vatregs->filter(function($vatreg, $key){
                return $vatreg->invoices_count == 0;
            });
       
            //CHECK and CREATE VAT Reg. Row            
            foreach($noInvoices as $key => $noInvoice)
            {
                $vat_reg_id = $noInvoice->id;

                $vatreg = $commonClass->getSpecificVatRegQuery($vat_reg_id); 
                $vatregmain_status = $vatreg->vatregmain->status;

                if($vatregmain_status)
                {
                    $refresh = true;
                    $from = 'cron';
                    $data = $commonClass->loadApiDatas($authUser, $vatreg, $systemapi, $refresh, $from);
                }
            }

            $this->info('Invoices has been updated successfully'); 
            Log::info('Invoices has been updated successfully');         
        }
        catch (\Exception $e) 
        {
            Log::error('apidatas:load - ' . $e->getMessage());
            return  $e->getMessage();
        }
    }
}
