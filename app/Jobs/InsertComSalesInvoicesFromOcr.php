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

use App\Models\Client;
use App\Models\ImportReconciliationFiles;
use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationSalesInvoices;
use App\Models\ImportReconciliationSalesInvoicesData;
use App\Models\Invoices;
use App\Models\VATReturns;
use App\Models\InvoiceOcrPdf;

use \App\Classes\CommonClass;

//use App\Events\ImportReconciliationComSalesInvoicesEvent;
use App\Events\OcrInvoicesSyncEvent;

class InsertComSalesInvoicesFromOcr implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $invoice_data;
    protected $allvatregs;    
    protected $authUser;
    protected $from;
    
    protected $commonClass;   

    /**
     * Create a new job instance.
     *
     * @return void
     */   
    public function __construct($invoice_data, $allvatregs, $authUser, $from)
    {                  
      $this->invoice_data = $invoice_data;
      $this->allvatregs = $allvatregs;    
      $this->authUser = $authUser;     
      $this->from = $from;      
      
      $this->commonClass = new CommonClass();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {      
        try {
          
        $comIds = collect($this->invoice_data)->pluck('com_id')->unique();
        
        $salesIds = collect($this->invoice_data)
            ->pluck('sales_ids')
            ->flatten()
            ->unique();

        // Fetch only required columns (important for memory)
        $comInvoices = InvoiceOcrPdf::whereIn('id', $comIds)
                        ->select('id', 'client_id', 'extracted_data', 'created_at', 'updated_at')
                        ->get()
                        ->keyBy('id');

        //$clientId = $comInvoices->pluck('client_id')->unique()->first();
        $clientId = $this->allvatregs->pluck('client_id')->unique()->first();        
        $client = ($clientId) ? Client::where('id', $clientId)->first() : null;

        $clientNameCache = ($client) ? $client->client_name : null;
//Log::info($clientNameCache);

        $parsedComCache = [];
      
        foreach ($comInvoices as $id => $com) {
            $parsedComCache[$id] = json_decode($com->extracted_data ?? null, true);
        }

        if ($clientNameCache && (
            stripos(strtolower($clientNameCache), "aubo") !== false || stripos(strtolower($clientNameCache), "beck") !== false ||
            stripos(strtolower($clientNameCache), "geisler") !== false || stripos(strtolower($clientNameCache), "noscomed") !== false ||
            stripos(strtolower($clientNameCache), "rexholm") !== false || stripos(strtolower($clientNameCache), "villy") !== false
            )
        ) 
        {
            $salesInvoices = ImportReconciliationSalesInvoicesData::whereIn('id', $salesIds)
                                ->select('id', 'invoice_no', 'invoice_date', 'currency_code', 'tax_total_net_amount', 
                                    'tax_total_amount', 'tax_total_percent', 'credit_note',
                                    'created_at', 'updated_at')
                                ->get()
                                ->keyBy('id');

            $parsedSalesCache = [];        
            foreach ($salesInvoices as $id => $sale) {
                $calculated_vat_amount = ($sale->tax_total_net_amount * $sale->tax_total_percent)/100;

                $parsedSalesCache[$id] = [
                    'invoice_no' => $sale->invoice_no,
                    'invoice_date' => $sale->invoice_date,
                    'currency' => $sale->currency_code,
                    'net_amount' => $sale->tax_total_net_amount,
                    'vat_amount' => $calculated_vat_amount,
                    'vat_rate' => $sale->tax_total_percent,
                    'variance_amount' => null,
                    'freight_amount' => null,
                    'total_amount' => $sale->tax_total_amount,
                    'discount_amount' => null,
                    'exchange_rate' => null,
                    'exchange_currency' => null,
                    'exchange_net_amount' => null,
                    'exchange_vat_amount' => null,
                    'exchange_total_amount' => null,
                    'credit_note' => $sale->credit_note
                ];
            }
        } //in sales invoice data table
        else
        {
            $salesInvoices = InvoiceOcrPdf::whereIn('id', $salesIds)
                                ->select('id', 'extracted_data', 'created_at', 'updated_at')
                                ->get()
                                ->keyBy('id'); 

            $parsedSalesCache = [];        
            foreach ($salesInvoices as $id => $sale) {
                $parsedSalesCache[$id] = json_decode($sale->extracted_data ?? null, true);
            }
        }//else from OCR table extracted_data
            
        $salesInvoiceMap = [];
        foreach ($this->invoice_data as $item) 
        {
            $com_invoice = $comInvoices[$item['com_id']] ?? null;
            if (!$com_invoice) continue;

            //$parsed = json_decode($com_invoice->extracted_data ?? null, true);
            $parsed = $parsedComCache[$item['com_id']] ?? null;
            if (!$parsed) continue;

            $invNo = isset($parsed['invoice_number'])
                ? ltrim($parsed['invoice_number'], '#')
                : null;

            $noInvNo = isset($parsed['no_invoice_number'])
                ? ltrim($parsed['no_invoice_number'], '#')
                : null;

            $client = isset($parsed['supplier']['name'])
                ? strtolower(trim($parsed['supplier']['name']))
                : null;
            
            if ($invNo && $noInvNo && $client && 
                (
                    str_contains($client, 'rainwear') 
                    || str_contains($client, 'engel')
                )
            ) 
            {
                $salesInvoiceMap[$noInvNo] = $invNo;
            }
        }
                            
        $invoiceData = $this->invoice_data;

        $unique_vat_reg_ids = [];
        $unique_logs = [];

        // Initialize in-memory hash for sales invoices
        $salesHash = [];

        $totalJobs = count($invoiceData);

        $document_status = 'Validated';
            foreach ($invoiceData as $item) {                    

                    DB::transaction(function () use ($item, $salesInvoiceMap, $comInvoices, $salesInvoices, $parsedComCache, $parsedSalesCache, $clientNameCache, $clientId, $document_status, 
                        &$unique_vat_reg_ids,
                        &$unique_logs,
                        &$salesHash)  {
                    // $com_invoice = $item['com_invoice'];
                    // $sales_invoices = $item['sales_invoices'];

                    $com_invoice = $comInvoices[$item['com_id']] ?? null;

                    if (!$com_invoice) return;

                    $sales_invoices = collect($item['sales_ids'])
                                        ->map(fn($id) => $salesInvoices[$id] ?? null)
                                        ->filter()
                                        ->values();

                    $saved_at = $com_invoice->created_at;
                    $last_modified_at = $com_invoice->updated_at;

                    // $parsed_com_extracted_data = null;
                    // if (!empty($com_invoice->extracted_data)) {
                    //     $parsed_com_extracted_data = json_decode($com_invoice->extracted_data, true);
                    //     if (json_last_error() !== JSON_ERROR_NONE) {
                    //         $parsed_com_extracted_data = null;
                    //     }
                    // }

                    $parsed_com_extracted_data = $parsedComCache[$com_invoice->id] ?? null;
                    
                    if ($parsed_com_extracted_data) {
                        $processed_com_extracted_data = $this->processInvoiceData($parsed_com_extracted_data, null, $salesInvoiceMap, $clientNameCache);

                        $commercial_invoice_no = $processed_com_extracted_data['invoice_no'];
                        $commercial_invoice_date = $processed_com_extracted_data['invoice_date'];
                        $commercial_net_amount = $processed_com_extracted_data['net_amount'];
                        
                        //$match_invoice_date = Carbon::parse($processed_com_extracted_data['invoice_date'])->format('Ymd');
                        try {
                            $match_invoice_date = Carbon::parse($processed_com_extracted_data['invoice_date'])->format('Ymd');
                        } catch (\Exception $e) {Log::error("bad OCR record");
                            return; // skip bad OCR record
                        }

                        $match_currency = $processed_com_extracted_data['currency'];

                        $matched_vatregid = '';
                        $matched_country = '-';
                        $matched_currency = '-';
                        $client_name = '';
                        $vatRegHeading = '';

                        // Find which VATReg period
                        $filtered_vatreg = $this->allvatregs->filter(function ($vatreg) use ($match_invoice_date) {
                            $frequency = $this->commonClass->getFrequency($vatreg->general_periods);
                            return (
                                ($match_invoice_date >= Carbon::parse($vatreg->service_start)->format('Ymd')) &&
                                ($match_invoice_date <= Carbon::parse($vatreg->service_start)->addMonth($frequency-1)->endOfMonth()->format('Ymd'))
                            );
                        });

                        $reindexed = array_values($filtered_vatreg->toArray());

                        if(count($reindexed) == 0) 
                        {
                            //use the last period
                            // $lastItem = $this->allvatregs
                            //                 ->sortBy('id')
                            //                 ->last();

                            $lastItem = $this->allvatregs
                                            ->sortBy('id')
                                            ->values()
                                            ->slice(-2, 1)
                                            ->first();
                            
                            $reindexed = [$lastItem->toArray()];
                        }
                       
                        if(count($reindexed) > 0) {
                            $matched_vatreg = $reindexed[0];
                            $matched_vatregid = $matched_vatreg['id'];
                            $client_id = $matched_vatreg['client_id'];
                            $client = $this->commonClass->getCompanyLazy($client_id);
                            $client_name = $client->client_name;
                            $vatRegHeading = $client_name . ' - ' . Carbon::parse($matched_vatreg['service_start'])->format('M Y') . ' ' . $matched_vatreg['country'] . ' ' . $matched_vatreg['general_periods'];
                            $matched_country = $matched_vatreg['country'];

                            if(!$match_currency) {
                                $countryMap = [
                                    "DK" => "DKK",
                                    "NO" => "NOK",
                                    "SE" => "SEK",
                                    "GB" => "GBP",
                                    "IN" => "INR",
                                    "FR" => "EUR",
                                    "CH" => "CHF"
                                ];
                                $matched_currency = $countryMap[$matched_country] ?? '-';
                            }
                        }

                        if($matched_vatregid != '' && $commercial_invoice_no) {
                            if(!in_array($matched_vatregid, $unique_vat_reg_ids, true))
                                $unique_vat_reg_ids[] = $matched_vatregid;
                            if(!in_array($vatRegHeading, $unique_logs, true))
                                $unique_logs[] = $vatRegHeading;

                            // Check and Insert into COM
                            $insert_cominvoice = ImportReconciliationComInvoices::updateOrCreate(
                                [
                                    'vat_reg_id' => $matched_vatregid,
                                    'invoice_no' => $commercial_invoice_no
                                ],
                                [
                                    'vat_reg_id' => $matched_vatregid,
                                    'data_from' => 'ocr',
                                    'month_year' => Carbon::parse($commercial_invoice_date)->format('m-Y'),
                                    'invoice_no' => $commercial_invoice_no,
                                    'invoice_date' => $commercial_invoice_date,
                                    'gs_invoice_date' => $commercial_invoice_date,
                                    'doc_status' => $document_status,
                                    'country' => $matched_country,
                                    'currency_code' => ($match_currency) ? $match_currency : $matched_currency,
                                    'net_amount' => $commercial_net_amount,
                                    'created_by' => $this->authUser->id,
                                    'updated_by' => $this->authUser->id,
                                    'saved_at' => $saved_at,
                                    'last_modified_at' => $last_modified_at
                                ]
                            );

                            InvoiceOcrPdf::where('id', $com_invoice->id)
                                ->update(['sync_status' => 1, 'is_locked' => 0]);

                            //if(count($sales_invoices) > 0)
                            if ($sales_invoices->isNotEmpty())
                            { 
                              // LOOP sales invoices
                              foreach ($sales_invoices as $sales_invoice) {
                                  $saved_at = $sales_invoice->created_at;
                                  $last_modified_at = $sales_invoice->updated_at;

                                  // $parsed_sales_extracted_data = null;
                                  // if (!empty($sales_invoice->extracted_data)) {
                                  //     $parsed_sales_extracted_data = json_decode($sales_invoice->extracted_data, true);
                                  //     if (json_last_error() !== JSON_ERROR_NONE) {
                                  //         $parsed_sales_extracted_data = null;
                                  //     }
                                  // }

                                  $parsed_sales_extracted_data = $parsedSalesCache[$sales_invoice->id] ?? null;
                                 
                                  if ($parsed_sales_extracted_data) {

                                    if ($clientNameCache && (
                                        stripos(strtolower($clientNameCache), "aubo") !== false || stripos(strtolower($clientNameCache), "beck") !== false ||
                                    stripos(strtolower($clientNameCache), "geisler") !== false || stripos(strtolower($clientNameCache), "noscomed") !== false ||
                                    stripos(strtolower($clientNameCache), "rexholm") !== false || stripos(strtolower($clientNameCache), "villy") !== false
                                        )
                                    ) 
                                    {
                                        $processed_sales_extracted_data = $parsed_sales_extracted_data;
                                    }
                                    else
                                      $processed_sales_extracted_data = $this->processInvoiceData($parsed_sales_extracted_data, 'sales', [], $clientNameCache);

                                      $sales_invoice_no = $processed_sales_extracted_data['invoice_no'];

                                      // --- Hash check to avoid duplicate processing ---
                                      $hashKey = $insert_cominvoice->id . '|' . $sales_invoice_no;
                                      if(isset($salesHash[$hashKey])) continue;
                                      $salesHash[$hashKey] = true;

                                      $sales_invoice_date = $processed_sales_extracted_data['invoice_date'];
                                      $sales_invoice_currency = $processed_sales_extracted_data['currency'];
                                      $sales_invoice_net_amount = $processed_sales_extracted_data['net_amount'];
                                      $sales_invoice_vat_amount = $processed_sales_extracted_data['vat_amount'];
                                      $sales_invoice_variance_amount = $processed_sales_extracted_data['variance_amount'];
                                      $sales_invoice_freight_amount = $processed_sales_extracted_data['freight_amount'];
                                      $sales_invoice_discount_amount = $processed_sales_extracted_data['discount_amount'];
                                      $sales_invoice_total_amount = $processed_sales_extracted_data['total_amount'];
                                      $sales_invoice_credit_note = $processed_sales_extracted_data['credit_note'];

                                      $sales_invoice_exchange_rate = $processed_sales_extracted_data['exchange_rate'];
                                      $sales_invoice_exchange_currency = $processed_sales_extracted_data['exchange_currency'];
                                      $sales_invoice_exchange_net_amount = $processed_sales_extracted_data['exchange_net_amount'];
                                      $sales_invoice_exchange_vat_amount = $processed_sales_extracted_data['exchange_vat_amount'];
                                      $sales_invoice_exchange_total_amount = $processed_sales_extracted_data['exchange_total_amount'];

                                      // Insert or update sales invoice
                                      ImportReconciliationSalesInvoices::updateOrCreate(
                                          [
                                              'vat_reg_id' => $matched_vatregid,
                                              'invoice_no' => $sales_invoice_no,
                                              'com_invoice_id' => $insert_cominvoice->id,
                                          ],
                                          [
                                              'com_invoice_id' => $insert_cominvoice->id,
                                              'vat_reg_id' => $matched_vatregid,
                                              'invoice_no' => $sales_invoice_no,
                                              'invoice_date' => $sales_invoice_date,
                                              'country' => $matched_country,
                                              'currency_code' => ($sales_invoice_currency) ? $sales_invoice_currency : $matched_currency,
                                              'doc_status' => $document_status,
                                              'net_amount' => $sales_invoice_net_amount,
                                              'vat_amount' => $sales_invoice_vat_amount,
                                              'total_amount' => $sales_invoice_total_amount,
                                              'shipping' => $sales_invoice_freight_amount,
                                              'variance' => $sales_invoice_variance_amount,
                                              'adjustment_amount' => $sales_invoice_discount_amount,
                                              'exchange_rate' => $sales_invoice_exchange_rate,
                                              'convert_currency_code' => $sales_invoice_exchange_currency,
                                              'convert_net_amount' => $sales_invoice_exchange_net_amount,
                                              'convert_vat_amount' => $sales_invoice_exchange_vat_amount,
                                              'convert_total_amount' => $sales_invoice_exchange_total_amount,
                                              'credit_note' => $sales_invoice_credit_note,
                                              'created_by' => $this->authUser->id,
                                              'updated_by' => $this->authUser->id,
                                              'saved_at' => $saved_at
                                          ]
                                      );

                                      if ($clientNameCache && (
                                        stripos(strtolower($clientNameCache), "aubo") !== false || stripos(strtolower($clientNameCache), "beck") !== false ||
                                    stripos(strtolower($clientNameCache), "geisler") !== false || stripos(strtolower($clientNameCache), "noscomed") !== false ||
                                    stripos(strtolower($clientNameCache), "rexholm") !== false || stripos(strtolower($clientNameCache), "villy") !== false
                                        )
                                    ) 
                                    {
                                    }
                                    else
                                      InvoiceOcrPdf::where('id', $sales_invoice->id)
                                          ->update(['sync_status' => 1, 'is_locked' => 0]);
                                  }
                              } // end sales loop
                            } // end sales
                            // else
                            // {
                            //   Log::info('NO Sales Invoices from OCR');
                            // }
                        } else {
                            InvoiceOcrPdf::where('id', $com_invoice->id)
                                ->update(['sync_status' => 0, 'is_locked' => 0]);
                        }
                    }
                
                }, 5); // end transaction - retry 5 times
            } // end invoice_data loop

                // // Log unique VAT regs
                // foreach ($unique_vat_reg_ids as $vat_reg_id) {
                //     $logType = match($this->from) {
                //         'ocr-search-refresh', 'specific-ocr-search-refresh' => 'importreconcilation-ocr-search-refresh',
                //         default => 'importreconcilation-control-refresh',
                //     };
                //     Log::info("Log Typeeeeeeeeeeeeeeeeeeeeee:");
                //     Log::info($this->from);
                //     Log::info($logType);
                //     $this->commonClass->addLog($this->authUser, $logType, ['VAT Reg.' => $unique_logs, 'VAR Reg. IDs' => $unique_vat_reg_ids]);

                //     //event(new ImportReconciliationComSalesInvoicesEvent($vat_reg_id, 'Updated the com./sales invoice'));
                // }
                
                // event(new OcrInvoicesSyncEvent($clientId, 'Synced the OCR invoices'));
            
        } catch (\Exception $e) {
            Log::error('Transaction failed for Com. & Sales Invoices from OCR: ' . $e->getMessage());
            $this->failed($e);
        }
    }

    // public function failed(\Exception $exception) {
    //     // Log the error or send a notification
    //     dd($exception);
    // }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Parse an amount string into float (handles thousand separators, decimal comma, etc.)
     */
    // public function parseAmountValue(string $amount, ?string $currency_code = null): float
    // {
    //     if (empty($amount)) return 0.0;

    //     $sanitized = str_replace('−', '-', $amount);

    //     // Detect formats
    //     if (preg_match('/\d{1,3}(,\d{3})+(\.\d+)?$/', $sanitized)) {
    //         // US/UK format: 3,648.94 → 3648.94
    //         $sanitized = str_replace(',', '', $sanitized);
    //     } elseif (preg_match('/\d{1,3}(\.\d{3})+(,\d+)?$/', $sanitized)) {
    //         // EU format: 3.648,94 → 3648.94
    //         $sanitized = str_replace('.', '', $sanitized);
    //         $sanitized = str_replace(',', '.', $sanitized);
    //     } else {
    //         // fallback: remove spaces, keep dot as decimal
    //         $sanitized = str_replace(' ', '', $sanitized);
    //     }

    //     return round(floatval($sanitized), 2);
    // }

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
    public function processInvoiceData(array $parsed_extracted_data, $type = null, array $salesInvoiceMap = [], $client_name = null): array
    {
        $result = [];

        // Invoice details
        $invoice_no = ($parsed_extracted_data['invoice_number'])
                        ? ltrim((string) $parsed_extracted_data['invoice_number'], '#')
                        : null;
        $invoice_date = $parsed_extracted_data['invoice_date'] ?? null;
        $currency = Arr::get($parsed_extracted_data, 'currency', null);
        $currency = $currency ? strtoupper(substr(preg_replace('/[^\w]/', '', trim($currency)), 0, 3)) : null;
        $currency = ($currency === 'KR') ? 'DKK' : $currency;

        $og_exchange_rate = Arr::get($parsed_extracted_data, 'exchange_rate', null);        

        $exchange_currency = Arr::get($parsed_extracted_data, 'exchange_currency', null);
        //$exchange_currency = $exchange_currency ? strtoupper(substr(preg_replace('/[^\w]/', '', trim($exchange_currency)), 0, 3)) : null;
        // $exchange_currency = $exchange_currency
        //                         ? strtoupper(
        //                             substr(
        //                                 preg_replace(
        //                                     '/[^\w]/',
        //                                     '',
        //                                     trim(
        //                                         end(explode('/', $exchange_currency))
        //                                     )
        //                                 ),
        //                                 0,
        //                                 3
        //                             )
        //                         )
        //                         : null;
        if ($exchange_currency) 
        {
            $parts = explode('/', $exchange_currency);
            $exchange_currency = strtoupper(substr(trim(end($parts)), 0, 3));
        }
        else
        {            
            $exchangeCurrencyPattern = '/\b([A-Z]{3})\b/i';

            $detectedExchangeCurrency = null;

            $fieldsToCheck = [
                Arr::get($parsed_extracted_data, 'exchange_rate', null),
                Arr::get($parsed_extracted_data, 'exchange_net_amount', null),
                Arr::get($parsed_extracted_data, 'exchange_vat_amount', null),
            ];

            foreach ($fieldsToCheck as $field) {

                if ($field && preg_match($exchangeCurrencyPattern, $field, $matches)) {

                    $detectedExchangeCurrency = strtoupper($matches[1]);

                    break;
                }
            }

            if ($detectedExchangeCurrency) {
                $exchange_currency = $detectedExchangeCurrency;
            }         
        }

        $exchange_currency = ($exchange_currency === 'KR') ? 'DKK' : $exchange_currency;

        // Net, VAT, total amounts
        $og_net_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'net_amount', '')
        );
        $net_amount = $this->parseAmountValue((string)$og_net_amount, $currency);
