<?php

namespace App\Jobs;

use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationSalesInvoices;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RematchOcrComInvoicesChunkJob implements ShouldQueue
{    
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;

    public $tries = 3;

    protected int $clientId;
    protected array $invoiceIds;

    public function __construct(int $clientId, array $invoiceIds)
    {
        $this->clientId = $clientId;
        $this->invoiceIds = $invoiceIds;
    }

    public function handle()
    {
        $invoices = ImportReconciliationComInvoices::query()
            ->with(['vatreg.client'])
            ->whereIn('id', $this->invoiceIds)
            ->get();

        foreach ($invoices as $invoice) {

            try {
                $this->processInvoice($invoice);
            } catch (\Throwable $e) {

                Log::error('OCR invoice rematching failed', [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'client_id' => $this->clientId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function processInvoice($invoice)
    {
        /*
         * Make sure another process hasn't already rematched it.
         */
        if ($invoice->rematch_ocr_com_invoice_id !== null) {
            return;
        }

        $clientName = strtoupper(
            $invoice->vatreg->client->client_name ?? ''
        );

        $invoiceNo = $invoice->invoice_no;

        /*
         * Build possible invoice numbers
         */
        $invoiceNumbers = [];

        if ($clientName === 'SECOND FEMALE NORGE AS') {

            if (Str::startsWith(Str::lower($invoiceNo), ['ic'])) {

                $invoiceNumbers[] = $invoiceNo;

            } else {

                $invoiceNumbers[] = str_replace(
                    'IC',
                    '',
                    $invoiceNo
                );
            }

        } elseif ($clientName === 'REXHOLM A/S') {

            $changedInvoiceNo = str_replace(
                'PROF',
                '',
                $invoiceNo
            );

            $invoiceNumbers[] = $changedInvoiceNo;
            $invoiceNumbers[] = $invoiceNo;

            if (str_starts_with($changedInvoiceNo, '0')) {

                $removeLeadingZero = preg_replace(
                    '/^0/',
                    '',
                    $changedInvoiceNo
                );

                $invoiceNumbers[] = $removeLeadingZero;
            }

        } elseif (stripos($clientName, 'BECKS') !== false) {

            $changedInvoiceNo = str_replace(
                'NIC',
                '',
                $invoiceNo
            );

            $invoiceNumbers[] = $changedInvoiceNo;
            $invoiceNumbers[] = $invoiceNo;

            if (str_starts_with($changedInvoiceNo, '0')) {

                $removeLeadingZero = preg_replace(
                    '/^0/',
                    '',
                    $changedInvoiceNo
                );

                $invoiceNumbers[] = $removeLeadingZero;
                $invoiceNumbers[] = 'NIC' . $removeLeadingZero;
            }

        } elseif ($clientName === 'DAN-FORM A/S') {

            $changedInvoiceNo = str_replace(
                'S-NO-',
                '',
                $invoiceNo
            );

            $invoiceNumbers[] = $changedInvoiceNo;
            $invoiceNumbers[] = $invoiceNo;
            $invoiceNumbers[] = str_replace(
                '-',
                '',
                $invoiceNo
            );

        } elseif ($clientName === 'F. ENGEL K/S') {

            $changedInvoiceNo = str_replace(
                '..FF',
                '',
                $invoiceNo
            );

            $invoiceNumbers[] = $changedInvoiceNo;
            $invoiceNumbers[] = $invoiceNo;
            // $invoiceNumbers[] = str_replace(
            //     '-',
            //     '',
            //     $invoiceNo
            // );    

        } else {

            $invoiceNumbers[] = $invoiceNo;
        }

        $invoiceNumbers = array_values(
            array_unique(
                array_filter($invoiceNumbers)
            )
        );

        /*
         * Find matching invoice.
         *
         * IMPORTANT:
         * vat_reg_id is used here because it avoids the expensive
         * whereHas() query. Only use this if vat_reg_id is correct
         * for your data relationship.
         */
        $query = ImportReconciliationComInvoices::query()
            ->where('data_from', 'ivf')
            //->whereNull('rematch_ocr_com_invoice_id')
            ->where('id', '!=', $invoice->id)
            ->whereNotNull('lope_no')
            ->where('unmatch', 0)
            ->where('disregard_invoice', 0)
            ->where('vat_reg_id', $invoice->vat_reg_id);

        if (
            $clientName === 'SECOND FEMALE NORGE AS' ||
            $clientName === 'REXHOLM A/S' ||
            stripos($clientName, 'BECKS') !== false ||
            $clientName === 'DAN-FORM A/S'
        ) {

            $query->whereIn(
                'invoice_no',
                $invoiceNumbers
            );

        } else if (            
            $clientName === 'F. ENGEL K/S'
        ) {

            $query->where(function ($query) use ($invoiceNo) {
                $query->where(
                    'invoice_no',
                    'LIKE',
                    $invoiceNo . '..FF'
                )->orWhere(
                    'invoice_no',
                    'LIKE',
                    $invoiceNo . 'FF'
                )->orWhere(
                    'invoice_no',
                    $invoiceNo
                );

            });   

        } else {

            $query->where(function ($query) use ($invoiceNo) {

                $query->where(
                    'invoice_no',
                    'LIKE',
                    'SPG-' . $invoiceNo . '-NO%'
                )->orWhere(
                    'invoice_no',
                    $invoiceNo
                );

            });
        }

        $matchInvoice = $query->first();

        if (!$matchInvoice) {
            return;
        }

        /*
         * Set rematch relation.
         */
        if (!$matchInvoice->no_of_split) {

            $matchInvoice->update([
                'rematch_ocr_com_invoice_id' => $invoice->id
            ]);
        }

        /*
         * Update period / VAT registration.
         */
        if ($invoice->month_year != $matchInvoice->month_year) {

            $invoice->update([
                'month_year' => $matchInvoice->month_year,
                'vat_reg_id' => $matchInvoice->vat_reg_id,
            ]);

            /*
             * Update sales invoices in one query.
             */
            ImportReconciliationSalesInvoices::query()
                ->whereNotNull('ocr_pdf_id')
                ->where('com_invoice_id', $invoice->id)
                ->update([
                    'vat_reg_id' => $matchInvoice->vat_reg_id,
                ]);
        }
    }
}