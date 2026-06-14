<?php

namespace App\Jobs;

use App\Services\AzureContentUnderstandingService;
use App\Services\AzureDocumentIntelligenceService;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

use App\Jobs\PollAnalyzeResultJob;

use App\Models\OcrPdf;

class SubmitAnalyzeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public array $clients,
        public int $documentId,
        public string $filePath,
        public string $fileName,
        public string $azureStudioType = 'analyzer',
        public string $analyzerId,        
        public string $invoiceType,
        public ?string $emailMessageId = null,
        public ?array $prevCapture = []
    ) {}
   
    public function handle()
    {
        $key = 'azure-ocr-rate-limit';

        if (RateLimiter::tooManyAttempts($key, 15)) {
            $this->release(5);
            return;
        }

        RateLimiter::hit($key, 1);

        $service = $this->azureStudioType === 'model'
            ? app(AzureDocumentIntelligenceService::class)
            : app(AzureContentUnderstandingService::class);

        // STREAM PDF → AZURE
        $operationUrl = $service->analyze(
            $this->filePath,  // STRING path, not fopen
            $this->fileName,
            $this->analyzerId
        );

        OcrPdf::query()->where('id', $this->documentId)->update([
            'operation_url' => $operationUrl,
            'status' => 'processing',
        ]);

        $updated = OcrPdf::query()
                    ->where('id', $this->documentId)
                    ->where('status', 'processing')
                    ->update(['status' => 'polling']);

        if (!$updated) {
            return;
        }

        PollAnalyzeResultJob::dispatch(
            $this->clients,
            $this->documentId,
            $this->filePath,
            $operationUrl,
            $this->azureStudioType,
            $this->invoiceType,
            $this->emailMessageId,
            now(),
            $this->prevCapture,
        )->delay(now()->addSeconds(10))->onQueue(config('queue.ocr.poll', 'ocrpdfinvoices'));
    }
}
