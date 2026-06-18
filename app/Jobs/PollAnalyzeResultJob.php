<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Models\OcrPdf;

use App\Classes\CommonClass;

use App\Mappers\InvoiceMapper;
use App\Mappers\ComInvoiceMapper;
use App\Mappers\CustomSalesInvoiceMapper;
use App\Mappers\CustomComInvoiceMapper;

use App\Services\AzureContentUnderstandingService;
use App\Services\AzureDocumentIntelligenceService;
use App\Services\MicrosoftMailService;
use App\Services\AzureStorageService;
use App\Services\OcrAccuracyService;
use App\Services\OcrParserStrategyService;
use App\Services\OcrAnalyzeService;

use App\Jobs\ValidateOcrInvoicesJob;

//use App\Http\Controllers\ocr\AnalyzePdfController;

class PollAnalyzeResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public array $clients,
        public int $documentId,
        public string $filePath,
        public string $operationUrl,
        public string $azureStudioType = 'analyzer',
        public string $invoiceType,
        public ?string $emailMessageId = null,
        public $startedAt = null,
        public ?array $prevCapture = [],
    ) {
        $this->startedAt = $startedAt ?? now();
    }

    public function handle()
    {
        /**
         * -------------------------------------------------
         * 1. Prevent re-processing completed/failed jobs
         * -------------------------------------------------
         */
        $status = OcrPdf::query()
            ->where('id', $this->documentId)
            ->value('status');

        //if (in_array($status, ['completed', 'failed', 'timeout'])) {
        if (in_array($status, ['completed', 'failed', 'timeout', 'duplicate'])) {
            $this->finalizeEmailBatchIfComplete();    
            return;
        }

        /**
         * -------------------------------------------------
         * 2. TIMEOUT CHECK
         * -------------------------------------------------
         */
        if (now()->diffInSeconds($this->startedAt) > 300) {
            OcrPdf::query()
                ->where('id', $this->documentId)
                ->update([
                    'status' => 'timeout',
                    'error' => 'Polling exceeded 5 minutes'
                ]);

            $this->finalizeEmailBatchIfComplete();

            return;
        }

        /**
         * -------------------------------------------------
         * 3. ATOMIC DB LOCK (NO RACE CONDITION)
         * -------------------------------------------------
         */        
        $locked = OcrPdf::query()
                    ->where('id', $this->documentId)
                    ->where(function ($query) {
                        $query->whereNull('polling_locked_at')
                            ->orWhere('polling_locked_at', '<', now()->subMinutes(2));
                    })
                    ->update(['polling_locked_at' => now()]);

        if ($locked === 0) {
            return; // another worker is processing
        }

        try {

            /**
             * -------------------------------------------------
             * 4. CALL AZURE
             * -------------------------------------------------
             */
            $service = $this->azureStudioType === 'model'
                ? app(AzureDocumentIntelligenceService::class)
                : app(AzureContentUnderstandingService::class);

            try {
                $result = $service->poll($this->operationUrl);
            } catch (\Throwable $e) {

                Log::error("Polling failed: " . $e->getMessage());

                self::dispatch(
                    $this->clients,
                    $this->documentId,
                    $this->filePath,
                    $this->operationUrl,
                    $this->azureStudioType,
                    $this->invoiceType,
                    $this->emailMessageId,
                    $this->startedAt,
                    $this->prevCapture
                )->delay(now()->addSeconds(10))
                 ->onQueue(config('queue.ocr.poll', 'ocrpdfinvoices'));

                return;
            }

            $status = strtolower($result['status'] ?? '');

            /**
             * -------------------------------------------------
             * 5. STILL PROCESSING → RE-DISPATCH
             * -------------------------------------------------
             */
            if (in_array($status, ['notstarted', 'running'])) {

                self::dispatch(
                    $this->clients,
                    $this->documentId,
                    $this->filePath,
                    $this->operationUrl,
                    $this->azureStudioType,
                    $this->invoiceType,
                    $this->emailMessageId,
                    $this->startedAt,
                    $this->prevCapture
                )->delay(now()->addSeconds(5))
                 ->onQueue(config('queue.ocr.poll', 'ocrpdfinvoices'));

                return;
            }

            /**
             * -------------------------------------------------
             * 6. FAILURE CASE
             * -------------------------------------------------
             */
            if ($status !== 'succeeded') {
                OcrPdf::query()
                    ->where('id', $this->documentId)
                    ->update([
                        'status' => 'failed',
                        'error' => json_encode($result),
                    ]);

                $this->finalizeEmailBatchIfComplete();

                return;
            }

            /**
             * -------------------------------------------------
             * 7. SUCCESS → MAP RESULT
             * -------------------------------------------------
             */
            $normalized = [];
            $org_no = null;
            $org_no_1 = null;
            $country = null;

            if (in_array($this->invoiceType, ['sales', 'multi-invoices'])) {

                $normalized = ($this->azureStudioType === 'model')
                    ? CustomSalesInvoiceMapper::map($result, $this->clients)
                    : InvoiceMapper::map($result);

                if($normalized)
                {
                    if(isset($normalized['change_invoice_type']))
                    {
                        if($normalized['change_invoice_type'])
                        {
                            //$controller = app(AnalyzePdfController::class);

                            $changeType = OcrPdf::query()->where('id', $this->documentId)->first();
                            $changeType->sync_status = 0;
                            $changeType->is_locked = 0;
                            $changeType->save();

                            $folder = 'com';                            
                            $batchId = $changeType->batch_id;
                            
                            //Get file from Azure storage
                            $sasPaths = $controller->getSasUrl($this->documentId, 'recapture');
                            $sasUrl = $sasPaths['signedUrl'];
                            $blobPath = $sasPaths['blobPath'];

                            $prevCaptures = [[
                                'prevId' => $this->documentId,
                                'sasUrl' => $sasUrl,
                                'blobPath' => $blobPath
                            ]];                            
                            
                            //$controller->analyzeStoredPdfs($this->clients, [$this->filePath], $folder, $batchId, null, $prevCaptures);
                            
                            $ocrAnalyzeService = new OcrAnalyzeService();
                            $ocrAnalyzeService->analyze($this->clients, [$this->filePath], $folder, $batchId, null, $prevCaptures); 

                            return;
                        }
                    }

                    if(isset($normalized['error']))
                    {
                    }
                    else
                    {
                        $org_no   = preg_replace('/\D/', '', $normalized['supplier']['cvr_number'] ?? '');
                        $org_no_1 = preg_replace('/\D/', '', $normalized['supplier']['org_number'] ?? '');
                        $country = preg_replace('/[^A-Z]/', '', $normalized['currency'] ?? '');
                    }
                }

            } elseif ($this->invoiceType === 'com') {

                $normalized = ($this->azureStudioType === 'model')
                    ? CustomComInvoiceMapper::map($result, $this->clients)
                    : ComInvoiceMapper::map($result);

                if($normalized)
                {
                    if(isset($normalized['error']))
                    {
                    }
                    else
                    {
                        $org_no   = preg_replace('/\D/', '', $normalized['recipient']['org_number'] ?? '');
                        $org_no_1 = $org_no;
                        $country = preg_replace('/[^A-Z]/', '', $normalized['currency'] ?? '');
                    }
                }
            }

            /**
             * -------------------------------------------------
             * 7a. ACCURACY SERVICE
             * -------------------------------------------------
             */
            if (is_array($normalized) && !isset($normalized['error'])) {
                // $normalized = app(OcrParserStrategyService::class)->apply(
                //     normalized: $normalized,
                //     azureResult: $result,
                //     clientId: null,
                //     invoiceType: $this->invoiceType
                // );

                $normalized = app(OcrAccuracyService::class)->enrich(
                    $normalized,
                    $result,
                    $this->invoiceType
                );
            }

            $hasNormalizedError = is_array($normalized)
                && array_key_exists('error', $normalized)
                && filled($normalized['error']);

            if ($hasNormalizedError) {
                $client_id = null;
            }

            /**
             * -------------------------------------------------
             * 8. CLIENT MATCHING
             * -------------------------------------------------
             */
            $client_id = null;

            if ($country) {
                $commonClass = new CommonClass();

                $vatregmains = $commonClass->getVatRegMainLazy(null, [
                    'country' => ['operator' => '=', 'value' => $country]
                ]);
                
                $vatregmain_filter = $vatregmains->filter(function ($vat) use ($org_no, $org_no_1) {
                    $vatOrg = trim($vat->org_no);

                    $orgNo = trim((string) $org_no);
                    $orgNo1 = trim((string) $org_no_1);

                    return (
                        ($orgNo && (stripos($orgNo, $vatOrg) !== false || $orgNo === $vatOrg)) ||
                        ($orgNo1 && $orgNo1 === $vatOrg)
                    );
                });

                if ($vatregmain_filter->count() > 0) {
                    $client_id = $vatregmain_filter->first()->client_id;
                }
            }

            /**
             * -------------------------------------------------
             * 9. SAVE RESULT
             * -------------------------------------------------
             */
            $hasNormalizedError = is_array($normalized)
                && array_key_exists('error', $normalized)
                && filled($normalized['error']);

            $finalStatus = $hasNormalizedError ? 'failed' : 'completed';

            OcrPdf::query()
                ->where('id', $this->documentId)
                ->update([
                    'client_id' => $client_id,
                    //'status' => isset($normalized['error']) ? 'failed' : 'completed',
                    //'error' => isset($normalized['error']) ? $normalized['error'] : null,
                    'status' => $finalStatus,
                    'error' => $hasNormalizedError ? $normalized['error'] : null,
                    'extracted_data' => json_encode($normalized),
                    'og_extracted_data' => json_encode($result),
                ]);

            /**
             * -------------------------------------------------
             * 10. CLEANUP FILE
             * -------------------------------------------------
             */
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            Cache::increment('inbox_completed', 1);

            /**
             * -------------------------------------------------
             * 11. EMAIL PROCESSING
             * -------------------------------------------------
             */
            if ($this->emailMessageId) {                
                $this->finalizeEmailBatchIfComplete();
            }
            else
            {
                //Re-capture
                if($this->prevCapture)
                {                    
                    $prevId = $this->prevCapture['prevId'];
                    $sasUrl = $this->prevCapture['sasUrl'];
                    $blobPath = $this->prevCapture['blobPath'];

                    //Delete prev file from Azure Blob Storage                   
                    if ($finalStatus === 'completed') {
                        $azureService = app(AzureStorageService::class);
                        $azureService->deleteFile($blobPath);

                        Log::info("Azure file deleted {$blobPath}");
                    } else {
                        Log::warning("Recapture failed; old Azure file kept {$blobPath}", [
                            'document_id' => $this->documentId,
                            'prev_id' => $prevId,
                            //'error' => $finalError,
                        ]);
                    }

                    $invoice = OcrPdf::query()->where('id', $prevId)->first();
                    $invoice->azure_sas_url = null;
                    $invoice->azure_sas_expiry = null;
                    $invoice->save();

                    $this->finalizeEmailBatchIfComplete();                    
                }
            }

        } finally {

            /**
             * -------------------------------------------------
             * 12. SAFE UNLOCK (IMPORTANT)
             * -------------------------------------------------
             */
            OcrPdf::query()
                ->where('id', $this->documentId)
                ->whereNotNull('polling_locked_at')
                ->update([
                    'polling_locked_at' => null
                ]);
        }
    }

    private function finalizeEmailBatchIfComplete(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }

        $batchId = OcrPdf::query()
            ->where('id', $this->documentId)
            ->value('batch_id');

        if (!$batchId) {
            return;
        }

        $remaining = OcrPdf::query()
            ->where('batch_id', $batchId)
            ->whereNotIn('status', ['completed', 'failed', 'duplicate', 'timeout'])
            ->count();

        if ($remaining !== 0) {
            return;
        }

        ValidateOcrInvoicesJob::dispatch($batchId)
            ->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices'));

        $cacheKey = "ocr_email_batch_finalized:{$batchId}";

        if (!Cache::add($cacheKey, true, now()->addHours(6))) {
            return;
        }

        try {
            if (!$this->emailMessageId) {
                return;
            }

            $mailService = app(MicrosoftMailService::class);

            $mailService->addCategory($this->emailMessageId);
            $mailService->markEmailAsRead($this->emailMessageId);
            $mailService->moveEmailToFolder($this->emailMessageId);

        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            throw $e;
        }
    }
}