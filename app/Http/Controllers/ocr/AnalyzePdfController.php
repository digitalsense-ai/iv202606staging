<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use \App\Classes\CommonClass;
use \App\Classes\FtpClass;

use App\Models\Client;
use App\Models\VATRegistrationMain;
use App\Models\VATRegistration;
use App\Models\InvoiceOcrPdf;
use App\Jobs\SplitPdfJob;
use App\Services\AzureStorageService;

use App\Services\MicrosoftMailService;
use App\Jobs\ProcessEmailJob;

use App\Repositories\ClientRepository;

use App\Jobs\ValidateOcrInvoicesJob;

use App\Helpers\DateHelper;

class AnalyzePdfController extends Controller
{
    public $authUser;

    public $commonClass;
    public $ftpClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
            
            $this->ftpClass = new FtpClass();

            return $next($request);
        });
    }   

    /* -- GET /analyzepdf -- */
    public function index()
    {       
        // $invoiceDate = DateHelper::parseInvoiceDate(
        //     '02/JUN/2026.'
        // );
        // dd($invoiceDate);

        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $analyzepdfs = InvoiceOcrPdf::
        //with(['client', 'client.vatregmain'])
                            orderBy('id', 'DESC')            
                            ->get(); 
      
        $vatregmains = VATRegistrationMain::with(['client'])
                        ->orderBy('id', 'ASC')
                        ->get();

        $only_org_no = $this->commonClass->OrgNoForOcr();
        // $syncclients = $analyzepdfs
        //                 //->where('client_id', 89)    
        //                 ->pluck('client')
        //                 ->filter() // remove nulls
        //                 ->unique('id')
        //                 ->filter(function ($client) use ($only_org_no) {
        //                     return $client->vatregmain->contains(function ($vat) use ($only_org_no) {
        //                         return in_array($vat->org_no, $only_org_no);
        //                     });
        //                 })
        //                 ->sortBy('client_name')
        //                 ->values();     

        $syncclients = $vatregmains
                        ->filter(function ($vatregmain) use ($only_org_no) {
                            return in_array($vatregmain->org_no, $only_org_no);
                        })
                        ->pluck('client')
                        ->filter()
                        ->unique('id')
                        ->sortBy('client_name')
                        ->values();   

        /* -- RETURN VIEW -- */
        return view('content.ocr.analyze', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,  
          'vatregmains' => $vatregmains,          
          'analyzepdfs' => isset($analyzepdfs) ? (($analyzepdfs) ? $analyzepdfs : NULL) : NULL,
          'syncclients' => $syncclients
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /analyzepdf -- */

    /* -- GET /analyzepdf/search -- */
    public function search()
    {   
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'analyzepdf-search');      
        /* --end PAGE CONFIG -- */

        $analyzepdfs = InvoiceOcrPdf::
        //with(['client'])
                            where('status', 'completed')
                            ->where('is_deleted', 0)
                            ->orderBy('id', 'ASC')            
                            ->get(); 
      
        $vatregmains = VATRegistrationMain::with(['client'])
                        ->orderBy('id', 'ASC')
                        ->get();

        /* -- RETURN VIEW -- */
        return view('content.ocr.search', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,  
          'vatregmains' => $vatregmains,          
          'analyzepdfs' => isset($analyzepdfs) ? (($analyzepdfs) ? $analyzepdfs : NULL) : NULL
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /analyzepdf/search -- */

    /* -- GET /fetchinbox -- */
    // public function fetchInbox()
    // {
    //     /* -- PAGE CONFIG -- */
    //     $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
    //     /* --end PAGE CONFIG -- */

    //     $mailService = new MicrosoftMailService();

    //     // Fetch all unread emails
    //     $emails = $mailService->getAllInboxEmails();

    //     foreach ($emails as &$email) {

    //         // Queue the processing instead of running it directly
    //         ProcessEmailJob::dispatch($email['id'], $email['subject'] ?? '')->onQueue('ocrpdfinvoices');
            
    //         // You can optionally mark that it is queued
    //         $email['attachments'] = ['status' => 'queued'];
    //     }

    //     $analyzepdfs = InvoiceOcrPdf::with(['client'])
    //                         ->orderBy('id', 'DESC')            
    //                         ->get(); 
      
    //     $vatregmains = VATRegistrationMain::with(['client'])
    //                     ->orderBy('id', 'ASC')
    //                     ->get();

    //     /* -- RETURN VIEW -- */
    //     return view('content.ocr.analyze', [
    //       'pageConfigs' => $pageConfigs, 
    //       'authUser' => $this->authUser,  
    //       'vatregmains' => $vatregmains,          
    //       'analyzepdfs' => isset($analyzepdfs) ? (($analyzepdfs) ? $analyzepdfs : NULL) : NULL
    //     ]);
    //     /* --end RETURN VIEW -- */
    // }

    public function fetchInbox()
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $mailService = new MicrosoftMailService();

        // Fetch all unread emails with attachments
        $emails = $mailService->getAllInboxEmails();

        Cache::forget('inbox_completed');
        Cache::forget('inbox_total');

        $clients = app(ClientRepository::class)->all();

        foreach ($emails as &$email) {

            if (stripos($email['subject'], "second female") !== false &&
                $email['sender']['emailAddress']['address'] == "tina.elsborg@intravat.com"
            ) 
            { 
                // Mark as remove
                $email['remove'] = true;
                
                $mailService->markEmailAsRead($email['id']);
                $mailService->moveEmailToFolder($email['id'], "Duplicate");                
            }
            else
            {
                // Queue email processing job
                ProcessEmailJob::dispatch($clients, $email['id'], $email['subject'] ?? '')
                    ->onQueue('ocrpdfinvoices');

                // // Increment total count for progress bar
                // Cache::increment('inbox_total');

                // Mark as queued in UI
                $email['attachments'] = ['status' => 'queued'];
            }
        }

        // $analyzepdfs = InvoiceOcrPdf::with(['client'])
        //                     ->orderBy('id', 'DESC')            
        //                     ->get(); 
      
        // $vatregmains = VATRegistrationMain::with(['client'])
        //                 ->orderBy('id', 'ASC')
        //                 ->get();

        // /* -- RETURN VIEW -- */
        // return view('content.ocr.analyze', [
        //   'pageConfigs' => $pageConfigs, 
        //   'authUser' => $this->authUser,  
        //   'vatregmains' => $vatregmains,          
        //   'analyzepdfs' => isset($analyzepdfs) ? (($analyzepdfs) ? $analyzepdfs : NULL) : NULL
        // ]);
        // /* --end RETURN VIEW -- */        

        // Remove emails marked as 'remove'
        $emails = array_values(array_filter($emails, function ($email) {
            return empty($email['remove']);
        }));

        return response()->json([
            'total' => count($emails),
            'queued_emails' => $emails,
        ], 202);
    }
    /* --end GET /fetchinbox -- */

    public function analyzeStoredPdfs(array $clients, array $paths, string $folder, string $batchId, string $emailMessageId = null, array $prevCaptures = [], bool $bulk =  false)
    {        
        $invoiceType = $folder; // 'sales' or 'com'
        $whichStudio = 'model';

        $analyzerId = match ($invoiceType) {
            'sales', 'multi-invoices' => 'sales_invoice_analyzer_v7',
            'com'   => 'com_invoice_analyzer_v8',
            default => 'sales_invoice_analyzer_v7',
        };

        $modelId = match ($invoiceType) {
            'sales', 'multi-invoices' => 'custom_sales_invoice_v17',
            'com'   => 'custom_com_invoice_v15',
            default => 'custom_sales_invoice_v17',
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
                $already_exist = InvoiceOcrPdf::where('invoice_type', $invoiceType)
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
                    $already_exist = InvoiceOcrPdf::where('invoice_type', $invoiceType)
                                        ->where('file_name', $originalName . '.pdf')
                                        ->where('status', 'completed')
                                        ->count();                    
                    $allow = ($prevCapture) ? true : (($already_exist > 0) ? false : true);
                }
            }

            if($allow)
            {
                // Increment total count for progress bar
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
                )->onQueue('ocrpdfinvoices');

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

    public function inboxProgress()
    {
        // Count jobs that are completed for this batch
        $total = Cache::get('inbox_total', 0);
        $completed = Cache::get('inbox_completed', 0);

        // $allDocs = DB::table('dv_invoice_ocr_pdfs')
        //             ->where('batch_id', $batchId)
        //             ->get();

        // $total = $allDocs->count();
        // $completed = $allDocs->whereIn('status', ['completed', 'failed'])->count();

        // // Collect error documents
        // $errorDocs = $allDocs
        //     ->where('status', 'failed')
        //     ->map(fn($doc) => [
        //         'document_id' => $doc->id,
        //         'file_name'   => $doc->file_name,
        //         'error'       => $doc->error,
        //     ])
        //     ->values();

        $analyzepdfs = InvoiceOcrPdf::
        //with(['client'])
                        orderBy('id', 'DESC')            
                        ->get();

        $vatregmains = VATRegistrationMain::with(['client'])
                        ->orderBy('id', 'ASC')
                        ->get();
        
        return response()->json([
            'total' => $total,
            'completed' => $completed,
            'analyzepdfs' => $analyzepdfs,
            'vatregmains' => $vatregmains,
        ]);
    }

    // /* -- POST /analyzepdf -- */
    // public function analyze(Request $request)
    // {
    //     /* ---------------- VALIDATION ---------------- */
    //     $baseRules = [
    //         'pdf_invoice_type' => 'required|string',
    //         //'email_message_id' => 'nullable|string'
    //     ];

    //     if ($request->pdf_invoice_type === 'multi-invoices') {
    //         $rules = array_merge($baseRules, [
    //             'pdf_file'    => 'required|file|mimes:pdf|max:65536',
    //             'page_ranges' => 'nullable|string',
    //         ]);
    //     } else {
    //         // if ($request->filled('pdf_paths')) 
    //         // {

    //         // }
    //         // else
    //         // {
    //             $rules = array_merge($baseRules, [
    //                 'pdfs'   => 'required|array|min:1',
    //                 'pdfs.*' => 'file|mimes:pdf|max:65536',
    //             ]);                
    //         //}
    //     }

    //     $request->validate($rules);

    //     /* ---------------- COMMON SETUP ---------------- */
    //     $invoiceType = $request->pdf_invoice_type;
    //     //$emailMessageId = $request->email_message_id;
    //     $batchId     = (string) Str::uuid();

    //     $whichStudio = 'model';

    //     $analyzerId = match ($invoiceType) {
    //         'sales', 'multi-invoices' => 'sales_invoice_analyzer_v7',
    //         'com' => 'com_invoice_analyzer_v8',
    //         default => 'sales_invoice_analyzer_v7',
    //     };

    //     $modelId = match ($invoiceType) {
    //         'sales', 'multi-invoices' => 'custom_sales_invoice_v12',
    //         'com' => 'custom_com_invoice_v9',
    //         default => 'custom_sales_invoice_v12',
    //     };        

    //     /* ---------------- MULTI-INVOICE ---------------- */
    //     if ($invoiceType === 'multi-invoices') {

    //         $file = $request->file('pdf_file');

    //         $originalName = pathinfo(
    //             $file->getClientOriginalName(),
    //             PATHINFO_FILENAME
    //         );
           
    //         // Store in storage/app/public/ocr/{invoice_type}
    //         $storedPath = $file->storeAs(
    //             'ocr/' . $invoiceType,
    //             $originalName.'.pdf',
    //             'public'
    //         );
    //         $fullPath = storage_path('app/public/' . $storedPath);

    //         SplitPdfJob::dispatch(
    //             $fullPath,
    //             $originalName,
    //             $invoiceType,
    //             $batchId,
    //             $whichStudio,
    //             ($whichStudio === 'model') ? $modelId : $analyzerId,
    //             $request->page_ranges,
    //             //$emailMessageId
    //         )->onQueue('ocrpdfinvoices');
    //     }

    //     /* ---------------- SALES / COM ---------------- */
    //     else {

    //         // $pdfFiles = [];

    //         // // Use already stored PDFs if provided
    //         // if ($request->filled('pdf_paths')) {
    //         //     foreach ($request->pdf_paths as $path) {
    //         //         $fullPath = storage_path('app/' . $path);

    //         //         if (!file_exists($fullPath)) {
    //         //             Log::warning("PDF not found: {$fullPath}");
    //         //             continue;
    //         //         }

    //         //         $pdfFiles[] = $fullPath;
    //         //     }
    //         // }
    //         // // Otherwise use uploaded files
    //         // elseif ($request->hasFile('pdfs')) {
    //         //     foreach ($request->file('pdfs') as $file) {
    //         //         $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    //         //         $storedPath = $file->storeAs('ocr/' . $invoiceType, $originalName.'.pdf', 'public');
    //         //         $pdfFiles[] = storage_path('app/public/' . $storedPath);
    //         //     }
    //         // }

    //         // foreach ($pdfFiles as $fullPath) {
    //         //     $originalName = pathinfo($fullPath, PATHINFO_FILENAME);

    //         //     Log::info("Queued PDF for analyze: {$originalName}");

    //         //     SplitPdfJob::dispatch(
    //         //         $fullPath,
    //         //         $originalName,
    //         //         $invoiceType,
    //         //         $batchId,
    //         //         $whichStudio,
    //         //         ($whichStudio === 'model') ? $modelId : $analyzerId,
    //         //         null,
    //         //         $emailMessageId
    //         //     )->onQueue('ocrpdfinvoices');
    //         // }

    //         foreach ($request->file('pdfs') as $file) {

    //             $originalName = pathinfo(
    //                 $file->getClientOriginalName(),
    //                 PATHINFO_FILENAME
    //             );
              
    //             // if ($request->filled('pdf_paths')) 
    //             // {
    //             //     // Determine full path from file object (for local storage, should exist already)
    //             //     $fullPath = $file->getRealPath();
    //             // }
    //             // else
    //             // {
    //                 // Store in storage/app/public/ocr/{invoice_type}
    //                 $storedPath = $file->storeAs(
    //                     'ocr/' . $invoiceType,
    //                     $originalName.'.pdf',
    //                     'public'
    //                 );            
    //                 $fullPath = storage_path('app/public/ocr/' . $invoiceType . '/' . $originalName . '.pdf');
    //             //}

    //             Log::info('Uploaded file for analyse: '. $originalName);

    //             // Reuse SplitPdfJob but WITHOUT page ranges
    //             SplitPdfJob::dispatch(
    //                 $fullPath,
    //                 $originalName,
    //                 $invoiceType,
    //                 $batchId,
    //                 $whichStudio,
    //                 ($whichStudio === 'model') ? $modelId : $analyzerId,
    //                 null,
    //                 //$emailMessageId
    //             )->onQueue('ocrpdfinvoices');
    //         }
    //     }

    //     return response()->json([
    //         'batch_id' => $batchId,
    //         'message'  => 'PDF processing queued',
    //     ], 202);
    // }

    /* -- GET /analyzepdf/batch/{batch}/progress -- */
    public function batchProgress(string $batchId)
    {
        $allDocs = DB::table('dv_invoice_ocr_pdfs')
            ->where('batch_id', $batchId)
            ->get();

        $total = $allDocs->count();
        $completed = $allDocs->whereIn('status', ['completed', 'failed'])->count();

        // Collect error documents
        $errorDocs = $allDocs
            ->where('status', 'failed')
            ->map(fn($doc) => [
                'document_id' => $doc->id,
                'file_name'   => $doc->file_name,
                'error'       => $doc->error,
            ])
            ->values();

        $analyzepdfs = InvoiceOcrPdf::
        //with(['client'])
                        orderBy('id', 'DESC')            
                        ->get();

        $vatregmains = VATRegistrationMain::with(['client'])
                        ->orderBy('id', 'ASC')
                        ->get();

        return response()->json([
            'analyzepdfs' => $analyzepdfs,
            'vatregmains' => $vatregmains,
            'total'       => $total,
            'completed'   => $completed,
            'percent'     => $total === 0 ? 0 : round(($completed / $total) * 100),
            'error_docs'  => $errorDocs,
        ]);
    }
    /* --end GET /analyzepdf/batch/{batch}/progress -- */

    /* -- PUT /analyzepdf/{analyze_id} -- */
    public function analyzeUpdate(Request $request)
    {     
        $invoice = InvoiceOcrPdf::find($request->analyzepdf_id);

        if (!$invoice)
            return;

        $updates = [];
        // Current JSON data
        $currentData = $invoice->extracted_data ?? [];

        // Check invoice_type
        if (($currentData['invoice_type'] ?? null) !== $request->invoice_type) {
            $updates['invoice_type'] = $request->invoice_type;
        }

        // Check supplier org_number
        if (($currentData['supplier']['org_number'] ?? null) !== $request->client_no) {
            $updates['extracted_data->supplier->org_number'] = $request->client_no;
        }

        // Check supplier name
        if (($currentData['supplier']['name'] ?? null) !== $request->client_name) {
            $updates['extracted_data->supplier->name'] = $request->client_name;
        }

        // Check recipient org_number
        if (($currentData['recipient']['org_number'] ?? null) !== $request->client_no) {
            $updates['extracted_data->recipient->org_number'] = $request->client_no;
        }

        // Check recipient name
        if (($currentData['recipient']['name'] ?? null) !== $request->client_name) {
            $updates['extracted_data->recipient->name'] = $request->client_name;
        }

        // Check invoice_date
        if (($currentData['invoice_date'] ?? null) !== $request->invoice_date) {
            $updates['extracted_data->invoice_date'] = $request->invoice_date;
        }

        if($request->client_name && (
                str_contains(strtolower($request->client_name), 'rainwear') 
                || str_contains(strtolower($request->client_name), 'engel') 
                || str_contains(strtolower($request->client_name), 'berendsohn')
                || str_contains(strtolower($request->client_name), 'horn bord')
            )
        )
        {

        }
        else
        {
            // Check invoice_number
            if (($currentData['invoice_number'] ?? null) !== $request->invoice_no) {
                $updates['extracted_data->invoice_number'] = $request->invoice_no;
            }
        }

        $currency               = $request->currency;
        $exchangeCurrency       = $request->exchange_currency;

        $netAmount              = $request->net_amount;
        $exchangeNetAmount      = $request->exchange_net_amount;

        $vatAmount              = $request->vat_amount;
        $exchangeVatAmount      = $request->exchange_vat_amount;

        $totalAmount            = $request->total_amount;
        $exchangeTotalAmount    = $request->exchange_total_amount;

        // Apply same swap logic as frontend
        if ($currency !== 'NOK' && $currency !== 'CHF') {

            [$currency, $exchangeCurrency] = [$exchangeCurrency, $currency];

            [$netAmount, $exchangeNetAmount] = [
                $exchangeNetAmount,
                $netAmount
            ];

            [$vatAmount, $exchangeVatAmount] = [
                $exchangeVatAmount,
                $vatAmount
            ];

            [$totalAmount, $exchangeTotalAmount] = [
                $exchangeTotalAmount,
                $totalAmount
            ];
        }

        // Check currency
        if (($currentData['currency'] ?? null) !== $currency) {
            $updates['extracted_data->currency'] = $currency;
        }

        // Check net_amount
        if (($currentData['net_amount'] ?? null) !== $netAmount) {
            $updates['extracted_data->net_amount'] = $netAmount;
        }

        // Check vat_rate
        if (($currentData['vat_rate'] ?? null) !== $request->vat_rate) {
            $updates['extracted_data->vat_rate'] = $request->vat_rate;
        }

        // Check vat_amount
        if (($currentData['vat_amount'] ?? null) !== $vatAmount) {
            $updates['extracted_data->vat_amount'] = $vatAmount;
        }

        // Check total_amount
        if (($currentData['total_amount'] ?? null) !== $totalAmount) {
            $updates['extracted_data->total_amount'] = $totalAmount;
        }

        // Check exchange_currency
        if (($currentData['exchange_currency'] ?? null) !== $exchangeCurrency) {
            $updates['extracted_data->exchange_currency'] = $exchangeCurrency;
        }

        // Check exchange_rate
        if (($currentData['exchange_rate'] ?? null) !== $request->exchange_rate) {
            $updates['extracted_data->exchange_rate'] = $request->exchange_rate;
        }

        // Check exchange_net_amount
        if (($currentData['exchange_net_amount'] ?? null) !== $exchangeNetAmount) {
            $updates['extracted_data->exchange_net_amount'] = $exchangeNetAmount;
        }

        // Check exchange_vat_amount
        if (($currentData['exchange_vat_amount'] ?? null) !== $exchangeVatAmount) {
            $updates['extracted_data->exchange_vat_amount'] = $exchangeVatAmount;
        }

        // Check exchange_total_amount
        if (($currentData['exchange_total_amount'] ?? null) !== $exchangeTotalAmount) {
            $updates['extracted_data->exchange_total_amount'] = $exchangeTotalAmount;
        }

      // // Check currency
      // if (($currentData['currency'] ?? null) !== $request->currency) {
      //     $updates['extracted_data->currency'] = $request->currency;
      // }

      // // Check net_amount
      // if (($currentData['net_amount'] ?? null) !== $request->net_amount) {
      //     $updates['extracted_data->net_amount'] = $request->net_amount;
      // }

      // // Check vat_rate
      // if (($currentData['vat_rate'] ?? null) !== $request->vat_rate) {
      //     $updates['extracted_data->vat_rate'] = $request->vat_rate;
      // }

      // // Check vat_amount
      // if (($currentData['vat_amount'] ?? null) !== $request->vat_amount) {
      //     $updates['extracted_data->vat_amount'] = $request->vat_amount;
      // }

      // // Check total_amount
      // if (($currentData['total_amount'] ?? null) !== $request->total_amount) {
      //     $updates['extracted_data->total_amount'] = $request->total_amount;
      // }
      
      // // Check exchange_currency
      // if (($currentData['exchange_currency'] ?? null) !== $request->exchange_currency) {
      //     $updates['extracted_data->exchange_currency'] = $request->exchange_currency;
      // }

      // // Check exchange_rate
      // if (($currentData['exchange_rate'] ?? null) !== $request->exchange_rate) {
      //     $updates['extracted_data->exchange_rate'] = $request->exchange_rate;
      // }

      // // Check exchange_net_amount
      // if (($currentData['exchange_net_amount'] ?? null) !== $request->exchange_net_amount) {
      //     $updates['extracted_data->exchange_net_amount'] = $request->exchange_net_amount;
      // }     

      // // Check exchange_vat_amount
      // if (($currentData['exchange_vat_amount'] ?? null) !== $request->exchange_vat_amount) {
      //     $updates['extracted_data->exchange_vat_amount'] = $request->exchange_vat_amount;
      // }

      // Check sales invoices
      $requestSalesInvoices = collect($request->input('sales-invoice', []))
                                ->pluck('number')
                                ->filter()
                                ->values()
                                ->toArray();

      $currentSalesInvoices = collect($currentData['sales_invoices'] ?? [])
                                ->values()
                                ->toArray();

      if ($currentSalesInvoices !== $requestSalesInvoices) {
          $updates['extracted_data->related_sales_invoices'] = $requestSalesInvoices;
      }

      // Check status
      if (($currentData['status'] ?? null) !== $request->analyzepdf_status) {
          $updates['status'] = 'completed';
      }

      // Only run update if something changed
      if (!empty($updates)) {
            $updates['sync_status'] = 0;
            $updates['is_locked'] = 0;

          $invoice->update($updates);

          $allDocs = DB::table('dv_invoice_ocr_pdfs')->get();

          $total = $allDocs->count();
          $completed = $allDocs->whereIn('status', ['completed', 'failed'])->count();

          // Collect error documents
          $errorDocs = $allDocs
              ->where('status', 'failed')
              ->map(fn($doc) => [
                  'document_id' => $doc->id,
                  'file_name'   => $doc->file_name,
                  'error'       => $doc->error,
              ])
              ->values();

          $analyzepdfs = InvoiceOcrPdf::
          //with(['client'])
                          orderBy('id', 'DESC')            
                          ->get();

          $vatregmains = VATRegistrationMain::with(['client'])
                          ->orderBy('id', 'ASC')
                          ->get();

          return response()->json([
              'analyzepdfs' => $analyzepdfs,
              'vatregmains' => $vatregmains,
              'total'       => $total,
              'completed'   => $completed,
              'percent'     => $total === 0 ? 0 : round(($completed / $total) * 100),
              'error_docs'  => $errorDocs,
          ]);
      }
    }
    /* --end PUT /analyzepdf/{analyze_id} -- */

    /* -- GET /analyzepdf/{analyze_id}/sas-url -- */
    public function getSasUrl($id, $type = null)
    {
        $invoice = InvoiceOcrPdf::findOrFail($id);

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
    /* --end GET /analyzepdf/{analyze_id}/sas-url -- */
    
    /* -- DELETE /analyzepdf/{analyze_id}/delete -- */
    public function deleteAnalyzePdf(Request $request, $analyze_id)
    {
        try
        {
            $selected_analyze_ids = ($analyze_id == '0') ? $request->analyzepdf_delete_id : $analyze_id;

            foreach (explode(',', $selected_analyze_ids) as $analyze_id)
            {
                $invoice = InvoiceOcrPdf::findOrFail($analyze_id);

                $invoice->is_deleted = 1;  
                $invoice->deleted_reason = $request->analyzepdf_delete_reason_quill;

                $invoice->save();

                $this->commonClass->addLog($this->authUser, 'analyzepdf-delete', 
                    [
                        'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
                        'File Name' => $invoice->file_name,                    
                        'Invoice Type' => $invoice->invoice_type
                    ]
                );
            }

            $analyzepdfs = InvoiceOcrPdf::
            //with(['client'])
                              orderBy('id', 'DESC')            
                              ->get();

            $vatregmains = VATRegistrationMain::with(['client'])
                              ->orderBy('id', 'ASC')
                              ->get();
          
            return response()->json(
                [
                    'status' => 200,             
                    'message' => "success",
                    'analyzepdfs' => $analyzepdfs,
                    'vatregmains' => $vatregmains,
                    'tab_name' => $request->tab_name
                ]
            );  
        }
        catch (\Exception $e) {
            return  $e->getMessage();
        }
    }  
    /* --end DELETE /analyzepdf/{analyze_id}/delete -- */ 

    /* -- GET /analyzepdf-sync -- */
    public function syncAnalyzePdf(Request $request)
    {
      try
      {
        $client_id = $request->client_id;
 
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();

        /* -- GET ALL VAT REG. FOR PRODUCT TYPE - 2/3 -- */
        $query = VATRegistration::with(['vatregmain','client',
                                  'importreconciliationcominvoices' => function($query) {                                   
                                    $query->where('data_from', '!=', 'ivf')
                                      ->where('data_from', '!=', 'ftp')
                                      ->orderBy('last_modified_at', 'desc')                                   
                                      ->get();
                                  }
                                ])
                                ->withCount('importreconciliationcominvoices')
                                ->withCount('importreconciliationsalesinvoices')
                                ->whereHas('vatregmain', function ($subquery) {
                                    $subquery->where('status', 1)
                                      ->where('product_type', 2)
                                      ->orWhere('product_type', 3)
                                      ->orWhere('product_type', 5); 
                                });
        if($client_id)        
          $vatregs = $query->whereHas('client', function ($subquery) use ($client_id) {                                        
              $subquery->whereIn('id', [$client_id]);
          });                 
        
        $vatregs = $query->get();
        /* --end GET ALL VAT REG. FOR PRODUCT TYPE - 2/3 -- */  

        if ($vatregs->isEmpty())
        {
            $client = Client::where('id', $client_id)->first();

            $client_name = ($client_id) ? (($client) ? $client->client_name : 'All') : 'All';
        }
        else
            $client_name = ($client_id) ? $vatregs->first()->client->client_name : 'All';    
       
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'analyzepdf-sync',
          [
            'Client Name' => ($client_id) ? $client_name : 'All'
          ]
        );
        /* --end LOG -- */
        
        $batchIds = [];
        $result = [];

        $unique_countries = [];
      
        $from = 'ocr-search-refresh';
        //$full_refresh = true;


        if (!$vatregs->isEmpty())
        {
            $vatreg = $vatregs->first();

            $vatregmain = $vatreg->vatregmain; 
           
            if($vatregmain->country == 'NO')
              $org_no = $vatregmain->org_no;        
            else
              $org_no = str_replace(['.', '-'], '', $vatregmain->vat_no);
                    
            $check_org_no = $org_no ? preg_replace('/\D/', '', $org_no) : '';
        
            $omit_org_no = $this->commonClass->OrgNoForOcr();
            if ($check_org_no && in_array($check_org_no, $omit_org_no))
            {            
                $insert_invoices = 0;
                $insert_invoices = $this->commonClass->loadImportReconciliationDatasFromOcr($this->authUser, $vatreg, $from);

                if(is_array($insert_invoices))
                {
                    $data = $insert_invoices['processed'];
                }
            }

            foreach($vatregs as $key => $vatreg)
            {
                if(!in_array($vatreg->country, $unique_countries, true))
                {
                    if ($client_name && (
                        stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
                        stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
                        stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
                        )
                    ) 
                    {      
                        $which_folder = (strtolower(env('APP_URL')) === "https://app.intravat.cloud" || strtolower(config('app.url')) === "https://app.intravat.cloud") ? 'main' : 'archive';
                                           
                        /* -- READ XML FILE FROM FTP -- */
                        $ftpdata = $this->ftpClass->getImportReconciliationFilesFromFtp($vatreg, $this->authUser, $which_folder); 
                        /* --end READ XML FILE FROM FTP -- */
                        
                        /* -- READ XML FILE FROM E-FACTO -- */
                        if (stripos(strtolower($client_name), "noscomed") !== false ||
                            stripos(strtolower($client_name), "rexholm") !== false)                    
                          $ftpdata = $this->ftpClass->getImportReconciliationFilesFromFtp($vatreg, $this->authUser, $which_folder, true);
                        /* --end READ XML FILE FROM E-FACTO -- */
                      
                        if(!in_array($vatreg->country, $unique_countries, true))                
                            array_push($unique_countries, $vatreg->country);                    
                    }
                } //read all at a time
            } //for 
        }//has vatreg


        // foreach($vatregs as $key => $vatreg)
        // {    
        //   $client_name = $vatreg->client->client_name;
       
        //     //$data = $this->commonClass->loadImportReconciliationDatasFromAzureDb($this->authUser, $vatreg, $from, $full_refresh);
            
        //     $client_id = $vatreg->client_id;
        //     $vat_reg_id = $vatreg->id;

        //     $vatregmain = $vatreg->vatregmain; 
        //     $vat_reg_main_id = $vatreg->vat_reg_main_id;

        //     if($vatregmain->country == 'NO')
        //       $org_no = $vatregmain->org_no;        
        //     else
        //       $org_no = str_replace(['.', '-'], '', $vatregmain->vat_no);
                    
        //     $check_org_no = $org_no ? preg_replace('/\D/', '', $org_no) : '';

        //     //second female - NO/CH; sports - NO; DFI
        //     $omit_org_no = $this->commonClass->OrgNoForOcr();
        //     if ($check_org_no && in_array($check_org_no, $omit_org_no))
        //     {              
        //       //sync from OCR extraction
        //       //$from = str_replace('global', 'ocr', $from);

        //       // $insert_invoices = 0;
        //       // $insert_invoices = $this->commonClass->loadImportReconciliationDatasFromOcr($this->authUser, $vatreg, $from);

        //       // if(is_array($insert_invoices))
        //       // {
        //       //   $data = $insert_invoices['processed'];
        //       // }

        //       // if($full_refresh && $from == 'ocr-search-refresh')
        //       // {
        //       //   if($insert_invoices == 0)
        //       //       $data = $insert_invoices;
        //       //   else
        //       //   {
        //       //       if(count($insert_invoices['result']) > 0)
        //       //         $data = $insert_invoices;
        //       //       else  
        //       //         $data = $insert_invoices['insert_invoices'];  
        //       //   }
        //       // }
        //       // else  
        //       //   $data = $insert_invoices;  

        //       //   if($data)
        //       //   {
        //       //     if(is_array($data))
        //       //     {
        //       //       if($data['insert_invoices'] > 0)
        //       //         $batchIds[] = [                
        //       //           'batchId' => $data['insert_invoices'],                
        //       //         ];
                   
        //       //       if($data['result']) 
        //       //       {       
        //       //         if($result)                      
        //       //           //$result = array_merge($result->toArray(), $data['result']);
        //       //           $result = $result->merge($data['result']);
        //       //         else     
        //       //           $result = $data['result'];
        //       //       }
        //       //     }
        //       //     else
        //       //     {
        //       //       if($data > 0)
        //       //       {
        //       //         $batchIds[] = [
        //       //           'batchId' => $data              
        //       //         ];
        //       //       }
        //       //     }
        //       //   }

        //         if(!in_array($vatreg->country, $unique_countries, true))
        //         {
        //             if (stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
        //             stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
        //             stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
        //             ) 
        //             {      
        //                 $which_folder = (strtolower(env('APP_URL')) === "https://app.intravat.cloud" || strtolower(config('app.url')) === "https://app.intravat.cloud") ? 'main' : 'archive';
                                           
        //                 /* -- READ XML FILE FROM FTP -- */
        //                 $ftpdata = $this->ftpClass->getImportReconciliationFilesFromFtp($vatreg, $this->authUser, $which_folder); 
        //                 /* --end READ XML FILE FROM FTP -- */
                        
        //                 /* -- READ XML FILE FROM E-FACTO -- */
        //                 if (stripos(strtolower($client_name), "noscomed") !== false ||
        //                     stripos(strtolower($client_name), "rexholm") !== false)                    
        //                   $ftpdata = $this->ftpClass->getImportReconciliationFilesFromFtp($vatreg, $this->authUser, $which_folder, true);
        //                 /* --end READ XML FILE FROM E-FACTO -- */
                      
        //                 if(!in_array($vatreg->country, $unique_countries, true))                
        //                     array_push($unique_countries, $vatreg->country);                    
        //             }
        //         } //read all at a time
        //     } //OCR 
        // }/* --end for VAT REG. -- */

        //$result = ($result) ? $result->values()->toArray() : $result;

        // if($client_id)
        // {
        //   session()->forget('ocrResults'.$client_id);
        //   session()->save();
        //   session()->put('ocrResults'.$client_id, $result);
        // }
        // else
        // {
        //   session()->forget('ocrResults');
        //   session()->save();
        //   session()->put('ocrResults');
        // }        

        /* -- RETURN JSON -- */
        return response()->json([
          'status' => 200,
          'message' => 'Done',
          //'batchIds' => $batchIds,
          'data' => isset($data) ? $data : null
          //'x' => $x
        ]);
        /* --end RETURN JSON -- */
      }      
      catch (\Exception $e) 
      {      dd($e);     
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Analyze Pdf Controller',
            'method' => 'syncAnalyzePdf',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    } 
    /* --end GET /analyzepdf-sync -- */
    
    /* -- GET /analyzepdf/{analyze_id}/recapture -- */
    public function recapture(Request $request, $id)
    {
        Cache::forget('inbox_completed');
        Cache::forget('inbox_total');

        $selected_analyze_ids = ($id == '0') ? $request->selected_analyzepdf_id : $id;
        $attachments = [];
        $grouped = [
            'sales' => [],
            'com' => []
        ];
        foreach (explode(',', $selected_analyze_ids) as $id)
        {            
            $invoice = InvoiceOcrPdf::findOrFail($id);

            $invoice->sync_status = 0;
            $invoice->is_locked = 0;
            $invoice->save();

            //Get file from Azure storage
            $sasPaths = $this->getSasUrl($id, 'recapture');

            $sasUrl = $sasPaths['signedUrl'];
            $blobPath = $sasPaths['blobPath'];
            
            $prevCapture = [
                'prevId' => $id,
                'sasUrl' => $sasUrl,
                'blobPath' => $blobPath
            ];

            //Save it in local
            $sasUrl = html_entity_decode($sasUrl);
            $sasUrl = str_replace(' ', '%20', $sasUrl);

            $stream = fopen($sasUrl, 'r');
            $fileName = basename($invoice->file_name);
            //Storage::disk('public')->put('ocr/' . $fileName, $stream);
            Storage::disk('local')->put('ocr/' . $fileName, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
            //$fullPath = storage_path('app/public/ocr/' . $fileName);            
            $fullPath = storage_path('app/ocr/' . $fileName);            

            // Unique batch ID for this email
            $batchId = (string) Str::uuid();
            
            $mailService = new MicrosoftMailService();            

            $content = file_get_contents($fullPath);
            $contentBytes = base64_encode($content);

            // Safe deletion
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $attachment = [
                'name' => $fileName,
                'contentBytes' => $contentBytes,
                'prevCapture' => $prevCapture,
                'prevFolder' => $invoice->invoice_type
            ];
            $grouped = $mailService->groupFiles($attachment, null, $grouped);            
        }
            
        // Safety check: skip if no attachments
        if (empty($grouped)) {
            Log::warning("No PDF attachments found for this recapture");
            return;
        }

        $clients = app(ClientRepository::class)->all();

        foreach ($grouped as $folder => $items) {
            if (!empty($items)) {
                // Trigger analysis for stored PDFs                
                
                $paths = [];
                $prevCaptures = [];

                foreach ($items as $item) {
                    $paths[] = $item['path'];
                    $prevCaptures[] = $item['prevCapture'];
                }

                $this->analyzeStoredPdfs($clients, $paths, $folder, $batchId, null, $prevCaptures);
            }
        }

        // Mark as queued in UI
        $grouped['attachments'] = ['status' => 'queued'];

        //Delete it from Azure Blob Storage
        //$azureService = new AzureStorageService();
        //$azureService->deleteFile($sasUrl);

        return response()->json([
            'total' => count($grouped['sales']) + count($grouped['com']),
            'queued_emails' => $grouped
        ], 202);
    }   
    /* --end GET /analyzepdf/{analyze_id}/recapture -- */

    /* -- POST /analyzepdf/bulk-upload -- */
    public function ocrBulkUpload(Request $request)
    {    
      try 
      { 
        $files = $request->file('file');
        $folder = $request->bulk_pdf_invoice_type;
        $total_uploaded_files = $request->bulk_total_uploads;
        $total_uploaded_files = $request->bulk_total_uploads;
       
        if($files && $folder)   
        {   
            $clients = app(ClientRepository::class)->all();

            // Unique batch ID for this email
            $batchId = (string) Str::uuid();

            // if(strtolower(env('APP_URL')) === "http://localhost:8000" || strtolower(config('app.url')) === "http://localhost:8000")
            // {
                
            // }
            // else
            // {                
                $this->analyzeStoredPdfs($clients, $files, $folder, $batchId, null, [], true);   
            //}
           
            return response()->json([
                'total' => $total_uploaded_files,//count($files),
                'queued_emails' => $files
            ], 202); 
        }  
        else
        {
            return response()->json([
              'status'=> 'error', 
              'message'=> 'Please select the invoice type and upload files.'
            ], 400);
        }      
      }//try
      catch (\Exception $e) 
      { 
        return response()->json([
          'status'=> 'error', 
          'message'=> $e->getMessage()
        ], 400); 
      }//catch
    }
    /* -- POST /analyzepdf/bulk-upload -- */

    /* -- GET /analyzepdf/reload -- */
    public function ocrReload(Request $request)
    {    
        try 
        { 
            //check in tracking table to reload the OCR and declaration page
            event(new OcrInvoicesSyncEvent($clientId, 'Synced the OCR invoices'));

            event(new ImportReconciliationComSalesInvoicesEvent($vat_reg_id, 'Updated the com./sales invoice'));
        }//try
        catch (\Exception $e) 
        { 
            return response()->json([
                'status'=> 'error', 
                'message'=> $e->getMessage()
            ], 400); 
        }//catch
    }
    /* -- GET /analyzepdf/reload -- */

    /* -- GET /analyzepdf/{analyze_id}/validate -- */
    public function analyzeValidate(Request $request, $id)
    {     
        try 
        { 
            Cache::forget('inbox_completed');
            Cache::forget('inbox_total');
            
            if($id == 'all')
            {
                $selected_analyze_ids = [];//41, 27281

                $selected_analyze_ids = InvoiceOcrPdf::where('status', 'completed')
                                ->where('invoice_type', 'com')                                
                                ->where('extracted_data', 'LIKE', '%932337274%')  
                                ->orderBy('id', 'ASC')            
                                ->pluck('id')
                                ->toArray(); 

                //dd($selected_analyze_ids);
            }
            else
            {
                $selected_analyze_ids = ($id == '0')
                    ? explode(',', $request->selected_analyzepdf_id)
                    : [$id];
            }

            dispatch((new ValidateOcrInvoicesJob(null, $selected_analyze_ids))->onQueue('ocrpdfvalidateinvoices'));
           
            return response()->json([
                'status'=> 'success', 
                'message'=> "Validation Done",
                'total' => count($selected_analyze_ids)
            ], 202);
        }//try
        catch (\Exception $e) 
        { 
            return response()->json([
                'status'=> 'error', 
                'message'=> $e->getMessage()
            ], 400); 
        }//catch
    }
    /* --end GET /analyzepdf/{analyze_id}/validate -- */
}
