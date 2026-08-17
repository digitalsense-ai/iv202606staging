<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Carbon;

use \App\Classes\CommonClass;
use \App\Classes\FtpClass;

use App\Models\Client;
use App\Models\VATRegistrationMain;
use App\Models\VATRegistration;
use App\Models\OcrPdf;
use App\Models\OcrSyncStatus;
use App\Jobs\SplitPdfJob;
use App\Services\AzureStorageService;
use App\Services\OcrCorrectionFeedbackService;
use App\Services\MicrosoftMailService;
use App\Services\OcrAnalyzeService;

use App\Repositories\ClientRepository;

use App\Jobs\ValidateOcrInvoicesJob;
use App\Jobs\ProcessEmailJob;
use App\Jobs\SyncDbFromOcr;

use App\Helpers\DateHelper;
use App\Helpers\EuropeanNumberHelper;
use App\Helpers\EnvironmentHelper;

class AnalyzePdfController extends Controller
{
    public $authUser;

    public $commonClass;
    public $ftpClass;
    public $environment;
    public $selectedFields = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
            
            $tempEmailList = config('app.temp_email_list', []);
            if (
                !$this->authUser ||
                !(
                    $this->authUser->role === 'super-admin' ||
                    (
                        $this->authUser->role === 'team-user' &&
                        in_array($this->authUser->email, $tempEmailList, true)
                    )
                )
            ) {
                abort(403);
            }

            $this->environment = EnvironmentHelper::getEnvironment();

            $this->ftpClass = new FtpClass();

            $this->selectedFields = [
                'id',   
                //'client_id',
                'invoice_type',
                'file_name',
                'start_pageno',
                'end_pageno',
                'status',
                'sync_db',
                //'sync_status',
                'is_deleted',
                'deleted_reason',
                //'is_locked',
                'extracted_data',
                'validation_status',
                'duplicate_message',
                'error',
                'azure_url',
                'azure_sas_url',
                'manual_input_by',
                'manual_input_status',                
                'manual_note',
                'manual_input_environment',
                'search_save_by',
                'search_save_status',                
                'search_save_note',
                'search_save_environment',
                'force_submitted',
                'analyzer_id',
                'created_at',
                'updated_at'
            ];

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

//         $netAmount = EuropeanNumberHelper::normalize(
//             '243.378,59
// 248.178,59'
//         );
//         dd($netAmount);

        Cache::forget('inbox_completed');

        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        // $analyzepdfs = OcrPdf::query()
        //                 //->with('syncStatus')
        //                 ->select($this->selectedFields)
        //                 //->where('extracted_data', 'LIKE', '%123456789%')
        //                 ->orderBy('id', 'DESC')            
        //                 ->get(); 
        
        $analyzepdfs = OcrPdf::query()                       
                        ->select($this->selectedFields)  
                        ->whereIn('status', ['completed', 'duplicate', 'failed', 'processing', 'queued'])                        
                        //->where('extracted_data', 'LIKE', '%123456789%')                  
                        ->orderBy('id', 'DESC')            
                        //->get(); 
                        ->count();

      //dd($env, $analyzepdfs->first());
        $vatregmains = VATRegistrationMain::
                        select([
                            'id',
                            'org_no',
                            'vat_no',
                            'country',
                            'client_id',
                        ])
                        ->with([
                            'client:id,client_name'
                        ])  
                        ->where('ocr_sync', 1)  
                        //with(['client'])
                        ->orderBy('id', 'ASC')
                        ->get();

        // $only_org_no = $this->commonClass->OrgNoForOcr();        
        // $syncclients = $vatregmains
        //                 ->filter(function ($vatregmain) use ($only_org_no) {
        //                     //return in_array($vatregmain->org_no, $only_org_no);
        //                     $orgNo = preg_replace('/\D+/', '', $vatregmain->org_no ?? '');
        //                     $vatNo = preg_replace('/\D+/', '', $vatregmain->vat_no ?? '');

        //                     return in_array($orgNo, $only_org_no) || in_array($vatNo, $only_org_no);
        //                 })
        //                 // ->pluck('client')
        //                 // ->filter()
        //                 // ->unique('id')
        //                 // ->sortBy('client_name')
        //                 // ->values();
        //                 ->map(function ($vatregmain) {
        //                     return [
        //                         'id' => $vatregmain->id,
        //                         'client'  => $vatregmain->client,
        //                         'country' => $vatregmain->country,
        //                     ];
        //                 })
        //                 ->filter(function ($item) {
        //                     return $item['client'];
        //                 })
        //                 // ->unique(function ($item) {
        //                 //     return $item['client']->id;
        //                 // })
        //                 ->sortBy(function ($item) {
        //                     return $item['client']->client_name;
        //                 })
        //                 ->values();  

