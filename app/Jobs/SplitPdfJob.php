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

use Imagick;

class SplitPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public array $clients,
        public string $fullPath,
        public string $originalName,
        public string $invoiceType,
        public string $batchId,
        public string $azureStudioType = 'analyzer',
        public string $analyzerId,
        public ?string $pageRanges = null,
        public ?string $emailMessageId = null,
        public ?array $prevCapture = []
    ) {}

    public function handle()
    {
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
                    'error' => "File too large ({$fileSizeMB}MB) for PDF processing",
                    'created_at' => now(),
                    'source_environment' => config('database.ocr_source_environment')
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
                $ocrPdf = OcrPdf::query()->create([
                //$docId = DB::table('dv_invoice_ocr_pdfs')->insertGetId([
                    'client_id'   => null,
                    'batch_id'    => $this->batchId,
                    'invoice_type'=> $this->invoiceType,
                    'file_name'   => $this->originalName . '.pdf',
                    'analyzer_id' => $this->analyzerId,
                    'status'      => 'queued',
                    'created_at'  => now(),
                    'source_environment' => config('database.ocr_source_environment')
                ]);
                $docId = $ocrPdf->id;

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
            $ocrpdf->save();

            // Delete local file
            // if (file_exists($this->fullPath)) {
            //     unlink($this->fullPath);
            // }
            //Store in azure storage blob

            SubmitAnalyzeJob::dispatch(
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
                $ocrpdf->save();

                SubmitAnalyzeJob::dispatch(
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

            // Queue OCR job            
            //$docId = DB::table('dv_invoice_ocr_pdfs')->insertGetId([
            $ocrPdf = OcrPdf::query()->create([
                'client_id' => null,
                'batch_id' => $this->batchId,
                'invoice_type' => $this->invoiceType,
                'file_name' => $this->originalName . '_' . $counter . '.pdf',
                'analyzer_id' => $this->analyzerId,
                'status' => 'queued',
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
                'source_environment' => config('database.ocr_source_environment')
            ]);
            $docId = $ocrPdf->id;

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
        $ranges = [];
        $invoiceByPage = [];

        // Load PDF info to get total pages
        $pdfInfo = new Fpdi();
        $totalPages = $pdfInfo->setSourceFile($this->fullPath);
        unset($pdfInfo);

        // Process each page individually
        for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {

            // Create temporary single-page PDF
            $tmpPdf = tempnam(sys_get_temp_dir(), 'pdfp_') . '.pdf';
            $fpdi = new Fpdi();
            $fpdi->setSourceFile($this->fullPath);
            $fpdi->AddPage();
            $tpl = $fpdi->importPage($pageNo);
            //$fpdi->useTemplate($tpl);
            
            $size = $fpdi->getTemplateSize($tpl);

            $orientation = ($size['width'] ?? 0) > ($size['height'] ?? 0) ? 'L' : 'P';
            $width = (float) ($size['width'] ?? 0);
            $height = (float) ($size['height'] ?? 0);

            $fpdi->AddPage($orientation, [$width, $height]);
            $fpdi->useTemplate($tpl, 0, 0, $width, $height, true);

            $fpdi->Output('F', $tmpPdf);
            unset($fpdi);

            // 1️⃣ Try Smalot parser first
            $text = '';
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($tmpPdf);
                $text = trim($pdf->getText());
            } catch (\Throwable $e) {}
// \Log::info("TEXT: ");
// \Log::info($text);
            // 2️⃣ Fallback to Azure OCR if Smalot fails
            if (trim($text) === '') {
                //$fixedPdf = $this->normalizePdfForOcr($tmpPdf);

                //$text = $this->azureOcrGetTextSafe($fixedPdf);
                $text = $this->azureOcrGetTextSafe($tmpPdf);
                //\Log::info("azure TEXT: ");
                //\Log::info($text);
                sleep(5); // throttle Azure requests
            }

            // Detect invoice number
            // if (
            //     preg_match('/NO-Invoice No\.?\s*(\S+)/i', $text, $m) ||
            //     preg_match('/Nummer\s*\n\s*(\S+)/i', $text, $m)
            // ) {
            //     $invoiceByPage[$pageNo] = $m[1];
            // }

            // 1️ If page contains "NO-Invoice No."
            if (preg_match('/NO-Invoice No\.?\s*(\S+)/i', $text)) {
//\Log::info("/NO-Invoice No");
                // Then extract using "Nummer"
                if (preg_match('/Nummer\s*\n\s*(\S+)/i', $text, $m)) {
                    //\Log::info("/Nummer");
                    $invoiceByPage[$pageNo] = $m[1];                   
                }
            } 
            // 2️ Check "Invoice No."
            //elseif (preg_match('/Invoice No\.?\s*(?:\r?\n)?\s*(\S+)/i', $text, $m)) {
            elseif (preg_match('/Invoice\s*No[^A-Za-z0-9]+([A-Za-z0-9-]+)/i', $text, $m)) {

                //\Log::info("Matched: Invoice No");
                //\Log::info($m);
                $invoiceByPage[$pageNo] = $m[1];

            }
            // 3 Otherwise fallback to "Fakturanr."
            elseif (preg_match('/Fakturanr\.?\s*(?:\r?\n)?\s*(\S+)/i', $text, $m)) {
                //\Log::info("/Fakturanr");
                $invoiceByPage[$pageNo] = $m[1];
            }
            else
            {
                // Then extract using "Nummer"
                if (preg_match('/Nummer\s*\n\s*(\S+)/i', $text, $m)) {
                    $invoiceByPage[$pageNo] = $m[1];
                    //\Log::info($m);
                    //\Log::info($invoiceByPage);
                }
            }
//\Log::info($invoiceByPage);
            // Delete temp PDF & free memory
            unlink($tmpPdf);
            gc_collect_cycles();
        }
//\Log::info("Total pages: ". $totalPages);
        // Build page ranges grouped by invoice number
        $currentInvoice = null;
        $rangeStart = 1;

        for ($page = 1; $page <= $totalPages; $page++) {
            $invoice = $invoiceByPage[$page] ?? $currentInvoice;
//\Log::info("Has invoice : ". $invoice);
            if ($currentInvoice === null) {
                $currentInvoice = $invoice;
                $rangeStart = $page;
                continue;
            }

            if ($invoice !== $currentInvoice) {
                $ranges[] = [$rangeStart, $page - 1];
                $currentInvoice = $invoice;
                $rangeStart = $page;
            }
        }

        if($totalPages == 2 && $rangeStart == 2)
            $rangeStart = 1;

        // Add the last range
        $ranges[] = [$rangeStart, $totalPages];

        return $ranges;
    }

    private function azureOcrGetTextSafe(string $filePath): string
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
        if (!$operationLocation) return '';

        do {
            sleep(2);
            $poll = $client->get($operationLocation, [
                'headers' => ['Ocp-Apim-Subscription-Key' => $apiKey],
            ]);
            $result = json_decode($poll->getBody(), true);
        } while (($result['status'] ?? '') === 'running');

        if (($result['status'] ?? '') !== 'succeeded') {
            return '';
        }

        $text = '';
        foreach ($result['analyzeResult']['pages'] ?? [] as $page) {
            $text .= implode("\n", array_column($page['lines'] ?? [], 'content')) . "\n";
        }

        return trim($text);
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
