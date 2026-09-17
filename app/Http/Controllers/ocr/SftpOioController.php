<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

use PDF;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use App\Models\ImportReconciliationFiles;

use App\Services\OcrProcessingService;
use App\Helpers\EnvironmentHelper;

class SftpOioController extends Controller
{
    public $authUser;

    public $commonClass;
    public $apiClass;

    public $ocrProcessingService;
    public $environment;
    
    public function __construct(OcrProcessingService $ocrProcessingService)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) use($ocrProcessingService) {                    
            $this->commonClass = new CommonClass();
            $this->apiClass = new ApiClass();

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

    /* -- GET /analyzepdf/sftp -- */
    public function index()
    {                       
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'analyzepdf');
        /* --end PAGE CONFIG -- */
               
        $sftpclients = ImportReconciliationFiles::with([
                            'vatreg.client'
                        ])
                        ->get()
                        ->pluck('vatreg.client.client_name')
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values()
                        ->toArray();

        
        /* -- RETURN VIEW -- */
        return view('content.ocr.sftpoio', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,
          'sftpclients' => $sftpclients,
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /analyzepdf/sftp -- */    

    /* -- GET /analyzepdf/sftpdata -- */
    public function sftpData(Request $request)
    {
        $clientName = trim($request->client_name ?? '');

        $page = (int) ($request->page ?? 1);
        $limit = 1000;

        $query = ImportReconciliationFiles::select([
            'id',
            'vat_reg_id',
            'file_id',
            'file_name',
            'o_file_name',
            'invoice_no',
            'created_at',
            'updated_at',
        ])->with([
            'vatreg:id,client_id,vat_reg_main_id',
            'vatreg.vatregmain:id,org_no,vat_no,country',
            'vatreg.client:id,client_name',
            'salesinvoicesdata:id,ir_file_id,invoice_no,invoice_date,currency_code,credit_note,tax_total_amount,tax_total_amount_currency_code,tax_total_net_amount,tax_total_percent',
        ]);

        if ($clientName !== '') {
            $query->whereHas('vatreg.client', function ($q) use ($clientName) {
                $q->where('client_name', $clientName);
            });
        }

        $sftpdatas = $query            
            ->orderByDesc('id')
            ->paginate($limit, ['*'], 'page', $page);
       
        $sftpdatas->getCollection()->transform(function ($item) {
            $item->ir_file_id = $item->id;

            $vatregmain = $item->vatreg?->vatregmain;
            $invoice = $item->salesinvoicesdata;

            $item->client_no = preg_replace(
                '/[^0-9]/',
                '',
                $vatregmain?->country === 'CH'
                    ? $vatregmain?->org_no
                    : $vatregmain?->vat_no
            );

            $item->client_name = $item->vatreg?->client?->client_name;

            $item->invoice_no = $invoice?->invoice_no;
            $item->invoice_date = $invoice?->invoice_date;
            $item->currency = $invoice?->currency_code;
            $item->credit_note = $invoice?->credit_note;

            $item->net_amount = $invoice?->tax_total_net_amount;
            $item->calc_net_amount = $invoice?->tax_total_net_amount;            
            $item->vat_amount = $invoice?->tax_total_amount;
            $item->vat_rate = $invoice?->tax_total_percent;

            $item->exchange_currency = null;

            return $item;
        });

        $response = [
            'data' => $sftpdatas->items(),
            'current_page' => $sftpdatas->currentPage(),
            'last_page' => $sftpdatas->lastPage(),
        ];

        return response()->json($response);
    }
    /* --end GET /analyzepdf/sftpdata -- */

    /* -- GET /analyzepdf/sftpdata/{id}/show -- */
    public function sftpShowData(Request $request, int $id): JsonResponse
    {                
        $query = ImportReconciliationFiles::select([
            'id',
            'vat_reg_id',
            'file_id',
            'file_name',
            'o_file_name',
            'invoice_no',
            'created_at',
            'updated_at',
        ])->with([
            'vatreg:id,client_id,vat_reg_main_id',
            'vatreg.vatregmain:id,org_no,vat_no,country',
            'vatreg.client:id,client_name',
            'salesinvoicesdata:id,ir_file_id,invoice_no,invoice_date,currency_code,credit_note,tax_total_amount,tax_total_amount_currency_code,tax_total_net_amount,tax_total_percent',
        ]);
       
        $sftpdata = $query
            ->where('id', $id)           
            ->first();

        if (!$sftpdata) {
            return response()->json([
                'message' => 'SFTP/OIO data not found.'
            ], 404);
        }
        
        $sftpdata->ir_file_id = $sftpdata->id;

        $vatregmain = $sftpdata->vatreg?->vatregmain;
        $invoice = $sftpdata->salesinvoicesdata;

        $sftpdata->client_no = preg_replace(
            '/[^0-9]/',
            '',
            $vatregmain?->country === 'CH'
                ? $vatregmain?->org_no
                : $vatregmain?->vat_no
        );

        $sftpdata->client_name = $sftpdata->vatreg?->client?->client_name;

        $sftpdata->invoice_no = $invoice?->invoice_no;
        $sftpdata->invoice_date = $invoice?->invoice_date;
        $sftpdata->currency = $invoice?->currency_code;
        $sftpdata->credit_note = $invoice?->credit_note;

        $sftpdata->net_amount = $invoice?->tax_total_net_amount;
        $sftpdata->calc_net_amount = $invoice?->tax_total_net_amount;            
        $sftpdata->vat_amount = $invoice?->tax_total_amount;
        $sftpdata->vat_rate = $invoice?->tax_total_percent;

        $sftpdata->exchange_currency = null;
        
        // $system = $this->commonClass->getSystemInfoLazy(); 
        // $systemapi = $system->systemapi->first();  

        // $downloadfile = $this->apiClass->loadFromOneDriveLazy($sftpdata, $systemapi);
        
        // $url = $downloadfile['download_url'];
        // $sftpdata->xmlSrc = $url; 
        // $sales_invoice_xml = (strpos($url, "https://") !== false) ? file_get_contents($url) : $downloadfile['file'];    
        // $sftpdata->xmlContent = $sales_invoice_xml; 

        // $sales_invoice_pdf = $this->commonClass->generateSalesInvoicePdfFromXml($downloadfile);
        // $sftpdata->pdfContent = $sales_invoice_pdf;

        return response()->json([
            'item' => $sftpdata            
        ]);
    }
    /* --end GET /analyzepdf/sftpdata/{id}/show -- */

    /* -- GET /analyzepdf/sftpdata/{id}/pdf -- */
    public function sftpPdf(Request $request, int $id)
    {
        $sftpdata = ImportReconciliationFiles::findOrFail($id);

        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();  

        $downloadfile = $this->apiClass->loadFromOneDriveLazy(
            $sftpdata,
            $systemapi
        );

        $sales_invoice_xml = $this->commonClass->generateSalesInvoicePdfFromXml($downloadfile);

        $data = [          
            'xmlContent' => $sales_invoice_xml               
        ];  
        $pdf = PDF::loadView('content.declaration.sales-invoice-pdf', $data);  
        
        //return $pdf->stream('invoice.pdf');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'X-Original-Xml-Url',
                $downloadfile['download_url']
            );
    }
    /* --end GET /analyzepdf/sftpdata/{id}/pdf -- */    
}