        $syncclients = $vatregmains                        
                        ->map(function ($vatregmain) {
                            return [
                                'id' => $vatregmain->id,
                                'client'  => $vatregmain->client,
                                'country' => $vatregmain->country,
                            ];
                        })
                        ->filter(function ($item) {
                            return $item['client'];
                        })                        
                        ->sortBy(function ($item) {
                            return $item['client']->client_name;
                        })
                        ->values(); 

        // $syncdbclients = VATRegistrationMain::
        //                 select([
        //                     'id',
        //                     'org_no',
        //                     'vat_no',
        //                     'country',
        //                     'client_id',
        //                 ])
        //                 ->with([
        //                     'client:id,client_name'
        //                 ]) 
        //                 ->orderBy('id', 'ASC')
        //                 ->get()
        //                 ->map(function ($vatregmain) {
        //                     return [
        //                         'id' => $vatregmain->id,
        //                         'client'  => $vatregmain->client,
        //                         'country' => $vatregmain->country,
        //                     ];
        //                 })
        //                 ->filter(function ($item) {
        //                     return $item['client'];
        //                 })                        
        //                 ->sortBy(function ($item) {
        //                     return $item['client']->client_name;
        //                 })
        //                 ->values();

        /* -- RETURN VIEW -- */
        return view('content.ocr.analyze', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,  
          //'vatregmains' => $vatregmains,          
          //'analyzepdfs' => isset($analyzepdfs) ? (($analyzepdfs) ? $analyzepdfs : NULL) : NULL,
          'hasanalyzepdfs' => $analyzepdfs,
          'syncclients' => $syncclients,
          //'syncdbclients' => $syncdbclients
          'environment' => $this->environment
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /analyzepdf -- */

    /* -- GET /analyzepdf/data -- */
    // public function analyzeData(Request $request)
    // {
    //     $page = (int) ($request->page ?? 1);
    //     $limit = 1000;

    //     $analyzepdfs = OcrPdf::query()
    //         ->select($this->selectedFields)
    //         ->orderByDesc('id')
    //         ->paginate($limit, ['*'], 'page', $page);

    //     $vatregmains = VATRegistrationMain::
    //             select([
    //                 'id',
    //                 'org_no',
    //                 'vat_no',
    //                 'country',
    //                 'client_id',
    //             ])
    //             ->with([
    //                 'client:id,client_name',
    //             ])                            
    //             ->orderBy('id', 'ASC')
    //             ->get();

    //     return response()->json([
    //         'data' => $analyzepdfs->items(),
    //         'current_page' => $analyzepdfs->currentPage(),
    //         'last_page' => $analyzepdfs->lastPage(),
    //         'vatregmains' => $vatregmains,
    //     ]);
    // }

    public function analyzeData(Request $request)
    {
        $page = (int) ($request->page ?? 1);
        $limit = 50000;

        $analyzepdfs = OcrPdf::query()
            ->select($this->selectedFields)
            //->where('extracted_data', 'LIKE', '%123456789%')
            ->whereIn('status', ['completed', 'duplicate', 'failed', 'processing', 'queued'])
            ->orderByDesc('id')
            ->paginate($limit, ['*'], 'page', $page);

        $response = [
            'data' => $analyzepdfs->items(),
            'current_page' => $analyzepdfs->currentPage(),
            'last_page' => $analyzepdfs->lastPage(),
        ];

        if ($page === 1) {

            $response['vatregmains'] = VATRegistrationMain::select([
                    'id',
                    'org_no',
                    'vat_no',
                    'country',
                    'client_id',
                ])
                ->with([
                    'client:id,client_name',
                ])
                ->orderBy('id', 'ASC')
                ->get();

        }
        $response['environment'] = $this->environment;
        return response()->json($response);
    }

    // public function analyzeData()
    // {
    //     $analyzepdfs = OcrPdf::query()                        
    //                     ->select($this->selectedFields)
    //                     //->where('extracted_data', 'LIKE', '%123456789%')
    //                     //->where('id', '38633')
    //                     //->where('extracted_data', 'LIKE', '%villy jensen%')
    //                     ->orderBy('id', 'DESC')            
    //                     ->get(); 
    //                     //->paginate(100);

    //     // $clients = OcrPdf::query()
    //     //     ->whereNotNull('extracted_data')
    //     //     ->selectRaw("
    //     //         COALESCE(
    //     //             JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.receipient.name')),
    //     //             JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.name'))
    //     //         ) as client_name
    //     //     ")
    //     //     ->whereRaw("
    //     //         COALESCE(
    //     //             JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.receipient.name')),
    //     //             JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.name'))
    //     //         ) IS NOT NULL
    //     //     ")
    //     //     ->distinct()
    //     //     ->orderBy('client_name')
    //     //     ->pluck('client_name');       

    //     $vatregmains = VATRegistrationMain::
    //                     select([
    //                         'id',
    //                         'org_no',
    //                         'vat_no',
    //                         'country',
    //                         'client_id',
    //                     ])
    //                     ->with([
    //                         'client:id,client_name',
    //                     ])                            
    //                     ->orderBy('id', 'ASC')
    //                     ->get();

    //     return response()->json([
    //         'vatregmains' => $vatregmains,
    //         //'clients' => $clients,
    //         'analyzepdfs' => isset($analyzepdfs) ? (($analyzepdfs) ? $analyzepdfs : NULL) : NULL,
    //     ], 200);
    // }
    /* --end GET /analyzepdf/data -- */

    /* -- GET /analyzepdf/search -- */
    public function search()
    {   
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'analyzepdf');      
        /* --end PAGE CONFIG -- */

        $analyzepdfs = OcrPdf::query()
                        ->with('syncStatus')
                        ->select($this->selectedFields)  
                        //->where('extracted_data', 'LIKE', '%292640361%')  
                        ->where('status', 'completed')
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

    // /* -- GET /analyzepdf/synceddb -- */
    // public function syncedDb()
    // {   
    //     /* -- PAGE CONFIG -- */
    //     $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'analyzepdf');      
    //     /* --end PAGE CONFIG -- */

    //     $synceddatas = OcrPdfSyncDb::query()
    //                     ->orderBy('id', 'ASC')
    //                     ->get(); 
      
    //     // $vatregmains = VATRegistrationMain::with(['client'])
    //     //                 ->orderBy('id', 'ASC')
    //     //                 ->get();

    //     /* -- RETURN VIEW -- */
    //     return view('content.ocr.synced', [
    //       'pageConfigs' => $pageConfigs, 
    //       'authUser' => $this->authUser,  
    //       //'vatregmains' => $vatregmains,          
    //       'synceddatas' => $synceddatas ?? null
    //     ]);
    //     /* --end RETURN VIEW -- */
    // }
    // /* --end GET /analyzepdf/synced -- */    

    // /* -- GET /analyzepdf/update-failed-syncdb -- */
    // public function updatefailedsyncdb()
    // {
    //     $connection = DB::connection(
    //         config('database.ocr_connection')
    //     );

    //     $updated = $connection->table('dv_ocr_pdfs')
    //         ->where('sync_db', 3)
    //         ->update([
    //             'sync_db' => 0,
    //             'sync_started_at' => null,
    //         ]);

    //     return response()->json([
    //         'message' => 'OCR sync failed jobs updated successfully.',
    //         'updated' => $updated,
    //     ]);
    // }
    // /* -- GET /analyzepdf/update-failed-syncdb -- */

    // /* -- GET /analyzepdf/syncdb -- */
    // public function syncDb()
    // {    
    //     $connection = DB::connection(
    //         config('database.ocr_connection')
    //     );

    //     $fetchPeriodFrom = '2026-04-01';

    //     /*
    //      * Recover records that were stuck in processing
    //      * for more than 1 hour.
    //      */
    //     $connection->table('dv_ocr_pdfs')
    //         ->where('sync_db', 2)
    //         ->where('sync_started_at', '<', now()->subHour())
    //         ->update([
    //             'sync_db' => 0,
    //             'sync_started_at' => null,
    //         ]);

    //     $totalSync = 0;
    //     do {

    //         /*
    //          * Claim up to 100 records.
    //          */
    //         $ocrPdfIds = $connection->transaction(function () use (
    //             $connection,
    //             $fetchPeriodFrom
    //         ) {
    //             //$org_no = '292640361';
    //             // $rows = $connection->select(
    //             //     "
    //             //     SELECT p.id
    //             //     FROM dv_ocr_pdfs p
    //             //     WHERE p.sync_db = 0
    //             //       AND p.is_deleted = 0
    //             //       AND p.status = 'completed'
    //             //       AND JSON_UNQUOTE(
    //             //           JSON_EXTRACT(
    //             //               p.extracted_data,
    //             //               '$.invoice_date'
    //             //           )
    //             //       ) >= ?
    //             //       AND (
    //             //           REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(p.extracted_data, '$.supplier.org_number')), '[^0-9]', '') = ?
    //             //           OR
    //             //           REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(p.extracted_data, '$.supplier.cvr_number')), '[^0-9]', '') = ?
    //             //           OR
    //             //           REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(p.extracted_data, '$.recipient.org_number')), '[^0-9]', '') = ?
    //             //         )
    //             //     ORDER BY p.id ASC
    //             //     LIMIT 100
    //             //     FOR UPDATE SKIP LOCKED
    //             //     ",
    //             //     [$fetchPeriodFrom, $org_no, $org_no, $org_no]
    //             // );

    //             $rows = $connection->select(
    //                 "
    //                 SELECT p.id
    //                 FROM dv_ocr_pdfs p
    //                 WHERE p.sync_db = 0
    //                   AND p.is_deleted = 0
    //                   AND p.status = 'completed'
    //                   AND JSON_UNQUOTE(
    //                       JSON_EXTRACT(
    //                           p.extracted_data,
    //                           '$.invoice_date'
    //                       )
    //                   ) >= ?                      
    //                 ORDER BY p.id ASC
    //                 LIMIT 100
    //                 FOR UPDATE SKIP LOCKED
    //                 ",
    //                 [$fetchPeriodFrom]
    //             );

    //             $ids = collect($rows)
    //                 ->pluck('id')
    //                 ->values();

    //             if ($ids->isNotEmpty()) {

    //                 $connection->table('dv_ocr_pdfs')
    //                     ->whereIn('id', $ids)
    //                     ->update([
    //                         'sync_db' => 2,
    //                         'sync_started_at' => now(),
    //                     ]);
    //             }

    //             return $ids;
    //         });

    //         /*
    //          * Nothing left to process.
    //          */
    //         if ($ocrPdfIds->isEmpty()) {
    //             break;
    //         }

    //         /*
    //          * 100 claimed records
    //          * -> 4 queue jobs x 25 records.
    //          */
    //         foreach ($ocrPdfIds->chunk(25) as $chunk) {

    //             Bus::dispatch(
    //                 (new SyncDbFromOcr(
    //                     $chunk->all(),
    //                     $this->authUser
    //                 ))->onQueue('ocrpdfsyncdb')
    //             );
    //         }

    //         $totalSync += count($ocrPdfIds);
    //     } while (true);

    //     return response()->json([
    //         'message' => 'OCR sync jobs dispatched successfully.',
    //         'totalSync' => $totalSync
    //     ]);
    // }
    // /* -- END GET /analyzepdf/syncdb -- */

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
                in_array($email['sender']['emailAddress']['address'], config('app.omit_email_list'))
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
                ProcessEmailJob::dispatch($clients, $email['id'], $email['subject'] ?? '', $email['replyAttachments'] ?? [])
                    ->onQueue(config('queue.ocr.inbox', 'ocrpdfinvoices'));

                // // Increment total count for progress bar
                // Cache::increment('inbox_total');

                // Mark as queued in UI
                $email['attachments'] = ['status' => 'queued'];
            }
        }
        
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

