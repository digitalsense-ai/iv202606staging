<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use App\Repositories\ClientRepository;

use App\Services\ValidateOcrInvoiceDuplicateService;
use App\Jobs\ValidateOcrCommercialInvoiceJob;
use App\Jobs\ValidateOcrSalesInvoiceJob;

use App\Models\OcrPdf;
use App\Models\OcrSyncStatus;

use App\Helpers\EnvironmentHelper;

class ValidateOcrInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected array $invoiceIds;
    protected bool $manual;
    protected bool $searchSave;

    public function __construct(public string $ocrProgressKey, public ?string $batchId = null, array $invoiceIds = [], bool $manual = false, bool $searchSave = false)
    {        
        $this->invoiceIds = $invoiceIds;
        $this->manual = $manual;
        $this->searchSave = $searchSave;

        //$this->onQueue('ocrpdfvalidateinvoices');
        $this->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices'));
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

        $duplicates = $duplicatesQuery
            //->select('invoice_type', 'duplicate_hash')
            ->selectRaw("
                CASE
                    WHEN invoice_type IN ('sales', 'multi-invoices') THEN 'sales'
                    ELSE invoice_type
                END AS invoice_group,
                duplicate_hash
            ")
            ->where('status', 'completed')
            ->whereNotNull('duplicate_hash')
            //->groupBy('invoice_type', 'duplicate_hash')
            ->groupBy('invoice_group', 'duplicate_hash')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty() && !empty($this->invoiceIds)) {

            $invoiceIds = array_map('intval', $this->invoiceIds);

            $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));

            $myquery = <<<SQL

                SELECT
                    CASE
                        WHEN p.invoice_type IN ('sales', 'multi-invoices') THEN 'sales'
                        ELSE p.invoice_type
                    END AS invoice_type,

                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            p.extracted_data,
                            '$.invoice_number'
                        )
                    ) AS invoice_no,

                    CASE
                        WHEN p.invoice_type = 'com' THEN
                            REGEXP_REPLACE(
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        p.extracted_data,
                                        '$.recipient.org_number'
                                    )
                                ),
                                '[^0-9]',
                                ''
                            )
                        ELSE
                            REGEXP_REPLACE(
                                COALESCE(
                                    NULLIF(
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                p.extracted_data,
                                                '$.supplier.org_number'
                                            )
                                        ),
                                        ''
                                    ),
                                    NULLIF(
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                p.extracted_data,
                                                '$.supplier.cvr_number'
                                            )
                                        ),
                                        ''
                                    )
                                ),
                                '[^0-9]',
                                ''
                            )
                    END AS client_no,

                    LOWER(TRIM(
                        JSON_UNQUOTE(
                            JSON_EXTRACT(
                                p.extracted_data,
                                '$.currency'
                            )
                        )
                    )) AS currency,

                    COUNT(*) AS duplicate_count,

                    GROUP_CONCAT(
                        p.id
                        ORDER BY p.id
                    ) AS invoice_ids

                FROM dv_ocr_pdfs p

                WHERE p.status = 'completed'
                  AND p.is_deleted = 0

                  AND EXISTS (

                      SELECT 1
                      FROM dv_ocr_pdfs target

                      WHERE target.id IN ($placeholders)

                        /* Same invoice type/group */
                        AND (
                            CASE
                                WHEN target.invoice_type IN ('sales', 'multi-invoices')
                                    THEN 'sales'
                                ELSE target.invoice_type
                            END
                        ) = (
                            CASE
                                WHEN p.invoice_type IN ('sales', 'multi-invoices')
                                    THEN 'sales'
                                ELSE p.invoice_type
                            END
                        )

                        /* Same invoice number */
                        AND JSON_UNQUOTE(
                            JSON_EXTRACT(
                                target.extracted_data,
                                '$.invoice_number'
                            )
                        ) = JSON_UNQUOTE(
                            JSON_EXTRACT(
                                p.extracted_data,
                                '$.invoice_number'
                            )
                        )

                        /* Same client number */
                        AND (
                            CASE
                                WHEN target.invoice_type = 'com' THEN
                                    REGEXP_REPLACE(
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                target.extracted_data,
                                                '$.recipient.org_number'
                                            )
                                        ),
                                        '[^0-9]',
                                        ''
                                    )
                                ELSE
                                    REGEXP_REPLACE(
                                        COALESCE(
                                            NULLIF(
                                                JSON_UNQUOTE(
                                                    JSON_EXTRACT(
                                                        target.extracted_data,
                                                        '$.supplier.org_number'
                                                    )
                                                ),
                                                ''
                                            ),
                                            NULLIF(
                                                JSON_UNQUOTE(
                                                    JSON_EXTRACT(
                                                        target.extracted_data,
                                                        '$.supplier.cvr_number'
                                                    )
                                                ),
                                                ''
                                            )
                                        ),
                                        '[^0-9]',
                                        ''
                                    )
                            END
                        ) = (
                            CASE
                                WHEN p.invoice_type = 'com' THEN
                                    REGEXP_REPLACE(
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                p.extracted_data,
                                                '$.recipient.org_number'
                                            )
                                        ),
                                        '[^0-9]',
                                        ''
                                    )
                                ELSE
                                    REGEXP_REPLACE(
                                        COALESCE(
                                            NULLIF(
                                                JSON_UNQUOTE(
                                                    JSON_EXTRACT(
                                                        p.extracted_data,
                                                        '$.supplier.org_number'
                                                    )
                                                ),
                                                ''
                                            ),
                                            NULLIF(
                                                JSON_UNQUOTE(
                                                    JSON_EXTRACT(
                                                        p.extracted_data,
                                                        '$.supplier.cvr_number'
                                                    )
                                                ),
                                                ''
                                            )
                                        ),
                                        '[^0-9]',
                                        ''
                                    )
                            END
                        )

                        /* Same currency */
                        AND LOWER(TRIM(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    target.extracted_data,
                                    '$.currency'
                                )
                            )
                        )) = LOWER(TRIM(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    p.extracted_data,
                                    '$.currency'
                                )
                            )
                        ))
                  )

                GROUP BY
                    CASE
                        WHEN p.invoice_type IN ('sales', 'multi-invoices') THEN 'sales'
                        ELSE p.invoice_type
                    END,

                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            p.extracted_data,
                            '$.invoice_number'
                        )
                    ),

                    CASE
                        WHEN p.invoice_type = 'com' THEN
                            REGEXP_REPLACE(
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        p.extracted_data,
                                        '$.recipient.org_number'
                                    )
                                ),
                                '[^0-9]',
                                ''
                            )
                        ELSE
                            REGEXP_REPLACE(
                                COALESCE(
                                    NULLIF(
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                p.extracted_data,
                                                '$.supplier.org_number'
                                            )
                                        ),
                                        ''
                                    ),
                                    NULLIF(
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                p.extracted_data,
                                                '$.supplier.cvr_number'
                                            )
                                        ),
                                        ''
                                    )
                                ),
                                '[^0-9]',
                                ''
                            )
                    END,

                    LOWER(TRIM(
                        JSON_UNQUOTE(
                            JSON_EXTRACT(
                                p.extracted_data,
                                '$.currency'
                            )
                        )
                    ))

                HAVING COUNT(*) > 1

                ORDER BY duplicate_count DESC

            SQL;

            $connection = DB::connection(
                config('database.ocr_connection')
            );

            $rows = $connection->select($myquery, $invoiceIds);

            $excludeIds = [55395, 55396, 55397];

            $selected_analyze_ids = collect($rows)
                ->pluck('invoice_ids')
                ->filter()
                ->flatMap(fn ($ids) => explode(',', $ids))
                ->map(fn ($id) => (int) trim($id))
                ->reject(fn ($id) => in_array($id, $excludeIds, true))
                ->unique()
                ->values()
                ->toArray();

            if (!empty($selected_analyze_ids)) {

                OcrPdf::query()
                    ->whereIn('id', $selected_analyze_ids)
                    ->where('status', 'completed')
                    ->chunkById(500, function ($invoices) use ($service) {

                        foreach ($invoices as $invoice) {

                            $data = $invoice->extracted_data ?? [];

                            if (!$service->hasMinimumFingerprint(
                                $data,
                                $invoice->invoice_type
                            )) {
                                continue;
                            }

                            $invoice->duplicate_hash = $service->generateHash(
                                $data,
                                $invoice->invoice_type
                            );

                            $invoice->save();
                        }
                    });

                $duplicates = OcrPdf::query()
                    ->selectRaw("
                        CASE
                            WHEN invoice_type IN ('sales', 'multi-invoices') THEN 'sales'
                            ELSE invoice_type
                        END AS invoice_group,
                        duplicate_hash
                    ")
                    ->where('status', 'completed')
                    ->whereNotNull('duplicate_hash')
                    ->whereIn('id', $selected_analyze_ids)
                    ->groupBy('invoice_group', 'duplicate_hash')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();
            }
        }

        foreach ($duplicates as $duplicate) {

            $invoiceTypes = $duplicate->invoice_group === 'sales'
                ? ['sales', 'multi-invoices']
                : [$duplicate->invoice_group];

            //$invoices = OcrPdf::query()->where('invoice_type', $duplicate->invoice_type)
            $invoices = OcrPdf::query()->whereIn('invoice_type', $invoiceTypes)
                ->where('duplicate_hash', $duplicate->duplicate_hash)
                ->where('status', 'completed')
                // ->when(!empty($this->invoiceIds), function ($query) {
                //     $query->whereIn('id', $this->invoiceIds);
                // })
                ->orderBy('id', 'DESC') // oldest(newest) first
                ->get();

            if ($invoices->count() <= 1) {
                continue;
            }

            // Keep the oldest(newest) invoice as the master/original, even when only
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

            if ($duplicateCandidates->isNotEmpty()) {
                Log::info('Duplicate group found', [
                    //'invoice_type' => $duplicate->invoice_type,
                    'invoice_type' => $invoiceTypes,
                    'original_id' => $original->id,
                    'remaing_duplicate_ids' => $invoices->skip(1)->pluck('id')->toArray(),
                    'duplicate_ids' => $duplicateCandidates->pluck('id')->toArray(),
                ]);
            }

            $environment = EnvironmentHelper::getEnvironment();
            //foreach ($invoices->skip(1) as $invoice) {
            foreach ($duplicateCandidates as $invoice) {
                $invoice_no = " / Invoice No. " . ($og_invoice_no ?? '');

                $invoice->update([
                    'status' => 'duplicate',
                    'duplicate_message' => "Duplicate of invoice ID {$original->id}{$invoice_no}",
                    'validation_status' => 'duplicate',
                    //'sync_status' => 0,
                    //'is_locked' => 1,
                ]);
                
                OcrSyncStatus::updateOrCreate(
                    [
                        'ocr_pdf_id' => $invoice->id,
                        'environment' => $environment,
                    ],
                    [                    
                        'sync_status' => 0,
                        'is_locked' => 1,
                    ]
                );
            }
        }

        /*
        |-------------------------------------------------
        | STEP 3: Dispatch processing jobs
        |-------------------------------------------------
        */
        
        //->where('status', '!=', 'duplicate')
        $query = clone $baseQuery;
