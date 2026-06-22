<?php

namespace App\Services;

//use Illuminate\Http\Request;
use Illuminate\Support\Str;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use App\Models\OcrPdf;

use App\Jobs\SplitPdfJob;

use App\Services\MicrosoftMailService;

class OcrAnalyzeService
{
    public function analyze(array $clients, array $paths, string $folder, string $batchId, string $emailMessageId = null, array $prevCaptures = [], bool $bulk =  false)
    {        
        $invoiceType = $folder; // 'sales' or 'com'
        $whichStudio = 'model';

        $analyzerId = match ($invoiceType) {
            'sales', 'multi-invoices' => 'sales_invoice_analyzer_v7',
            'com'   => 'com_invoice_analyzer_v8',
            default => 'sales_invoice_analyzer_v7',
        };

        $modelId = match ($invoiceType) {
            'sales', 'multi-invoices' => 'custom_sales_invoice_v20',
            'com'   => 'custom_com_invoice_v17',
            default => 'custom_sales_invoice_v20',
        };

        foreach ($paths as $key => $fullPath) {
            //$fullPath = storage_path('app/' . $path);

            if($bulk)
            {
                $originalName = pathinfo(
                    $fullPath->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                // Store in storage/app/ocr/{invoice_type}
                $storedPath = $fullPath->storeAs(
                    'ocr/' . $invoiceType,
                    $originalName.'.pdf',
                    'local'
                );                
                $fullPath = storage_path('app/' . $storedPath);
            }

            $prevCapture = ($prevCaptures) ? $prevCaptures[$key] : [];

            if (!file_exists($fullPath)) {
                Log::error("File not found: {$fullPath} for batch {$batchId}");
                continue;
            }

            $originalName = pathinfo($fullPath, PATHINFO_FILENAME);

            $allow = true;
            if($invoiceType == 'multi-invoices')
            {
                $already_exist = OcrPdf::query()->where('invoice_type', $invoiceType)
                                    ->where('file_name', 'LIKE', $originalName . '%\.pdf')
                                    ->where('status', 'completed')
                                    ->count();
                $allow = ($already_exist > 0) ? false : true;
            }
            else
            {
                if(strtolower($originalName) == 'report50022')
                    $allow = true;
                else
                {
                    $already_exist = OcrPdf::query()->where('invoice_type', $invoiceType)
                                        ->where('file_name', $originalName . '.pdf')
                                        ->where('status', 'completed')
                                        ->count();                    
                    $allow = ($prevCapture) ? true : (($already_exist > 0) ? false : true);
                }
            }

            if($allow)
            {
                // Increment total count for progress bar
                if(!$bulk)
                    Cache::increment('inbox_total');                

                SplitPdfJob::dispatch(
                    $clients,
                    $fullPath,
                    $originalName,
                    $invoiceType,
                    $batchId,
                    $whichStudio,
                    ($whichStudio === 'model') ? $modelId : $analyzerId,
                    null,
                    $emailMessageId,
                    $prevCapture
                )->onQueue(config('queue.ocr.split', 'ocrpdfinvoices'));

                Log::info("Queued SplitPdfJob for {$originalName} in batch {$batchId}");
            } //allow
            else
            {
                if($emailMessageId)
                {
                    $mailService = new MicrosoftMailService();
                                    
                    $mailService->markEmailAsRead($emailMessageId);
                    $mailService->moveEmailToFolder($emailMessageId, "Duplicate");

                    Cache::increment('inbox_completed', 1);

                    Log::info("Duplicate file {$originalName} in batch {$batchId}");
                }
            } //not allow
        }
    }

    public function getSasUrl($id, $type = null)
    {
        $invoice = OcrPdf::query()->findOrFail($id);

        if (!$invoice->azure_url) {
            return response()->json(['error' => 'PDF not available'], 404);
        }

        $azureService = new AzureStorageService();

        $invoice_azure_url = $invoice->azure_url;
        if ($invoice->azure_sas_url && $invoice->azure_sas_expiry && now()->lt($invoice->azure_sas_expiry))
            $signedUrl = $invoice->azure_sas_url;
        else 
        {
            //$invoice_azure_url = $invoice->azure_url;
            if (stripos($invoice->azure_url, "multi-invoices/") !== false)
            {
                if($type == 'recapture')
                    $invoice_azure_url = $invoice->azure_url;
                // else             
                //     $invoice_azure_url = preg_replace('/_\d+\.pdf$/', '.pdf', $invoice->azure_url);
            }
            
            $signedUrl = $azureService->generateSasUrl($invoice_azure_url);
            $invoice->azure_sas_url = $signedUrl;
            $invoice->azure_sas_expiry = now()->addHours(1);
            $invoice->save();
        }

        if($type == 'recapture')
        {
            //return $signedUrl;
            return [
                'blobPath' =>  $invoice_azure_url,
                'signedUrl' =>  $signedUrl,
            ];  
        }
        else    
            return response()->json([
                'azure_signed_url' => $signedUrl,
                'start_pageno' => $invoice->start_pageno ?? 1
            ]);
    }   
}