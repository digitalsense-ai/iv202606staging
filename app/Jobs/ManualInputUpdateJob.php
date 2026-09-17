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
use Illuminate\Support\Facades\Cache;

class ManualInputUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $ocrProgressKey, 
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

                $total = 1;
                $originalProgressKey = $this->ocrProgressKey;

                $this->ocrProgressKey = preg_replace(
                    '/^ocr_progress:[^:]+:/',
                    'ocr_progress:validate:',
                    $this->ocrProgressKey
                );

                if ($this->ocrProgressKey !== $originalProgressKey) {

                    $ttl = now()->addHour();

                    Cache::put(
                        "{$this->ocrProgressKey}:total",
                        $total,
                        $ttl
                    );

                    Cache::put(
                        "{$this->ocrProgressKey}:completed",
                        0,
                        $ttl
                    );
                }

                ValidateOcrInvoicesJob::dispatch($this->ocrProgressKey, null, [$invoice->id], true)
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