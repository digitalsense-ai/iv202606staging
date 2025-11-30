<?php

namespace App\Console\Commands;

use App\Models\VATRegistration;

use \App\Classes\CommonClass;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VatRegTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vatreg:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and create new VAT Reg. daily at 12AM';

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
                   
            //CHECK and CREATE VAT Reg. Row
            $vat_reg_main = $commonClass->checkAndCreateVATReg($authUser);
           
            $this->info('VAT Reg. has been created successfully');
            Log::info('VAT Reg. has been created successfully');             
        }
        catch (\Exception $e) 
        {
            Log::error('vatreg:task - ' . $e->getMessage());
            return  $e->getMessage();
        }
    }
}
