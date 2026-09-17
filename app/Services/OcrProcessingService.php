<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batch;
use Throwable;

use App\Jobs\ProcessEmailJob;
use App\Repositories\ClientRepository;
use App\Services\MicrosoftMailService;
use App\Services\OcrAnalyzeService;

use \App\Classes\CommonClass;

use App\Models\OcrPdf;
use App\Models\OcrPdfSyncDb;
use App\Models\VATRegistration;
use App\Models\VATRegistrationMain;

use App\Jobs\SyncDbFromOcr;
use App\Jobs\OcrIrSyncJob;
use App\Jobs\AutoRefreshNewDeclarationJob;

use App\Helpers\EnvironmentHelper;
use App\Helpers\OcrProgressKeyHelper;

class OcrProcessingService
{
    public $commonClass;
    public $ocrAnalyzeService;
    public $clients;

    public function __construct(        
        protected MicrosoftMailService $mailService,
        protected ClientRepository $clientRepository,
        public ?string $ocrProgressKey = null
    ) {
        $this->commonClass = new CommonClass();

        $this->ocrAnalyzeService = new OcrAnalyzeService();

        $this->clients = $clientRepository->all();
    }

    /*
    public function fetchAndQueueInbox(): array
    {
        // Fetch all unread emails with attachments
        $emails = $this->mailService->getAllInboxEmails();

        // Cache::forget('inbox_completed');
        // Cache::forget('inbox_total');

        //$clients = $this->clientRepository->all();

        $queuedEmails = [];

        foreach ($emails as &$email) {

            $senderEmail =
                $email['sender']['emailAddress']['address'] ?? '';

            $subject =
                $email['subject'] ?? '';

            // Duplicate email
            if (
                stripos($subject, 'second female') !== false &&
                in_array(
                    $senderEmail,
                    config('app.omit_email_list')
                )
            ) {
                $email['remove'] = true;

                $this->mailService->markEmailAsRead($email['id']);

                $this->mailService->moveEmailToFolder(
                    $email['id'],
                    'Duplicate'
                );

                continue;
            }

            // Queue email processing
            ProcessEmailJob::dispatch(
                $this->clients,
                $email['id'],
                $subject,
                $email['replyAttachments'] ?? []
            )->onQueue(
                config('queue.ocr.inbox', 'ocrpdfinvoices')
            );

            // Mark as queued
            $email['attachments'] = [
                'status' => 'queued'
            ];

            $queuedEmails[] = $email;
        }

        return [
            'total' => count($queuedEmails),
            'queued_emails' => $queuedEmails,
        ];
    }
    */    

    public function fetchAndQueueInbox(): array
    {
        // Fetch all unread emails with attachments
        $emails = $this->mailService->getAllInboxEmails();

        /*
         * One progress ID for this entire inbox fetch operation.
         */
        $ocrProgressOperation = 'inbox';

        $ocrProgressId = (string) Str::uuid();

        $ocrProgressKey = OcrProgressKeyHelper::getProgressKey(
            $ocrProgressOperation,
            $ocrProgressId
        );

        $queuedEmails = [];

        /*
         * First identify emails that should actually be queued.
         */
        foreach ($emails as &$email) {

            $senderEmail =
                $email['sender']['emailAddress']['address'] ?? '';

            $subject =
                $email['subject'] ?? '';

            // Duplicate email
            if (
                stripos($subject, 'second female') !== false &&
                in_array(
                    $senderEmail,
                    config('app.omit_email_list')
                )
            ) {
                $email['remove'] = true;

                $this->mailService->markEmailAsRead(
                    $email['id']
                );

                $this->mailService->moveEmailToFolder(
                    $email['id'],
                    'Duplicate'
                );

                continue;
            }

            $queuedEmails[] = $email;
        }

        unset($email);

        /*
         * Total number of emails that will be processed.
         */
        $total = count($queuedEmails);

        /*
         * Initialize progress BEFORE dispatching jobs.
         */
        $ttl = now()->addHour();

        Cache::put(
            "{$ocrProgressKey}:total",
            $total,
            $ttl
        );

        Cache::put(
            "{$ocrProgressKey}:completed",
            0,
            $ttl
        );

        /*
         * Now queue the emails.
         */
        foreach ($queuedEmails as &$email) {

            ProcessEmailJob::dispatch(
                $ocrProgressKey,
                $this->clients,
                $email['id'],
                $email['subject'] ?? '',
                $email['replyAttachments'] ?? []
            )->onQueue(
                config('queue.ocr.inbox', 'ocrpdfinvoices')
            );

            // Mark as queued
            $email['attachments'] = [
                'status' => 'queued'
            ];
        }

        unset($email);

        return [
            'total' => $total,
            'queued_emails' => $queuedEmails,

            // Required if a browser needs to poll this operation
            'ocr_progress_operation' => $ocrProgressOperation,
            'ocr_progress_id' => $ocrProgressId,
        ];
    }


