<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Classes\CommonClass;

use App\Mappers\InvoiceMapper;
use App\Mappers\ComInvoiceMapper;
use App\Mappers\CustomSalesInvoiceMapper;
use App\Mappers\CustomComInvoiceMapper;

use App\Services\AzureContentUnderstandingService;
use App\Services\AzureDocumentIntelligenceService;
use App\Services\MicrosoftMailService;
use App\Services\AzureStorageService;

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
        $status = DB::table('dv_invoice_ocr_pdfs')
            ->where('id', $this->documentId)
            ->value('status');

        if (in_array($status, ['completed', 'failed', 'timeout'])) {
            return;
        }

        /**
         * -------------------------------------------------
         * 2. TIMEOUT CHECK
         * -------------------------------------------------
         */
        if (now()->diffInSeconds($this->startedAt) > 300) {
            DB::table('dv_invoice_ocr_pdfs')
                ->where('id', $this->documentId)
                ->update([
                    'status' => 'timeout',
                    'error' => 'Polling exceeded 5 minutes'
                ]);

            return;
        }

        /**
         * -------------------------------------------------
         * 3. ATOMIC DB LOCK (NO RACE CONDITION)
         * -------------------------------------------------
         */
        $locked = DB::update("
            UPDATE dv_invoice_ocr_pdfs
            SET polling_locked_at = NOW()
            WHERE id = ?
            AND polling_locked_at IS NULL
        ", [$this->documentId]);

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
                 ->onQueue('ocrpdfinvoices');

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
                 ->onQueue('ocrpdfinvoices');

                return;
            }

            /**
             * -------------------------------------------------
             * 6. FAILURE CASE
             * -------------------------------------------------
             */
            if ($status !== 'succeeded') {
                DB::table('dv_invoice_ocr_pdfs')
                    ->where('id', $this->documentId)
                    ->update([
                        'status' => 'failed',
                        'error' => json_encode($result),
                    ]);

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
            DB::table('dv_invoice_ocr_pdfs')
                ->where('id', $this->documentId)
                ->update([
                    'client_id' => $client_id,
                    'status' => isset($normalized['error']) ? 'failed' : 'completed',
                    'error' => isset($normalized['error']) ? $normalized['error'] : null,
                    'extracted_data' => json_encode($normalized),
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

                $batchId = DB::table('dv_invoice_ocr_pdfs')
                    ->where('id', $this->documentId)
                    ->value('batch_id');

                $remaining = DB::table('dv_invoice_ocr_pdfs')
                    ->where('batch_id', $batchId)
                    ->whereNotIn('status', ['completed', 'failed'])
                    ->count();

                // Cache::increment('inbox_completed', 1);

                if ($remaining === 0) {

                    $mailService = app(MicrosoftMailService::class);

                    $mailService->addCategory($this->emailMessageId);
                    $mailService->markEmailAsRead($this->emailMessageId);
                    $mailService->moveEmailToFolder($this->emailMessageId);
                }
            }
            else
            {
                //Re-capture
                if($this->prevCapture)
                {                    
                    $prevId = $this->prevCapture['prevId'];
                    $sasUrl = $this->prevCapture['sasUrl'];

                    //Delete prev file from Azure Blob Storage
                    $azureService = app(AzureStorageService::class);
                    $azureService->deleteFile($sasUrl);

                    // if ($this->invoiceType == 'multi-invoices')
                    // {
                    //     $prevocrpdf = InvoiceOcrPdf::where('id', $prevId)->first();

                    //     if($prevocrpdf)
                    //     {
                    //         $prevFileName = preg_replace('/_\d+\.pdf$/', '', $prevocrpdf->file_name);

                    //         //Delete prev row from database
                    //         $already_exist = InvoiceOcrPdf::where('invoice_type', $this->invoiceType)
                    //                             ->where('file_name', 'LIKE', $prevFileName . '%\.pdf')->delete();
                    //     }
                    // }
                }
            }

        } finally {

            /**
             * -------------------------------------------------
             * 12. SAFE UNLOCK (IMPORTANT)
             * -------------------------------------------------
             */
            DB::table('dv_invoice_ocr_pdfs')
                ->where('id', $this->documentId)
                ->whereNotNull('polling_locked_at')
                ->update([
                    'polling_locked_at' => null
                ]);
        }
    }
}