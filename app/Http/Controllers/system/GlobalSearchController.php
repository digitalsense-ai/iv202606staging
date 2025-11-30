<?php

namespace App\Http\Controllers\system;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

use App\Models\VATRegistration;
use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationSalesInvoices;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;
use \App\Classes\FtpClass;

class GlobalSearchController extends Controller
{
    public $authUser;
    
    public $commonClass;
    public $apiClass;
    public $ftpClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                     
            $this->commonClass = new CommonClass();
            $this->apiClass = new ApiClass();
            $this->ftpClass = new FtpClass();
            $this->authUser = $this->commonClass->getAuthUser();     
          
            return $next($request);
        });
    }      
   
    /* -- GET /global-search -- */
    public function index()
    {   
      try
      {    
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */                            
      
        /* -- GET COMPANY/CLIENT -- */
        $clients = $this->commonClass->getCompanyLazy();        
        $clients = $clients
                      ->filter(fn($client) => $client->status == 1)
                      ->map(function ($client) {                         
                          $client->vatregmain = $client->vatregmain->filter(fn($vrmain) => ($vrmain->status == 1 && $vrmain->product_type > 1));
                          return $client;
                      })
                      ->filter(fn($client) => $client->vatregmain->isNotEmpty()) // keep only clients with at least one item left
                      ->sortBy('client_name')
                      ->values();            
        /* --end GET COMPANY/CLIENT -- */

        /* -- GET PENDING BATCHES -- */
        $batches = DB::table('job_batches')->where('pending_jobs', '>', 0)->first();             
        $batchIds = [];
        if($batches)
        {
          foreach($batches as $key => $batch)
          {   
            if(isset($batch->id))             
              $batchIds[] = [
                'batchId' => $batch->id         
              ];
          }/* --end for VAT REG. -- */
        }
        /* --end GET PENDING BATCHES -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'globalsearch-view');
        /* --end LOG -- */

        /* -- RETURN VIEW -- */
        return view('content.globalsearch.index', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,
          'clients' => $clients,
          'batchIds' => $batchIds
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Global Search Controller',
            'method' => 'index',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }  
    }  
    /* --end GET /global-search -- */

    /* -- GET /global-search-refresh -- */
    public function refreshGlobalSearch(Request $request)
    {
      try
      {         
        $client_id = $request->client_id;
 
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();

        /* -- GET ALL VAT REG. FOR PRODUCT TYPE - 2/3 -- */
        $query = VATRegistration::with(['vatregmain','client',
                                  'importreconciliationcominvoices' => function($query) {                                   
                                    $query->where('data_from', '!=', 'ivf')
                                      ->where('data_from', '!=', 'ftp')
                                      ->orderBy('last_modified_at', 'desc')                                   
                                      ->get();
                                  }
                                ])
                                ->withCount('importreconciliationcominvoices')
                                ->withCount('importreconciliationsalesinvoices')
                                ->whereHas('vatregmain', function ($subquery) {
                                    $subquery->where('status', 1)
                                      ->where('product_type', 2)
                                      ->orWhere('product_type', 3)
                                      ->orWhere('product_type', 5); 
                                });
        if($client_id)        
          $vatregs = $query->whereHas('client', function ($subquery) use ($client_id) {                                        
              $subquery->whereIn('id', [$client_id]);
          });                 
        
        $vatregs = $query->get();
        /* --end GET ALL VAT REG. FOR PRODUCT TYPE - 2/3 -- */  

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'globalsearch-refresh',
          [
            'Client Name' => ($client_id) ? $vatregs->first()->client->client_name : 'All'
          ]
        );
        /* --end LOG -- */
        
        $batchIds = [];
        $result = [];

        $unique_countries = [];
      
        $from = 'global-search-refresh';
        $full_refresh = true;
        foreach($vatregs as $key => $vatreg)
        {    
          $client_name = $vatreg->client->client_name;
       
            $data = $this->commonClass->loadImportReconciliationDatasFromAzureDb($this->authUser, $vatreg, $from, $full_refresh);
            
            if($data)
            {              
              if($data['insert_invoices'] > 0)
                $batchIds[] = [                
                  'batchId' => $data['insert_invoices'],                
                ];
             
              if($data['result']) 
              {       
                if($result)                      
                  $result = array_merge($result, $data['result']);
                else     
                  $result = $data['result'];
              }
            }

            if(!in_array($vatreg->country, $unique_countries, true))
            {
                if (stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
                stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
                stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
                ) 
                {      
                    $which_folder = (strtolower(env('APP_URL')) === "https://app.intravat.cloud" || strtolower(config('app.url')) === "https://app.intravat.cloud") ? 'main' : 'archive';
                                       
                    /* -- READ XML FILE FROM FTP -- */
                    $ftpdata = $this->ftpClass->getImportReconciliationFilesFromFtp($vatreg, $this->authUser, $which_folder); 
                    /* --end READ XML FILE FROM FTP -- */
                    
                    /* -- READ XML FILE FROM E-FACTO -- */
                    if (stripos(strtolower($client_name), "noscomed") !== false ||
                        stripos(strtolower($client_name), "rexholm") !== false)                    
                      $ftpdata = $this->ftpClass->getImportReconciliationFilesFromFtp($vatreg, $this->authUser, $which_folder, true);
                    /* --end READ XML FILE FROM E-FACTO -- */
                  
                    if(!in_array($vatreg->country, $unique_countries, true))                
                        array_push($unique_countries, $vatreg->country);                    
                }
            } //read all at a time 
        }/* --end for VAT REG. -- */

        if($client_id)
        {
          session()->forget('gsResults'.$client_id);
          session()->save();
          session()->put('gsResults'.$client_id, $result);
        }
        else
        {
          session()->forget('gsResults');
          session()->save();
          session()->put('gsResults');
        }        

        /* -- RETURN JSON -- */
        return response()->json([
          'status' => 200,
          'message' => 'Done',
          'batchIds' => $batchIds,
          //'x' => $x
        ]);
        /* --end RETURN JSON -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Global Search Controller',
            'method' => 'refreshGlobalSearch',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    } 
    /* --end GET /global-search-refresh -- */

    /* -- GET /global-search-refresh/batch-status/{batch_id} -- */
    public function refreshGlobalSearchStatus(Request $request, $batch_id)
    {
      try
      {              
        $client_id = $request->client_id;

        if($client_id)
          $result = session()->get('gsResults'.$client_id);
        else
          $result = session()->get('gsResults');

        $pending_jobs = DB::table('job_batches')->where('options', '<>', 'a:0:{}')->where('pending_jobs', '>', 0)->sum('pending_jobs');          
        if($pending_jobs > 0)
          return response()->json(['status' => 'processing', 'pending_jobs' => $pending_jobs]); 
        else
        {          
          return response()->json(['status' => 'unknown', 'failed' => false]);  
        }
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Global Search Controller',
            'method' => 'refreshGlobalSearchStatus',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    } 
    /* --end GET /global-search-refresh/batch-status/{batch_id} -- */
}