    public function fetchAndQueueSFtp($which_folder = 'main'): array
    {        
        $environment = EnvironmentHelper::getEnvironment();

        $system_ftp = $this->commonClass->getSystemInfoLazy('FTP', 'Production');
        
        $ftp_connection = $system_ftp->systemapi->first();

        $sftp_server = $ftp_connection->api_base_url;
        $sftp_username = $ftp_connection->api_client_id; 
        $sftp_password = $ftp_connection->api_secret_key; 
        // $sftp_foldername = ($efacto) ? 'efacto' : (preg_replace('/[^A-Za-z0-9\-]/', '', 
        //                     preg_replace('/\s+/', '', 
        //                         strtolower(
        //                             iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $client->client_name)
        //                         )
        //                     )
        //                 )); 

        // if($sftp_foldername == 'auboproductionas')      
        //     $sftp_foldername = 'aubo';
        // else if($sftp_foldername == 'becksondergaardaps' || $sftp_foldername == 'becksndergaardaps')        
        //     $sftp_foldername = 'becksondergaard';
        // else if($sftp_foldername == 'asvillyjensenbesaetningsartiklerengros' || $sftp_foldername == 'asvillyjensen')        
        //     $sftp_foldername = 'villyjensen';
        // else if($sftp_foldername == 'dfi-geisleras')
        //     $sftp_foldername = 'dfigeisler';
        // else if($sftp_foldername == 'riekerschuhag')
            $sftp_foldername = 'riekerschuh';

        $driver = Storage::createSFtpDriver([
                'host'     => $sftp_server,
                'username' => $sftp_username,
                'password' => $sftp_password,
                //'port'     => 65002,                         
                'timeout'  => 10,
            ]);
        
        $sftp_path = '/var/sftp/uploads/';      
                
        $sftp_subfoldername = $sftp_foldername;

        if($which_folder == 'main')
            $pdffiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/".$sftp_subfoldername, false));
        else if($which_folder == 'archive')     
            $pdffiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/Archive", false));              
        else if($which_folder == 'both')
        {
            $main_pdffiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/".$sftp_subfoldername, false));

            $archive_pdffiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/Archive", false));

            $pdffiles = $main_pdffiles->merge($archive_pdffiles);
        }

        // $targetDate = Carbon::parse(
        //     '2026-08-24',
        //     config('app.timezone')
        // )->startOfDay();

        // $pdffiles = $pdffiles
        //     ->filter(function ($file) use ($targetDate) {

        //         if (($file['type'] ?? null) !== 'file') {
        //             return false;
        //         }

        //         if (empty($file['lastModified'])) {
        //             return false;
        //         }

        //         $fileDate = Carbon::createFromTimestamp(
        //             $file['lastModified'],
        //             config('app.timezone')
        //         );

        //         return $fileDate->greaterThanOrEqualTo($targetDate);
        //     })
        //     ->values();
     
        // Unique batch ID for this email
        $batchId = (string) Str::uuid();
        $localFiles = [];
        if(count($pdffiles) > 0) 
        {
            /*
             * One progress ID for this entire inbox fetch operation.
             */
            $ocrProgressOperation = 'sftp';

            $ocrProgressId = (string) Str::uuid();

            $ocrProgressKey = OcrProgressKeyHelper::getProgressKey(
                $ocrProgressOperation,
                $ocrProgressId
            );
                        
            foreach ($pdffiles as $sftpFile) {

                $sftpPath = $sftpFile->path();
                $fileName = basename($sftpPath);

                // Save to localhost
                $localPath = 'ocr/sftp/' . $fileName;

                $stored = Storage::disk('local')->put(
                    $localPath,
                    $driver->get($sftpPath)
                );

                if (!$stored) {
                    Log::error("Failed to save SFTP file locally: {$sftpPath}");
                    continue;
                }
                
                $localFiles[] = $localPath;

                // Move ORIGINAL SFTP file to Archive
                if ($environment === 'live') {
                    $newFileName =
                        $sftp_path .
                        $sftp_foldername .
                        '/Archive/' .
                        $fileName;

                    $driver->move(
                        $sftpPath,
                        $newFileName
                    );
                }
            }            

            $this->ocrAnalyzeService->analyze($ocrProgressKey, $this->clients, $localFiles, 'sales', $batchId, null, [], true);

            //For TESTING
            // $localFiles = Storage::disk('local')->files('ocr/sftp');  
            // //$this->ocrAnalyzeService->analyze($ocrProgressKey, $this->clients, [$localFiles[0] ?? null], 'sales', $batchId, null, [], true);   
            // $this->ocrAnalyzeService->analyze($ocrProgressKey, $this->clients, $localFiles, 'sales', $batchId, null, [], true);   
        }

        return [
            // 'total' => $pdffiles->count(),
            // 'files' => $pdffiles->all(),
            'total' => count($localFiles),
            'files' => $localFiles,
            'batch_id' => $batchId,
        ];
    }

