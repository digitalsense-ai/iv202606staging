<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use App\Adapters\ValidateOcrCommercialAdapter;
use App\Models\InvoiceOcrPdf;
use App\Mappers\CustomComInvoiceMapper;
use App\Services\ValidateOcrInvoiceUpdateService;

class ValidateOcrCommercialInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    
    public function __construct(public array $clients, public int $invoiceId) {}

    public function handle()
    {
        $invoice = InvoiceOcrPdf::find($this->invoiceId);

        if (!$invoice) return;
        
        $data = $invoice->extracted_data;

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (!is_array($data)) {
            \Log::warning("Invalid extracted_data", [
                'invoice_id' => $invoice->id,
            ]);

            return;
        }

        $fields = app(ValidateOcrCommercialAdapter::class)
            ->fromExtracted($data);

        $result = [
            'analyzeResult' => [
                'documents' => [
                    [
                        'fields' => $fields
                    ]
                ]
            ]
        ];

        $mapped = CustomComInvoiceMapper::map($result, $this->clients, true);

        app(ValidateOcrInvoiceUpdateService::class)->apply($invoice, $mapped);
    }
}