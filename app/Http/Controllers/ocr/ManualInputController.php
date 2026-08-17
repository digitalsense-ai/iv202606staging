<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\OcrPdf;
use App\Models\OcrSyncStatus;
use App\Models\VATRegistrationMain;
use App\Services\OcrAnalyzeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use \App\Classes\CommonClass;

use App\Jobs\ManualInputUpdateJob;
use App\Jobs\SearchSaveUpdateJob;

use App\Helpers\EnvironmentHelper;

class ManualInputController extends Controller
{
    public $authUser;

    public $commonClass;

    protected $vatRegistrations = null;

    public $searchSave =  false;
    public $syncedPage =  false;    
    
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

            $this->searchSave =  false;
            $this->syncedPage =  false;
            
            return $next($request);
        });
    }   

    public function index()
    {
        /* -- PAGE CONFIG -- */        
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'analyzepdf');  
        /* --end PAGE CONFIG -- */ 

        /* -- RETURN VIEW -- */
        return view('content.ocr.manual-input', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser          
        ]);
        /* --end RETURN VIEW -- */
    }

    public function queue(Request $request): JsonResponse
    {
        $this->searchSave = ($request->input('searchSave') == 'false') ? false :  true;
        
        $items = $this->baseQueueQuery()
            //->orderBy('updated_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(fn (OcrPdf $invoice) => $this->summaryPayload($invoice))
            ->values();

        return response()->json([
            'total' => $items->count(),
            'items' => $items,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->syncedPage = ($request->input('syncedPage') == 'false') ? false :  true;

        $invoice = OcrPdf::query()->findOrFail($id);

        return response()->json([
            'item' => $this->detailPayload($invoice),
            'position' => $this->positionFor($invoice->id),
            'total' => $this->baseQueueQuery()->count(),
        ]);
    }
   
    public function save(Request $request, int $id): JsonResponse
    {
        $invoice = OcrPdf::query()->findOrFail($id);

        $environment = EnvironmentHelper::getEnvironment();
        if($request->searchSave)
        {
            $this->searchSave =  true;

            $invoice->update([
                'search_save_status' => 'queued',
                'search_save_at' => now(),
                'search_save_by' => auth()->id(),
                'search_save_environment' => $environment,
            ]);

            SearchSaveUpdateJob::dispatch(
                $id,
                $request->all(),
                false,
                auth()->id()
            );

            //sleep(5); // Wait 5 seconds

            $invoice->refresh();
            return response()->json([
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type
            ]);     
        }
        else
        {
            $invoice->update([
                'manual_input_status' => 'queued',
                'manual_input_at' => now(),
                'manual_input_by' => auth()->id(),
                'manual_input_environment' => $environment,
            ]);

            ManualInputUpdateJob::dispatch(
                $id,
                $request->all(),
                false,
                auth()->id()
            );

            return response()->json($this->queuedResponse($id));
        }        

        // return response()->json([
        //     'queued' => true,
        //     'next' => $this->nextQueueItem($id)
        // ]);       

        
    }
   
    public function forceSubmit(Request $request, int $id): JsonResponse
    {
        $invoice = OcrPdf::query()->findOrFail($id);

        $invoice->update([
            'manual_input_status' => 'queued',
            'manual_input_at' => now(),
            'manual_input_by' => auth()->id(),
            'force_submitted' => true,
        ]);

        ManualInputUpdateJob::dispatch(
            $id,
            $request->all(),
            true,
            auth()->id()
        );

        // return response()->json([
        //     'queued' => true,
        //     'next' => $this->nextQueueItem($id),
        // ]);        

        return response()->json($this->queuedResponse($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $invoice = OcrPdf::query()->findOrFail($id);

        $invoice->update([
            'is_deleted' => 1,
            'deleted_reason' => 'Deleted from manual input workflow',
            'is_locked' => 0,
        ]);

        //return response()->json($this->nextResponse($invoice->id));
        return response()->json($this->queuedResponse($id));
    }

    public function clientLookup(Request $request): JsonResponse
    {
        $clientNo = trim((string) $request->query('client_no'));
        $invoiceId = trim((string) $request->query('invoice_id'));

        if ($clientNo === '') {
            return response()->json(['client' => null]);
        }
       
        //$vatRegistration = $this->vatRegistrationForOrgNo($clientNo);
        $vatRegistration = $this->findVatRegistration($clientNo);

        if ($vatRegistration && $vatRegistration->client) {
            return response()->json([
                'client' => [
                    'id' => $vatRegistration->client->id,
                    'name' => $vatRegistration->client->client_name,
                    'client_no' => $clientNo,
                    'country_code' => $vatRegistration->country,
                ],
            ]);
        }

        $client = Client::query()
            ->where('client_name', 'like', '%' . $clientNo . '%')
            ->first();

        if ($invoiceId !== '') {
            $invoice = OcrPdf::query()->findOrFail($invoiceId);

            $data = $invoice->extracted_data ?? [];
            
            $extracted_name = data_get($data, 'recipient')
                                ? (data_get($data, 'recipient.name') ?? data_get($data, 'recipient.extracted_name'))
                                : (data_get($data, 'supplier.name') ?? data_get($data, 'supplier.extracted_name'))
                                ;
        }

        return response()->json([
            'client' => $client ? [
                'id' => $client->id,
                'name' => $client->client_name,
                'client_no' => $clientNo,
                'country_code' => null,
            ] : 
            [
                'extracted_name' => $extracted_name ?? null,
            ],
        ]);
    }

    private function baseQueueQuery()
    {
        // return OcrPdf::query()
        //     ->where('is_deleted', 0)
        //     ->where(function ($query) {
        //         $query->where('status', 'failed')
        //             ->orWhere('validation_status', 'not_yet_validated')
        //             ->orWhereNotNull('error');
        //     });

        //return OcrPdf::query()            
        $sync_db = 0;
        $status = 'failed';
        if($this->searchSave)
            $status = 'completed';

        if($this->syncedPage)
        {
            $status = 'completed';
            $sync_db = 1;
        }

        return OcrPdf::
            select([
                'id',
                'file_name',
                'invoice_type',
                'error',
                'status',
                'validation_status',
                'manual_input_status',
                'search_save_status',
                'created_at',
                'updated_at',
                'extracted_data',
                'azure_url'
            ])        
            ->where('is_deleted', 0)
            ->where('status', $status)
            ->where('sync_db', $sync_db)
            //->where('extracted_data', 'LIKE', '%123456789%')  
            ->where(function ($query) {
                $query->whereNull('force_submitted')
                    ->orWhere('force_submitted', false);
            })
            // ->where(function ($query) {
            //     $query->whereNull('manual_input_status')                    
            //         //->orWhereNotIn('manual_input_status', ['validated']);
            //         ->orWhereNotIn('manual_input_status', ['queued', 'processing', 'validation_queued', 'validating', 'validated']);
            // }); 
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('manual_input_status')
                        ->orWhereNotIn('manual_input_status', [
                            'queued',
                            'processing',
                            'validation_queued',
                            'validating',
                            'validated',
                        ]);
                })
                ->orWhere(function ($query) {
                    $query->whereNull('search_save_status')
                        ->orWhereNotIn('search_save_status', [
                            'queued',
                            'processing',
                            'validation_queued',
                            'validating',
                            'validated',
                        ]);
                });
            });      
    }

    private function summaryPayload(OcrPdf $invoice): array
    {
        $data = $invoice->extracted_data ?? [];

        $clientNo = data_get($data, 'recipient.org_number') ?? data_get($data, 'supplier.org_number');
        //$vatRegistration = $this->vatRegistrationForOrgNo($clientNo);

        $clientName = data_get($data, 'recipient.name')
            ?? data_get($data, 'supplier.name');

        $vatRegistration = $this->findVatRegistration($clientNo, $clientName);

        return [
            'id' => $invoice->id,
            'file_name' => $invoice->file_name,
            'invoice_type' => $invoice->invoice_type,
            'invoice_type_name' => $this->invoiceTypeName($invoice->invoice_type),
            //'client_no' => data_get($data, 'recipient.org_number') ?? data_get($data, 'supplier.org_number'),
            //'client_name' => data_get($data, 'recipient.name') ?? data_get($data, 'supplier.name'),
            'client_no' => $clientNo,
            // 'client_name' => $vatRegistration?->client?->client_name
            //     ?? data_get($data, 'recipient.name')
            //     ?? data_get($data, 'supplier.name'),
            'client_name' => $vatRegistration?->client?->client_name
                ?? (
                    data_get($data, 'recipient')
                        ? (data_get($data, 'recipient.name') ?? data_get($data, 'recipient.extracted_name'))
                        : (data_get($data, 'supplier.name') ?? data_get($data, 'supplier.extracted_name'))
                ),
            'country_code' => $vatRegistration?->country,
            'invoice_no' => data_get($data, 'invoice_number'),
            'invoice_date' => data_get($data, 'invoice_date'),
            'error' => $invoice->error,
            'status' => $invoice->status,
            'validation_status' => $invoice->validation_status,
            'manual_input_status' => $invoice->manual_input_status,
            'search_save_status' => $invoice->search_save_status,
            'updated_at' => optional($invoice->updated_at)->format('d-m-Y'),
            'created_at' => optional($invoice->created_at)->format('d-m-Y'),
        ];
    }

    private function detailPayload(OcrPdf $invoice): array
    {
        $data = $invoice->extracted_data ?? [];

        $clientNo = data_get($data, 'recipient.org_number') ?? data_get($data, 'supplier.org_number');
        //$vatRegistration = $this->vatRegistrationForOrgNo($clientNo);

        $clientName = data_get($data, 'recipient.name')
            ?? data_get($data, 'supplier.name');

        $vatRegistration = $this->findVatRegistration($clientNo, $clientName);

        return array_merge($this->summaryPayload($invoice), [
            // 'client_name' => $vatRegistration?->client?->client_name
            //     ?? data_get($data, 'recipient.name')
            //     ?? data_get($data, 'supplier.name'),
            'client_name' => $vatRegistration?->client?->client_name
                ?? (
                    data_get($data, 'recipient')
                        ? (data_get($data, 'recipient.name') ?? data_get($data, 'recipient.extracted_name'))
                        : (data_get($data, 'supplier.name') ?? data_get($data, 'supplier.extracted_name'))
                ),
            'country_code' => $vatRegistration?->country,
            'credit_note' => (bool) data_get($data, 'credit_note', false),
            'no_invoice_number' => data_get($data, 'no_invoice_number'),
            'order_number' => data_get($data, 'order_number'),
            'currency' => data_get($data, 'currency'),
            'exchange_currency' => data_get($data, 'exchange_currency'),
            'vat_rate' => data_get($data, 'vat_rate'),
            'exchange_rate' => data_get($data, 'exchange_rate'),
            'net_amount' => data_get($data, 'net_amount'),
            'additional_charges' => data_get($data, 'additional_charges'),
            'variance' => data_get($data, 'variance'),
            'discount_amount' => data_get($data, 'discount_amount'),
            'exchange_net_amount' => data_get($data, 'exchange_net_amount'),
            'vat_amount' => data_get($data, 'vat_amount'),
            'exchange_vat_amount' => data_get($data, 'exchange_vat_amount'),
            'total_amount' => data_get($data, 'total_amount'),
            'exchange_total_amount' => data_get($data, 'exchange_total_amount'),
            'related_sales_invoices' => $this->referencesAsArray(data_get($data, 'related_sales_invoices')),            
            //'note' => data_get($data, 'manual_note') ?? $invoice->manual_note,
            // 'note' => data_get($data, 'manual_note')
            //     ?? data_get($data, 'search_save_note')
            //     ?? $invoice->manual_note,
            'note' => $invoice->manual_note
                ?? $invoice->search_save_note
                ?? null,
            'azure_url' => $invoice->azure_url,
            'sas_url' => app(OcrAnalyzeService::class)->getSasUrl($invoice->id),
        ]);
    }

    private function applyManualInput(OcrPdf $invoice, Request $request, bool $force): void
    {
        $data = $invoice->extracted_data ?? [];

        data_set($data, 'invoice_type', $request->input('invoice_type'));
        data_set($data, 'country_code', $request->input('country_code'));
        data_set($data, 'recipient.org_number', $request->input('client_no'));
        data_set($data, 'recipient.name', $request->input('client_name'));
        data_set($data, 'supplier.org_number', $request->input('client_no'));
        data_set($data, 'supplier.name', $request->input('client_name'));
        data_set($data, 'invoice_date', $request->input('invoice_date'));
        data_set($data, 'invoice_number', $request->input('invoice_no'));
        data_set($data, 'credit_note', $request->boolean('credit_note'));
        data_set($data, 'currency', $request->input('currency'));
        data_set($data, 'exchange_currency', $request->input('exchange_currency'));
        data_set($data, 'vat_rate', $request->input('vat_rate'));
        data_set($data, 'exchange_rate', $request->input('exchange_rate'));
        data_set($data, 'net_amount', $request->input('net_amount'));
        data_set($data, 'exchange_net_amount', $request->input('exchange_net_amount'));
        data_set($data, 'vat_amount', $request->input('vat_amount'));
        data_set($data, 'exchange_vat_amount', $request->input('exchange_vat_amount'));
        data_set($data, 'total_amount', $request->input('total_amount'));
        data_set($data, 'exchange_total_amount', $request->input('exchange_total_amount'));
        data_set($data, 'related_sales_invoices', array_values(array_filter((array) $request->input('related_sales_invoices', []))));
        data_set($data, 'manual_note', $request->input('note'));
        data_set($data, 'manual_input.force_submitted', $force);
        data_set($data, 'manual_input.updated_at', now()->toDateTimeString());

        $missing = $this->missingRequiredFields($data);

        $invoice->update([
            'invoice_type' => $request->input('invoice_type', $invoice->invoice_type),
            'extracted_data' => $data,
            'status' => ($missing && !$force) ? 'failed' : 'completed',
            'error' => ($missing && !$force) ? implode("\n", $missing) : null,
            'validation_status' => ($missing && !$force) ? 'not_yet_validated' : 'validated_with_changes',
            //'sync_status' => 0,
            //'is_locked' => 0,
            'sync_db' => ($missing && !$force) ? $invoice->sync_db : 0
        ]);

        $environment = EnvironmentHelper::getEnvironment();
        OcrSyncStatus::updateOrCreate(
            [
                'ocr_pdf_id' => $invoice->id,
                'environment' => $environment,
            ],
            [                    
                'sync_status' => 0,
                'is_locked' => 0,
            ]
        );
    }

    private function missingRequiredFields(array $data): array
    {
        $missing = [];

        foreach ([
            'invoice_type' => 'Document type missing',
            'invoice_date' => 'Invoice Date missing',
            'invoice_number' => 'Invoice no. missing',
            'currency' => 'Currency missing',
            //'total_amount' => 'Total amount missing',
            'net_amount' => 'Net amount missing',
        ] as $field => $message) {
            if (blank(data_get($data, $field))) {
                $missing[] = $message;
            }
        }

        if (data_get($data, 'invoice_type') !== 'com' && blank(data_get($data, 'total_amount'))) {
            $missing[] = 'Total amount missing';
        }

        if (blank(data_get($data, 'recipient.org_number')) && blank(data_get($data, 'supplier.org_number'))) {
            $missing[] = 'Client no. missing';
        }

        return $missing;
    }

    // private function nextResponse(int $currentId): array
    // {
    //     $items = $this->baseQueueQuery()
    //         ->orderBy('updated_at')
    //         ->orderBy('id')
    //         ->get();

    //     $next = $items->firstWhere('id', '!=', $currentId);

    //     return [
    //         'total' => $items->count(),
    //         'next' => $next ? $this->detailPayload($next) : null,
    //         'position' => $next ? $this->positionFor($next->id) : null,
    //     ];
    // }

    private function nextResponse(int $currentId): array
    {
        $next = $this->nextQueueItem($currentId);
        $total = $this->baseQueueQuery()->count();

        return [
            'total' => $total,
            'next' => $next,
            'position' => $next ? $this->positionFor($next['id']) : null,
        ];
    }

    private function queuedResponse(int $currentId): array
    {
        $next = $this->nextQueueItem($currentId);
        $total = $this->baseQueueQuery()->count();

        return [
            'queued' => true,
            'total' => $total,
            'next' => $next,
            'position' => $next ? $this->positionFor($next['id']) : null,
        ];
    }

    private function nextQueueItem(int $currentId): ?array
    {
        $current = OcrPdf::find($currentId);

        if (!$current) {
            return null;
        }

        $next = $this->baseQueueQuery()
            ->where(function ($query) use ($current) {
                $query->where('created_at', '>', $current->created_at)
                      ->orWhere(function ($q) use ($current) {
                          $q->where('created_at', $current->created_at)
                            ->where('id', '>', $current->id);
                      });
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();

        return $next ? $this->detailPayload($next) : null;
    }

    // private function nextQueueItem(int $currentId): ?array
    // {
    //     $next = $this->baseQueueQuery()
    //         ->where('id', '!=', $currentId)
    //         //->orderBy('updated_at')
    //         ->orderBy('created_at')
    //         ->orderBy('id')
    //         ->first();

    //     return $next ? $this->detailPayload($next) : null;
    // }

    // private function positionFor(int $id): ?int
    // {
    //     $ids = $this->baseQueueQuery()
    //         //->orderBy('updated_at')
    //         ->orderBy('created_at')
    //         ->orderBy('id')
    //         ->pluck('id')
    //         ->values();

    //     $index = $ids->search($id);

    //     return $index === false ? null : $index + 1;
    // }

    private function positionFor(int $id): ?int
    {
        $invoice = OcrPdf::find($id);

        if (!$invoice) {
            return null;
        }

        return $this->baseQueueQuery()
            ->where(function ($query) use ($invoice) {
                $query->where('created_at', '<', $invoice->created_at)
                    ->orWhere(function ($q) use ($invoice) {
                        $q->where('created_at', $invoice->created_at)
                            ->where('id', '<=', $invoice->id);
                    });
            })
            ->count();
    }

    private function getVatRegistrations()
    {
        if ($this->vatRegistrations === null) {
            $this->vatRegistrations = VATRegistrationMain::query()
                ->select([
                    'id',
                    'org_no',
                    'vat_no',
                    'country',
                    'client_id',
                ])
                ->with([
                    'client:id,client_name',
                ])
                ->get();
        }

        return $this->vatRegistrations;
    }

    private function findVatRegistration(?string $orgNo, ?string $clientName = null): ?VATRegistrationMain
    {
        // $vatRegistrations = VATRegistrationMain::query()
        //     ->select([
        //         'id',
        //         'org_no',
        //         'vat_no',
        //         'country',
        //         'client_id',
        //     ])
        //     ->with([
        //         'client:id,client_name',
        //     ])
        //     //->with('client')
        //     ->get();

        $vatRegistrations = $this->getVatRegistrations();

        $normalized = preg_replace('/\D+/', '', (string) $orgNo);

        if ($normalized !== '') {
            $match = $vatRegistrations->first(function (VATRegistrationMain $vatRegistration) use ($normalized) {
                return $this->normalizedEquals($vatRegistration->org_no, $normalized)
                    || $this->normalizedEquals($vatRegistration->vat_no, $normalized);
            });

            if ($match) {
                return $match;
            }
        }

        if (!blank($clientName)) {
            $clientName = mb_strtolower(trim($clientName));

            return $vatRegistrations->first(function (VATRegistrationMain $vatRegistration) use ($clientName) {
                return $vatRegistration->client
                    && str_contains(
                        $clientName,
                        mb_strtolower($vatRegistration->client->client_name)
                    );
            });
        }

        return null;
    }

    // private function vatRegistrationForOrgNo(?string $orgNo): ?VATRegistrationMain
    // {
    //     $normalized = preg_replace('/\D+/', '', (string) $orgNo);

    //     if ($normalized === '') {
    //         return null;
    //     }

    //     return VATRegistrationMain::query()
    //         ->with('client')
    //         ->get()
    //         ->first(function (VATRegistrationMain $vatRegistration) use ($normalized) {
    //             return $this->normalizedEquals($vatRegistration->org_no, $normalized)
    //                 || $this->normalizedEquals($vatRegistration->vat_no, $normalized);
    //         });
    // }

    private function normalizedEquals(?string $value, string $normalized): bool
    {
        return preg_replace('/\D+/', '', (string) $value) === $normalized;
    }

    private function referencesAsArray($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    private function invoiceTypeName(?string $invoiceType): string
    {       
        switch ($invoiceType) {
            case 'com':
                return 'Commercial Invoice';
            case 'multi-invoices':
                return 'Multi invoices in single PDF';
            case 'sales':
                return 'Sales Invoice';
            default:
                return ucfirst((string) $invoiceType);
        }
    }
}