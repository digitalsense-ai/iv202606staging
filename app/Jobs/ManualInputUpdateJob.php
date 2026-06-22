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

class ManualInputUpdateJob implements ShouldQueue
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
            'manual_input_status' => 'processing',
        ]);

        try {
            $result = $correctionService->apply(
                $invoice,
                $this->payload,
                $this->forceSubmitted,
                $this->userId
            );

            if ($result['completed'] ?? false) {
                $invoice->refresh();

                $invoice->update([
                    'manual_input_status' => 'validation_queued',
                ]);

                ValidateOcrInvoicesJob::dispatch(null, [$invoice->id])
                    ->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices'));

                return;
            }

            $invoice->refresh();
            $invoice->update([
                'manual_input_status' => 'failed',
            ]);
        } catch (\Throwable $exception) {
            $invoice->update([
                'manual_input_status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            Log::error('Manual input update failed', [
                'invoice_id' => $this->invoiceId,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
