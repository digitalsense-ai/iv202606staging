<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use App\Models\OcrPdf;
use App\Mappers\CustomComInvoiceMapper;
use App\Services\ValidateOcrInvoiceUpdateService;

class ValidateOcrCommercialInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    
    public function __construct(public array $clients, public int $invoiceId) {}

    public function handle()
    {
        $invoice = OcrPdf::query()->find($this->invoiceId);

        if (!$invoice) return;
        
        $result = ($invoice->og_extracted_data) ? $invoice->og_extracted_data : $invoice->extracted_data;

        if (is_string($result)) {
            $result = json_decode($result, true);
        }

        if (!is_array($result)) {
            Log::warning("Invalid OCR result for commercial validation", [
                'invoice_id' => $invoice->id,
            ]);

            return;
        }

        $mapped = CustomComInvoiceMapper::map($result, $this->clients, true);

        app(ValidateOcrInvoiceUpdateService::class)->apply($invoice, $mapped);
    }
}
