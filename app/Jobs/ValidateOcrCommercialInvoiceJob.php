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
use App\Models\OcrPdf;
use App\Mappers\CustomComInvoiceMapper;

use App\Services\ValidateOcrInvoiceUpdateService;
use App\Services\OcrAccuracyService;
use App\Services\OcrParserStrategyService;

class ValidateOcrCommercialInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    
    public function __construct(public array $clients, public int $invoiceId) {}

    public function handle()
    {
        $invoice = OcrPdf::query()->find($this->invoiceId);

        if (!$invoice) return;
        
        $data = ($invoice->og_extracted_data) ? $invoice->og_extracted_data : [];

        if (!$data) 
            return;

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (!is_array($data)) {
            \Log::warning("Invalid extracted_data", [
                'invoice_id' => $invoice->id,
            ]);

            return;
        }

        //if($invoice->og_extracted_data)
            $result = $data;
        // else
        // {
        //     $fields = app(ValidateOcrCommercialAdapter::class)
        //         ->fromExtracted($data);

        //     $result = [
        //         'analyzeResult' => [
        //             'documents' => [
        //                 [
        //                     'fields' => $fields
        //                 ]
        //             ]
        //         ]
        //     ];
        // }        

        $mapped = CustomComInvoiceMapper::map($result, $this->clients, true);

        /**
         * -------------------------------------------------
         * 7a. ACCURACY SERVICE
         * -------------------------------------------------
         */
        if (is_array($mapped) && !isset($mapped['error'])) {
            // $mapped = app(OcrParserStrategyService::class)->apply(
            //     normalized: $mapped,
            //     azureResult: $result,
            //     clientId: null,
            //     invoiceType: $invoice->invoice_type
            // );

            $mapped = app(OcrAccuracyService::class)->enrich(
                $mapped,
                $result,
                $invoice->invoice_type
            );           
        }

        app(ValidateOcrInvoiceUpdateService::class)->apply($invoice, $mapped);
    }
}