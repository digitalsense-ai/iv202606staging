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

use App\Models\OcrPdfSyncDb;
use App\Models\VATRegistrationMain;

use App\Jobs\SyncDbFromOcr;

use App\Services\OcrProcessingService;
use App\Services\OcrClientNameResolver;
use App\Helpers\EnvironmentHelper;

class SyncDbController extends Controller
{
    public $authUser;

    public $commonClass;
    public $ocrProcessingService;
    public $environment;
    
    public function __construct(OcrProcessingService $ocrProcessingService)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) use($ocrProcessingService) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
            $this->environment = EnvironmentHelper::getEnvironment();

            $this->ocrProcessingService = $ocrProcessingService;
                 
            if($this->environment === 'live')
            {
                if (
                    !$this->authUser ||
                    !in_array($this->authUser->role, ['super-admin', 'team-user'], true)
                ) {
                    abort(403);
                }
            }
            else
            {
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
            }
            
            return $next($request);
        });
    }   

    /* -- GET /analyzepdf -- */
    public function index()
    {                       
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'analyzepdf');      
        /* --end PAGE CONFIG -- */
        
        $nameResolver = app(OcrClientNameResolver::class);
        $synceddbclients = OcrPdfSyncDb::query()
                            // ->select('client_name')
                            // ->distinct()
                            // ->orderBy('client_name', 'ASC')
                            // ->pluck('client_name')
                            // ->toArray();
                            ->select(['client_no', 'client_name'])
                            ->get()
                            ->unique(fn (OcrPdfSyncDb $record) => $record->client_no)
                            ->map(fn (OcrPdfSyncDb $record) => [
                                'client_no' => $record->client_no,
                                'client_name' => $nameResolver->resolve($record->client_no, $record->client_name),
                            ])
                            ->sortBy('client_name', SORT_NATURAL | SORT_FLAG_CASE)
                            ->values()
                            ->all();
        
        /* -- RETURN VIEW -- */
        return view('content.ocr.synced', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,
          'synceddbclients' => $synceddbclients,
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /analyzepdf -- */

    /* -- GET /analyzepdf/synceddbdata -- */
    public function syncedDbData(Request $request)
    {
        //$clientName = trim($request->client_name ?? '');
        $clientNo = trim($request->client_no ?? '');
        $legacyClientName = trim($request->client_name ?? '');

        $page = (int) ($request->page ?? 1);
        $limit = 1000;

        // $synceddbdatas = OcrPdfSyncDb::query()
        //                 ->orderBy('client_name', 'ASC')
        //                 ->orderByDesc('id')
        //                 ->paginate($limit, ['*'], 'page', $page);

        $query = OcrPdfSyncDb::query()
                    ->whereHas('ocrPdf', function ($q) {
                        $q->where('status', '!=', 'duplicate')
                          ->where('is_deleted', 0);
                    });
           
        // if ($clientName !== '') {
        //     $query->where("client_name", $clientName);
        if ($clientNo !== '') {
            $query->where('client_no', $clientNo);
        } elseif ($legacyClientName !== '') {
            // Backward compatibility for cached/older frontends. New clients
            // filter by the stable client number instead of this snapshot name.
            $query->where('client_name', $legacyClientName);
        }

        $synceddbdatas = $query
                            ->orderBy('client_name', 'ASC')
                            ->orderByDesc('id')
                            ->paginate($limit, ['*'], 'page', $page);
    
        $nameResolver = app(OcrClientNameResolver::class);
        $synceddbdatas->setCollection(
            $synceddbdatas->getCollection()->map(function (OcrPdfSyncDb $record) use ($nameResolver) {
                $record->client_name = $nameResolver->resolve($record->client_no, $record->client_name);

                return $record;
            })
        );
        
        $response = [
            'data' => $synceddbdatas->items(),
            'current_page' => $synceddbdatas->currentPage(),
            'last_page' => $synceddbdatas->lastPage(),
        ];
        
        return response()->json($response);
    }
    /* --end GET /analyzepdf/synceddbdata -- */

    /* -- GET /analyzepdf/update-failed-syncdb -- */
    public function updateFailedSyncDb()
    {
        $connection = DB::connection(
            config('database.ocr_connection')
        );

        $updated = $connection->table('dv_ocr_pdfs')
            ->where('sync_db', 3)
            ->update([
                'sync_db' => 0,
                'sync_db_remarks' => null,
                'sync_started_at' => null,
            ]);

        return response()->json([
            'message' => 'OCR sync failed jobs updated successfully.',
            'updated' => $updated,
        ]);
    }
    /* -- GET /analyzepdf/update-failed-syncdb -- */

    /* -- GET /analyzepdf/syncdb -- */
    public function syncDb()
    {    
        // $connection = DB::connection(
        //     config('database.ocr_connection')
        // );

        // $fetchPeriodFrom = '2026-04-01';

        // $vatregmains = VATRegistrationMain::select([
        //         'id',
        //         'org_no',
        //         'vat_no',
        //         'country',
        //         'client_id',
        //     ])
        //     ->where('ocr_sync', 1)
        //     ->orderBy('id', 'ASC')
        //     ->get();

        // $OrgNo = $vatregmains
        //     ->flatMap(function ($item) {
        //         return [
        //             $item->org_no,
        //             $item->vat_no,
        //         ];
        //     })
        //     ->filter()
        //     ->map(function ($value) {
        //         return preg_replace('/[^0-9]/', '', $value);
        //     })
        //     ->filter()
        //     ->unique()
        //     ->values()
        //     ->toArray();
        
        // // $OrgNo = [
        // //     '292640361', //Berg - CH
        // //     '136731107', //Kite - CH
        // //     '332375380', //Samsøe
        // //     '454158271', //Second female - CH
        // //     '235759090' //Woden
        // // ];
        
        // /*
        //  * Recover records that were stuck in processing
        //  * for more than 1 hour.
        //  */
        // $connection->table('dv_ocr_pdfs')
        //     ->where('sync_db', 2)
        //     ->where('sync_started_at', '<', now()->subHour())
        //     ->update([
        //         'sync_db' => 0,
        //         'sync_started_at' => null,
        //     ]);

        // $totalSync = 0;
        // do {

        //     /*
        //      * Claim up to 100 records.
        //      */
        //     $ocrPdfIds = $connection->transaction(function () use (
        //         $connection,
        //         $fetchPeriodFrom,
        //         $OrgNo
        //     ) {
        //         //$org_no = '292640361';
        //         // $rows = $connection->select(
        //         //     "
        //         //     SELECT p.id
        //         //     FROM dv_ocr_pdfs p
        //         //     WHERE p.sync_db = 0
        //         //       AND p.is_deleted = 0
        //         //       AND p.status = 'completed'
        //         //       AND JSON_UNQUOTE(
        //         //           JSON_EXTRACT(
        //         //               p.extracted_data,
        //         //               '$.invoice_date'
        //         //           )
        //         //       ) >= ?
        //         //       AND (
        //         //           REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(p.extracted_data, '$.supplier.org_number')), '[^0-9]', '') = ?
        //         //           OR
        //         //           REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(p.extracted_data, '$.supplier.cvr_number')), '[^0-9]', '') = ?
        //         //           OR
        //         //           REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(p.extracted_data, '$.recipient.org_number')), '[^0-9]', '') = ?
        //         //         )
        //         //     ORDER BY p.id ASC
        //         //     LIMIT 100
        //         //     FOR UPDATE SKIP LOCKED
        //         //     ",
        //         //     [$fetchPeriodFrom, $org_no, $org_no, $org_no]
        //         // );
        //         /*$placeholders = implode(',', array_fill(0, count($OrgNo), '?'));
        //         $rows = $connection->select(
        //             "
        //             SELECT p.id
        //             FROM dv_ocr_pdfs p
        //             WHERE p.sync_db = 0
        //               AND p.is_deleted = 0
        //               AND p.status = 'completed'
        //               AND JSON_UNQUOTE(
        //                   JSON_EXTRACT(
        //                       p.extracted_data,
        //                       '$.invoice_date'
        //                   )
        //               ) >= ?
        //               AND (
        //                   REGEXP_REPLACE(
        //                       JSON_UNQUOTE(
        //                           JSON_EXTRACT(p.extracted_data, '$.supplier.org_number')
        //                       ),
        //                       '[^0-9]',
        //                       ''
        //                   ) IN ($placeholders)

        //                   OR

        //                   REGEXP_REPLACE(
        //                       JSON_UNQUOTE(
        //                           JSON_EXTRACT(p.extracted_data, '$.supplier.cvr_number')
        //                       ),
        //                       '[^0-9]',
        //                       ''
        //                   ) IN ($placeholders)

        //                   OR

        //                   REGEXP_REPLACE(
        //                       JSON_UNQUOTE(
        //                           JSON_EXTRACT(p.extracted_data, '$.recipient.org_number')
        //                       ),
        //                       '[^0-9]',
        //                       ''
        //                   ) IN ($placeholders)
        //               )                   
        //             ORDER BY p.id ASC
        //             LIMIT 100
        //             FOR UPDATE SKIP LOCKED
        //             ",
        //             [$fetchPeriodFrom, $OrgNo, $OrgNo, $OrgNo]
        //         );
        //         */

        //         $placeholders = implode(',', array_fill(0, count($OrgNo), '?'));

        //         $sql = "
        //             SELECT p.id
        //             FROM dv_ocr_pdfs p
        //             WHERE p.sync_db = 0
        //               AND p.is_deleted = 0
        //               AND p.status = 'completed'
        //               AND JSON_UNQUOTE(
        //                   JSON_EXTRACT(
        //                       p.extracted_data,
        //                       '$.invoice_date'
        //                   )
        //               ) >= ?

        //               AND (
        //                   REGEXP_REPLACE(
        //                       JSON_UNQUOTE(
        //                           JSON_EXTRACT(p.extracted_data, '$.supplier.org_number')
        //                       ),
        //                       '[^0-9]',
        //                       ''
        //                   ) IN ($placeholders)

        //                   OR

        //                   REGEXP_REPLACE(
        //                       JSON_UNQUOTE(
        //                           JSON_EXTRACT(p.extracted_data, '$.supplier.cvr_number')
        //                       ),
        //                       '[^0-9]',
        //                       ''
        //                   ) IN ($placeholders)

        //                   OR

        //                   REGEXP_REPLACE(
        //                       JSON_UNQUOTE(
        //                           JSON_EXTRACT(p.extracted_data, '$.recipient.org_number')
        //                       ),
        //                       '[^0-9]',
        //                       ''
        //                   ) IN ($placeholders)
        //               )

        //             ORDER BY p.id ASC
        //             LIMIT 100
        //             FOR UPDATE SKIP LOCKED
        //         ";

        //         $bindings = array_merge(
        //             [$fetchPeriodFrom],
        //             $OrgNo,
        //             $OrgNo,
        //             $OrgNo
        //         );

        //         $rows = $connection->select($sql, $bindings);

        //         $ids = collect($rows)
        //             ->pluck('id')
        //             ->values();

        //         if ($ids->isNotEmpty()) {

        //             $connection->table('dv_ocr_pdfs')
        //                 ->whereIn('id', $ids)
        //                 ->update([
        //                     'sync_db' => 2,
        //                     'sync_started_at' => now(),
        //                 ]);
        //         }

        //         return $ids;
        //     });

        //     /*
        //      * Nothing left to process.
        //      */
        //     if ($ocrPdfIds->isEmpty()) {
        //         break;
        //     }

        //     /*
        //      * 100 claimed records
        //      * -> 4 queue jobs x 25 records.
        //      */
        //     foreach ($ocrPdfIds->chunk(25) as $chunk) {

        //         Bus::dispatch(
        //             (new SyncDbFromOcr(
        //                 $chunk->all(),
        //                 $this->authUser
        //             ))->onQueue('ocrpdfsyncdb')
        //         );
        //     }

        //     $totalSync += count($ocrPdfIds);
        // } while (true);

        // return response()->json([
        //     'message' => 'OCR sync jobs dispatched successfully.',
        //     'totalSync' => $totalSync
        // ]);
        
        $result = $this->ocrProcessingService->syncDbFromOcr($this->authUser);

        return response()->json(
            $result
        );
    }
    /* -- END GET /analyzepdf/syncdb -- */
}