/*
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
*/
    public function inboxProgress()
    {
        // Count jobs that are completed for this batch
        $total = Cache::get('inbox_total', 0);
        $completed = Cache::get('inbox_completed', 0);        

        $analyzepdfs = OcrPdf::query()
                        ->with('syncStatus')
                        ->select($this->selectedFields)
                        ->orderBy('id', 'DESC')            
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

    /* -- GET /analyzepdf/batch/{batch}/progress -- */
    public function batchProgress(string $batchId)
    {
        $allDocs = OcrPdf::query()
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

        $analyzepdfs = OcrPdf::query()
                        ->with('syncStatus')
                        ->select($this->selectedFields)
                        ->orderBy('id', 'DESC')            
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
        $invoice = OcrPdf::query()->find($request->analyzepdf_id);

        if (!$invoice)
            return;

        $updates = [];
        // Current JSON data
        $currentData = $invoice->extracted_data ?? [];

        $feedback = app(OcrCorrectionFeedbackService::class);
        $layoutFingerprint = $currentData['_ocr']['layout_fingerprint'] ?? null;
        $feedbackItems = [];

        // Check invoice_type
        if (($currentData['invoice_type'] ?? null) !== $request->invoice_type) {
            $updates['invoice_type'] = $request->invoice_type;

            $feedbackItems[] = [
                'field' => 'invoice_type',
                'original' => $currentData['invoice_type'] ?? null,
                'corrected' => $request->invoice_type,
            ];
        }

        // Check supplier org_number
        if (($currentData['supplier']['org_number'] ?? null) !== $request->client_no) {
            $updates['extracted_data->supplier->org_number'] = $request->client_no;

            $feedbackItems[] = [
                'field' => 'org_number',
                'original' => $currentData['supplier']['org_number'] ?? null,
                'corrected' => $request->client_no,
            ];
        }

        // Check supplier name
        if (($currentData['supplier']['name'] ?? null) !== $request->client_name) {
            $updates['extracted_data->supplier->name'] = $request->client_name;

            $feedbackItems[] = [
                'field' => 'name',
                'original' => $currentData['supplier']['name'] ?? null,
                'corrected' => $request->client_name,
            ];
        }

        // Check recipient org_number
        if (($currentData['recipient']['org_number'] ?? null) !== $request->client_no) {
            $updates['extracted_data->recipient->org_number'] = $request->client_no;

            $feedbackItems[] = [
                'field' => 'org_number',
                'original' => $currentData['recipient']['org_number'] ?? null,
                'corrected' => $request->client_no,
            ];
        }

        // Check recipient name
        if (($currentData['recipient']['name'] ?? null) !== $request->client_name) {
            $updates['extracted_data->recipient->name'] = $request->client_name;

            $feedbackItems[] = [
                'field' => 'name',
                'original' => $currentData['recipient']['name'] ?? null,
                'corrected' => $request->client_name,
            ];
        }

        // Check invoice_date
        if (($currentData['invoice_date'] ?? null) !== $request->invoice_date) {
            $updates['extracted_data->invoice_date'] = $request->invoice_date;

            $feedbackItems[] = [
                'field' => 'invoice_date',
                'original' => $currentData['invoice_date'] ?? null,
                'corrected' => $request->invoice_date,
            ];
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

                $feedbackItems[] = [
                    'field' => 'invoice_number',
                    'original' => $currentData['invoice_number'] ?? null,
                    'corrected' => $request->invoice_no,
                ];
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

            $feedbackItems[] = [
                'field' => 'currency',
                'original' => $currentData['currency'] ?? null,
                'corrected' => $currency,
            ];
        }

        // Check net_amount
        if (($currentData['net_amount'] ?? null) !== $netAmount) {
            $updates['extracted_data->net_amount'] = $netAmount;

            $feedbackItems[] = [
                'field' => 'net_amount',
                'original' => $currentData['net_amount'] ?? null,
                'corrected' => $netAmount,
            ];
        }

        // Check vat_rate
        if (($currentData['vat_rate'] ?? null) !== $request->vat_rate) {
            $updates['extracted_data->vat_rate'] = $request->vat_rate;

            $feedbackItems[] = [
                'field' => 'vat_rate',
                'original' => $currentData['vat_rate'] ?? null,
                'corrected' => $request->vat_rate,
            ];
        }

        // Check vat_amount
        if (($currentData['vat_amount'] ?? null) !== $vatAmount) {
            $updates['extracted_data->vat_amount'] = $vatAmount;

            $feedbackItems[] = [
                'field' => 'vat_amount',
                'original' => $currentData['vat_amount'] ?? null,
                'corrected' => $vatAmount,
            ];
        }

        // Check total_amount
        if (($currentData['total_amount'] ?? null) !== $totalAmount) {
            $updates['extracted_data->total_amount'] = $totalAmount;

            $feedbackItems[] = [
                'field' => 'total_amount',
                'original' => $currentData['total_amount'] ?? null,
                'corrected' => $totalAmount,
            ];
        }

        // Check exchange_currency
        if (($currentData['exchange_currency'] ?? null) !== $exchangeCurrency) {
            $updates['extracted_data->exchange_currency'] = $exchangeCurrency;

            $feedbackItems[] = [
                'field' => 'exchange_currency',
                'original' => $currentData['exchange_currency'] ?? null,
                'corrected' => $exchangeCurrency,
            ];
        }

        // Check exchange_rate
        if (($currentData['exchange_rate'] ?? null) !== $request->exchange_rate) {
            $updates['extracted_data->exchange_rate'] = $request->exchange_rate;

            $feedbackItems[] = [
                'field' => 'exchange_rate',
                'original' => $currentData['exchange_rate'] ?? null,
                'corrected' => $request->exchange_rate,
            ];
        }

        // Check exchange_net_amount
        if (($currentData['exchange_net_amount'] ?? null) !== $exchangeNetAmount) {
            $updates['extracted_data->exchange_net_amount'] = $exchangeNetAmount;

            $feedbackItems[] = [
                'field' => 'exchange_net_amount',
                'original' => $currentData['exchange_net_amount'] ?? null,
                'corrected' => $exchangeNetAmount,
            ];
        }

        // Check exchange_vat_amount
        if (($currentData['exchange_vat_amount'] ?? null) !== $exchangeVatAmount) {
            $updates['extracted_data->exchange_vat_amount'] = $exchangeVatAmount;

            $feedbackItems[] = [
                'field' => 'exchange_vat_amount',
                'original' => $currentData['exchange_vat_amount'] ?? null,
                'corrected' => $exchangeVatAmount,
            ];
        }

        // Check exchange_total_amount
        if (($currentData['exchange_total_amount'] ?? null) !== $exchangeTotalAmount) {
            $updates['extracted_data->exchange_total_amount'] = $exchangeTotalAmount;

            $feedbackItems[] = [
                'field' => 'exchange_total_amount',
                'original' => $currentData['exchange_total_amount'] ?? null,
                'corrected' => $exchangeTotalAmount,
            ];
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
            // $updates['sync_status'] = 0;
            // $updates['is_locked'] = 0;

            foreach ($feedbackItems as $item) {
                $feedback->capture(
                    invoiceId: $invoice->id,
                    field: $item['field'],
                    originalValue: $item['original'],
                    correctedValue: $item['corrected'],
                    clientId: $invoice->client_id,
                    layoutFingerprint: $layoutFingerprint
                );
            }

            $invoice->update($updates);
            
            OcrSyncStatus::updateOrCreate(
                [
                    'ocr_pdf_id' => $invoice->id,
                    'environment' => $this->environment,
                ],
                [                    
                    'sync_status' => 0,
                    'is_locked' => 0,
                ]
            );

            $allDocs = OcrPdf::query()->get();

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

            $analyzepdfs = OcrPdf::query()
                            ->with('syncStatus')
                            ->select($this->selectedFields)
                          ->orderBy('id', 'DESC')            
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
        $ocrAnalyzeService = new OcrAnalyzeService();
        $sasUrl = $ocrAnalyzeService->getSasUrl($id, $type);

        return $sasUrl;
        // $invoice = OcrPdf::query()->findOrFail($id);

        // if (!$invoice->azure_url) {
        //     return response()->json(['error' => 'PDF not available'], 404);
        // }

        // $azureService = new AzureStorageService();

        // $invoice_azure_url = $invoice->azure_url;
        // if ($invoice->azure_sas_url && $invoice->azure_sas_expiry && now()->lt($invoice->azure_sas_expiry))
        //     $signedUrl = $invoice->azure_sas_url;
        // else 
        // {
        //     //$invoice_azure_url = $invoice->azure_url;
        //     if (stripos($invoice->azure_url, "multi-invoices/") !== false)
        //     {
        //         if($type == 'recapture')
        //             $invoice_azure_url = $invoice->azure_url;
        //         // else             
        //         //     $invoice_azure_url = preg_replace('/_\d+\.pdf$/', '.pdf', $invoice->azure_url);
        //     }
            
        //     $signedUrl = $azureService->generateSasUrl($invoice_azure_url);
        //     $invoice->azure_sas_url = $signedUrl;
        //     $invoice->azure_sas_expiry = now()->addHours(1);
        //     $invoice->save();
        // }

        // if($type == 'recapture')
        // {
        //     //return $signedUrl;
        //     return [
        //         'blobPath' =>  $invoice_azure_url,
        //         'signedUrl' =>  $signedUrl,
        //     ];  
        // }
        // else    
        //     return response()->json([
        //         'azure_signed_url' => $signedUrl,
        //         'start_pageno' => $invoice->start_pageno ?? 1
        //     ]);
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
                $invoice = OcrPdf::query()->findOrFail($analyze_id);

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

            $analyzepdfs = OcrPdf::query()
                            ->with('syncStatus')
                            ->select($this->selectedFields)
                            ->orderBy('id', 'DESC')            
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
        $country = $request->country;
 
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
        
        if($country)        
          $vatregs = $query->where('country', $country);

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
          
            $fetch_period_from = null;
            if ($vatregmain->country == 'CH') {
                $fetch_period_from = ($vatregmain->service_start >= '2026-04-01')
                    ? '2026-04-01'
                    : null;
            } else {
                $fetch_period_from = ($vatregmain->service_start >= '2026-06-01')
                    ? '2026-06-01'
                    : null;
            }
            //$omit_org_no = $this->commonClass->OrgNoForOcr();
            //if ($check_org_no && in_array($check_org_no, $omit_org_no))
            if ($vatregmain->ocr_sync || !$fetch_period_from)
            {
                $insert_invoices = 0;
                // $insert_invoices = $this->commonClass->loadImportReconciliationDatasFromOcr($this->authUser, $vatreg, $from, $fetch_period_from);

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
                        $which_folder = ($this->environment === "live") ? 'main' : 'archive';
                                           
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
        $ocrAnalyzeService = new OcrAnalyzeService();
        
        Cache::forget('inbox_completed');
        Cache::forget('inbox_total');

        $selected_analyze_ids = ($id == '0') ? $request->selected_analyzepdf_id : $id;

        // $analyzepdfs = OcrPdf::query()
        //                 ->select([
        //                     'id', 
        //                     'file_name',
        //                     'extracted_data'
        //                 ])                        
        //                 ->whereIn('status', ['completed', 'failed'])
        //                 ->where('is_deleted', 0)
        //                 ->whereNull('og_extracted_data')
        //                 ->orderBy('id', 'DESC')            
        //                 ->get(); 
        // $arr_selected_analyze_ids = $analyzepdfs->pluck('id')->toArray();
        // unset($arr_selected_analyze_ids[16]);
        // $arr_selected_analyze_ids = array_values($arr_selected_analyze_ids);
        // $selected_analyze_ids = implode(',', $arr_selected_analyze_ids);
            

        $attachments = [];
        $grouped = [
            'sales' => [],
            'com' => []
        ];
        foreach (explode(',', $selected_analyze_ids) as $id)
        {            
            $invoice = OcrPdf::query()->findOrFail($id);

            // if($invoice->og_extracted_data)
            // {                
            //     ValidateOcrInvoicesJob::dispatch(null, [$id])
            //         ->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices'));
            // }
            // else
            // {
                $invoice->no_of_attempts = 0;
                $invoice->sync_db = 0;
                $invoice->manual_input_status = null;
                $invoice->search_save_status = null;
                //$invoice->sync_status = 0;
                //$invoice->is_locked = 0;
                $invoice->save();
                
                OcrSyncStatus::updateOrCreate(
                    [
                        'ocr_pdf_id' => $invoice->id,
                        'environment' => $this->environment,
                    ],
                    [                    
                        'sync_status' => 0,
                        'is_locked' => 0,
                    ]
                );

                //Get file from Azure storage
                $sasPaths = $ocrAnalyzeService->getSasUrl($id, 'recapture');

                $sasUrl = $sasPaths['signedUrl'];
                $blobPath = $sasPaths['blobPath'];
                
                $prevCapture = [
                    'prevId' => $id,
                    'sasUrl' => $sasUrl,
                    'blobPath' => $blobPath
                ];

                //Save it in local
                $sasUrl = html_entity_decode($sasUrl);
                // $sasUrl = str_replace(' ', '%20', $sasUrl);
                
                // $stream = fopen($sasUrl, 'r');

                // Encode only unsafe characters in path
                $parts = parse_url($sasUrl);

                $path = implode('/',
                    array_map(function ($segment) {
                        return rawurlencode(rawurldecode($segment));
                    }, explode('/', $parts['path']))
                );

                $rebuiltUrl =
                    $parts['scheme'] . '://' .
                    $parts['host'] .
                    $path .
                    (isset($parts['query']) ? '?' . $parts['query'] : '');
               
                $stream = fopen($rebuiltUrl, 'r');


                $fileName = basename($invoice->file_name);
                
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
            //} //og_extracted_data
        }
            
        // Safety check: skip if no attachments
        //if (empty($grouped)) {
        if (empty($grouped['sales']) && empty($grouped['multi-invoices']) && empty($grouped['com'])) {
            Log::warning("No PDF attachments found for this recapture");
            return;
        }

        $clients = app(ClientRepository::class)->all();

        $ocrAnalyzeService = new OcrAnalyzeService();

        foreach ($grouped as $folder => $items) {
            if (!empty($items)) {
                // Trigger analysis for stored PDFs                
                
                $paths = [];
                $prevCaptures = [];

                foreach ($items as $item) {
                    $paths[] = $item['path'];
                    $prevCaptures[] = $item['prevCapture'];
                }

                //$this->analyzeStoredPdfs($clients, $paths, $folder, $batchId, null, $prevCaptures);
                $ocrAnalyzeService->analyze($clients, $paths, $folder, $batchId, null, $prevCaptures);
            }
        }

        // Mark as queued in UI
        $grouped['attachments'] = ['status' => 'queued'];

        //Delete it from Azure Blob Storage
        //$azureService = new AzureStorageService();
        //$azureService->deleteFile($sasUrl);

        $total = 0;
        if(isset($grouped['sales']))
            $total += count($grouped['sales']);

        if(isset($grouped['com']))
            $total += count($grouped['com']);

        if(isset($grouped['multi-invoices']))
            $total += count($grouped['multi-invoices']);

        return response()->json([
            'total' => $total,
            'queued_emails' => $grouped
        ], 202);
    }   
    /* --end GET /analyzepdf/{analyze_id}/recapture -- */

    /* -- GET /analyzepdf/{analyze_id}/split -- */
    public function split(Request $request, $id)
    {
        $ocrAnalyzeService = new OcrAnalyzeService();
        
        Cache::forget('inbox_completed');
        Cache::forget('inbox_total');

        $selected_analyze_ids = ($id == '0') ? $request->selected_analyzepdf_id : $id;
               
        $attachments = [];
        $grouped = [
            'sales' => [],
            'com' => [],
            'multi-invoices' => []
        ];
        foreach (explode(',', $selected_analyze_ids) as $id)
        {            
            $invoice = OcrPdf::query()->findOrFail($id);
            
            $invoice->no_of_attempts = 0;
            $invoice->sync_db = 0;
            $invoice->manual_input_status = null;
            $invoice->search_save_status = null;
            //$invoice->sync_status = 0;
            //$invoice->is_locked = 0;
            $invoice->save();
            
            OcrSyncStatus::updateOrCreate(
                [
                    'ocr_pdf_id' => $invoice->id,
                    'environment' => $this->environment,
                ],
                [                    
                    'sync_status' => 0,
                    'is_locked' => 0,
                ]
            );

            //Get file from Azure storage
            $sasPaths = $ocrAnalyzeService->getSasUrl($id, 'split');

            $sasUrl = $sasPaths['signedUrl'];
            $blobPath = $sasPaths['blobPath'];
            
            $prevCapture = [
                'prevId' => $id,
                'sasUrl' => $sasUrl,
                'blobPath' => $blobPath,
                'split' => true
            ];

            //Save it in local
            $sasUrl = html_entity_decode($sasUrl);
            
            // Encode only unsafe characters in path
            $parts = parse_url($sasUrl);

            $path = implode('/',
                array_map(function ($segment) {
                    return rawurlencode(rawurldecode($segment));
                }, explode('/', $parts['path']))
            );

            $rebuiltUrl =
                $parts['scheme'] . '://' .
                $parts['host'] .
                $path .
                (isset($parts['query']) ? '?' . $parts['query'] : '');
           
            $stream = fopen($rebuiltUrl, 'r');


            $fileName = basename($invoice->file_name);
            
            Storage::disk('local')->put('ocr/' . $fileName, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }               
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
                'prevFolder' => 'multi-invoices'
            ];
            $grouped = $mailService->groupFiles($attachment, null, $grouped);            
        }
            
        // Safety check: skip if no attachments      
        if (empty($grouped['sales']) && empty($grouped['multi-invoices']) && empty($grouped['com'])) {
            Log::warning("No PDF attachments found for this split");
            return;
        }

        $clients = app(ClientRepository::class)->all();

        $ocrAnalyzeService = new OcrAnalyzeService();

        foreach ($grouped as $folder => $items) {
            if (!empty($items)) {
                // Trigger analysis for stored PDFs   
                $paths = [];
                $prevCaptures = [];

                foreach ($items as $item) {
                    $paths[] = $item['path'];
                    $prevCaptures[] = $item['prevCapture'];
                }
             
                $ocrAnalyzeService->analyze($clients, $paths, $folder, $batchId, null, $prevCaptures);
            }
        }

        // Mark as queued in UI
        $grouped['attachments'] = ['status' => 'queued'];

        $total = 0;
        if(isset($grouped['sales']))
            $total += count($grouped['sales']);

        if(isset($grouped['com']))
            $total += count($grouped['com']);

        if(isset($grouped['multi-invoices']))
            $total += count($grouped['multi-invoices']);

        return response()->json([
            'total' => $total,
            'queued_emails' => $grouped
        ], 202);
    }   
    /* --end GET /analyzepdf/{analyze_id}/split -- */

    /* -- POST /analyzepdf/bulk-upload -- */
    public function ocrBulkUpload(Request $request)
    {    
      try 
      { 
        $files = $request->file('file');
        $folder = $request->bulk_pdf_invoice_type;
        $total_uploaded_files = $request->bulk_total_uploads;
        
        Cache::put('inbox_total', $total_uploaded_files); 

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
            //    $this->analyzeStoredPdfs($clients, $files, $folder, $batchId, null, [], true);   
            //}

            $ocrAnalyzeService = new OcrAnalyzeService();
            $ocrAnalyzeService->analyze($clients, $files, $folder, $batchId, null, [], true);   
           
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
                $selected_analyze_ids = [];

                $selected_analyze_ids = OcrPdf::query()
                                ->where('status', 'completed')
                                ->where('is_deleted', 0)
                                ->where('sync_db', 0)
                                ->where('invoice_type', 'com')                                
                                //->where('extracted_data', 'LIKE', '%123456789%')  
                                ->orderBy('id', 'ASC')            
                                ->pluck('id')
                                ->toArray(); 

                dd("change the query");
            }
            else
            {
                $selected_analyze_ids = ($id == '0')
                    ? explode(',', $request->selected_analyzepdf_id)
                    : [$id];
            }

            OcrPdf::query()
                ->whereIn('id', $selected_analyze_ids)
                ->update([
                    'no_of_attempts' => 0,
                    'sync_db' => 0,
                ]);
    
            //dispatch((new ValidateOcrInvoicesJob(null, $selected_analyze_ids))->onQueue('ocrpdfvalidateinvoices'));

            ValidateOcrInvoicesJob::dispatch(null, $selected_analyze_ids)
                     ->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices'));
           
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
