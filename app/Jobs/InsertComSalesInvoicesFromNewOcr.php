<?php

namespace App\Jobs;

use App\Models\OcrPdfSyncDb;
use App\Models\OcrSyncStatus;
use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationSalesInvoices;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

use App\Models\ImportReconciliationSalesInvoicesData;

use App\Helpers\EnvironmentHelper;

class InsertComSalesInvoicesFromNewOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $comInvoiceId,
        public array $salesInvoiceIds,
        public $vatreg,
        public $authUser,
        public $from
    ) {
    }

    public function handle(): void
    {
        $startedAt = microtime(true);

        DB::listen(function ($query) {
            if ($query->time > 100) {
                Log::info('Slow OCR query', [
                    'time_ms' => $query->time,
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                ]);
            }
        });

        $allowedFtpClients = EnvironmentHelper::getFtpClients();

        $environment = EnvironmentHelper::getEnvironment();

        $salesCount = 0;
        DB::transaction(function () use ($environment, $allowedFtpClients, &$salesCount) {
            
            $comInvoice = OcrPdfSyncDb::where('id', $this->comInvoiceId)->first();

            $salesInvoices = OcrPdfSyncDb::query()
                ->whereIn('id', $this->salesInvoiceIds)
                ->get();

            if (!$comInvoice) {
                return;
            }
            
            $salesCount = count($this->salesInvoiceIds);

            if ($salesCount === 0) {
                $salesCount = count($comInvoice->related_sales_invoices ?? []);
            }
            
            /*
             * ---------------------------------------------------------
             * COM invoice values
             * ---------------------------------------------------------
             */

            $commercial_invoice_no = $comInvoice->invoice_no;
            $commercial_invoice_date = $comInvoice->invoice_date;
            
            $commercial_currency = $comInvoice->currency ?? null;
            $commercial_net_amount = $comInvoice->net_amount ?? null;

            /*
             * Your existing matching logic should go here.
             *
             * Example:
             *
             * $matched_vatregid = ...
             * $matched_country = ...
             * $matched_currency = ...
             * $document_status = ...
             * $saved_at = ...
             * $last_modified_at = ...
             * $environment = ...
             */

            $matched_vatregid = $this->vatreg->id;
            $matched_country = $this->vatreg->country ?? null;
            $matched_currency = $this->vatreg->currency_code ?? null;

            // $match_currency = null;

            $document_status = 'Validated';

            $saved_at = now();
            $last_modified_at = now();            

            /*
             * ---------------------------------------------------------
             * Remove existing COM invoice with same VAT Reg + Invoice No
             * when it belongs to another OCR PDF.
             * ---------------------------------------------------------
             */
            ImportReconciliationComInvoices::where(
                    'vat_reg_id',
                    $matched_vatregid
                )
                ->where(
                    'invoice_no',
                    $commercial_invoice_no
                )
                ->whereNotNull('ocr_pdf_id')
                ->where(
                    'ocr_pdf_id',
                    '!=',
                    $comInvoice->ocr_pdf_id
                )
                ->delete();

            /*
             * ---------------------------------------------------------
             * Insert / update COM invoice
             * ---------------------------------------------------------
             */

            $insert_cominvoice = ImportReconciliationComInvoices::updateOrCreate(
                [
                    'vat_reg_id' => $matched_vatregid,
                    'ocr_pdf_id' => $comInvoice->ocr_pdf_id,
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
                    'currency_code' => $commercial_currency,
                    'net_amount' => $commercial_net_amount,
                    'created_by' => $this->authUser->id,
                    'updated_by' => $this->authUser->id,
                    'saved_at' => $saved_at,
                    'last_modified_at' => $last_modified_at,
                ]
            );

            /*
             * ---------------------------------------------------------
             * Insert / update Sales invoices
             * ---------------------------------------------------------
             */
            $client_name = $comInvoice->client_name ?? null;
            if ($salesInvoices->isEmpty()) {

                $isAllowedFtpClient = collect($allowedFtpClients)
                    ->contains(fn ($hint) => str_contains(
                        strtolower($client_name),
                        strtolower($hint)
                    ));

                if ($client_name && $isAllowedFtpClient) {

                    // Get related sales invoice numbers
                    $related_sales_invoices = $comInvoice->related_sales_invoices ?? [];

                    foreach ($related_sales_invoices as $relatedInvoiceNo) {

                        /*
                         * Find sales invoice data by invoice number
                         * and make sure the related file belongs to the matched VAT Reg.
                         */
                        $sales_invoice = ImportReconciliationSalesInvoicesData::where(
                                'invoice_no',
                                $relatedInvoiceNo
                            )
                            ->whereHas('irFile', function ($query) use ($matched_vatregid) {
                                $query->where('vat_reg_id', $matched_vatregid);
                            })
                            ->first();

                        if (!$sales_invoice) {
                            // Log::warning(
                            //     "Sales invoice {$relatedInvoiceNo} not found for VAT Reg {$matched_vatregid}"
                            // );

                            continue;
                        }

                        /*
                         * Get the related ImportReconciliationFiles record
                         */
                        $irFile = $sales_invoice->irFile;

                        if (!$irFile) {
                            continue;
                        }

                        $sales_invoice_no = $sales_invoice->invoice_no;
                        $sales_invoice_date = $sales_invoice->invoice_date;

                        // Replace these with your actual OCR/model fields
                        $sales_invoice_currency =
                            $sales_invoice->tax_total_amount_currency_code ?? 
                            $sales_invoice->currency_code ?? null;

                        $sales_invoice_net_amount =
                            $sales_invoice->tax_total_net_amount ?? null;

                        $sales_invoice_vat_amount =
                            $sales_invoice->tax_total_amount ?? null;

                        $sales_invoice_total_amount =
                            $sales_invoice->total_amount ?? null;

                        $sales_invoice_freight_amount = null;

                        $sales_invoice_variance_amount =
                            $sales_invoice->allowance_charge ?? null;

                        $sales_invoice_discount_amount =
                            $sales_invoice->adjustment_amount ?? null;

                        $sales_invoice_exchange_rate = null;

                        $sales_invoice_exchange_currency = null;

                        $sales_invoice_exchange_net_amount = null;

                        $sales_invoice_exchange_vat_amount = null;

                        $sales_invoice_exchange_total_amount = null;

                        $sales_invoice_credit_note =
                            $sales_invoice->credit_note ?? false;
                        
                        /*
                         * Insert / update Sales Invoice
                         */
                        ImportReconciliationSalesInvoices::updateOrCreate(
                            [
                                'vat_reg_id' => $matched_vatregid,
                                //'ocr_pdf_id' => $sales_invoice->ocr_pdf_id,
                                'invoice_no' => $sales_invoice->invoice_no,
                                'com_invoice_id' => $insert_cominvoice->id,
                            ],
                            [
                                'com_invoice_id' => $insert_cominvoice->id,
                                'vat_reg_id' => $matched_vatregid,
                                'invoice_no' => $sales_invoice->invoice_no,
                                'invoice_date' => $sales_invoice->invoice_date,
                                'country' => $matched_country,
                                'currency_code' => $sales_invoice_currency,
                                'doc_status' => $document_status,
                                'net_amount' => $sales_invoice->net_amount,
                                'vat_amount' => $sales_invoice->vat_amount,
                                'total_amount' => $sales_invoice->total_amount,
                                'shipping' => $sales_invoice->additional_amount,
                                'variance' => $sales_invoice->variance,
                                'adjustment_amount' => $sales_invoice->adjustment_amount,
                                'exchange_rate' => $sales_invoice->exchange_rate,
                                'convert_currency_code' => $sales_invoice->exchange_currency,
                                'convert_net_amount' => $sales_invoice->exchange_net_amount,
                                'convert_vat_amount' => $sales_invoice->exchange_vat_amount,
                                'convert_total_amount' => $sales_invoice->exchange_total_amount,
                                'credit_note' => $sales_invoice->credit_note ?? false,
                                'created_by' => $this->authUser->id,
                                'updated_by' => $this->authUser->id,
                                'saved_at' => $saved_at,
                            ]
                        );
                    }
                }//this is only for selected client

            } else {

                foreach ($salesInvoices as $sales_invoice) {

                    $sales_invoice_no = $sales_invoice->invoice_no;
                    $sales_invoice_date = $sales_invoice->invoice_date;

                    // Replace these with your actual OCR/model fields
                    $sales_invoice_currency =
                        $sales_invoice->currency ?? null;

                    $sales_invoice_net_amount =
                        $sales_invoice->net_amount ?? null;

                    $sales_invoice_vat_amount =
                        $sales_invoice->vat_amount ?? null;

                    $sales_invoice_total_amount =
                        $sales_invoice->total_amount ?? null;

                    $sales_invoice_freight_amount =
                        $sales_invoice->additional_amount ?? null;

                    $sales_invoice_variance_amount =
                        $sales_invoice->variance ?? null;

                    $sales_invoice_discount_amount =
                        $sales_invoice->adjustment_amount ?? null;

                    $sales_invoice_exchange_rate =
                        $sales_invoice->exchange_rate ?? null;

                    $sales_invoice_exchange_currency =
                        $sales_invoice->exchange_currency ?? null;

                    $sales_invoice_exchange_net_amount =
                        $sales_invoice->exchange_net_amount ?? null;

                    $sales_invoice_exchange_vat_amount =
                        $sales_invoice->exchange_vat_amount ?? null;

                    $sales_invoice_exchange_total_amount =
                        $sales_invoice->exchange_total_amount ?? null;

                    $sales_invoice_credit_note =
                        $sales_invoice->credit_note ?? false;

                    /*
                      * ---------------------------------------------------------
                      * Remove existing Sales Invoice with same VAT Reg + Invoice No
                      * when it belongs to another OCR PDF.
                      * ---------------------------------------------------------
                      */
                    ImportReconciliationSalesInvoices::where(
                            'vat_reg_id',
                            $matched_vatregid
                        )
                        ->where(
                            'invoice_no',
                            $sales_invoice_no
                        )
                        ->where(
                            'com_invoice_id',
                            $insert_cominvoice->id
                        )
                        ->whereNotNull('ocr_pdf_id')
                        ->where(
                            'ocr_pdf_id',
                            '!=',
                            $sales_invoice->ocr_pdf_id
                        )
                        ->delete();

                    ImportReconciliationSalesInvoices::updateOrCreate(
                        [
                            'vat_reg_id' => $matched_vatregid,
                            'ocr_pdf_id' => $sales_invoice->ocr_pdf_id,
                            'com_invoice_id' => $insert_cominvoice->id,
                        ],
                        [
                            'com_invoice_id' => $insert_cominvoice->id,
                            'vat_reg_id' => $matched_vatregid,
                            'invoice_no' => $sales_invoice_no,
                            'invoice_date' => $sales_invoice_date,
                            'country' => $matched_country,
                            'currency_code' =>
                                $sales_invoice_currency ?: $matched_currency,
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
                            'saved_at' => $saved_at,
                        ]
                    );

                    /*
                     * ---------------------------------------------------------
                     * Mark OCR COM invoice as synced
                     * ---------------------------------------------------------
                     */

                    OcrSyncStatus::updateOrCreate(
                        [
                            'ocr_pdf_id' => $sales_invoice->ocr_pdf_id,
                            'environment' => $environment,
                        ],
                        [
                            'sync_status' => true,
                            'is_locked' => false,
                            'locked_at' => null,
                            'synced_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                /*
                 * ---------------------------------------------------------
                 * Remove unwanted Sales Invoices
                 *
                 * Delete existing reconciliation sales invoices belonging
                 * to this COM invoice, if they are not part of the current
                 * OCR sales invoice list.
                 * ---------------------------------------------------------
                 */
                $currentSalesOcrPdfIds = $salesInvoices
                    ->pluck('ocr_pdf_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                ImportReconciliationSalesInvoices::where(
                    'vat_reg_id',
                    $matched_vatregid
                )
                ->where(
                    'com_invoice_id',
                    $insert_cominvoice->id
                )
                ->whereNotNull('ocr_pdf_id')
                ->whereNotIn('ocr_pdf_id', $currentSalesOcrPdfIds)
                ->delete();
            }

            /*
             * ---------------------------------------------------------
             * Mark OCR COM invoice as synced
             * ---------------------------------------------------------
             */

            OcrSyncStatus::updateOrCreate(
                [
                    'ocr_pdf_id' => $comInvoice->ocr_pdf_id,
                    'environment' => $environment,
                ],
                [
                    'sync_status' => true,
                    'is_locked' => false,
                    'locked_at' => null,
                    'synced_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });

        Log::info('OCR invoice job completed', [
            'com_invoice_id' => $this->comInvoiceId,
            'sales_count' => $salesCount,
            'duration_seconds' => round(
                microtime(true) - $startedAt,
                2
            ),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        // Optional: update OcrSyncStatus here as failed
        // Log the exception if required.
        Log::error('OCR Job failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}