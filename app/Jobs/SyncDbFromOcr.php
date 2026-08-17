<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

// use App\Models\Client;
// use App\Models\ImportReconciliationFiles;
// use App\Models\ImportReconciliationComInvoices;
// use App\Models\ImportReconciliationSalesInvoices;
// use App\Models\ImportReconciliationSalesInvoicesData;
// use App\Models\Invoices;
// use App\Models\VATReturns;
use App\Models\VATRegistration;
use App\Models\OcrPdf;
use App\Models\OcrPdfSyncDb;

use \App\Classes\CommonClass;

//use App\Events\ImportReconciliationComSalesInvoicesEvent;
//use App\Events\OcrInvoicesSyncEvent;

use App\Helpers\EnvironmentHelper;

class SyncDbFromOcr implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $invoice_ids;
    //protected $allvatregmains;    
    protected $authUser;
    //protected $from;
    
    protected $commonClass;   

    /**
     * Create a new job instance.
     *
     * @return void
     */   
    public function __construct($invoice_ids, $authUser)
    {                  
      $this->invoice_ids = $invoice_ids;
      //$this->allvatregmains = $allvatregmains;    
      $this->authUser = $authUser;     
      //$this->from = $from;      
      
      $this->commonClass = new CommonClass();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $environment = EnvironmentHelper::getEnvironment();

        $ocrConnection = DB::connection(
            config('database.ocr_connection')
        );

        /*
         * Get VAT registrations.
         */
        // $vatregmains = $this->commonClass->getLazy(
        //     'vatregmain',
        //     ['client'],
        //     [],
        //     [],
        //     ['id' => 'DESC'],
        //     'get'
        // );        
        $vatregs = VATRegistration::select([
                    'id',
                    'vat_reg_main_id',
                    'service_start',
                ])
                ->with([
                    'vatregmain' => function ($query) {
                        $query->select([
                            'id',
                            'general_periods',
                            'country',
                            'org_no',
                            'vat_no',
                        ]);
                    },
                ])
                ->get()
                ->filter(function ($vatreg) {

                    if (!$vatreg->vatregmain) {
                        return false;
                    }

                    $frequency = $this->commonClass->getFrequency(
                        $vatreg->vatregmain->general_periods
                    );

                    if (!$frequency || $frequency < 1) {
                        return false;
                    }

                    $serviceStart = Carbon::parse(
                        $vatreg->service_start
                    );

                    $serviceEnd = $serviceStart
                        ->copy()
                        ->addMonths($frequency - 1)
                        ->endOfMonth();

                    if ($vatreg->vatregmain->country === 'CH') {
                        $fetchPeriodFrom = '20260401';
                    } else {
                        $fetchPeriodFrom = '20260601';
                    }

                    // $matched =
                    //     $fetchPeriodFrom >= $serviceStart->format('Ymd')
                    //     &&
                    //     $fetchPeriodFrom <= $serviceEnd->format('Ymd');

                    $matched = $serviceEnd->format('Ymd') >= $fetchPeriodFrom;

                    // Log::info([
                    //     'vatreg' => $vatreg->id,
                    //     'vat_reg_main_id' => $vatreg->vat_reg_main_id,
                    //     'country' => $vatreg->vatregmain->country,
                    //     'frequency' => $frequency,
                    //     'fetchPeriodFrom' => $fetchPeriodFrom,
                    //     'serviceStart' => $serviceStart->format('Ymd'),
                    //     'serviceEnd' => $serviceEnd->format('Ymd'),
                    //     'matched' => $matched,
                    // ]);

                    return $matched;
                });     

        // Log::info(
        //     "Matched vatregs",
        //     [
        //         'vatregs' => $vatregs
        //     ]
        // );

        /*
         * Fetch only invoices that are currently being processed.
         *
         * This is important when Laravel retries the queue job.
         * Records already changed to sync_db = 1 or 3 are skipped.
         */
        $invoices = $ocrConnection
            ->table('dv_ocr_pdfs')
            ->whereIn('id', $this->invoice_ids)
            ->where('sync_db', 2)
            ->select(
                'id',
                'invoice_type',
                'extracted_data'
            )
            ->get()
            ->keyBy('id');

        /*
         * Parse OCR data once per invoice.
         */
        $parsedCache = [];

        foreach ($invoices as $invoice) {

            $parsedCache[$invoice->id] = [
                'type' => $invoice->invoice_type === 'com'
                    ? 'com'
                    : 'sales',

                'manual_note' => $invoice->manual_note ?? null,
                'search_save_note' => $invoice->search_save_note ?? null,

                'extracted_data' => is_array($invoice->extracted_data)
                    ? $invoice->extracted_data
                    : json_decode(
                        $invoice->extracted_data ?? '[]',
                        true
                    ),
            ];
        }

        /*
         * Process each invoice independently.
         *
         * One failed invoice does not roll back other invoices.
         */
        foreach ($this->invoice_ids as $invoiceId) {

            /*
             * Skip invoices already processed or no longer in state 2.
             */
            if (!isset($parsedCache[$invoiceId])) {
                continue;
            }

            try {

                $ocrConnection->transaction(function () use (
                    $ocrConnection,
                    $invoiceId,
                    $parsedCache,
                    $vatregs,
                    $environment
                ) {

                    /*
                     * Make sure this invoice is still being processed.
                     */
                    $invoice = $ocrConnection
                        ->table('dv_ocr_pdfs')
                        ->where('id', $invoiceId)
                        ->where('sync_db', 2)
                        ->select(
                            'id',
                            'invoice_type',
                            'extracted_data'
                        )
                        ->first();

                    if (!$invoice) {
                        return;
                    }

                    $parsedCacheData = $parsedCache[$invoiceId] ?? null;

                    if (!$parsedCacheData) {

                        Log::error(
                            "OCR data not found for invoice {$invoiceId}"
                        );

                        /*
                         * Permanent failure.
                         */
                        $ocrConnection
                            ->table('dv_ocr_pdfs')
                            ->where('id', $invoiceId)
                            ->update([
                                'sync_db' => 3,
                                'sync_db_remarks' => "OCR data not found for invoice {$invoiceId}",
                                'sync_started_at' => null,
                            ]);

                        return;
                    }

                    /*
                     * Process OCR data.
                     */
                    $processed = $this->processInvoiceData(
                        $parsedCacheData
                    );

                    /*
                     * Validate invoice date.
                     */
                    try {

                        $matchInvoiceDate = Carbon::parse(
                            $processed['invoice_date']
                        )->format('Ymd');

                    } catch (\Throwable $e) {

                        Log::error(
                            "Bad OCR invoice date: {$invoiceId}",
                            [
                                'invoice_date' =>
                                    $processed['invoice_date'] ?? null,
                                'error' => $e->getMessage(),
                            ]
                        );

                        /*
                         * Permanent OCR/data failure.
                         */
                        $ocrConnection
                            ->table('dv_ocr_pdfs')
                            ->where('id', $invoiceId)
                            ->update([
                                'sync_db' => 3,
                                'sync_db_remarks' => "Bad OCR invoice date: {$invoiceId}",
                                'sync_started_at' => null,
                            ]);

                        return;
                    }

                    /*
                     * Normalize client/org number.
                     */
                    $orgNo = $processed['client_no'];

                    /*
                     * Find matching VAT registration.
                     */
                    $matchedVatReg = $vatregs
                        ->filter(function ($vatreg) use (
                            $invoiceId,
                            $orgNo,
                            $matchInvoiceDate
                        ) {

                            $frequency = $this->commonClass->getFrequency(
                                $vatreg->vatregmain->general_periods
                            );

                            if (!$frequency || $frequency < 1) {
                                return false;
                            }

                            $vatOrgNo = preg_replace(
                                '/\D+/',
                                '',
                                $vatreg->vatregmain->org_no ?? ''
                            );

                            $vatNo = preg_replace(
                                '/\D+/',
                                '',
                                $vatreg->vatregmain->vat_no ?? ''
                            );

                            $orgMatch =
                                $orgNo &&
                                (
                                    ($vatOrgNo && $vatOrgNo === $orgNo) ||
                                    ($vatNo && $vatNo === $orgNo)
                                );

                            if (!$orgMatch) {
                                return false;
                            }

                            if (!$vatreg->service_start) {
                                return false;
                            }

                            $serviceStart = Carbon::parse(
                                $vatreg->service_start
                            );

                            $serviceEnd = $serviceStart
                                ->copy()
                                ->addMonths($frequency - 1)
                                ->endOfMonth();

                            return
                                $matchInvoiceDate >=
                                    $serviceStart->format('Ymd')
                                &&
                                $matchInvoiceDate <=
                                    $serviceEnd->format('Ymd');
                        })
                        ->first();

                        // Log::info(
                        //     'VAT registration matched OCR invoice',
                        //     [
                        //         'ocr_pdf_id' => $invoiceId,
                        //         'matchedVatRegId' => $matchedVatReg->id,
                        //         'country' =>
                        //             $matchedVatReg->vatregmain->country,
                        //         'service_start' =>
                        //             $matchedVatReg->service_start,
                        //     ]
                        // );
                    /*
                     * No VAT registration matched.
                     */
                    if (!$matchedVatReg) {

                        Log::warning(
                            'No VAT registration matched OCR invoice',
                            [
                                'ocr_pdf_id' => $invoiceId,
                                'client_no' => $orgNo,
                            ]
                        );

                        /*
                         * Permanent/business failure.
                         */
                        $ocrConnection
                            ->table('dv_ocr_pdfs')
                            ->where('id', $invoiceId)
                            ->update([
                                'sync_db' => 3,
                                'sync_db_remarks' => "No VAT registration matched OCR invoice: {$invoiceId}",
                                'sync_started_at' => null,
                            ]);

                        return;
                    }

                    /*
                     * Determine applicable fetch period.
                     */
                    $fetchPeriodFrom = null;

                    $frequency = $this->commonClass->getFrequency(
                        $matchedVatReg->vatregmain->general_periods
                    );

                    if (!$frequency || $frequency < 1) {
                        return false;
                    }

                    $serviceStart = Carbon::parse(
                        $matchedVatReg->service_start
                    );

                    $serviceEnd = $serviceStart
                        ->copy()
                        ->addMonths($frequency - 1)
                        ->endOfMonth();

                    if ($matchedVatReg->vatregmain->country === 'CH') {

                        if ($serviceEnd->format('Ymd') >= '20260401') {
                            $fetchPeriodFrom = '2026-04-01';
                        }

                    } else {

                        if ($serviceEnd->format('Ymd') >= '20260601') {
                            $fetchPeriodFrom = '2026-06-01';
                        }
                    }

                    /*
                     * No applicable period.
                     */
                    if (!$fetchPeriodFrom) {

                        Log::warning(
                            "No applicable fetch period for OCR invoice {$invoiceId}",
                            [
                                'country' =>
                                    $matchedVatReg->vatregmain->country,
                                'service_start' =>
                                    $matchedVatReg->service_start,
                            ]
                        );

                        $ocrConnection
                            ->table('dv_ocr_pdfs')
                            ->where('id', $invoiceId)
                            ->update([
                                'sync_db' => 3,
                                'sync_db_remarks' => "No applicable fetch period for OCR invoice {$invoiceId}",
                                'sync_started_at' => null,
                            ]);

                        return;
                    }

                    /*
                     * Invoice date must be within fetch period.
                     */
                    if ($processed['invoice_date'] < $fetchPeriodFrom) {

                        Log::warning(
                            'Invoice is before applicable fetch period',
                            [
                                'ocr_pdf_id' => $invoiceId,
                                'invoice_date' =>
                                    $processed['invoice_date'],
                                'fetch_period_from' =>
                                    $fetchPeriodFrom,
                            ]
                        );

                        $ocrConnection
                            ->table('dv_ocr_pdfs')
                            ->where('id', $invoiceId)
                            ->update([
                                'sync_db' => 3,
                                'sync_db_remarks' => "Invoice is before applicable fetch period: {$invoiceId}",
                                'sync_started_at' => null,
                            ]);

                        return;
                    }

                    /*
                     * Currency.
                     */
                    $matchCurrency = $processed['currency'] ?? null;

                    if (!$matchCurrency) {

                        $countryMap = [
                            'DK' => 'DKK',
                            'NO' => 'NOK',
                            'SE' => 'SEK',
                            'GB' => 'GBP',
                            'IN' => 'INR',
                            'FR' => 'EUR',
                            'CH' => 'CHF',
                        ];

                        $matchCurrency =
                            $countryMap[
                                $matchedVatregmain->country
                            ] ?? '-';
                    }

                    /*
                     * Prepare sync DB data.
                     */
                    $invoiceType = $processed['invoice_type'];

                    $updateFields = [
                        'invoice_type' => $invoiceType,

                        'client_no' => $processed['client_no'],
                        'client_name' => $processed['client_name'],

                        'invoice_no' => $processed['invoice_no'],
                        'invoice_date' => $processed['invoice_date'],

                        'currency' => $matchCurrency,
                        'net_amount' => $processed['net_amount'],

                        'exchange_currency' =>
                            $processed['exchange_currency'] ?? null,

                        'exchange_net_amount' =>
                            $processed['exchange_net_amount'] ?? null,

                        'note' =>
                            $processed['note'] ?? null,

                        'created_by_environment' => $environment,
                        'created_by' => $this->authUser->id,

                        'updated_by' => $this->authUser->id,
                        'updated_by_environment' => $environment,
                    ];

                    if ($invoiceType === 'sales') {       
            
                        $updateFields = array_merge(
                            $updateFields,
                            [
                                'credit_note' =>
                                    $processed['credit_note'] ?? false,

                                'vat_rate' =>
                                    $processed['vat_rate'] ?? null,

                                'vat_amount' =>
                                    $processed['vat_amount'] ?? null,

                                'total_amount' =>
                                    $processed['total_amount'] ?? null,

                                'calc_net_amount' =>
                                    $processed['calc_net_amount'] ?? null,

                                'additional_amount' =>
                                    $processed['additional_amount'] ?? null,

                                'variance' =>
                                    $processed['variance_amount'] ?? null,

                                'adjustment_amount' =>
                                    $processed['adjustment_amount'] ?? null,

                                'exchange_rate' =>
                                    $processed['exchange_rate'] ?? null,

                                'exchange_vat_amount' =>
                                    $processed['exchange_vat_amount'] ?? null,

                                'exchange_total_amount' =>
                                    $processed['exchange_total_amount'] ?? null,
                            ]
                        );

                    } else {

                        $updateFields = array_merge(
                            $updateFields,
                            [
                                'related_sales_invoices' =>
                                    $processed['related_sales_invoices'] ?? [],
                            ]
                        );
                    }

                    /*
                     * STEP 1
                     *
                     * Insert/update sync DB.
                     *
                     * ocr_pdf_id is UNIQUE, so this is idempotent.
                     */
                    OcrPdfSyncDb::updateOrCreate(
                        [
                            'ocr_pdf_id' => $invoiceId,
                        ],
                        $updateFields
                    );

                    /*
                     * STEP 2
                     *
                     * Only after sync DB succeeds:
                     *
                     * 2 = processing
                     * 1 = successfully synced
                     */
                    $ocrConnection
                        ->table('dv_ocr_pdfs')
                        ->where('id', $invoiceId)
                        ->where('sync_db', 2)
                        ->update([
                            'sync_db' => 1,                            
                            'sync_started_at' => null,
                        ]);
                });

            } catch (\Throwable $e) {

                /*
                 * Real technical/database exception.
                 *
                 * Transaction has already rolled back.
                 *
                 * Make this invoice available for another attempt.
                 */
                $ocrConnection
                    ->table('dv_ocr_pdfs')
                    ->where('id', $invoiceId)
                    ->where('sync_db', 2)
                    ->update([
                        'sync_db' => 0,
                        'sync_db_remarks' => "Error in Invoice processing: {$invoiceId}",
                        'sync_started_at' => null,
                    ]);

                Log::error(
                    "Failed to sync OCR PDF {$invoiceId}",
                    [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]
                );

                /*
                 * Allow Laravel queue to retry the job.
                 */
                throw $e;
            }
        }
    }
   
    public function failed(\Throwable $exception)
    {
        Log::error('Sync DB Job failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
   
    public function parseAmountValue(string $amount, ?string $currency_code = null, bool $isExchangeRate = false): float
    {
        if (empty($amount)) return 0.0;

        $sanitized = str_replace(['−', ' '], ['-', ''], $amount);

        // Case 1: US format (1,234.56)
        if (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $sanitized)) {
            $sanitized = str_replace(',', '', $sanitized);
        }

        // Case 2: EU format with thousands (1.234,56)
        elseif (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $sanitized)) {
            $sanitized = str_replace('.', '', $sanitized);
            $sanitized = str_replace(',', '.', $sanitized);
        }

        // Case 3: EU format WITHOUT thousands (639,60)
        elseif (preg_match('/^\d+,\d+$/', $sanitized)) {
            $sanitized = str_replace(',', '.', $sanitized);
        }

        // Case 4: Plain number (1234.56 or 1234)
        // do nothing

        $precision = $isExchangeRate ? 4 : 2;

        return round((float) $sanitized, $precision);

        //return round((float) $sanitized, 2);
    }

    public function parseVatRate(?string $str): ?float
    {
        if (!$str) {
            return null;
        }

        // Trim spaces
        $str = trim($str);

        // Keep only digits, dot, comma
        $str = preg_replace('/[^0-9.,]/', '', $str);

        if (!$str) {
            return null;
        }

        // If both dot and comma exist → dot = decimal, comma = thousands
        if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
            $str = str_replace(',', '', $str);
        }
        // If only comma exists → treat as decimal
        elseif (strpos($str, ',') !== false) {
            $str = str_replace(',', '.', $str);
        }

        $num = floatval($str);

        if (!is_numeric($num)) {
            return null;
        }

        return $num;
    }

    /**
     * Process parsed extracted data into Laravel-ready invoice array
     */
    public function processInvoiceData(array $parsed_cache): array
    {
        $type = $parsed_cache['type'];
        $manual_note = $parsed_cache['manual_note'];
        $search_save_note = $parsed_cache['search_save_note'];
        $parsed_extracted_data = $parsed_cache['extracted_data'];

        $result = [];

        // Invoice details
        $invoice_no = ($parsed_extracted_data['invoice_number'])
                        ? ltrim((string) $parsed_extracted_data['invoice_number'], '#')
                        : null;
        $invoice_date = $parsed_extracted_data['invoice_date'] ?? null;
        $currency = Arr::get($parsed_extracted_data, 'currency', null);
        $currency = $currency ? strtoupper(substr(preg_replace('/[^\w]/', '', trim($currency)), 0, 3)) : null;
        //$currency = ($currency === 'KR') ? 'DKK' : $currency;
        
        $notes = array_filter([
            $manual_note ?? null,
            $search_save_note ?? null,
        ]);

        $note = !empty($notes)
            ? implode("\n", $notes)
            : null;

        // Client/org number from supplier or recipient       
        $org_no = null;

        $party = $parsed_extracted_data['supplier'] ?? $parsed_extracted_data['recipient'] ?? null;
        if ($party) 
        {
          $vat_numeric = preg_replace('/\D/', '', $party['org_number'] ?? '');
          if ($vat_numeric && strlen($vat_numeric) == 17) {
              $org_no = substr($vat_numeric, 0, 9);
          } elseif ($vat_numeric && (strlen($vat_numeric) >= 8)) {
              $org_no = $vat_numeric;
          }
        }
        
        //$client_name = empty($party['name']) ? $client_name : $party['name'];
        $client_name = $party['name'] ?? null;
        if ( $type == 'sales' &&          
            !empty($client_name) &&
            (
                str_contains(strtolower($client_name), 'rainwear') 
                || str_contains(strtolower($client_name), 'engel') 
                || str_contains(strtolower($client_name), 'berendsohn')
                || str_contains(strtolower($client_name), 'horn bord')                
            )    
          ) 
        {
            if(str_contains(strtolower($client_name), 'horn bord'))
            {
                $invoice_no = ($parsed_extracted_data['order_number'])
                            ? ltrim((string) $parsed_extracted_data['order_number'], '#')
                            : $invoice_no;
            }            
            else
            {
                $invoice_no = ($parsed_extracted_data['no_invoice_number'])
                            ? ltrim((string) $parsed_extracted_data['no_invoice_number'], '#')
                            : $invoice_no;
            }
        }

        if (!empty($client_name) &&
            (
                str_contains(strtolower($client_name), 'dfi-geisler')
            )    
          ) 
        {
            $invoice_no = ($invoice_no)
                            ? $invoice_no
                            : (($invoice_date) ? $invoice_date.replace('/-/', '') : null);
        }

        if(str_contains(strtolower($client_name), 'stof'))
            $invoice_no = preg_replace('/-/', '', $invoice_no);

        // Net, VAT, total amounts
        $og_net_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'net_amount', '')
        );
        $net_amount = $this->parseAmountValue((string)$og_net_amount, $currency);

        $exchange_currency = Arr::get($parsed_extracted_data, 'exchange_currency', null);
        $og_exchange_net_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'exchange_net_amount', '')
        );
        $exchange_net_amount = $this->parseAmountValue((string)$og_exchange_net_amount, $exchange_currency);

        if($type == 'sales')
        {                  
            $credit_note = false;
            if(isset($parsed_extracted_data['credit_note']))
                $credit_note = ($parsed_extracted_data['credit_note']) ? true : false;        

            $og_vat_rate = Arr::get($parsed_extracted_data, 'vat_rate', '');
            $vat_rate = $this->parseVatRate((string)$og_vat_rate);

            $og_vat_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'vat_amount', '')
            );
            $og_variance_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'variance', '')
            );
            $og_freight_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'additional_charges', '')
            );
            if(str_contains(strtolower($client_name), 'dfi-geisler'))
            {
                $og_freight_amount = '';
            }

            $og_discount_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'discount_amount', '')
            );
            if(str_contains(strtolower($client_name), 'rieker')
                || str_contains(strtolower($client_name), 'woden')
                || str_contains(strtolower($client_name), 'pier one')
            )
            {
                $og_discount_amount = '';
            }

            $og_total_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'total_amount', '')
            );          

            $vat_amount = $this->parseAmountValue((string)$og_vat_amount, $currency);
            $variance_amount = $this->parseAmountValue((string)$og_variance_amount, $currency);
            $freight_amount = $this->parseAmountValue((string)$og_freight_amount, $currency);          
            $discount_amount = $this->parseAmountValue((string)$og_discount_amount, $currency);
            $total_amount = $this->parseAmountValue((string)$og_total_amount, $currency);

            $og_exchange_rate = Arr::get($parsed_extracted_data, 'exchange_rate', null);
            $exchange_rate = $this->parseAmountValue((string)$og_exchange_rate, $exchange_currency, true);

            $og_exchange_vat_amount = preg_replace(                    
                '/[^\d.,]/',
                '',
                Arr::get($parsed_extracted_data, 'exchange_vat_amount', '')
            );
            $exchange_vat_amount = $this->parseAmountValue((string)$og_exchange_vat_amount, $exchange_currency); 

            $og_exchange_total_amount = preg_replace(                    
                '/[^\d.,]/',
                '',
                Arr::get($parsed_extracted_data, 'exchange_total_amount', '')
            );
            $exchange_total_amount = $this->parseAmountValue((string)$og_exchange_total_amount, $exchange_currency);      

            if (!empty($client_name) &&
                (
                    str_contains(strtolower($client_name), 'sgi wholesale')
                    || str_contains(strtolower($client_name), 'sand cph')
                )    
              ) 
            {
                $calc_net_amount = (abs($net_amount) + abs($freight_amount)) - (abs($variance_amount) + abs($discount_amount));
            }
            else
                $calc_net_amount = (abs($net_amount) + abs($freight_amount) + abs($variance_amount)) - abs($discount_amount);
        }        

        

        // Related sales invoices
        $related_sales_invoices = [];

        if($type != 'sales')
        {            
            $sales_invoices_raw = $parsed_extracted_data['related_sales_invoices'] ?? null;

            if (!empty($sales_invoices_raw)) {
                $related_sales_invoices = $this->expandSalesInvoiceRefs(
                    $sales_invoices_raw,
                    $client_name
                );
            } else {
                $sales_orders_raw = $parsed_extracted_data['related_sales_orders'] ?? null;

                if (!empty($sales_orders_raw)) {
                    $related_sales_invoices = $this->expandSalesInvoiceRefs(
                        $sales_orders_raw,
                        $client_name
                    );
                } else {
                    $shipment_numbers_raw = $parsed_extracted_data['related_shipment_nos'] ?? null;

                    $related_sales_invoices = $this->expandSalesInvoiceRefs(
                        $shipment_numbers_raw ?? [],
                        $client_name
                    );
                }
            }
                    
          if (
            !empty($salesInvoiceMap) &&
            !empty($client_name) &&
            (
                str_contains(strtolower($client_name), 'rainwear')
            )
          ) 
          {              
            if (!empty($related_sales_invoices)) {
                $clientKey = strtolower(trim($client_name));

                $matchedInvoice = null;

                foreach ($related_sales_invoices as $inv) {
                    $cleanInv = trim($inv);
                   
                    $key = $cleanInv;

                    if (!empty($salesInvoiceMap[$key]) && $matchedInvoice === null) {                        
                        $matchedInvoice = $salesInvoiceMap[$key];
                    }
                }

                if ($matchedInvoice !== null) {
                    // Use matched sales invoice number
                    $invoice_no = $matchedInvoice;
                }
            }
          }//RAINWEAR
        }//sales related invoices        

        if($type == 'sales')
          $result = [
            'invoice_type' => 'sales',
            'invoice_no' => $invoice_no,
            'invoice_date' => $invoice_date,
            'currency' => $currency,
            'net_amount' => $net_amount,
            'vat_amount' => $vat_amount,
            'vat_rate' => $vat_rate,
            'variance_amount' => $variance_amount ?? null,
            'additional_amount' => $freight_amount ?? null,         
            'total_amount' => $total_amount,
            'adjustment_amount' => $discount_amount ?? null,
            'calc_net_amount' => $calc_net_amount,            
            'exchange_rate' => $exchange_rate ?? null,
            'exchange_currency' => $exchange_currency ?? null,
            'exchange_net_amount' => ($exchange_currency === null) ? null : ($exchange_net_amount ?? null),
            'exchange_vat_amount' => ($exchange_currency === null) ? null : ($exchange_vat_amount ?? null),
            'exchange_total_amount' => ($exchange_currency === null) ? null : ($exchange_total_amount ?? null),
            'credit_note' => $credit_note,            
            'client_no' => $org_no,
            'client_name' => $client_name,
            'note' => $note
          ];
        else
          $result = [
            'invoice_type' => 'com',
            'invoice_no' => isset($matched_sales_invoice) ? $matched_sales_invoice : $invoice_no,
            'invoice_date' => $invoice_date,
            'currency' => $currency,
            'net_amount' => $net_amount,
            'client_no' => $org_no,
            'client_name' => $client_name,
            'related_sales_invoices' => $related_sales_invoices,
            'note' => $note
          ];

        return $result;
    }    

    private function expandSalesInvoiceRefs($values, ?string $clientName = null): array
    {
        if (empty($values)) {
            return [];
        }

        if (!is_array($values)) {
            $values = [$values];
        }

        $invoiceValues = [];

        foreach ($values as $val) {
            if (empty($val)) {
                continue;
            }

            $val = (string) $val;

            // Remove spaces around ranges: ABC123 - ABC126 -> ABC123-ABC126
            $val = preg_replace_callback(
                '/([A-Za-z]*\d+\s*-\s*[A-Za-z]*\d+)/',
                fn($m) => preg_replace('/\s+/', '', $m[0]),
                $val
            );

            // Beck special handling
            if (
                $clientName &&
                stripos($clientName, 'beck') !== false &&
                stripos($val, 'bosl') !== false
            ) {
                $val = preg_replace('/([A-Za-z]+)\s+(\d+)/', '$1$2', $val);
            }

            // Split on commas or whitespace
            $parts = preg_split('/[,\s]+/', $val);

            foreach ($parts as $part) {
                $part = trim(preg_replace('/[.,;]+$/', '', $part));

                if ($part === '') {
                    continue;
                }

                if (
                    preg_match(
                        '/^([A-Za-z]*)(\d+)\s*(?:-|\.\.|\.\.\.)\s*([A-Za-z]*)(\d+)$/',
                        $part,
                        $matches
                    )
                ) {
                    $prefixStart = $matches[1];
                    $startNum    = (int) $matches[2];
                    $prefixEnd   = $matches[3];
                    $endNum      = (int) $matches[4];

                    // Handle shorthand ranges: 8992-99
                    if (strlen((string) $endNum) < strlen((string) $startNum)) {
                        $startStr = (string) $startNum;
                        $endStr   = (string) $endNum;

                        $endStr = substr(
                            $startStr,
                            0,
                            strlen($startStr) - strlen($endStr)
                        ) . $endStr;

                        $endNum = (int) $endStr;
                    }

                    // Handle reversed ranges
                    if ($startNum > $endNum) {
                        [$startNum, $endNum] = [$endNum, $startNum];
                    }

                    if ($prefixStart === $prefixEnd) {
                        for ($i = $startNum; $i <= $endNum; $i++) {
                            $invoiceValues[] =
                                $prefixStart .
                                str_pad(
                                    (string) $i,
                                    strlen($matches[2]),
                                    '0',
                                    STR_PAD_LEFT
                                );
                        }
                    }
                } else {
                    // Match values like OSDJ 16492, INV123, ABC001
                    if (
                        preg_match_all(
                            '/[A-Za-z]+(?:\s+\d+|\d+)/',
                            $part,
                            $matchList
                        )
                    ) {
                        foreach ($matchList[0] as $m) {
                            $invoiceValues[] = trim($m);
                        }
                    } else {
                        $invoiceValues[] = $part;
                    }
                }
            }
        }

        $invoiceValues = array_values(array_unique($invoiceValues));

        usort($invoiceValues, function ($a, $b) {
            preg_match('/\d+/', $a, $m1);
            preg_match('/\d+/', $b, $m2);

            $numA = isset($m1[0]) ? (int) $m1[0] : null;
            $numB = isset($m2[0]) ? (int) $m2[0] : null;

            if ($numA !== null && $numB !== null) {
                return $numA <=> $numB;
            }

            return strcmp($a, $b);
        });

        return $invoiceValues;
    }
}