    public function syncDbFromOcr($authUser): array
    {
        $connection = DB::connection(
            config('database.ocr_connection')
        );

        $fetchPeriodFrom = '2026-04-01';

        $vatregmains = VATRegistrationMain::select([
                'id',
                'org_no',
                'vat_no',
                'country',
                'client_id',
            ])
            ->where('ocr_sync', 1)
            ->orderBy('id', 'ASC')
            ->get();

        $OrgNo = $vatregmains
            ->flatMap(function ($item) {
                return [
                    $item->org_no,
                    $item->vat_no,
                ];
            })
            ->filter()
            ->map(function ($value) {
                return preg_replace('/[^0-9]/', '', $value);
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        
        if (empty($OrgNo)) {
            return [
                'message' => 'No OCR-enabled VAT registrations found.',
                'totalSync' => 0,
            ];
        }

        /*
         * Recover records that were stuck in processing
         * for more than 1 hour.
         */
        $connection->table('dv_ocr_pdfs')
            ->where('sync_db', 2)
            ->where('sync_started_at', '<', now()->subHour())
            ->update([
                'sync_db' => 0,
                'sync_started_at' => null,
            ]);

        $totalSync = 0;
        $jobs = [];
        do {

            /*
             * Claim up to 100 records.
             */
            $ocrPdfIds = $connection->transaction(function () use (
                $connection,
                $fetchPeriodFrom,
                $OrgNo,
                $authUser
            ) {                
                $placeholders = implode(',', array_fill(0, count($OrgNo), '?'));

                $sql = "
                    SELECT p.id
                    FROM dv_ocr_pdfs p
                    WHERE p.sync_db = 0
                      AND p.is_deleted = 0
                      AND p.status = 'completed'
                      AND JSON_UNQUOTE(
                          JSON_EXTRACT(
                              p.extracted_data,
                              '$.invoice_date'
                          )
                      ) >= ?

                      AND (
                          REGEXP_REPLACE(
                              JSON_UNQUOTE(
                                  JSON_EXTRACT(p.extracted_data, '$.supplier.org_number')
                              ),
                              '[^0-9]',
                              ''
                          ) IN ($placeholders)

                          OR

                          REGEXP_REPLACE(
                              JSON_UNQUOTE(
                                  JSON_EXTRACT(p.extracted_data, '$.supplier.cvr_number')
                              ),
                              '[^0-9]',
                              ''
                          ) IN ($placeholders)

                          OR

                          REGEXP_REPLACE(
                              JSON_UNQUOTE(
                                  JSON_EXTRACT(p.extracted_data, '$.recipient.org_number')
                              ),
                              '[^0-9]',
                              ''
                          ) IN ($placeholders)
                      )

                    ORDER BY p.id ASC
                    LIMIT 100
                    FOR UPDATE SKIP LOCKED
                ";

                $bindings = array_merge(
                    [$fetchPeriodFrom],
                    $OrgNo,
                    $OrgNo,
                    $OrgNo
                );

                $rows = $connection->select($sql, $bindings);

                $ids = collect($rows)
                    ->pluck('id')
                    ->values();

                if ($ids->isNotEmpty()) {

                    $connection->table('dv_ocr_pdfs')
                        ->whereIn('id', $ids)
                        ->update([
                            'sync_db' => 2,
                            'sync_started_at' => now(),
                        ]);
                }

                return $ids;
            });

            /*
             * Nothing left to process.
             */
            if ($ocrPdfIds->isEmpty()) {
                break;
            }

            /*
             * 100 claimed records
             * -> 4 queue jobs x 25 records.
             */
            foreach ($ocrPdfIds->chunk(25) as $chunk) {

                // Bus::dispatch(
                //     (new SyncDbFromOcr(
                //         $chunk->all(),
                //         $authUser
                //     ))->onQueue('ocrpdfsyncdb')
                // );

                $jobs[] = (new SyncDbFromOcr(
                    $chunk->all(),
                    $authUser
                ))->onQueue('ocrpdfsyncdb');
            }

            $totalSync += count($ocrPdfIds);
        } while (true);

        /*
         * Nothing was dispatched.
         */
        if (empty($jobs)) {
            return [
                'message' => 'No OCR sync jobs found.',
                'totalSync' => 0,
            ];
        }

        Log::info(
            'OCR sync jobs successfully',
            [
                'totalSync' => $totalSync
            ]
        );

        /*
         * ALL ocrpdfsyncdb jobs are now in ONE batch.
         *
         * AutoRefreshNewDeclarationJob will run only after
         * ALL SyncDbFromOcr jobs have successfully completed.
         */
        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($authUser) {

                // AutoRefreshNewDeclarationJob::dispatch($authUser)
                //     ->onQueue('ocrpdfsyncinvoices');

                AutoRefreshNewDeclarationJob::dispatch($authUser)
                    ->onQueue('ocrpdfsyncdb');

            })           
            ->catch(function (Batch $batch, Throwable $e) {

                Log::error('OCR DB sync batch failed', [
                    'batch_id' => $batch->id,
                    'error' => $e->getMessage(),
                ]);

            })
            ->name('OCR DB Sync Auto refresh')
            ->dispatch();

        return [
            'message' => 'OCR sync jobs dispatched successfully.',
            'totalSync' => $totalSync
        ];
    }

    public function autoRefreshNewDeclaration($authUser): array
    {
        $totalSync = 0;

        /*
         * =========================================================
         * STEP 1
         *
         * Get OCR PDFs which:
         *
         * - are already synced to Sync DB
         * - have no sync status OR
         *   sync_status = 0 AND is_locked = 0
         * =========================================================
         */

        $syncDbTable = (new OcrPdfSyncDb())->getTable();

        $items = OcrPdf::query()
            ->select('id')
            ->with([
                'syncDb:ocr_pdf_id,client_no,client_name,invoice_date,invoice_type,invoice_no,related_sales_invoices',
                'syncStatus:ocr_pdf_id,environment,sync_status,is_locked',
            ])
            ->where('sync_db', 1)
            // ->whereHas('syncDb', function ($query) {
            //     $query->where('client_no', '928729605');
            // })
            ->whereHas('syncDb')
            ->where(function ($query) {
                $query
                    ->whereDoesntHave('syncStatus')
                    ->orWhereHas('syncStatus', function ($query) {
                        $query
                            ->where('sync_status', 0)
                            ->where('is_locked', 0);
                    });
            })
            ->orderBy(
                OcrPdfSyncDb::query()
                    ->select('client_name')
                    ->whereColumn(
                        "{$syncDbTable}.ocr_pdf_id",
                        'dv_ocr_pdfs.id'
                    )
                    ->limit(1)
            )
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return [
                'message' => 'No pending OCR records found for refresh.',
                'totalSync' => 0,
            ];
        }


        /*
         * =========================================================
         * STEP 2
         *
         * Normalize client numbers.
         * =========================================================
         */

        // $clientNos = $items
        //     ->map(function ($item) {
        //         return preg_replace(
        //             '/\D+/',
        //             '',
        //             $item->syncDb?->client_no ?? ''
        //         );
        //     })
        //     ->filter()
        //     ->unique()
        //     ->values();

        // if ($clientNos->isEmpty()) {
        //     return [
        //         'message' => 'No valid client numbers found.',
        //         'totalSync' => 0,
        //     ];
        // }


        /*
         * =========================================================
         * STEP 3
         *
         * Load VAT registrations once.
         * =========================================================
         */

        $vatregs = VATRegistration::query()
            ->select([
                'id',
                'vat_reg_main_id',
                'service_start',
                'general_periods',
                'country',
            ])
            ->with([
                'vatregmain:id,org_no,vat_no,country,ocr_sync,product_type,general_periods',
            ])
            ->whereHas('vatregmain', function ($query) {
                $query
                    ->where('ocr_sync', 1)
                    ->whereIn('product_type', [2, 3, 5]);
            })
            ->get();


        /*
         * client_no => [VATRegistration, ...]
         */
        $vatregsByClientNo = [];

        foreach ($vatregs as $vatreg) {

            $vatregmain = $vatreg->vatregmain;

            if (!$vatregmain) {
                continue;
            }

            $orgNo = preg_replace(
                '/\D+/',
                '',
                $vatregmain->org_no ?? ''
            );

            $vatNo = preg_replace(
                '/\D+/',
                '',
                $vatregmain->vat_no ?? ''
            );

            if ($orgNo !== '') {
                $vatregsByClientNo[$orgNo][] = $vatreg;
            }

            if ($vatNo !== '' && $vatNo !== $orgNo) {
                $vatregsByClientNo[$vatNo][] = $vatreg;
            }
        }


        /*
         * =========================================================
         * STEP 4
         *
         * Get the Sync DB records for the pending OCR PDFs.
         * =========================================================
         */

        $ocrPdfIds = $items
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $syncRecords = OcrPdfSyncDb::query()
            ->select([
                'id',
                'ocr_pdf_id',
                'client_no',
                'client_name',
                'invoice_date',
                'invoice_type',
                'invoice_no',
                'related_sales_invoices',
            ])
            ->whereIn('ocr_pdf_id', $ocrPdfIds)
            ->get();

        /*
         * =========================================================
         * STEP 5
         *
         * Build a SALES invoice number => COM invoice ID map.
         *
         * IMPORTANT:
         *
         * We need ALL COM records referenced by the pending sales
         * invoices, even when the COM PDF itself is not pending.
         * =========================================================
         */

        $salesInvoiceNos = $syncRecords
            ->where('invoice_type', 'sales')
            ->pluck('invoice_no')
            ->filter()
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values();

        /*
         * Find COM invoices containing these sales invoice numbers.
         */
        $comInvoices = collect();

        if ($salesInvoiceNos->isNotEmpty()) {

            $comInvoices = OcrPdfSyncDb::query()
                ->select([
                    'id',
                    'ocr_pdf_id',
                    'client_no',
                    'client_name',
                    'invoice_date',
                    'invoice_type',
                    'invoice_no',
                    'related_sales_invoices',
                ])
                ->where('invoice_type', 'com')
                ->get()
                ->filter(function ($comInvoice) use ($salesInvoiceNos) {

                    $relatedSalesInvoices =
                        $comInvoice->related_sales_invoices ?? [];

                    if (!is_array($relatedSalesInvoices)) {
                        $relatedSalesInvoices = [
                            $relatedSalesInvoices
                        ];
                    }

                    $relatedSalesInvoices = array_map(
                        'strval',
                        $relatedSalesInvoices
                    );

                    return $salesInvoiceNos
                        ->intersect($relatedSalesInvoices)
                        ->isNotEmpty();
                })
                ->values();
        }

        /*
         * Also include COM records which are themselves pending.
         */
        $pendingComInvoices = $syncRecords
            ->where('invoice_type', 'com')
            ->values();

        /*
         * Merge and remove duplicate COM invoices.
         */
        $allComInvoices = $pendingComInvoices
            ->merge($comInvoices)
            //->unique('id')
            ->unique('ocr_pdf_id')
            ->values();

        /*
         * =========================================================
         * STEP 6
         *
         * Build COM groups.
         *
         * ONE COM = ONE group = ONE dispatched job.
         * =========================================================
         */

        $comGroups = [];

        foreach ($allComInvoices as $comInvoice) {

            $comGroups[$comInvoice->id] = [
                'com_invoice_id' => $comInvoice->id,
                'ocr_pdf_id' => $comInvoice->ocr_pdf_id,
                'client_no' => $comInvoice->client_no,
                'invoice_date' => $comInvoice->invoice_date,
            ];
        }


        /*
         * =========================================================
         * STEP 7
         *
         * Match each COM group to VAT registration.
         * =========================================================
         */

        foreach ($comGroups as $comGroup) {

            $comInvoiceId = $comGroup['com_invoice_id'];
            $ocrPdfId = $comGroup['ocr_pdf_id'];

            $clientNo = preg_replace(
                '/\D+/',
                '',
                $comGroup['client_no'] ?? ''
            );

            $invoiceDateValue =
                $comGroup['invoice_date'] ?? null;


            /*
             * Validate client number and invoice date.
             */
            if (!$clientNo || !$invoiceDateValue) {

                // Log::warning(
                //     'Unable to match COM invoice to VAT registration',
                //     [
                //         'com_invoice_id' => $comInvoiceId,
                //         'ocr_pdf_id' => $ocrPdfId,
                //         'client_no' => $clientNo,
                //         'invoice_date' => $invoiceDateValue,
                //     ]
                // );

                continue;
            }


            try {

                $invoiceDate = Carbon::parse(
                    $invoiceDateValue
                );

            } catch (\Throwable $e) {

                // Log::warning(
                //     'Invalid COM invoice date',
                //     [
                //         'com_invoice_id' => $comInvoiceId,
                //         'ocr_pdf_id' => $ocrPdfId,
                //         'invoice_date' => $invoiceDateValue,
                //     ]
                // );

                continue;
            }


            /*
             * ---------------------------------------------------------
             * Find VAT registrations for this client.
             * ---------------------------------------------------------
             */

            $possibleVatRegs =
                $vatregsByClientNo[$clientNo] ?? [];

            if (empty($possibleVatRegs)) {
                continue;
            }


            /*
             * ---------------------------------------------------------
             * Find VAT registration whose service period contains
             * the COM invoice date.
             * ---------------------------------------------------------
             */

            $matchedVatReg = null;

            foreach ($possibleVatRegs as $vatreg) {

                $vatregmain = $vatreg->vatregmain;

                if (
                    !$vatreg->service_start ||
                    !$vatregmain
                ) {
                    continue;
                }

                $frequency =
                    $this->commonClass->getFrequency(
                        $vatregmain->general_periods
                    );

                if (!$frequency || $frequency < 1) {
                    continue;
                }

                try {

                    $serviceStart = Carbon::parse(
                        $vatreg->service_start
                    );

                    $serviceEnd = $serviceStart
                        ->copy()
                        ->addMonths($frequency - 1)
                        ->endOfMonth();

                } catch (\Throwable $e) {
                    continue;
                }

                if (
                    $invoiceDate->between(
                        $serviceStart,
                        $serviceEnd
                    )
                ) {
                    $matchedVatReg = $vatreg;
                    break;
                }
            }


            if (!$matchedVatReg) {
                continue;
            }


            /*
             * =========================================================
             * STEP 8
             *
             * Dispatch ONE job per COM.
             *
             * We pass the COM OCR PDF ID.
             * =========================================================
             */

            try {

                $start = microtime(true);

                $result =
                    $this->commonClass
                        ->loadImportReconciliationDatasFromNewOcr(
                            $authUser,
                            $matchedVatReg,
                            'ocr-auto-refresh',
                            null,
                            $ocrPdfId
                        );

                $durationMs = round(
                    (microtime(true) - $start) * 1000,
                    2
                );

                Log::info(
                    'Auto refresh dispatch timing',
                    [
                        'com_invoice_id' => $comInvoiceId,
                        'ocr_pdf_id' => $ocrPdfId,
                        'result' => $result,
                        'duration_ms' => $durationMs,
                    ]
                );


                /*
                 * Only count an actual dispatched job.
                 */
                if ($result > 0) {
                    $totalSync++;
                }

            } catch (\Throwable $e) {

                Log::error(
                    'Failed to refresh OCR reconciliation data',
                    [
                        'com_invoice_id' => $comInvoiceId,
                        'ocr_pdf_id' => $ocrPdfId,
                        'client_no' => $clientNo,
                        'vat_reg_id' => $matchedVatReg->id,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }


        return [
            'message' =>
                'OCR reconciliation data refreshed successfully.',
            'totalSync' => $totalSync,
        ];
    }

    
//     public function autoRefreshNewDeclaration($authUser): array
//     {
//         $totalSync = 0;

//         /*
//          * =========================================================
//          * STEP 1
//          *
//          * Get OCR PDFs which:
//          *
//          * - are already synced to Sync DB
//          * - have no sync status OR
//          *   sync_status = 0 AND is_locked = 0
//          * =========================================================
//          */

//         $syncDbTable = (new OcrPdfSyncDb())->getTable();

//         $items = OcrPdf::query()
//             ->select('id')
//             ->with([
//                 'syncDb:ocr_pdf_id,client_no,client_name,invoice_date,invoice_type,invoice_no,related_sales_invoices',
//                 'syncStatus:ocr_pdf_id,environment,sync_status,is_locked',
//             ])
//             ->where('sync_db', 1)
//             ->whereHas('syncDb')
//             ->where(function ($query) {
//                 $query
//                     ->whereDoesntHave('syncStatus')
//                     ->orWhereHas('syncStatus', function ($query) {
//                         $query
//                             ->where('sync_status', 0)
//                             ->where('is_locked', 0);
//                     });
//             })
//             ->orderBy(
//                 OcrPdfSyncDb::query()
//                     ->select('client_name')
//                     ->whereColumn(
//                         "{$syncDbTable}.ocr_pdf_id",
//                         'dv_ocr_pdfs.id'
//                     )
//                     ->limit(1)
//             )
//             ->orderBy('id')
//             ->get();

//         if ($items->isEmpty()) {
//             return [
//                 'message' => 'No pending OCR records found for refresh.',
//                 'totalSync' => 0,
//             ];
//         }


//         /*
//          * =========================================================
//          * STEP 2
//          *
//          * Normalize client numbers.
//          * =========================================================
//          */

//         $clientNos = $items
//             ->map(function ($item) {
//                 return preg_replace(
//                     '/\D+/',
//                     '',
//                     $item->syncDb?->client_no ?? ''
//                 );
//             })
//             ->filter()
//             ->unique()
//             ->values();

//         if ($clientNos->isEmpty()) {
//             return [
//                 'message' => 'No valid client numbers found.',
//                 'totalSync' => 0,
//             ];
//         }


//         /*
//          * =========================================================
//          * STEP 3
//          *
//          * Load VAT registrations once.
//          * =========================================================
//          */

//         $vatregs = VATRegistration::query()
//             ->select([
//                 'id',
//                 'vat_reg_main_id',
//                 'service_start',
//                 'general_periods',
//                 'country',
//             ])
//             ->with([
//                 'vatregmain:id,org_no,vat_no,country,ocr_sync,product_type,general_periods',
//             ])
//             ->whereHas('vatregmain', function ($query) {
//                 $query
//                     ->where('ocr_sync', 1)
//                     ->whereIn('product_type', [2, 3, 5]);
//             })
//             ->get();


//         /*
//          * client_no => [VATRegistration, ...]
//          */
//         $vatregsByClientNo = [];

//         foreach ($vatregs as $vatreg) {

//             $vatregmain = $vatreg->vatregmain;

//             if (!$vatregmain) {
//                 continue;
//             }

//             $orgNo = preg_replace(
//                 '/\D+/',
//                 '',
//                 $vatregmain->org_no ?? ''
//             );

//             $vatNo = preg_replace(
//                 '/\D+/',
//                 '',
//                 $vatregmain->vat_no ?? ''
//             );

//             if ($orgNo !== '') {
//                 $vatregsByClientNo[$orgNo][] = $vatreg;
//             }

//             if ($vatNo !== '' && $vatNo !== $orgNo) {
//                 $vatregsByClientNo[$vatNo][] = $vatreg;
//             }
//         }


//         /*
//          * =========================================================
//          * STEP 4
//          *
//          * IMPORTANT:
//          *
//          * Resolve COM groups before dispatching.
//          *
//          * This prevents:
//          *
//          * COM 7635
//          *   -> Sales PDF 1
//          *   -> Sales PDF 2
//          *   -> Sales PDF 3
//          *
//          * from dispatching COM 7635 three times.
//          * =========================================================
//          */

//         $syncRecords = $items
//             ->pluck('syncDb')
//             ->filter();

//         /*
//          * Build:
//          *
//          * sales invoice number => COM invoice
//          */
//         $comBySalesInvoiceNo = [];

//         /*
//          * COM invoice ID => first OCR PDF ID
//          *
//          * This is what we ultimately use for dispatching.
//          */
//         $comGroups = [];


//         foreach ($syncRecords as $syncRecord) {

//             /*
//              * ---------------------------------------------------------
//              * COM invoice
//              * ---------------------------------------------------------
//              */
//             if ($syncRecord->invoice_type === 'com') {

//                 $comGroups[$syncRecord->id] = [
//                     'com_invoice_id' => $syncRecord->id,
//                     'ocr_pdf_id' => $syncRecord->ocr_pdf_id,
//                 ];

//                 foreach (
//                     ($syncRecord->related_sales_invoices ?? [])
//                     as $salesInvoiceNo
//                 ) {

//                     $salesInvoiceNo = (string) $salesInvoiceNo;

//                     if ($salesInvoiceNo !== '') {
//                         $comBySalesInvoiceNo[$salesInvoiceNo] =
//                             $syncRecord;
//                     }
//                 }
//             }
//         }


//         /*
//          * ---------------------------------------------------------
//          * Add SALES OCR PDFs to their COM group.
//          * ---------------------------------------------------------
//          */

//         foreach ($syncRecords as $syncRecord) {

//             if ($syncRecord->invoice_type !== 'sales') {
//                 continue;
//             }

//             $salesInvoiceNo = (string) $syncRecord->invoice_no;

//             $commInvoice =
//                 $comBySalesInvoiceNo[$salesInvoiceNo] ?? null;

//             if (!$commInvoice) {
//                 continue;
//             }

//             /*
//              * Already grouped by COM ID.
//              *
//              * Therefore multiple SALES PDFs belonging to the same
//              * COM will result in only ONE refresh.
//              */
//             if (!isset($comGroups[$commInvoice->id])) {
//                 $comGroups[$commInvoice->id] = [
//                     'com_invoice_id' => $commInvoice->id,
//                     'ocr_pdf_id' => $commInvoice->ocr_pdf_id,
//                 ];
//             }
//         }

// dd($syncRecords, $comGroups);
//         /*
//          * =========================================================
//          * STEP 5
//          *
//          * Match each COM group to a VAT registration.
//          *
//          * We only process each COM once.
//          * =========================================================
//          */

//         foreach ($comGroups as $comGroup) {

//             $comInvoiceId = $comGroup['com_invoice_id'];
//             $ocrPdfId = $comGroup['ocr_pdf_id'];

//             /*
//              * Find the original OCR item belonging to this COM.
//              */
//             $item = $items->first(function ($item) use ($ocrPdfId) {
//                 return $item->id == $ocrPdfId;
//             });

//             if (!$item) {
//                 continue;
//             }

//             $clientNo = preg_replace(
//                 '/\D+/',
//                 '',
//                 $item->syncDb?->client_no ?? ''
//             );

//             $invoiceDateValue =
//                 $item->syncDb?->invoice_date;

//             if (!$clientNo || !$invoiceDateValue) {

//                 Log::warning(
//                     'Unable to match COM invoice to VAT registration',
//                     [
//                         'com_invoice_id' => $comInvoiceId,
//                         'ocr_pdf_id' => $ocrPdfId,
//                         'client_no' => $clientNo,
//                         'invoice_date' => $invoiceDateValue,
//                     ]
//                 );

//                 continue;
//             }

//             try {

//                 $invoiceDate = Carbon::parse(
//                     $invoiceDateValue
//                 );

//             } catch (\Throwable $e) {

//                 Log::warning(
//                     'Invalid OCR invoice date',
//                     [
//                         'com_invoice_id' => $comInvoiceId,
//                         'ocr_pdf_id' => $ocrPdfId,
//                         'invoice_date' => $invoiceDateValue,
//                     ]
//                 );

//                 continue;
//             }


//             /*
//              * ---------------------------------------------------------
//              * Find VAT registrations for this client.
//              * ---------------------------------------------------------
//              */

//             $possibleVatRegs =
//                 $vatregsByClientNo[$clientNo] ?? [];

//             if (empty($possibleVatRegs)) {
//                 continue;
//             }


//             /*
//              * ---------------------------------------------------------
//              * Find VAT registration whose service period contains
//              * the invoice date.
//              * ---------------------------------------------------------
//              */

//             $matchedVatReg = null;

//             foreach ($possibleVatRegs as $vatreg) {

//                 $vatregmain = $vatreg->vatregmain;

//                 if (
//                     !$vatreg->service_start ||
//                     !$vatregmain
//                 ) {
//                     continue;
//                 }

//                 $frequency =
//                     $this->commonClass->getFrequency(
//                         $vatregmain->general_periods
//                     );

//                 if (!$frequency || $frequency < 1) {
//                     continue;
//                 }

//                 try {

//                     $serviceStart = Carbon::parse(
//                         $vatreg->service_start
//                     );

//                     $serviceEnd = $serviceStart
//                         ->copy()
//                         ->addMonths($frequency - 1)
//                         ->endOfMonth();

//                 } catch (\Throwable $e) {
//                     continue;
//                 }

//                 if (
//                     $invoiceDate->between(
//                         $serviceStart,
//                         $serviceEnd
//                     )
//                 ) {
//                     $matchedVatReg = $vatreg;
//                     break;
//                 }
//             }


//             if (!$matchedVatReg) {
//                 continue;
//             }


//             /*
//              * =========================================================
//              * STEP 6
//              *
//              * Dispatch ONE job for ONE COM group.
//              * =========================================================
//              */

//             try {

//                 $start = microtime(true);

//                 $result =
//                     $this->commonClass
//                         ->loadImportReconciliationDatasFromNewOcr(
//                             $authUser,
//                             $matchedVatReg,
//                             'ocr-auto-refresh',
//                             null,
//                             $ocrPdfId
//                         );

//                 $durationMs = round(
//                     (microtime(true) - $start) * 1000,
//                     2
//                 );

//                 Log::info(
//                     'Auto refresh dispatch timing',
//                     [
//                         'com_invoice_id' => $comInvoiceId,
//                         'ocr_pdf_id' => $ocrPdfId,
//                         'result' => $result,
//                         'duration_ms' => $durationMs,
//                     ]
//                 );

//                 /*
//                  * Only count an actual dispatched job.
//                  */
//                 if ($result > 0) {
//                     $totalSync++;
//                 }

//             } catch (\Throwable $e) {

//                 Log::error(
//                     'Failed to refresh OCR reconciliation data',
//                     [
//                         'com_invoice_id' => $comInvoiceId,
//                         'ocr_pdf_id' => $ocrPdfId,
//                         'client_no' => $clientNo,
//                         'vat_reg_id' => $matchedVatReg->id,
//                         'error' => $e->getMessage(),
//                     ]
//                 );
//             }
//         }


//         return [
//             'message' =>
//                 'OCR reconciliation data refreshed successfully.',
//             'totalSync' => $totalSync,
//         ];
//     }

}