// Log::info('ValidateOcrInvoicesJob', [    
//     'invoiceIds count' => count($this->invoiceIds),
//     'empty_or_not' => empty($this->invoiceIds)    
// ]);

        if (empty($this->invoiceIds))
            $query->where('status', 'completed');

        $query
        ->when($this->batchId, fn ($query) => $query->where('batch_id', $this->batchId))
        ->whereIn('validation_status', [
            'not_yet_validated',
            'validated_with_changes'
        ])
        ->chunkById(500, function ($invoices) use ($clients) {

            foreach ($invoices as $invoice) {

                // if (!$this->manual) 
                // {
                    if($invoice->manual_input_by)
                        $this->manual =  true;                    
                    else
                        $this->manual =  false;
                // }                

                if($this->manual)
                {
                    $invoice->update([
                        'manual_input_status' => 'validating',
                    ]);
                }

                if($invoice->search_save_by)
                    $this->searchSave =  true;                    
                else
                    $this->searchSave =  false;
                if($this->searchSave)
                {
                    $invoice->update([
                        'search_save_status' => 'validating',
                    ]);
                }
                
                $originalProgressKey = $this->ocrProgressKey;

                $this->ocrProgressKey = preg_replace(
                    '/^ocr_progress:[^:]+:/',
                    'ocr_progress:validate:',
                    $this->ocrProgressKey
                );

                if ($this->ocrProgressKey !== $originalProgressKey) {

                    $ttl = now()->addHour();

                    Cache::put(
                        "{$this->ocrProgressKey}:total",
                        $total,
                        $ttl
                    );

                    Cache::put(
                        "{$this->ocrProgressKey}:completed",
                        0,
                        $ttl
                    );
                }

                if ($invoice->invoice_type === 'com') {
                    dispatch((new ValidateOcrCommercialInvoiceJob(
                        $this->ocrProgressKey,
                        $clients,
                        $invoice->id,
                        $this->manual,
                        $this->searchSave
                    ))->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices')));
                }

                if ($invoice->invoice_type === 'sales' || $invoice->invoice_type === 'multi-invoices') {                    
                    dispatch((new ValidateOcrSalesInvoiceJob(
                        $this->ocrProgressKey,
                        $clients,
                        $invoice->id,
                        $this->manual,
                        $this->searchSave                        
                    ))->onQueue(config('queue.ocr.validate', 'ocrpdfvalidateinvoices')));
                }
            }
        });        
    }
}