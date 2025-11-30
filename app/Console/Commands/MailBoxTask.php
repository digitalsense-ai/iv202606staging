<?php

namespace App\Console\Commands;

use App\Models\VATRegistration;

use \App\Classes\CommonClass;
use \App\Classes\EmailBoxApiClass;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MailBoxTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailbox:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read mailbox daily at 12AM';

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
            $emailBoxApiClass =  new EmailBoxApiClass();
            
            $authUser = $commonClass->getAuthUser(1); 
                   
            //Read mailbox
            $mailbox = $emailBoxApiClass->readEmailForCompany($authUser);
           
            $this->info('Mailbox read and moved the atachment to one-drive successfully');
            Log::info('Mailbox read and moved the atachment to one-drive successfully');               
        }
        catch (\Exception $e) 
        {
            Log::error('mailbox:task - ' . $e->getMessage());
            return  $e->getMessage();
        }
    }
}
