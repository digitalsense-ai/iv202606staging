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
            'sales', 'multi-invoices' => 'custom_sales_invoice_v19',
            'com'   => 'custom_com_invoice_v16',
            default => 'custom_sales_invoice_v19',
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
}