// Log::error('OG NET Amount for Invoices from OCR: ' . $og_net_amount);
// Log::error('PARSED NET Amount for Invoices from OCR: ' . $net_amount);        

        if($type == 'sales')
        {          
            $credit_note = false;
            if(isset($parsed_extracted_data['credit_note']))
                $credit_note = ($parsed_extracted_data['credit_note']) ? true : false;        

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
            $og_discount_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'discount_amount', '')
            );
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
// Log::error('OG VAT Amount for Invoices from OCR: ' . $og_vat_amount);
// Log::error('PARSED VAT Amount for Invoices from OCR: ' . $vat_amount);
            // Adjust net for discount
            // if ($discount_amount > 0 && $discount_amount <= $net_amount)
            //     $net_amount -= $discount_amount; 

            if($exchange_currency)
            {
                $exchange_rate = $this->parseAmountValue((string)$og_exchange_rate, $exchange_currency, true);

                $og_vat_rate = Arr::get($parsed_extracted_data, 'vat_rate', '');
                $vat_rate = $this->parseVatRate((string)$og_vat_rate);

                //$og_exchange_vat_amount = Arr::get($parsed_extracted_data, 'exchange_vat_amount', '');
                $og_exchange_vat_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'exchange_vat_amount', '')
                );
                $exchange_vat_amount = $this->parseAmountValue((string)$og_exchange_vat_amount, $exchange_currency);                

                //$og_exchange_net_amount = Arr::get($parsed_extracted_data, 'exchange_net_amount', '');
                $og_exchange_net_amount = preg_replace(                    
                    '/[^\d.,]/',
                    '',
                    Arr::get($parsed_extracted_data, 'exchange_net_amount', '')
                );
                $exchange_net_amount = $this->parseAmountValue((string)$og_exchange_net_amount, $exchange_currency);

                $epsilon = 0.00001;
                $isNetZero = abs($exchange_net_amount) < $epsilon;
                $isVatZero = abs($exchange_vat_amount) < $epsilon;

                if ($vat_rate) {
                    if ($isNetZero && !$isVatZero) {
                        $exchange_net_amount = ($exchange_vat_amount / $vat_rate) * 100;
                    } elseif ($isVatZero && !$isNetZero) {
                        $exchange_vat_amount = ($exchange_net_amount * $vat_rate) / 100;
                    }
                }

                if($exchange_net_amount && $exchange_vat_amount)
                    $exchange_total_amount = $exchange_net_amount + $exchange_vat_amount;
            } 
            else
            {
                $exchange_rate = $this->parseAmountValue((string)$og_exchange_rate, $currency, true);
            }         
        }

        // Client/org number from supplier or recipient
        //$client_name = null;
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

          //$filtered = $vatregmains->filter(fn($v) => !empty($v->org_no) && preg_replace('/\D/', '', $v->org_no) === $org_no);
          //$client_name = $filtered->isNotEmpty() ? $filtered->first()->client->client_name : ($party['name'] ?? null);
        }
        
        $client_name = empty($party['name']) ? $client_name : $party['name'];
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

        if(str_contains(strtolower($client_name), 'stof'))
            $invoice_no = preg_replace('/-/', '', $invoice_no);        

        // Related sales invoices
        $related_sales_invoices = [];

        if($type != 'sales')
        {
          $raw_related = $parsed_extracted_data['related_sales_invoices'] ?? null;

          if ($raw_related) 
          {
            $raw_related = is_array($raw_related) ? $raw_related : [$raw_related];
            $invoiceValues = [];

            foreach ($raw_related as $val) {
                if (!$val) continue;
                $parts = explode(',', $val);
                foreach ($parts as $part) {
                    $part = trim(preg_replace('/[.,;]+$/', '', $part));
                    if (!$part) continue;

                    if (preg_match('/^([A-Za-z]*)(\d+)\s*-\s*([A-Za-z]*)(\d+)$/', $part, $rangeMatch)) {
                        $prefixStart = $rangeMatch[1];
                        $startNum = (int)$rangeMatch[2];
                        $prefixEnd = $rangeMatch[3];
                        $endNum = (int)$rangeMatch[4];

                        // Handle shorthand ranges like 8992-99
                        if (strlen((string)$endNum) < strlen((string)$startNum)) {
                            $startStr = (string)$startNum;
                            $endStr   = (string)$endNum;

                            $endStr = substr($startStr, 0, strlen($startStr) - strlen($endStr)) . $endStr;

                            $startNum = (int)$startStr;
                            $endNum   = (int)$endStr;
                        }
                        
                        if ($prefixStart === $prefixEnd && $startNum <= $endNum) {
                            for ($i = $startNum; $i <= $endNum; $i++) {
                                $invoiceValues[] = $prefixStart . str_pad($i, strlen($rangeMatch[2]), '0', STR_PAD_LEFT);
                            }
                        }
                    } else {
                        $spaceParts = preg_split('/\s+/', $part);
                        foreach ($spaceParts as $p) {
                            if ($p) $invoiceValues[] = $p;
                        }
                    }
                }
            }

            $related_sales_invoices = collect($invoiceValues)->unique()->sort()->values()->all();
          }
                    
          if (
            !empty($salesInvoiceMap) &&
            !empty($client_name) &&
            (
                str_contains(strtolower($client_name), 'rainwear') || 
                str_contains(strtolower($client_name), 'engel')
            )
          ) 
          {  
            $matched_sales_invoice = null;
            if ($type !== 'sales' && !empty($related_sales_invoices)) 
            {
              foreach ($related_sales_invoices as $inv) 
              {
                $inv = trim($inv);

                if (isset($salesInvoiceMap[$inv])) {
                    $matched_sales_invoice = $salesInvoiceMap[$inv];
                    break;
                }
              }
            }
          }//RAINWEAR
        }//sales related invoices        

        if($type == 'sales')
          $result = [
            'invoice_no' => $invoice_no,
            'invoice_date' => $invoice_date,
            'currency' => $currency,
            'net_amount' => $net_amount,
            'vat_amount' => $vat_amount,
            'variance_amount' => $variance_amount,
            'freight_amount' => $freight_amount,            
            'total_amount' => $total_amount,
            'discount_amount' => $discount_amount,
            'exchange_rate' => isset($exchange_rate) ? $exchange_rate : null,
            'exchange_currency' => isset($exchange_currency) ? $exchange_currency : null,
            'exchange_net_amount' => isset($exchange_net_amount) ? $exchange_net_amount : null,
            'exchange_vat_amount' => isset($exchange_vat_amount) ? $exchange_vat_amount : null,
            'exchange_total_amount' => isset($exchange_total_amount) ? $exchange_total_amount : null,
            'credit_note' => $credit_note,            
            'org_no' => $org_no
          ];
        else
          $result = [
            'invoice_no' => isset($matched_sales_invoice) ? $matched_sales_invoice : $invoice_no,
            'invoice_date' => $invoice_date,
            'currency' => $currency,
            'net_amount' => $net_amount,
            'org_no' => $org_no,
            'related_sales_invoices' => $related_sales_invoices
          ];

        return $result;
    }
}