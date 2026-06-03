<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
//use Illuminate\Database\Eloquent\Relations\MorphTo;

// use App\Models\CRMReminder;
// use App\Models\CRMLead;
// use App\Models\CRMQuote;

// use Mail;
// use App\Mail\CRMNoQuoteReminder;

use \App\Classes\CommonClass;

class CRMReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crmreminder:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send CRM Reminder email to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try 
        {                  
            $commonClass =  new CommonClass();

            $authUser = $commonClass->getAuthUser(1); 

            $result = $commonClass->scheduleCRMReminder($authUser);    

            if($result == 0)
            {
                $this->info('No CRM Reminder emails sent.');
                Log::info('No CRM Reminder emails sent.'); 
            }
            else if($result > 0)
            {
                $this->info('CRM Reminder emails has been sent successfully');   
                Log::info('CRM Reminder emails has been sent successfully');
            }
            else
            {
                $this->error('Error in sending CRM Reminder emails.' . $result);
                Log::error('Error in sending CRM Reminder emails.' . $result);
            }
        }
        catch (\Exception $e) 
        {
            Log::error('crmreminder:task - ' . $e->getMessage());
            return  $e->getMessage();
        }        
    }
}
