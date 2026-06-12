<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Models\InvoiceOcrPdf;

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

use App\Jobs\ValidateOcrInvoicesJob;

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
        $status = DB::table('dv_invoice_ocr_pdfs')
            ->where('id', $this->documentId)
            ->value('status');

        if (in_array($status, ['completed', 'failed', 'timeout', 'duplicate'])) {
            $this->finalizeEmailBatchIfComplete();
            return;
        }

        if (now()->diffInSeconds($this->startedAt) > 300) {
            DB::table('dv_invoice_ocr_pdfs')
                ->where('id', $this->documentId)
                ->update([
                    'status' => 'timeout',
                    'error' => 'Polling exceeded 5 minutes'
                ]);

            $this->finalizeEmailBatchIfComplete();
            return;
        }

        $locked = DB::table('dv_invoice_ocr_pdfs')
            ->where('id', $this->documentId)
            ->where(function ($query) {
                $query->whereNull('polling_locked_at')
                    ->orWhere('polling_locked_at', '<', now()->subMinutes(2));
            })
            ->update(['polling_locked_at' => now()]);

        if ($locked === 0) {
            return;
        }

        try {
            $service = $this->azureStudioType === 'model'
                ? app(AzureDocumentIntelligenceService::class)
                : app(AzureContentUnderstandingService::class);

            try {
                $result = $service->poll($this->operationUrl);
            } catch (\Throwable $e) {
                Log::error('Polling failed: ' . $e->getMessage());
                $this->redispatchPoll(10);
                return;
            }

            $status = strtolower($result['status'] ?? '');

            if (in_array($status, ['notstarted', 'running'])) {
                $this->redispatchPoll(5);
                return;
            }

            if ($status !== 'succeeded') {
                DB::table('dv_invoice_ocr_pdfs')
                    ->where('id', $this->documentId)
                    ->update([
                        'status' => 'failed',
                        'error' => json_encode($result),
                        'og_extracted_data' => json_encode($result),
                    ]);

                $this->finalizeEmailBatchIfComplete();
                return;
            }

            $normalized = [];
            $org_no = null;
            $org_no_1 = null;
            $country = null;

            if (in_array($this->invoiceType, ['sales', 'multi-invoices'])) {
                $normalized = ($this->azureStudioType === 'model')
                    ? CustomSalesInvoiceMapper::map($result, $this->clients)
                    : InvoiceMapper::map($result);

                if ($normalized && !isset($normalized['error'])) {
                    $org_no = preg_replace('/\D/', '', $normalized['supplier']['cvr_number'] ?? '');
                    $org_no_1 = preg_replace('/\D/', '', $normalized['supplier']['org_number'] ?? '');
                    $country = preg_replace('/[^A-Z]/', '', $normalized['currency'] ?? '');
                }
            } elseif ($this->invoiceType === 'com') {
                $normalized = ($this->azureStudioType === 'model')
                    ? CustomComInvoiceMapper::map($result, $this->clients)
                    : ComInvoiceMapper::map($result);

                if ($normalized && !isset($normalized['error'])) {
                    $org_no = preg_replace('/\D/', '', $normalized['recipient']['org_number'] ?? '');
                    $org_no_1 = $org_no;
                    $country = preg_replace('/[^A-Z]/', '', $normalized['currency'] ?? '');
                }
            }

            $normalized = app(OcrAccuracyService::class)->enrich($normalized, $result, $this->invoiceType);

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

            DB::table('dv_invoice_ocr_pdfs')
                ->where('id', $this->documentId)
                ->update([
                    'client_id' => $client_id,
                    'status' => isset($normalized['error']) ? 'failed' : 'completed',
                    'error' => isset($normalized['error']) ? $normalized['error'] : null,
                    'extracted_data' => json_encode($normalized),
                    'og_extracted_data' => json_encode($result),
                ]);

            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            Cache::increment('inbox_completed', 1);

            if ($this->emailMessageId) {
                $this->finalizeEmailBatchIfComplete();
            } elseif ($this->prevCapture) {
                $prevId = $this->prevCapture['prevId'];
                $blobPath = $this->prevCapture['blobPath'];

                $azureService = app(AzureStorageService::class);
                $azureService->deleteFile($blobPath);

                Log::info("Azure file deleted {$blobPath}");

                $invoice = InvoiceOcrPdf::where('id', $prevId)->first();

                if ($invoice) {
                    $invoice->azure_sas_url = null;
                    $invoice->azure_sas_expiry = null;
                    $invoice->save();
                }
            }
        } finally {
            DB::table('dv_invoice_ocr_pdfs')
                ->where('id', $this->documentId)
                ->whereNotNull('polling_locked_at')
                ->update([
                    'polling_locked_at' => null
                ]);
        }
    }

    private function redispatchPoll(int $delaySeconds): void
    {
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
        )->delay(now()->addSeconds($delaySeconds))
            ->onQueue(config('queue.ocr.poll', 'ocrpdfinvoices'));
    }

    private function finalizeEmailBatchIfComplete(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }

        $batchId = DB::table('dv_invoice_ocr_pdfs')
            ->where('id', $this->documentId)
            ->value('batch_id');

        if (!$batchId) {
            return;
        }

        $remaining = DB::table('dv_invoice_ocr_pdfs')
            ->where('batch_id', $batchId)
            ->whereNotIn('status', ['completed', 'failed', 'duplicate', 'timeout'])
            ->count();

        if ($remaining !== 0) {
            return;
        }

        $cacheKey = "ocr_email_batch_finalized:{$batchId}";

        if (!Cache::add($cacheKey, true, now()->addHours(6))) {
            return;
        }

        if ($this->emailMessageId) {
            $mailService = app(MicrosoftMailService::class);
            $mailService->addCategory($this->emailMessageId);
            $mailService->markEmailAsRead($this->emailMessageId);
            $mailService->moveEmailToFolder($this->emailMessageId);
        }

        ValidateOcrInvoicesJob::dispatch($batchId)
            ->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices'));
    }
}
