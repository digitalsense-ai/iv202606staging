<?php

namespace App\Jobs;

use App\Models\OcrPdf;
use App\Services\OcrInvoiceCorrectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SearchSaveUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $invoiceId,
        public array $payload,
        public bool $forceSubmitted = false,
        public ?int $userId = null
    ) {
        $this->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices'));
    }

    public function handle(OcrInvoiceCorrectionService $correctionService): void
    {
        $invoice = OcrPdf::query()->findOrFail($this->invoiceId);

        $invoice->update([
            'search_save_status' => 'processing',
        ]);

        try {
            $result = $correctionService->apply(
                $invoice,
                $this->payload,
                $this->forceSubmitted,
                $this->userId,
                true
            );

            if ($result['completed'] ?? false) {
                $invoice->refresh();

                $invoice->update([
                    'search_save_status' => 'validation_queued',
                ]);

                $invoice->update([
                    'search_save_status' => 'validating',
                ]);

                $invoice->update([
                    'search_save_status' => 'validated',
                ]);
                // ValidateOcrInvoicesJob::dispatch(null, [$invoice->id], true)
                //     ->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices'));

                return;
            }

            $invoice->refresh();
            $invoice->update([
                'search_save_status' => 'failed',
            ]);
        } catch (\Throwable $exception) {
            $invoice->update([
                'search_save_status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            Log::error('Search Save update failed', [
                'invoice_id' => $this->invoiceId,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}