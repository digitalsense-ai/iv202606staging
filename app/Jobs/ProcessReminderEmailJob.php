<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use \App\Classes\CommonClass;

class ProcessReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reminder;
    protected $sender;
    protected $authUser;
    protected $send_test_text_yes;
    protected $send_test_text_no;
    protected $sent_at;
    protected $send_to_client;
    
    protected $commonClass; 

    /**
     * Create a new job instance.
     */
    public function __construct($job_reminder, $job_sender, $job_authUser, $job_send_test_text_yes, $job_send_test_text_no, $job_sent_at = '')
    {      
        $this->reminder = $job_reminder;
        $this->sender = $job_sender;
        $this->authUser = $job_authUser;
        $this->send_test_text_yes = $job_send_test_text_yes;
        $this->send_test_text_no = $job_send_test_text_no;
        $this->sent_at = $job_sent_at;
        $this->send_to_client = $this->reminder->send_to_client;
 
        $this->commonClass = new CommonClass();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {    
        try {
         
            $this->commonClass->processReminderEmail(
                $this->reminder,
                $this->sender,
                $this->authUser,
                $this->send_test_text_yes,
                $this->send_test_text_no,
                $this->send_to_client,
                $this->sent_at           
            );
        } catch (\Exception $e) {
            \Log::error('Processing Reminder Email Job failed: ' . $e->getMessage());
            $this->failed($e);
        }                   
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Reminder Email Job failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
