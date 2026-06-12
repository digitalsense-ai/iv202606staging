<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use \App\Classes\CommonClass;
use \App\Classes\FtpClass;

use App\Models\Client;
use App\Models\VATRegistrationMain;
use App\Models\VATRegistration;
use App\Models\InvoiceOcrPdf;
use App\Jobs\SplitPdfJob;
use App\Services\AzureStorageService;

use App\Services\MicrosoftMailService;
use App\Jobs\ProcessEmailJob;

use App\Repositories\ClientRepository;

use App\Jobs\ValidateOcrInvoicesJob;

use App\Helpers\DateHelper;

class AnalyzePdfController extends Controller
{
    public $authUser;

    public $commonClass;
    public $ftpClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
            
            $this->ftpClass = new FtpClass();

            return $next($request);
        });
    }   

    /* -- GET /analyzepdf -- */
    public function index()
    {       
        // $invoiceDate = DateHelper::parseInvoiceDate(
        //     '02/JUN/2026.'
        // );
        // dd($invoiceDate);

        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $analyzepdfs = InvoiceOcrPdf::
        //with(['client', 'client.vatregmain'])
                            orderBy('id', 'DESC')            
                            ->get(); 
      
        $vatregmains = VATRegistrationMain::with(['client'])
                        ->orderBy('id', 'ASC')
                        ->get();

        $only_org_no = $this->commonClass->OrgNoForOcr();
        // $syncclients = $analyzepdfs
        //                 //->where('client_id', 89)    
        //                 ->pluck('client')
        //                 ->filter() // remove nulls
        //                 ->unique('id')
        //                 ->filter(function ($client) use ($only_org_no) {
        //                     return $client->vatregmain->contains(function ($vat) use ($only_org_no) {
        //                         return in_array($vat->org_no, $only_org_no);
        //                     });
        //                 })
        //                 ->sortBy('client_name')
        //                 ->values();     

        $syncclients = $vatregmains
                        ->filter(function ($vatregmain) use ($only_org_no) {
                            return in_array($vatregmain->org_no, $only_org_no);
                        })
                        ->pluck('client')
                        ->filter()
                        ->unique('id')
                        ->sortBy('client_name')
                        ->values();   

        /* -- RETURN VIEW -- */
        return view('content.ocr.analyze', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,  
          'vatregmains' => $vatregmains,          
          'analyzepdfs' => isset($analyzepdfs) ? (($analyzepdfs) ? $analyzepdfs : NULL) : NULL,
          'syncclients' => $syncclients
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /analyzepdf -- */

    /* -- GET /analyzepdf/search -- */
    public function search()
    {   
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'analyzepdf-search');      
        /* --end PAGE CONFIG -- */

        $analyzepdfs = InvoiceOcrPdf::
        //with(['client'])
                            where('status', 'completed')
                            ->where('is_deleted', 0)
                            ->orderBy('id', 'ASC')            
                            ->get(); 
      
        $vatregmains = VATRegistrationMain::with(['client'])
                        ->orderBy('id', 'ASC')
                        ->get();

        /* -- RETURN VIEW -- */
        return view('content.ocr.search', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,  
          'vatregmains' => $vatregmains,          
          'analyzepdfs' => isset($analyzepdfs) ? (($analyzepdfs) ? $analyzepdfs : NULL) : NULL
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /analyzepdf/search -- */
}
