<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client as GuzzleClient;
use setasign\Fpdi\Fpdi;
use App\Services\AzureStorageService;
use App\Models\OcrPdf;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

use Imagick;

class SplitPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Large PDFs can legitimately take several minutes in Azure Document
     * Intelligence. Keep the worker alive longer than its default timeout.
     */
    // This must remain lower than the queue connection's retry_after value.
    public int $timeout = 3300;

    public int $tries = 2;

    public function __construct(
        public string $ocrProgressKey, 
        public array $clients,
        public string $fullPath,
        public string $originalName,
        public string $invoiceType,
        public string $batchId,
        public string $azureStudioType = 'analyzer',
        public string $analyzerId,
        public ?string $pageRanges = null,
        public ?string $emailMessageId = null,
        public ?array $prevCapture = [],
        public ?string $emailSubject = null
    ) {}

    public function handle()
    {
        $data_from = null;
        if (str_starts_with($this->ocrProgressKey, 'ocr_progress:inbox:'))
            $data_from = 'inbox';
        else if (str_starts_with($this->ocrProgressKey, 'ocr_progress:recapture:')) 
            $data_from = 'recapture';
        else if (str_starts_with($this->ocrProgressKey, 'ocr_progress:split:')) 
            $data_from = 'split';
        else if (str_starts_with($this->ocrProgressKey, 'ocr_progress:bulkupload:')) 
            $data_from = 'bulkupload';
        else if (str_starts_with($this->ocrProgressKey, 'ocr_progress:sftp:')) 
            $data_from = 'sftp';

        // Step 0: Optional file size check
        $maxSizeMB = 50; // Set max allowed PDF size
        $fileSizeMB = filesize($this->fullPath) / (1024 * 1024);

        if ($fileSizeMB > $maxSizeMB) {
            if($this->prevCapture)
            {
                $ocrpdf = OcrPdf::query()->where('id', $this->prevCapture['prevId'])->first();

                $ocrpdf->client_id = null;
                $ocrpdf->batch_id = $this->batchId;
                $ocrpdf->invoice_type = $this->invoiceType;
                $ocrpdf->file_name = $this->originalName;
                $ocrpdf->analyzer_id = $this->analyzerId;
                $ocrpdf->status = 'failed';
                $ocrpdf->error = "File too large ({$fileSizeMB}MB) for PDF processing";
                $ocrpdf->updated_at = now();

                if ($ocrpdf && $data_from) {
                    $sources = collect([
                        $ocrpdf->data_from,
                        $data_from,
                    ])
                    ->flatMap(fn ($value) => $value ? explode(' - ', $value) : [])
                    ->filter()
                    ->unique()
                    ->values();

                    $ocrpdf->data_from = $sources->implode(' - ');                   
                }

                if ($data_from === 'inbox') {
                    $ocrpdf->subject = $this->emailSubject;
                }

                $ocrpdf->save();
            }
            else    
                //DB::table('dv_invoice_ocr_pdfs')->insertGetId([
                OcrPdf::query()->create([
                    'client_id' => null,
                    'batch_id' => $this->batchId,
                    'invoice_type' => $this->invoiceType,
                    'file_name' => $this->originalName,
                    'analyzer_id' => $this->analyzerId,
                    'status' => 'failed',
                    'no_of_attempts' => 1,
                    'error' => "File too large ({$fileSizeMB}MB) for PDF processing",
                    'created_at' => now(),
                    'source_environment' => config('database.ocr_source_environment'),
                    'data_from' => $data_from ?? null,
                    'subject' => $data_from === 'inbox' ? $this->emailSubject : null,
                ]);

            if (file_exists($this->fullPath)) unlink($this->fullPath);
            return;
        }

        /* =====================================================
           EARLY EXIT — SINGLE INVOICE (NO SPLIT)
        ===================================================== */
        if ($this->invoiceType !== 'multi-invoices') {
            if($this->prevCapture)
                $docId = $this->prevCapture['prevId'];
            else
            {
                $ocrPdfId = OcrPdf::query()->create([
                //$docId = DB::table('dv_invoice_ocr_pdfs')->insertGetId([
                    'client_id'   => null,
                    'batch_id'    => $this->batchId,
                    'invoice_type'=> $this->invoiceType,
                    'file_name'   => $this->originalName . '.pdf',
                    'analyzer_id' => $this->analyzerId,
                    'status'      => 'queued',
                    'no_of_attempts' => 1,
                    'created_at'  => now(),
                    'source_environment' => config('database.ocr_source_environment'),
                    'data_from' => $data_from ?? null,
                    'subject' => $data_from === 'inbox' ? $this->emailSubject : null,
                ]);
                $docId = $ocrPdfId->id;
            }

            //Store in azure storage blob
            $azureService = new AzureStorageService();
            $azurePath = $this->invoiceType . '/' . $this->originalName . '.pdf';
            $azureUrl = $azureService->uploadFile($this->fullPath, $azurePath);

            // Update record            
            $ocrpdf = OcrPdf::query()->where('id', $docId)->first();
            $ocrpdf->azure_url = $azureUrl;
            if($this->prevCapture)
            {
                $ocrpdf->client_id = null;
                $ocrpdf->batch_id = $this->batchId;
                $ocrpdf->invoice_type = $this->invoiceType;
                $ocrpdf->file_name = $this->originalName . '.pdf';
                $ocrpdf->analyzer_id = $this->analyzerId;
                $ocrpdf->status = 'queued';
                $ocrpdf->updated_at = now();
            }

            if ($ocrpdf && $data_from) {
                $sources = collect([
                    $ocrpdf->data_from,
                    $data_from,
                ])
                ->flatMap(fn ($value) => $value ? explode(' - ', $value) : [])
                ->filter()
                ->unique()
                ->values();

                $ocrpdf->data_from = $sources->implode(' - ');                   
            }

            if ($data_from === 'inbox') {
                $ocrpdf->subject = $this->emailSubject;
            }
            
            $ocrpdf->save();

            // Delete local file
            // if (file_exists($this->fullPath)) {
            //     unlink($this->fullPath);
            // }
            //Store in azure storage blob

            SubmitAnalyzeJob::dispatch(
                $this->ocrProgressKey, 
                $this->clients,
                $docId,
                $this->fullPath,
                basename($this->fullPath),
                $this->azureStudioType,
                $this->analyzerId,                
                $this->invoiceType,
                $this->emailMessageId,
                $this->prevCapture
            )->onQueue(config('queue.ocr.submit', 'ocrpdfinvoices'));

            return;
        }
        else
        {
            if($this->prevCapture)
            {
                if(isset($this->prevCapture['split']))
                {

                } //split
                else
                {
                    $docId = $this->prevCapture['prevId'];

                    //Store in azure storage blob
                    $azureService = new AzureStorageService();
                    $azurePath = $this->invoiceType . '/' . $this->originalName . '.pdf';
                    $azureUrl = $azureService->uploadFile($this->fullPath, $azurePath);

                    // Update record            
                    $ocrpdf = OcrPdf::query()->where('id', $docId)->first();
                    $ocrpdf->azure_url = $azureUrl;
                    if($this->prevCapture)
                    {
                        $ocrpdf->client_id = null;
                        $ocrpdf->batch_id = $this->batchId;
                        $ocrpdf->invoice_type = $this->invoiceType;
                        $ocrpdf->file_name = $this->originalName . '.pdf';
                        $ocrpdf->analyzer_id = $this->analyzerId;
                        $ocrpdf->status = 'queued';
                        $ocrpdf->updated_at = now();
                    }

                    if ($ocrpdf && $data_from) {
                        $sources = collect([
                            $ocrpdf->data_from,
                            $data_from,
                        ])
                        ->flatMap(fn ($value) => $value ? explode(' - ', $value) : [])
                        ->filter()
                        ->unique()
                        ->values();

                        $ocrpdf->data_from = $sources->implode(' - ');                   
                    }
                    
                    $ocrpdf->save();

                    SubmitAnalyzeJob::dispatch(
                        $this->ocrProgressKey, 
                        $this->clients,
                        $docId,
                        $this->fullPath,
                        basename($this->fullPath),
                        $this->azureStudioType,
                        $this->analyzerId,                
                        $this->invoiceType,
                        $this->emailMessageId,
                        $this->prevCapture
                    )->onQueue(config('queue.ocr.submit', 'ocrpdfinvoices'));

                    return;
                }
            }
            else
            {
                // Load PDF info to get total pages
                $pdfInfo = new Fpdi();
                try {
                    $totalPages = $pdfInfo->setSourceFile($this->fullPath);
                } catch (\Throwable $e) {
                    \Log::error('FPDI failed to read PDF. Retrying as single invoice.', [
                        'file' => $this->fullPath,
                        'error' => $e->getMessage(),
                    ]);

                    self::dispatch(
                        $this->ocrProgressKey,
                        $this->clients,
                        $this->fullPath,                        
                        $this->originalName,
                        'sales',
                        $this->batchId,
                        $this->azureStudioType,
                        $this->analyzerId,
                        null,
                        $this->emailMessageId,
                        $this->prevCapture
                    )->onQueue(config('queue.ocr.submit', 'ocrpdfinvoices'));

                    return;
                }
            }
        }//multi-invoices

        $ranges = [];

        /* STEP 1: USE MANUAL PAGE RANGES IF PROVIDED */
        if (!empty($this->pageRanges)) {
            $pdfInfo = new Fpdi();
            $totalPages = $pdfInfo->setSourceFile($this->fullPath);
            foreach (explode(',', $this->pageRanges) as $part) {
                $part = trim($part);
                if (preg_match('/^(\d+)-(\d+)$/', $part, $m)) {
                    $start = (int)$m[1]; $end = (int)$m[2];
                } else { $start = $end = (int)$part; }
                $ranges[] = [$start, $end];
            }
            unset($pdfInfo);
        }

        /* STEP 2: AUTO-DETECT PAGE RANGES BASED ON INVOICE NUMBERS */
        if (empty($ranges)) {
            \Log::info('PDF ranges');
            $ranges = $this->splitPdfByInvoiceNumber();
        }
        \Log::info('Detected PDF ranges', $ranges);

        //Store in azure storage blob
        $azureService = new AzureStorageService();
        $azurePath = $this->invoiceType . '/' . $this->originalName . '.pdf';
        $azureUrl = $azureService->uploadFile($this->fullPath, $azurePath);        
        //Store in azure storage blob

        /* STEP 3: SPLIT PDF AND QUEUE OCR JOBS */
        //$outputDir = storage_path('app/public/ocr/' . $this->invoiceType);
        $outputDir = storage_path('app/ocr/' . $this->invoiceType);
        if (!file_exists($outputDir)) mkdir($outputDir, 0777, true);

        $counter = 1;
        foreach ($ranges as [$start, $end]) {
            $pdfSplit = new Fpdi();
            $pageCount = $pdfSplit->setSourceFile($this->fullPath);

            $rangeLayout = [];

            // for ($page = $start; $page <= $end; $page++) {
            //     if ($page > $pageCount) break;
            //     $pdfSplit->AddPage();
            //     $tpl = $pdfSplit->importPage($page);
            //     $pdfSplit->useTemplate($tpl);
            // }

            for ($page = $start; $page <= $end; $page++) {
                if ($page > $pageCount) {
                    break;
                }

                $tpl = $pdfSplit->importPage($page);
                $size = $pdfSplit->getTemplateSize($tpl);

                $orientation = ($size['width'] ?? 0) > ($size['height'] ?? 0) ? 'L' : 'P';
                $width = (float) ($size['width'] ?? 0);
                $height = (float) ($size['height'] ?? 0);

                $pdfSplit->AddPage($orientation, [$width, $height]);
                $pdfSplit->useTemplate($tpl, 0, 0, $width, $height, true);

                $rangeLayout[] = [
                    'page' => $page,
                    'width' => $width,
                    'height' => $height,
                    'orientation' => $orientation,
                ];
            }

            $splitPath = $outputDir.'/'.$this->originalName.'_'.$counter.'.pdf';
            $pdfSplit->Output('F', $splitPath);
            unset($pdfSplit);

            $this->optimizeSplitPdfForWeb($splitPath);

            // Queue OCR job            
            //$docId = DB::table('dv_invoice_ocr_pdfs')->insertGetId([
            $ocrPdfId = OcrPdf::query()->create([
                'client_id' => null,
                'batch_id' => $this->batchId,
                'invoice_type' => $this->invoiceType,
                'file_name' => $this->originalName . '_' . $counter . '.pdf',
                'analyzer_id' => $this->analyzerId,
                'status' => 'queued',
                'no_of_attempts' => 1,
                'start_pageno' => $start,
                'end_pageno' => $end,
                'layout_metadata' => json_encode([
                    'source_file' => $this->originalName . '.pdf',
                    'range' => [
                        'start' => $start,
                        'end' => $end,
                    ],
                    'pages' => $rangeLayout,
                    'orientation_summary' => collect($rangeLayout)
                        ->pluck('orientation')
                        ->countBy()
                        ->toArray(),
                ]),
                'created_at' => now(),
                'source_environment' => config('database.ocr_source_environment'),
                'data_from' => $data_from ?? null,
                'subject' => $data_from === 'inbox' ? $this->emailSubject : null,
            ]);
            $docId = $ocrPdfId->id;

            //Store in azure storage blob
            $azureService = new AzureStorageService();
            $azurePath = $this->invoiceType . '/' . $this->originalName . '_' . $counter . '.pdf';
            $azureUrl = $azureService->uploadFile($splitPath, $azurePath);

            // Update record with Azure URL           
            $ocrpdf = OcrPdf::query()->where('id', $docId)->first();
            $ocrpdf->azure_url = $azureUrl;            
            $ocrpdf->save();

            // Delete local file
            // if (file_exists($splitPath)) {
            //     unlink($splitPath);
            // }
            //Store in azure storage blob

            SubmitAnalyzeJob::dispatch(
                $this->ocrProgressKey, 
                $this->clients,
                $docId,
                $splitPath,
                basename($splitPath),
                $this->azureStudioType,
                $this->analyzerId,                
                $this->invoiceType,
                $this->emailMessageId,
                $this->prevCapture
            )->onQueue(config('queue.ocr.submit', 'ocrpdfinvoices'));

            $counter++;
        }

        //if (file_exists($this->fullPath)) unlink($this->fullPath);
    }

    /**
     * Split PDF pages into ranges based on invoice number
     * Returns array of [startPage, endPage] ranges
     */
    private function splitPdfByInvoiceNumber(): array
    {
        $startedAt = microtime(true);

        $ranges = [];
        $invoiceByPage = [];
       
        $pageTextByPage = [];

        // Load PDF info to get total pages
        $pdfInfo = new Fpdi();        
        try {
            $totalPages = $pdfInfo->setSourceFile($this->fullPath);
        } catch (\Throwable $e) {
            \Log::error('FPDI failed to read PDF. Retried as single invoice.', [
                'file' => $this->fullPath,
                'error' => $e->getMessage(),
            ]);
            return $ranges;
        }
        unset($pdfInfo);

        // // Process each page individually
        // for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {

//             // Create temporary single-page PDF
//             $tmpPdf = tempnam(sys_get_temp_dir(), 'pdfp_') . '.pdf';
//             $fpdi = new Fpdi();
//             $fpdi->setSourceFile($this->fullPath);
//             $fpdi->AddPage();
//             $tpl = $fpdi->importPage($pageNo);
//             //$fpdi->useTemplate($tpl);
            
//             $size = $fpdi->getTemplateSize($tpl);

//             $orientation = ($size['width'] ?? 0) > ($size['height'] ?? 0) ? 'L' : 'P';
//             $width = (float) ($size['width'] ?? 0);
//             $height = (float) ($size['height'] ?? 0);

//             $fpdi->AddPage($orientation, [$width, $height]);
//             $fpdi->useTemplate($tpl, 0, 0, $width, $height, true);

        \Log::info('Detecting invoice ranges', [
            'file' => $this->originalName,
            'pages' => $totalPages,
        ]);

//             $fpdi->Output('F', $tmpPdf);
//             unset($fpdi);

        $localTextPageLimit = max(0, (int) config(
            'ocr_profiles.pdf_processing.local_text_max_pages',
            25
        ));

//             // 1️⃣ Try Smalot parser first
//             $text = '';

        // Smalot materializes the complete PDF object graph. It is useful for
        // small PDFs but can consume more than 1 GB for a few hundred pages.
        // Large documents skip it and use the single batch OCR request below.
        if ($localTextPageLimit > 0 && $totalPages <= $localTextPageLimit) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
//                 $pdf = $parser->parseFile($tmpPdf);
//                 $text = trim($pdf->getText());
//             } catch (\Throwable $e) {}
// // \Log::info("TEXT: ");
// // \Log::info($text);
//             // 2️⃣ Fallback to Azure OCR if Smalot fails
//             if (trim($text) === '') {
//                 //$fixedPdf = $this->normalizePdfForOcr($tmpPdf);

//                 //$text = $this->azureOcrGetTextSafe($fixedPdf);
//                 $text = $this->azureOcrGetTextSafe($tmpPdf);
//                 //\Log::info("azure TEXT: ");
//                 //\Log::info($text);
//                 sleep(5); // throttle Azure requests
                $pdf = $parser->parseFile($this->fullPath);
                foreach ($pdf->getPages() as $index => $page) {
                    $pageTextByPage[$index + 1] = trim($page->getText());
                }
                unset($pdf, $parser);
            } catch (\Throwable $e) {
                \Log::warning('Local PDF text extraction failed; using batch OCR.', [
                    'file' => $this->originalName,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            \Log::info('Skipping memory-intensive local PDF parser', [
                'file' => $this->originalName,
                'pages' => $totalPages,
                'local_text_page_limit' => $localTextPageLimit,
            ]);
        }
           
            // $pageTextByPage[$pageNo] = trim($text);

            // // Detect invoice number
            // // if (
            // //     preg_match('/NO-Invoice No\.?\s*(\S+)/i', $text, $m) ||
            // //     preg_match('/Nummer\s*\n\s*(\S+)/i', $text, $m)
            // // ) {
            // //     $invoiceByPage[$pageNo] = $m[1];
            // // }

        $pagesWithoutText = 0;
        for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {
            if (trim($pageTextByPage[$pageNo] ?? '') === '') {
                $pagesWithoutText++;
            }
        }

//             // 1️ If page contains "NO-Invoice No."
//             if (preg_match('/NO-Invoice No\.?\s*(\S+)/i', $text)) {
// //\Log::info("/NO-Invoice No");
//                 // Then extract using "Nummer"
//                 if (preg_match('/Nummer\s*\n\s*(\S+)/i', $text, $m)) {
//                     //\Log::info("/Nummer");
//                     $invoiceByPage[$pageNo] = $m[1];                   

        // Submit the original document once instead of making one Azure call
        // (plus a fixed five-second sleep) for every scanned page.
        if ($pagesWithoutText > 0) {
            \Log::info('Requesting one batch OCR operation for PDF', [
                'file' => $this->originalName,
                'pages' => $totalPages,
                'pages_without_embedded_text' => $pagesWithoutText,
            ]);
            $ocrTextByPage = $this->azureOcrGetTextByPageSafe($this->fullPath);
            for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {
                if (trim($pageTextByPage[$pageNo] ?? '') === '') {
                    $pageTextByPage[$pageNo] = trim($ocrTextByPage[$pageNo] ?? '');
                }                    

            // } 
            // // 1-1 If page contains "NO-Faktura nr."
            // elseif (preg_match('/NO-Faktura\s*nr\.?\s*([A-Za-z0-9-]+)/i', $text, $m)) {
            //     $invoiceByPage[$pageNo] = $m[1];
            }
            // // 2️ Check "Invoice No."
            // //elseif (preg_match('/Invoice No\.?\s*(?:\r?\n)?\s*(\S+)/i', $text, $m)) {
            // elseif (preg_match('/Invoice\s*No[^A-Za-z0-9]+([A-Za-z0-9-]+)/i', $text, $m)) {

            //     //\Log::info("Matched: Invoice No");
            //     //\Log::info($m);
            //     $invoiceByPage[$pageNo] = $m[1];
        }

        for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {
            $text = $pageTextByPage[$pageNo] ?? '';

            $invoiceNumber = self::extractInvoiceNumber($text);
            if ($invoiceNumber !== null) {
                $invoiceByPage[$pageNo] = $invoiceNumber;
            } elseif (self::containsInvoiceNumberLabel($text)) {
                // A repeated heading can occur on continuation pages. Without
                // a readable identifier it is not safe to invent a boundary;
                // the range builder attaches it to the neighboring invoice.
                \Log::notice('Invoice heading found without a readable invoice number', [
                    'file' => $this->originalName,
                    'page' => $pageNo,
                ]);
            }
//             // 3 Otherwise fallback to "Fakturanr."
//             elseif (preg_match('/Fakturanr\.?\s*(?:\r?\n)?\s*(\S+)/i', $text, $m)) {
//                 //\Log::info("/Fakturanr");
//                 $invoiceByPage[$pageNo] = $m[1];
//             }
//             // 4 Otherwise fallback to "Rechnungsnr."
//             elseif (preg_match('/Rechnungsnr\.?\s*(?:\r?\n)?\s*(\S+)/i', $text, $m)) {
//                 //\Log::info("/Rechnungsnr");
//                 $invoiceByPage[$pageNo] = $m[1];
//             }
//             else
//             {
//                 // Then extract using "Nummer"
//                 if (preg_match('/Nummer\s*\n\s*(\S+)/i', $text, $m)) {
//                     $invoiceByPage[$pageNo] = $m[1];
//                     //\Log::info($m);
//                     //\Log::info($invoiceByPage);
//                 }
//             }
// //\Log::info($invoiceByPage);
//             // Delete temp PDF & free memory
//             unlink($tmpPdf);
//             gc_collect_cycles();
            }
//\Log::info("Total pages: ". $totalPages);
//\Log::info($invoiceByPage);        
        // Build page ranges grouped by invoice number
        $ranges = self::buildPageRanges($invoiceByPage, $pageTextByPage, $totalPages);

        \Log::info('Invoice range detection completed', [
            'file' => $this->originalName,
            'pages' => $totalPages,
            'ranges' => count($ranges),
            'duration_seconds' => round(microtime(true) - $startedAt, 2),
        ]);

        return $ranges;
    }

    private static function buildPageRanges(array $invoiceByPage, array $pageTextByPage, int $totalPages): array
    {
        $ranges = [];
        $currentInvoice = null;
        $rangeStart = 1;
        //$headers = ['Nr.', 'Fakturadato', 'FSC™'];
        $headers = ['NR', 'FAKTURADATO', 'FSC™'];
        for ($page = 1; $page <= $totalPages; $page++) {
            $invoice = $invoiceByPage[$page] ?? $currentInvoice;
//\Log::info("Has invoice : ". $invoice);
          
            if (in_array(trim($invoice), $headers, true)) {
                $nextInvoice = $invoiceByPage[$page + 1] ?? null;
                $pageText = $pageTextByPage[$page] ?? '';

                if ($nextInvoice && stripos($pageText, $nextInvoice) !== false) {
                    // Header page belongs to next invoice
                    $invoice = $nextInvoice;
                }
            }

            if ($currentInvoice === null) {
                // $currentInvoice = $invoice;
                // $rangeStart = $page;

                // Keep rangeStart at page 1 when initial pages have no readable
                // number. Once the first number appears those leading pages
                // belong to it, rather than being omitted from all ranges.
                if ($invoice !== null) {
                    $currentInvoice = $invoice;
                }
                continue;
            }

            if ($invoice !== $currentInvoice) {                
                $ranges[] = [$rangeStart, $page - 1];
                $currentInvoice = $invoice;
                $rangeStart = $page;
            }
        }

        // if($totalPages == 2 && $rangeStart == 2)
        //     $rangeStart = 1;

        // Add the last range
        $ranges[] = [$rangeStart, $totalPages];        

        return $ranges;
    }    

    private static function extractInvoiceNumber(string $text): ?string
    {
        // Preserve the proven layouts used by the original splitter. OCR can
        // return invoice identifiers that start with punctuation or contain
        // characters outside a conservative letter/number allow-list, so the
        // value after these labels deliberately uses \S+.
        if (
            preg_match('/NO-Invoice No\.?\s*(\S+)/i', $text)
            && preg_match('/Nummer\s*\n\s*(\S+)/i', $text, $match)
        ) {
            return self::normalizeInvoiceNumber($match[1]);
        }

        $legacyPatterns = [
            '/NO-Faktura\s*nr\.?\s*(\S+)/i',
            '/Invoice\s*No[^A-Za-z0-9]+(\S+)/i',
            '/Fakturanr\.?\s*(?:\r?\n)?\s*(\S+)/i',
            '/Rechnungsnr\.?\s*(?:\r?\n)?\s*(\S+)/i',
            '/Nummer\s*\n\s*(\S+)/i',
        ];

        foreach ($legacyPatterns as $pattern) {
            if (preg_match($pattern, $text, $match)) {
                return self::normalizeInvoiceNumber($match[1]);
            }
        }

        // Some Norwegian layouts contain both labels. Prefer the value after
        // "Nummer", but fall back to the value next to "NO-Invoice No.". The
        // old code discarded the latter, causing an unreadable last page to be
        // merged into the preceding range.
        if (preg_match('/NO[-\s]*Invoice\s*No\.?/iu', $text)) {
            if (preg_match('/Nummer\s*[:#-]?\s*\R?\s*([\pL\pN][\pL\pN._\/-]*)/iu', $text, $match)) {
                return self::normalizeInvoiceNumber($match[1]);
            }

            if (preg_match('/NO[-\s]*Invoice\s*No\.?\s*[:#-]?\s*\R?\s*([\pL\pN][\pL\pN._\/-]*)/iu', $text, $match)) {
                return self::normalizeInvoiceNumber($match[1]);
            }
        }

        $patterns = [
            '/NO[-\s]*Faktura\s*nr\.?\s*[:#-]?\s*\R?\s*([\pL\pN][\pL\pN._\/-]*)/iu',
            '/Invoice\s*No\.?\s*[:#-]?\s*\R?\s*([\pL\pN][\pL\pN._\/-]*)/iu',
            '/Faktura\s*nr\.?\s*[:#-]?\s*\R?\s*([\pL\pN][\pL\pN._\/-]*)/iu',
            '/Rechnungs\s*nr\.?\s*[:#-]?\s*\R?\s*([\pL\pN][\pL\pN._\/-]*)/iu',
            '/Nummer\s*[:#-]?\s*\R?\s*([\pL\pN][\pL\pN._\/-]*)/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $match)) {
                return self::normalizeInvoiceNumber($match[1]);
            }
        }

        return null;
    }

    private static function containsInvoiceNumberLabel(string $text): bool
    {
        return preg_match(
            '/(?:NO[-\s]*)?Invoice\s*No\.?|(?:NO[-\s]*)?Faktura\s*nr\.?|Rechnungs\s*nr\.?/iu',
            $text
        ) === 1;
    }

    private static function normalizeInvoiceNumber(string $invoiceNumber): string
    {
        return mb_strtoupper(trim($invoiceNumber, " \t\n\r\0\x0B.,:;#"), 'UTF-8');
    }

    private function optimizeSplitPdfForWeb(string $path): void
    {
        if (!config('ocr_profiles.pdf_processing.optimize_split_files', true)) {
            return;
        }

        $minimumBytes = max(0, (int) config(
            'ocr_profiles.pdf_processing.optimize_split_min_bytes',
            1048576
        ));
        if (!is_file($path) || filesize($path) < $minimumBytes) {
            return;
        }

        $configuredBinary = config('ocr_profiles.pdf_processing.ghostscript_binary');
        $finder = new ExecutableFinder();
        $binary = $configuredBinary ?: $finder->find('gs')
            ?: $finder->find('gswin64c')
            ?: $finder->find('gswin32c');

        if (!$binary) {
            \Log::notice('Ghostscript not found; split PDF was not optimized for web viewing', [
                'file' => $path,
            ]);
            return;
        }

        $optimizedPath = $path.'.optimized.pdf';
        try {
            $process = new Process([
                $binary,
                '-q',
                '-dNOPAUSE',
                '-dBATCH',
                '-dSAFER',
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.7',
                '-dDetectDuplicateImages=true',
                '-dCompressFonts=true',
                '-dSubsetFonts=true',
                '-dFastWebView=true',
                '-sOutputFile='.$optimizedPath,
                $path,
            ]);
            $process->setTimeout(120);
            $process->run();

            if ($process->isSuccessful() && is_file($optimizedPath) && filesize($optimizedPath) > 0) {
                // rename() replaces files atomically on Linux. copy() is the
                // Windows fallback, where replacing an existing file can fail.
                if (@rename($optimizedPath, $path)) {
                    return;
                }
                if (@copy($optimizedPath, $path)) {
                    @unlink($optimizedPath);
                    return;
                }
            }

            \Log::warning('Ghostscript could not optimize split PDF for web viewing', [
                'file' => $path,
                'error' => trim($process->getErrorOutput()),
            ]);
        } catch (\Throwable $e) {
            // Viewer optimization must never prevent OCR submission.
            \Log::warning('Split PDF web optimization was skipped', [
                'file' => $path,
                'error' => $e->getMessage(),
            ]);
        } finally {
            if (is_file($optimizedPath)) {
                @unlink($optimizedPath);
            }
        }
    }

    /** @return array<int, string> Text keyed by one-based PDF page number. */
    private function azureOcrGetTextByPageSafe(string $filePath): array
    {
        $endpoint = rtrim(config('services.azure_form.endpoint'), '/');
        $apiKey   = config('services.azure_form.key');

        $url = $endpoint . "/formrecognizer/documentModels/prebuilt-read:analyze?api-version=2023-07-31";

        $client = new GuzzleClient(['timeout' => 90]);

        $attempts = 0;
        $maxAttempts = 5;

        retry:
        try {
            $response = $client->post($url, [
                'headers' => [
                    'Ocp-Apim-Subscription-Key' => $apiKey,
                    'Content-Type' => 'application/pdf',
                ],
                'body' => fopen($filePath, 'r'),
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()?->getStatusCode() === 429 && $attempts < $maxAttempts) {
                $attempts++;
                sleep(2 ** $attempts); // exponential backoff
                goto retry;
            }
            throw $e;
        }

        $operationLocation = $response->getHeaderLine('operation-location');
        //if (!$operationLocation) return '';
        if (!$operationLocation) return [];

        $pollStartedAt = microtime(true);
        do {
            sleep(2);
            $poll = $client->get($operationLocation, [
                'headers' => ['Ocp-Apim-Subscription-Key' => $apiKey],
            ]);
            $result = json_decode($poll->getBody(), true);

            if (microtime(true) - $pollStartedAt > 1800) {
                throw new \RuntimeException('Azure batch OCR did not finish within 30 minutes.');
            }
        } while (($result['status'] ?? '') === 'running');

        if (($result['status'] ?? '') !== 'succeeded') {
            //return '';
            return [];
        }

        //$text = '';
        $textByPage = [];
        foreach ($result['analyzeResult']['pages'] ?? [] as $page) {
            //$text .= implode("\n", array_column($page['lines'] ?? [], 'content')) . "\n";
            $pageNumber = (int) ($page['pageNumber'] ?? count($textByPage) + 1);
            $textByPage[$pageNumber] = trim(implode("\n", array_column($page['lines'] ?? [], 'content')));
        }

        //return trim($text);
        return $textByPage;
    }    

    private function normalizePdfForOcr(string $inputPath): string
    {
        $outputPath = storage_path('app/temp/' . uniqid() . '_normalized.pdf');

        $imagick = new Imagick();

        // Higher DPI improves OCR quality
        $imagick->setResolution(300, 300);

        $imagick->readImage($inputPath);

        foreach ($imagick as $page) {

            $orientation = $page->getImageOrientation();

            switch ($orientation) {

                case Imagick::ORIENTATION_RIGHTTOP:
                    $page->rotateImage("#ffffff", 90);
                    break;

                case Imagick::ORIENTATION_LEFTBOTTOM:
                    $page->rotateImage("#ffffff", -90);
                    break;

                case Imagick::ORIENTATION_BOTTOMRIGHT:
                    $page->rotateImage("#ffffff", 180);
                    break;
            }

            // Reset orientation metadata
            $page->setImageOrientation(
                Imagick::ORIENTATION_TOPLEFT
            );

            $page->setImageFormat('pdf');
        }

        $imagick->writeImages($outputPath, true);

        $imagick->clear();
        $imagick->destroy();

        return $outputPath;
    }
}
