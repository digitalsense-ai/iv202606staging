<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use App\Adapters\ValidateOcrSalesAdapter;
use App\Models\OcrPdf;
use App\Mappers\CustomSalesInvoiceMapper;

use App\Services\ValidateOcrInvoiceUpdateService;
use App\Services\OcrAccuracyService;
use App\Services\OcrParserStrategyService;
use App\Services\OcrAnalyzeService;

class ValidateOcrSalesInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    
    public function __construct(public array $clients, public int $invoiceId, public bool $manual = false, public bool $searchSave = false) {}

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
        //     $fields = app(ValidateOcrSalesAdapter::class)
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

        if (!$this->manual) 
        {
            if($invoice->manual_input_by)
                $this->manual =  true;
        }
        
        if (!$this->searchSave) 
        {
            if($invoice->search_save_by)
                $this->searchSave =  true;
        }

        if ($this->manual || $this->searchSave) 
        {
            $fields = app(ValidateOcrSalesAdapter::class)
                        ->fromExtracted($invoice->extracted_data);

            $content = $result['analyzeResult']['content'] ?? [];

            $manualInput = [
                'analyzeResult' => [
                    'documents' => [
                        [
                            'fields' => $fields                        
                        ]
                    ],
                    'content' => $content
                ]
            ];

            $result = $manualInput;
            $mapped = CustomSalesInvoiceMapper::map($result, $this->clients, $invoice->created_at, $this->manual, $this->searchSave);
        }
        else
            $mapped = CustomSalesInvoiceMapper::map($result, $this->clients, $invoice->created_at);

        /**
         * -------------------------------------------------
         * INVOICE TYPE IS WRONG
         * -------------------------------------------------
         */
        if (isset($mapped['change_invoice_type']) && !$this->manual && !$this->searchSave) 
        { 
            if($mapped['change_invoice_type'])
            {       
                $ocrAnalyzeService = new OcrAnalyzeService();
                        
                // $changeType = OcrPdf::query()->where('id', $this->invoiceId)->first();
                // $changeType->sync_status = 0;
                // $changeType->is_locked = 0;
                // $changeType->save();

                $folder = 'com';                            
                $batchId = $invoice->batch_id;
                
                if($invoice->no_of_attempts <= 2)
                {
                    $no_of_attempts = $invoice->no_of_attempts;

                    $invoice->no_of_attempts = $no_of_attempts + 1;
                    $invoice->save();

                    //Get file from Azure storage
                    $sasPaths = $ocrAnalyzeService->getSasUrl($this->invoiceId, 'recapture');
                    $sasUrl = $sasPaths['signedUrl'];
                    $blobPath = $sasPaths['blobPath'];

                    $prevCaptures = [[
                        'prevId' => $this->invoiceId,
                        'sasUrl' => $sasUrl,
                        'blobPath' => $blobPath
                    ]];
                    
                    //Save it in local
                    $sasUrl = html_entity_decode($sasUrl);
                    //$sasUrl = str_replace([' ', '+'], ['%20', '%2B'], $sasUrl);
                    $sasUrl = str_replace(' ', '%20', $sasUrl);
                    $fileName = basename($invoice->file_name);

                    $stream = @fopen($sasUrl, 'r');

                    if (!$stream) {
                        Log::error("Failed to open SAS URL", [
                            'url' => $sasUrl,
                            'invoice_id' => $this->invoiceId
                        ]);
                        return;
                    }
                    
                    Storage::disk('local')->put('ocr/' . $fileName, stream_get_contents($stream));

                    if (is_resource($stream))
                        fclose($stream);
                                            
                    $fullPath = storage_path('app/ocr/' . $fileName);            
                    
                    // $content = file_get_contents($fullPath);
                    // $contentBytes = base64_encode($content);

                    // // Safe deletion
                    // if (file_exists($fullPath)) {
                    //     unlink($fullPath);
                    // }

                    // $path = "ocr/$folder/$fileName";
                    // Storage::disk('local')->put($path, base64_decode($contentBytes));

                    // $fullPath = storage_path('app/' . $path); // this will exist
                
                    
                    $ocrAnalyzeService->analyze($this->clients, [$fullPath], $folder, $batchId, null, $prevCaptures);

                    return;
                }
                // else
                // {
                //     if(isset($mapped['error']))
                //         $mapped['error'] = $mapped['error'] . "Invalid document type\n";  
                //     else
                //         $mapped['error'] = "Invalid document type\n";                           
                // }
            }
        }

        if (isset($mapped['invalid_invoice_type'])) 
        {
            $invoice->refresh();

            $invoice->update([
                'is_deleted' => isset($mapped['invalid_invoice_type']) ? 1 : 0,
                'deleted_reason' => isset($mapped['invalid_invoice_type']) ? ('Invalid document type - ' . $mapped['invalid_invoice_type']) : null,
            ]);
        }
        
        /**
         * -------------------------------------------------
         * 7a. ACCURACY SERVICE
         * -------------------------------------------------
         */
        if (is_array($mapped) && !isset($mapped['error'])) {     
// Log::info('Before parser strategy', [
//     'no' => $mapped['invoice_number'] ?? null,
//     'date' => $mapped['invoice_date'] ?? null,
//     'net' => $mapped['net_amount'] ?? null,
//     'vat' => $mapped['vat_amount'] ?? null,
//     'total' => $mapped['total_amount'] ?? null,
// ]);

            $mapped = app(OcrParserStrategyService::class)->apply(
                normalized: $mapped,
                azureResult: $result,
                clientId: null,
                invoiceType: $invoice->invoice_type
            );
        
// Log::info('After parser strategy', [
//     'no' => $mapped['invoice_number'] ?? null,
//     'date' => $mapped['invoice_date'] ?? null,
//     'net' => $mapped['net_amount'] ?? null,
//     'vat' => $mapped['vat_amount'] ?? null,
//     'total' => $mapped['total_amount'] ?? null,
// ]);

            $mapped = app(OcrAccuracyService::class)->enrich(
                $mapped,
                $result,
                $invoice->invoice_type
            );  
// Log::info('After Accuracy strategy', [
//     'no' => $mapped['invoice_number'] ?? null,
//     'date' => $mapped['invoice_date'] ?? null,
//     'net' => $mapped['net_amount'] ?? null,
//     'vat' => $mapped['vat_amount'] ?? null,
//     'total' => $mapped['total_amount'] ?? null,
// ]);
        }

        app(ValidateOcrInvoiceUpdateService::class)->apply($invoice, $mapped, $this->manual, $this->searchSave);

        $invoice->refresh();

        // if ($this->manual) {
        //     $invoice->update([
        //         'manual_input_status' => 'validated',
        //     ]);
        // }        
    }
}