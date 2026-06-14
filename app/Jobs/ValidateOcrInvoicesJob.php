<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use App\Repositories\ClientRepository;

use App\Services\ValidateOcrInvoiceDuplicateService;
use App\Jobs\ValidateOcrCommercialInvoiceJob;
use App\Jobs\ValidateOcrSalesInvoiceJob;

use App\Models\OcrPdf;

class ValidateOcrInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected array $invoiceIds;

    public function __construct(public ?string $batchId = null, array $invoiceIds = [])
    {
        $this->invoiceIds = $invoiceIds;

        $this->onQueue('ocrpdfvalidateinvoices');
    }

    public function handle()
    {
        $clients = app(ClientRepository::class)->all();

        $service = app(ValidateOcrInvoiceDuplicateService::class);

        $baseQuery = OcrPdf::query();

        if (!empty($this->invoiceIds)) {
            $baseQuery->whereIn('id', $this->invoiceIds);
        }

        /*
        |-------------------------------------------------
        | STEP 1: Generate duplicate_hash (ALL invoices)
        |-------------------------------------------------
        */        
        $query = clone $baseQuery;

        $query->where('status', 'completed')
            //->whereNull('duplicate_hash')
            ->when($this->batchId, fn ($query) => $query->where('batch_id', $this->batchId))
            ->chunkById(500, function ($invoices) use ($service) {

                foreach ($invoices as $invoice) {

                    // \Log::info('Validation OCR invoice', [
                    //     'id' => $invoice->id,
                    //     'invoice_type' => $invoice->invoice_type,
                    // ]);

                    //$hash = $service->generateHash($invoice->extracted_data ?? []);
                    $data = $invoice->extracted_data ?? [];

                    if (!$service->hasMinimumFingerprint($data, $invoice->invoice_type)) {
                        Log::warning('Skipping OCR duplicate hash; minimum fingerprint fields are missing', [
                            'invoice_id' => $invoice->id,
                            'invoice_type' => $invoice->invoice_type,
                        ]);

                        $invoice->duplicate_hash = null;
                        $invoice->save();

                        continue;
                    }
                    $hash = $service->generateHash($data, $invoice->invoice_type);

                    $invoice->duplicate_hash = $hash;
                    $invoice->save();
                }
            });        

        /*
        |-------------------------------------------------
        | STEP 2: Mark duplicates per invoice_type
        |-------------------------------------------------
        */        
        $duplicatesQuery = clone $baseQuery;

        $duplicates = $duplicatesQuery->select('invoice_type', 'duplicate_hash')
            ->where('status', 'completed')
            ->whereNotNull('duplicate_hash')
            ->groupBy('invoice_type', 'duplicate_hash')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {

            $invoices = OcrPdf::query()->where('invoice_type', $duplicate->invoice_type)
                ->where('duplicate_hash', $duplicate->duplicate_hash)
                ->where('status', 'completed')
                // ->when(!empty($this->invoiceIds), function ($query) {
                //     $query->whereIn('id', $this->invoiceIds);
                // })
                ->orderBy('id') // oldest first
                ->get();

            if ($invoices->count() <= 1) {
                continue;
            }

            // Keep the oldest invoice as the master/original, even when only
            // validating a batch or recaptured invoice subset.
            $original = $invoices->first();
            $og_invoice_no = $original->extracted_data['invoice_number'] ?? null;

            $duplicateCandidates = $invoices->where('id', '!=', $original->id);

            if ($this->batchId) {
                $duplicateCandidates = $duplicateCandidates->where('batch_id', $this->batchId);
            }

            if (!empty($this->invoiceIds)) {
                $duplicateCandidates = $duplicateCandidates->whereIn('id', $this->invoiceIds);
            }

            Log::info('Duplicate group found', [
                'invoice_type' => $duplicate->invoice_type,
                'original_id' => $original->id,
                'duplicate_ids' => $invoices->skip(1)->pluck('id')->toArray(),
                'duplicate_ids' => $duplicateCandidates->pluck('id')->toArray(),
            ]);

            //foreach ($invoices->skip(1) as $invoice) {
            foreach ($duplicateCandidates as $invoice) {
                $invoice_no = " / Invoice No. " . ($og_invoice_no ?? '');

                $invoice->update([
                    'status' => 'duplicate',
                    'duplicate_message' => "Duplicate of invoice ID {$original->id}{$invoice_no}",
                    'validation_status' => 'duplicate',
                    'sync_status' => 0,
                    'is_locked' => 1,
                ]);
            }
        }

        /*
        |-------------------------------------------------
        | STEP 3: Dispatch processing jobs
        |-------------------------------------------------
        */
        
        //->where('status', '!=', 'duplicate')
        $query = clone $baseQuery;

        $query->where('status', 'completed')
        ->when($this->batchId, fn ($query) => $query->where('batch_id', $this->batchId))
        ->whereIn('validation_status', [
            'not_yet_validated',
            'validated_with_changes'
        ])
        ->chunkById(500, function ($invoices) use ($clients) {

            foreach ($invoices as $invoice) {

                if ($invoice->invoice_type === 'com') {
                    dispatch((new ValidateOcrCommercialInvoiceJob(
                        $clients,
                        $invoice->id
                    ))->onQueue('ocrpdfvalidateinvoices'));
                }

                if ($invoice->invoice_type === 'sales' || $invoice->invoice_type === 'multi-invoices') {                    
                    dispatch((new ValidateOcrSalesInvoiceJob(
                        $clients,
                        $invoice->id                        
                    ))->onQueue('ocrpdfvalidateinvoices'));
                }
            }
        });        
    }
}