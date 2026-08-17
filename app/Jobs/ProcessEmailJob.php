<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use App\Services\MicrosoftMailService;
use App\Services\OcrAnalyzeService;
//use App\Http\Controllers\ocr\AnalyzePdfController;
use App\Helpers\EnvironmentHelper;

class ProcessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public array $clients,
        public string $emailId,
        public string $subject = '',
        public array $replyAttachments = []
    ) {}

    public function handle()
    {
        $environment = EnvironmentHelper::getEnvironment();;

        $mailService = new MicrosoftMailService();

        // Unique batch ID for this email
        $batchId = (string) Str::uuid();

        // Download attachments grouped by folder
        $attachments = $mailService->downloadPdfAttachments($this->emailId, $this->subject, $this->replyAttachments);
       
        // Safety check: skip if no attachments
        //if (empty($attachments)) {
        if (empty($attachments['sales']) && empty($attachments['multi-invoices']) && empty($attachments['com'])) {
            Log::warning("No PDF attachments found for email {$this->emailId}");

            $mailService->markEmailAsRead($this->emailId);
            $mailService->moveEmailToFolder($this->emailId, "No Attachment");

            Cache::increment('inbox_completed', 1);
            
            return;
        }

        if($environment === 'local')
            Log::info("SUBJECT " . $this->subject);  

        $ocrAnalyzeService = new OcrAnalyzeService();
        foreach ($attachments as $folder => $items) {
            if (!empty($items)) {
                if($environment !== 'local')
                {
                    // Trigger analysis for stored PDFs                
                    $paths = [];
                    $prevCaptures = [];
                    foreach ($items as $item) 
                    {
                        $paths[] = $item['path'];
                        $prevCaptures[] = $item['prevCapture'];
                    }               
                    $ocrAnalyzeService->analyze($this->clients, $paths, $folder, $batchId, $this->emailId, $prevCaptures);
                }

                if($environment === 'local')
                {
                    foreach ($items as $item) 
                    {   
                        $fullPath = $item['path'];
                        Log::info("Queued PDF folder " . strtoupper($folder) . " with paths {$fullPath}");               
                    }
                    Log::info("--------------- END ---------------");
                    Log::info("                                                               ");
                }
            }
        }

        // // Increment completed count after all attachments processed
        // Cache::increment('inbox_completed');

        //Log::info("Queued PDF analysis for email {$this->emailId} with batch {$batchId}");
    }
}

// class ProcessEmailJob implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable;

//     public int $tries = 3;
//     public int $backoff = 10; // seconds

//     public function __construct(
//         public string $messageId,
//         public ?string $subject = null
//     ) {}

//     public function handle()
//     {
//         $mailService = app(MicrosoftMailService::class);

//         try {
//             // 1️⃣ Download PDF attachments and trigger analysis
//             $attachments = $mailService->downloadPdfAttachments($this->messageId);

//             Log::info("Email processed: {$this->messageId}", ['attachments' => $attachments]);            

//         } catch (\Throwable $e) {
//             Log::error("Failed processing email {$this->messageId}: ".$e->getMessage(), [
//                 'trace' => $e->getTraceAsString()
//             ]);

//             // optionally release job to retry
//             $this->release(30); // retry in 30 seconds
//         }
//     }
// }