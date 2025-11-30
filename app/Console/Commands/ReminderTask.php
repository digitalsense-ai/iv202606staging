<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use \App\Classes\CommonClass;
use Illuminate\Support\Facades\Log;

class ReminderTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder email to Client and Team users';

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
            $result = $commonClass->scheduleReminderEmail($authUser);

            if($result)
            {
                $this->info('Reminder email has been sent successfully'. $result);   
                Log::info('Reminder email has been sent successfully' . $result);      
            }
            else
            {
                $this->error('Error in sending reminder email.' . $result);          
                Log::error('Error in sending reminder email.' . $result);   
            }
        }
        catch (\Exception $e) 
        {
            Log::error('reminder:task - ' . $e->getMessage());
            return  $e->getMessage();
        }
    }
}
