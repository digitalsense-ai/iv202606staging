<?php

namespace App\Http\Controllers\declaration;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use App\Jobs\ConvertImportReconcilationInvoiceCurrency;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;
use \App\Classes\CargoDeclarationClass;
use \App\Classes\FtpClass;

use App\Models\VATRegistration;
use \App\Models\ImportVatFiles;
use \App\Models\ImportReconciliationFiles;
use \App\Models\ImportReconciliationComInvoices;
use \App\Models\ImportReconciliationSalesInvoices;
use \App\Models\ImportReconciliationSalesInvoicesData;
use \App\Models\ImportReconciliationSalesInvoicesDataItems;
use App\Models\JobLog;

use App\Events\ImportReconciliationSalesInvoiceDisregardEvent;

use Webklex\IMAP\Facades\Client as MailBoxClient;
use Storage;

class DeclarationController extends Controller
{  
    public $authUser;
    public $clientIds;

    public $commonClass;
    public $apiClass;
    public $cargoDeclarationClass;
    public $ftpClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   

            //GET CLIENT IDs based on VAT Reg. for Team User           
            if($this->authUser->role == 'team-user')            
              $this->clientIds = $this->commonClass->getClientIdsFromVatReg($this->authUser);    
            else if($this->authUser->role == 'client-user')            
              $this->clientIds = $this->commonClass->getClientIdsForClientUser($this->authUser);            
            else            
              $this->clientIds = $this->commonClass->getClientIdsFromClient();

            $this->apiClass = new ApiClass();
            $this->cargoDeclarationClass = new CargoDeclarationClass();
            $this->ftpClass = new FtpClass();
            
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */   
    public function dummy(Request $request)
    {
      try 
      {          
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'declaration');         

          return view('content.declaration.dummy', 
            [
              'pageConfigs' => $pageConfigs, 
              'pageName' => 'declaration-page',
              'authUser' => $this->authUser,             
            ]
          );
        }
        catch (\Exception $e) 
        {
          dd($e);
          return  $e->getMessage();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */   
    public function index(Request $request, $vat_reg_id)
    {
      // try 
      // {          
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'declaration');
        
        $vatreg = VATRegistration::where('id', $vat_reg_id)->first();
        $client_id = $vatreg->client_id;

        $show = true;
        if(($this->authUser->role == 'client-user') && !in_array($client_id, $this->clientIds))
          $show = false;      

        if($show)                          
        {
          $declarations = $this->reloadDeclarations($vat_reg_id, true);   

          return view('content.declaration.index', 
            [
              'pageConfigs' => $pageConfigs, 
              'pageName' => 'declaration-page',
              'authUser' => $this->authUser,
              'declarations' => $declarations,  

              'currency_code' => isset($declarations['currency_code']) ? $declarations['currency_code'] : NULL,                    
              'from_currencies' => isset($declarations['from_currencies']) ? $declarations['from_currencies'] : NULL,  
              'todays_rate' => isset($declarations['todays_rate']) ? $declarations['todays_rate'] : NULL,

              'last_exchange_rates' => isset($declarations['last_exchange_rates']) ? $declarations['last_exchange_rates'] : NULL             
            ]
          );
        }
        else
          return abort(403, 'User does not have the right to access this page.');  
      // }
      // catch (\Exception $e) 
      // {
      //   dd($e);
      //   return  $e->getMessage();
      // }
    }

    public function reloadDeclarations($vat_reg_id, $auto_rematch = false)
    {    
        try
        {
          $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);

          $client_id = $vatreg->client_id;

          if($auto_rematch)
          {
            $rematch = $this->commonClass->rematchComInvoices($client_id);

            if($vatreg->country == 'NO')
            {
              $cominvoices = ImportReconciliationComInvoices::with(['vatreg', 'vatreg.client', 'relationmatchno'])                 
                  ->where('vat_reg_id', $vat_reg_id)                             
                  ->whereHas('relationmatchno', function ($query) {
                    $query->whereNotNull('relation_match_no'); 
                  })
                  ->whereDoesntHave('relationmatchno', function ($query) {
                      $query->whereColumn('com_invoice_id', 'dv_import_reconciliation_com_invoices.id');
                  })
                  ->get();
                   
              foreach($cominvoices as $cominvoice)       
              {  
                $com_invoice_id = $cominvoice->id;

                $salesinvoices = ImportReconciliationSalesInvoices::where('com_invoice_id', $com_invoice_id)->get();

                if($salesinvoices)
                {
                  foreach($salesinvoices as $salesinvoice)
                  {
                    $previous_cominvoiceid = $salesinvoice->com_invoice_id;

                    $salesinvoice->com_invoice_id = $com_invoice_id;
                    $salesinvoice->save();

                    $this->commonClass->addLog($this->authUser, 'importreconcilation-sales-invoice-relation-rematch', 
                      [
                        'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,   
                        'Re-matched' => "Relation match for sales invoice <b><u>" . $salesinvoice->invoice_no . "</u></b> changed from <b><u>" . $previous_cominvoiceid . "</u></b> to <b><u>" . $com_invoice_id . "</u></b><br>"       
                      ]
                    );
                  }
                }           
              }//for loop
            }//NO  
          }

          /* -- GET ALL COM. INVOICES FOR THE CLIENT -- */
          $other_period_importreconciliationcominvoices = ImportReconciliationComInvoices::with(['vatreg', 'vatreg.client'])
                ->select('id', 'vat_reg_id', 'invoice_no')
                ->whereHas('vatreg.client', function ($subquery) use($client_id) {                                        
                    $subquery->where('id', $client_id);
                })           
                ->whereNot('data_from', 'ivf')
                ->whereNot('data_from', 'ftp')          
                ->get();            
          /* --end GET ALL COM. INVOICES FOR THE CLIENT -- */

          /* -- GET IVF same COM. INVOICES FOR THE CLIENT -- */
          $other_period_ivf_group = ImportReconciliationComInvoices::with(['vatreg', 'vatreg.client'])
                          ->select('invoice_no', DB::raw('COUNT(*) as invoice_count'),                            
                            DB::raw('GROUP_CONCAT(id ORDER BY id ASC) as ids'),
                            DB::raw('GROUP_CONCAT(vat_reg_id ORDER BY vat_reg_id ASC) as vat_reg_ids')
                          )                         
                          ->whereHas('vatreg.client', function ($subquery) use($client_id) {                                        
                              $subquery->where('id', $client_id);
                          })           
                          ->whereNotNull('invoice_no')  
                          ->where('data_from', 'ivf')   
                          ->groupBy('invoice_no')
                          ->having('invoice_count', '>', 1)            
                          ->get();  

          // Convert to Collection
          $arr_other_period_ivf_group = $other_period_ivf_group->flatMap(function ($invoice) {
              $ids = explode(',', $invoice->ids);
              $vat_reg_ids = explode(',', $invoice->vat_reg_ids);
              
              $result = [];
              $count = min(count($ids), count($vat_reg_ids)); // Ensure matching pairs

              for ($i = 0; $i < $count; $i++) {
                  $result[] = [
                      'invoice_no' => $invoice->invoice_no,
                      'invoice_count' => $invoice->invoice_count,
                      'ids' => $ids[$i],
                      'vat_reg_ids' => $vat_reg_ids[$i]
                  ];
              }
              
              return $result;
          });
          $other_period_ids = $arr_other_period_ivf_group->pluck('ids')->toArray();
          
          $other_period_ivf_importreconciliationcominvoices = ImportReconciliationComInvoices::with(['vatreg', 'vatreg.client'])
                ->select('id', 'vat_reg_id', 'invoice_no')
                ->whereHas('vatreg.client', function ($subquery) use($client_id) {                                        
                    $subquery->where('id', $client_id);
                })           
                ->whereIn('id', $other_period_ids)                
                ->get(); 

          $other_period_importreconciliationcominvoices = $other_period_importreconciliationcominvoices->merge($other_period_ivf_importreconciliationcominvoices);                
          /* --end GET IVF same COM. INVOICES FOR THE CLIENT -- */                 

          $declarations = $this->commonClass->getSpecificVatRegQuery($vat_reg_id);           

          $system = $this->commonClass->getSystemInfoLazy();
          $systemapi = $system->systemapi->first();

          $importvatfiles = $declarations->importvatfiles;
          foreach($importvatfiles as $importvatfile)       
          {       
            if($importvatfile->file_type == 'xml')
            {
              if($importvatfile->file_id != NULL)
              {              
                $importvatfileName = $this->apiClass->loadFromOneDriveLazy($importvatfile, $systemapi);              
                if(isset($importvatfileName->error))   
                {

                } 
                else    
                  $importvatfile->xml = $this->apiClass->xmlExtractByLine($importvatfile,$importvatfileName['download_url']);    
              } 
            }                   
          }
          
          $declarations['other_period_importreconciliationcominvoices'] = $other_period_importreconciliationcominvoices;

          /*Currrency conversion*/
          if($vatreg->country == 'CH')
          {
            $currency_code = 'CHF';
            $from_currencies = $declarations->importreconciliationcominvoices->pluck('currency_code')
                                ->filter(fn($code) => $code !== $currency_code)
                                ->unique()
                                ->values();
                                
            if(count($from_currencies) > 0)
            {
              $fetch_currency = $from_currencies->implode(', ');

              $todays_rate = [];
              if($fetch_currency != '') 
                $todays_rate = $this->apiClass->rssExtractExchangeRate('https://www.nationalbanken.dk/api/currencyratesxml?lang=en', $fetch_currency, $currency_code);
              
              $declarations['currency_code'] = $currency_code;
              $declarations['from_currencies'] = $fetch_currency;
              $declarations['todays_rate'] = $todays_rate; 

              $last_exchange_rates = $declarations->importreconciliationcominvoices
                                      ->groupBy('month_year')
                                      ->map(function ($group) {
                                          return $group
                                              ->pluck('exchange_rate')
                                              ->filter(fn($value) => !is_null($value))
                                              ->unique()
                                              ->first();
                                      });

              $declarations['last_exchange_rates'] = $last_exchange_rates; 
            }         
          }
          /*Currrency conversion*/

          return $declarations;
        }
        catch (\Exception $e) 
        {
          dd($e);
          return  $e->getMessage();
        }
    }

    public function refreshGlobalSearch($vat_reg_id)
    {    
        try
        {
          /* -- GET VAT REG. FOR PRODUCT TYPE - 2/3 -- */
          $vatreg = VATRegistration::with(['vatregmain','client',
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
                                      $subquery->where('product_type', 2)
                                        ->orWhere('product_type', 3)
                                        ->orWhere('product_type', 5); 
                                  })
                                  ->where('id', $vat_reg_id)
                                  ->first();
          /* --end GET ALL VAT REG. FOR PRODUCT TYPE - 2/3 -- */ 

          if($vatreg)
          {            
            $from = 'specific-global-search-refresh';
         
            $data = $this->commonClass->loadImportReconciliationDatasFromAzureDb($this->authUser, $vatreg, $from);
            
            $batchIds = [];
            if($data > 0)
            {           
              $batchIds[] = [
                'batchId' => $data              
              ];
            } 

            /* -- FTP DATA's -- */
            if(strtolower(env('APP_URL')) === "https://app.intravat.cloud" || strtolower(config('app.url')) === "https://app.intravat.cloud")
            {
              $client_name = $vatreg->client->client_name;
              if (stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
              stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
              stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
              ) 
              {      
                  $which_folder = 'main';
                                     
                  /* -- READ XML FILE FROM FTP -- */
                  $ftpdata = $this->ftpClass->getImportReconciliationFilesFromFtp($vatreg, $this->authUser, $which_folder); 
                  /* --end READ XML FILE FROM FTP -- */
                  
                  /* -- READ XML FILE FROM E-FACTO -- */
                  if (stripos(strtolower($client_name), "noscomed") !== false ||
                      stripos(strtolower($client_name), "rexholm") !== false)                    
                    $ftpdata = $this->ftpClass->getImportReconciliationFilesFromFtp($vatreg, $this->authUser, $which_folder, true);
                  /* --end READ XML FILE FROM E-FACTO -- */                                
              }
            }
            /* --end FTP DATA's -- */

            /* -- RETURN JSON -- */
            return response()->json([
              'status' => 200,
              'message' => 'Done',
              'batchIds' => $batchIds
            ]);
            /* --end RETURN JSON -- */
          }
          else
            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'Error',                 
              'message' => 'No such VAT reg.'
            ]);
            /* --end RETURN JSON -- */ 
        }
        catch (\Exception $e) 
        {         
          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'error-log', 
            [
              'status' => 'Error',
              'controller' => 'Declaration Controller',
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

    /* -- GET /declaration-invoice/{vat_reg_id}/batch-status/{batch_id} -- */
    public function refreshGlobalSearchStatus($vat_reg_id, $batch_id)
    {
      try
      {            
        $pending_jobs = DB::table('job_batches')
                          ->where('id', $batch_id)
                          ->where('options', '<>', 'a:0:{}')
                          ->where('pending_jobs', '>', 0)
                          ->sum('pending_jobs');

        if($pending_jobs > 0)
          return response()->json(['status' => 'processing', 'pending_jobs' => $pending_jobs]); 
        else
        {
          if($batch_id)
          {
            $result['declarations'] = $this->reloadDeclarations($vat_reg_id, true); 

            return response()->json(['status' => 'unknown', 'failed' => false, 'result' => $result]);  
          }
          else
            dd("no batch ids");
        }        
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Declaration Controller',
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
    /* --end GET /declaration-invoice/{vat_reg_id}/batch-status/{batch_id} -- */

    //GET cargo-declaration-files/{import_vat_id}
    public function cargoDeclarationFiles(Request $request, $import_vat_id)
    {        
      $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'invoice');
                  
      $cargodeclarationfiles = $this->commonClass->getCargoDeclarationFilesLazy($import_vat_id);    
      
      $this->commonClass->addLog($this->authUser, 'invoice-view', 
        [
          'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,          
        ]
      );

      return view('content.declaration.cargo', 
        [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,           
          'cargodeclarationfiles' => $cargodeclarationfiles,             
        ]
      );      
    }    

    //POST declaration-invoice/$invoice_id/disregard (BOTH COMMENT & DISREGARD/ENABLE/RETAIN)
    public function invoiceDisregard(Request $request, $invoice_id)
    {
      try
      { 
        $is_disregard = $request->is_disregard;
        $disregard_type = $request->disregard_type;

        $is_enable = $request->is_enable;
        $invoice_name = $request->invoice_name;

        $selected_invoice_ids = ($invoice_id == '0') ? $request->invoice_id : $invoice_id;
        $selected_invoices = $request->invoice_no;

        $log_name_text_suffix = 'comment-add';
        foreach (explode(',', $selected_invoice_ids) as $invoice_id)
        {
          if($invoice_name == 'com')
          {
            $invoice_name_text = 'Commercial Invoices';
            $log_name_text = 'com-invoice';

            if($disregard_type)
            {
              if($disregard_type == 'ivf')
              {
                $log_name_text = 'xml-com-invoice';
                $log_name_text_suffix = 'disregard';

                $chk_invoice_nos = $request->chk_invoice_no;

                foreach($chk_invoice_nos as $chk_invoice_no)
                {
                  $invoice = ImportReconciliationComInvoices::where('id', $chk_invoice_no)->first();  

                  $invoice->disregard_invoice = 1;
                  $invoice->disregard_type = $disregard_type;
                  $invoice->disregard_reason = $request->declaration_invoice_disregard;
                  $invoice->disregard_comment = $request->declaration_invoice_disregard_comment_quill;  

                  $invoice->save(); 
                } //loop wrong XML com. invoice
              } //disregard_type IVF
              else if($disregard_type == 'lopeno')
              {
                $invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();  

                if($is_disregard == "1")
                {
                  $log_name_text_suffix = 'lopeno-disregard';

                  $invoice->disregard_invoice = 1;
                  $invoice->disregard_type = $disregard_type;
                  $invoice->disregard_reason = $request->declaration_invoice_disregard;
                  $invoice->disregard_comment = $request->declaration_invoice_disregard_comment_quill;
                }
                else if($is_enable == "1")
                {
                  $log_name_text_suffix = 'lopeno-retain';

                  $invoice->disregard_invoice = 0;
                  $invoice->disregard_type = NULL;
                  $invoice->disregard_reason = NULL;
                  $invoice->disregard_comment = NULL; 
                }

                $invoice->save();                  
              } //disregard_type lopeno
            } // XML Com. invoice disregard
            else
            {
              $invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();  
              if($is_disregard == "1")
              {           
                $log_name_text_suffix = 'disregard';

                $invoice->disregard_invoice = 1;                
                $invoice->disregard_reason = $request->declaration_invoice_disregard;
                $invoice->disregard_comment = $request->declaration_invoice_disregard_comment_quill;
              } // Com. invoice disregard
              else if($is_enable == "1")
              {
                $log_name_text_suffix = 'retain';

                $invoice->disregard_invoice = 0;
                $invoice->disregard_type = NULL;
                $invoice->disregard_reason = NULL;
                $invoice->disregard_comment = NULL; 
              }
              else
              {                
                $invoice->comment_visiblity = ($request->comment_visiblity) ? ((strtolower($request->comment_visiblity) == 'public') ? 1 : 2) : 1;      
                $invoice->comment_reason = $request->declaration_invoice_disregard;
                $invoice->comment = $request->declaration_invoice_disregard_comment_quill; 
              } // Com. invoice comment
            }
          }
          else if($invoice_name == 'sales')
          {
            $invoice_name_text = 'Sales Invoices';
            $log_name_text = 'sales-invoice';

            $invoice = ImportReconciliationSalesInvoices::where('id', $invoice_id)->first();       

            if($is_disregard == "1")
            {
              $log_name_text_suffix = 'disregard';

              $invoice->disregard_invoice = 1;
              $invoice->disregard_reason = $request->declaration_invoice_disregard;
              $invoice->disregard_comment = $request->declaration_invoice_disregard_comment_quill;  
            } // Sales invoice disregard
            else if($is_enable == "1")
            {
              $log_name_text_suffix = 'enable';

              $invoice->disregard_invoice = 0;
              $invoice->disregard_reason = NULL;
              $invoice->disregard_comment = NULL;  
            } // Sales invoice enable
            else
            {
              $invoice->comment_visiblity = ($request->comment_visiblity) ? ((strtolower($request->comment_visiblity) == 'public') ? 1 : 2) : 1;

              $invoice->comment_reason = $request->declaration_invoice_disregard;
              $invoice->comment = $request->declaration_invoice_disregard_comment_quill;     
            } // Sales invoice comment
          }
          else
          {
            $ivf_id = $invoice_id;
            $invoice_name_text = 'Declaration';
            $log_name_text = 'declaration';

            $invoice = ImportVatFiles::where('id', $ivf_id)->first();   

            $invoice->comment_visiblity = ($request->comment_visiblity) ? ((strtolower($request->comment_visiblity) == 'public') ? 1 : 2) : 1;

            $invoice->comment_reason = $request->declaration_invoice_disregard;
            $invoice->comment = $request->declaration_invoice_disregard_comment_quill;                 
          } // Declaration comment

          if($disregard_type)
          {

          }
          else
            $invoice->save(); 
        }       

        $vat_reg_id = ($request->invoice_vat_reg_id) ? $request->invoice_vat_reg_id : $request->vat_reg_id;            
        $vatreg = $this->reloadDeclarations($vat_reg_id);   

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $selected_invoices,
            'Invoice Name' => $invoice_name_text
          ]
        );

        if($is_disregard == "1")
        {
          // Broadcast the event              
          event(new ImportReconciliationSalesInvoiceDisregardEvent($vat_reg_id, 'Disregarded the sales invoice'));
        }

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->tab_name
          ]
        );  
      }
      catch (\Exception $e) {
          return  $e->getMessage();
        }
    }

    //DELETE declaration-invoice/$invoice_id/deletecomment
    public function invoiceDeleteComment(Request $request, $invoice_id)
    {
      try
      {
        $invoice_name = $request->invoice_name;
        $selected_invoices = $request->invoice_no;

        $log_name_text_suffix = 'comment-delete';
        if($invoice_name == 'com')
        {
          $invoice_name_text = 'Commercial Invoices';
          $log_name_text = 'com-invoice';

          $invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();              
        }
        else if($invoice_name == 'sales')
        {
          $invoice_name_text = 'Sales Invoices';
          $log_name_text = 'sales-invoice';

          $invoice = ImportReconciliationSalesInvoices::where('id', $invoice_id)->first();       
        }
        else
        {
          $ivf_id = $invoice_id;
          $invoice_name_text = 'Declaration';
          $log_name_text = 'declaration';

          $invoice = ImportVatFiles::where('id', $ivf_id)->first();                        
        }
        
        $invoice->comment_reason = NULL;
        $invoice->comment = NULL;  
        $invoice->comment_visiblity = 0;  

        $invoice->save(); 

        $vat_reg_id = $request->vat_reg_id;
       
        $vatreg = $this->reloadDeclarations($vat_reg_id);  

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $selected_invoices,
            'Invoice Name' => $invoice_name_text
          ]
        );

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->tab_name
          ]
        );  
      }
      catch (\Exception $e) {
        return  $e->getMessage();
      }
    }   
    
    /* -- POST declaration-invoice/$invoice_id/rematch  -- */ 
    public function invoiceRematch(Request $request, $invoice_id)
    {
      try
      {   
        $invoice_name = $request->rematch_invoice_name;
        $no_of_split = $request->no_of_split;        

        $selected_invoice_ids = ($invoice_id == '0') ? $request->rematch_invoice_id : $invoice_id;
        $selected_invoices = $request->rematch_invoice_no;

        $rematch_invoice_id = $request->declaration_cominvoice_rematch;

        $log_name_text_suffix = 'rematch';
        foreach (explode(',', $selected_invoice_ids) as $key => $invoice_id)
        {
          //insert new rows no. of times 
          if($no_of_split)
          {
            if($key == 0)
            {
              $insert_invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first(); 

              if($insert_invoice->no_of_split)
              {

              }
              else
              {
                $insert_invoice->unmatch = ($insert_invoice->unmatch) ? 0 : $insert_invoice->unmatch;
                $insert_invoice->no_of_split = $no_of_split; 
                $insert_invoice->save();

                for($i = 0; $i < ($no_of_split-1); $i++)
                {
                  if($insert_invoice)
                  {
                    $newRow = $insert_invoice->replicate();  
                    
                    $newRow->unmatch = ($newRow->unmatch) ? 0 : $newRow->unmatch;
                    $newRow->data_from = 'replicate';
                    $newRow->save();
                  }
                }
              }
            } //one time only
          }
          //insert new rows no. of times 

          if($invoice_name == 'com')
          {
            $invoice_name_text = 'Commercial Invoices';
            $log_name_text = 'com-invoice';

            $invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();    
          
            $invoice->unmatch = ($invoice->unmatch) ? 0 : $invoice->unmatch;
            $invoice->rematch_com_invoice_id = $rematch_invoice_id;     
            $invoice->save(); 

            //get rematch invoice 
            $rematch_invoice = ImportReconciliationComInvoices::where('id', $rematch_invoice_id)->first();
            if($rematch_invoice->month_year != $invoice->month_year)
            {     
              $rematch_invoice->unmatch = ($rematch_invoice->unmatch) ? 0 : $rematch_invoice->unmatch;
              $rematch_invoice->month_year = $invoice->month_year;
              $rematch_invoice->vat_reg_id = $invoice->vat_reg_id;
              $rematch_invoice->save();

              $salesinvoices = ImportReconciliationSalesInvoices::where('com_invoice_id', $rematch_invoice->id);
              if($salesinvoices)
              {                                
                  $update_sales = ImportReconciliationSalesInvoices::where('com_invoice_id', $rematch_invoice->id)
                                      ->update(['vat_reg_id' => $invoice->vat_reg_id]);                  
              }
            }
            //month year varies
          }
        }       

        $vat_reg_id = $request->rematch_invoice_vat_reg_id;
       
        $vatreg = $this->reloadDeclarations($vat_reg_id);  

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $selected_invoices,
            'Invoice Name' => $invoice_name_text
          ]
        );
        
        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->rematch_tab_name
          ]
        );  
      }
      catch (\Exception $e) {
        return  $e->getMessage();
      }
    }
    /* --end POST declaration-invoice/$invoice_id/rematch  -- */ 

    /* -- DELETE declaration-invoice/$invoice_id/rematch  -- */ 
    public function invoiceRemoveRematch(Request $request, $invoice_id)
    {
      try
      {
        $group_invoice_ids = $request->group_invoice_ids;
        $selected_invoices = $request->invoice_no;
        $invoice_name = $request->invoice_name;

        $log_name_text_suffix = 'rematch-delete';
        
        $invoice_name_text = 'Commercial Invoices';
        $log_name_text = 'com-invoice';
        
        if(stripos($group_invoice_ids, "***") !== false)
        {
          $group_invoice_id = explode("***", $group_invoice_ids);
          foreach ($group_invoice_id as $invoice_id) 
          {
            $invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();

            $invoice->unmatch = ($request->unmatch) ? $request->unmatch : $invoice->unmatch;
            $invoice->rematch_com_invoice_id = NULL;        
            $invoice->save();  
          }
        } 
        else 
        {
          $invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();              
        
          $invoice->unmatch = ($request->unmatch) ? $request->unmatch : $invoice->unmatch;
          $invoice->rematch_com_invoice_id = NULL;        
          $invoice->save(); 
        }

        $vat_reg_id = $request->vat_reg_id;
     
        $vatreg = $this->reloadDeclarations($vat_reg_id);  

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $selected_invoices,
            'Invoice Name' => $invoice_name_text
          ]
        );

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->tab_name
          ]
        );  
      }
      catch (\Exception $e) {
        return  $e->getMessage();
      }
    }  
    /* --end DELETE declaration-invoice/$invoice_id/rematch  -- */ 
   
    /* -- DELETE declaration-invoice/$invoice_id  -- */ //(DELETE PERMANENTLY)
    public function invoiceDelete(Request $request, $invoice_id)
    {
      try
      {
        $invoice_name = $request->invoice_name;
        $selected_invoices = $request->invoice_no;

        $log_name_text_suffix = 'delete';
        
        $invoice_name_text = ($invoice_name == 'com') ? 'Commercial Invoices' : 'Sales Invoices';
        $log_name_text = ($invoice_name == 'com') ? 'com-invoice' : 'sales-invoice';

        if($invoice_name == 'com')
        {
          $cominvoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();
        }
        
        $vat_reg_id = $request->vat_reg_id;    
        $vatreg = $this->reloadDeclarations($vat_reg_id);  

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $selected_invoices,
            'Invoice Name' => $invoice_name_text
          ]
        );

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->tab_name
          ]
        );  
      }
      catch (\Exception $e) {dd($e);
        return  $e->getMessage();
      }
    }  
    /* --end DELETE declaration-invoice/$invoice_id  -- */ 

    /* -- POST declaration-invoice/$invoice_id/move  -- */ 
    public function invoiceMove(Request $request, $invoice_id)
    {
      try
      {   
        $com_invoice_id = $request->move_cominvoice_id;
        $com_invoice_no = $request->move_cominvoice_no;

        $invoice_name = $request->move_invoice_name;

        $selected_invoice_ids = ($invoice_id == '0') ? $request->move_invoice_id : $invoice_id;
        $selected_invoices = $request->move_invoice_no;

        $move_invoice_id = $request->declaration_cominvoice_move;        

        $log_name_text_suffix = 'move';
        foreach (explode(',', $selected_invoice_ids) as $invoice_id)
        {
          if($invoice_name == 'sales')
          {
            $invoice_name_text = 'Sales Invoices';
            $log_name_text = 'sales-invoice';

            $com_invoice = ImportReconciliationComInvoices::where('id', $move_invoice_id)->first();    

            $invoice = ImportReconciliationSalesInvoices::where('id', $invoice_id)->first();    
            
            $invoice->vat_reg_id = ($invoice->vat_reg_id == $com_invoice->vat_reg_id) ? $invoice->vat_reg_id : $com_invoice->vat_reg_id;   
            $invoice->com_invoice_id = $move_invoice_id; 
            $invoice->save();  

            $ir_file = ImportReconciliationFiles::where('invoice_no', $selected_invoices)->first(); 
            $ir_file->vat_reg_id = ($ir_file->vat_reg_id == $com_invoice->vat_reg_id) ? $ir_file->vat_reg_id : $com_invoice->vat_reg_id;  
            $ir_file->save();             
          }
        }       

        $vat_reg_id = $request->move_invoice_vat_reg_id;      
        $vatreg = $this->reloadDeclarations($vat_reg_id);  

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $selected_invoices,
            'Invoice Name' => $invoice_name_text
          ]
        );
        
        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->move_tab_name
          ]
        );  
      }
      catch (\Exception $e) {
          return  $e->getMessage();
        }
    }
    /* --end POST declaration-invoice/$invoice_id/move  -- */    

    /* -- GET declaration-invoice/$invoice_id/edit  -- */ 
    public function invoiceEdit(Request $request, $invoice_id)
    {
      try
      {
        $xmlData = [];

        $vat_reg_id = $request->vat_reg_id;
        $file_id = $request->invoice_xml_id;
        $tab_name = $request->tab_name;

        if($file_id)
        {
          if($request->edit_from == 'xml')
          {             
            $file = $this->commonClass->getImportReconciliationFilesLazy($file_id); 

            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();  

            $downloadfile = $this->apiClass->loadFromOneDriveLazy($file, $systemapi);  

            if(isset($downloadfile->error))   
              return '';
            else  
            {            
              $xmlData = $this->commonClass->generateSalesInvoicePdfFromXml($downloadfile);             
            }
          } // from XML
          else
          {
            $salesinvoicedata = ImportReconciliationSalesInvoicesData::with('items')
                                  ->where('id', $request->edit_from)->first();
           
            if($salesinvoicedata->note)
            {
              $invoices = [];
              foreach ($salesinvoicedata->items as $salesinvoicesdataitem) 
              {          
                $invoices[] = [
                  'no' => $salesinvoicesdataitem->item_no,
                  'qty' => $salesinvoicesdataitem->qty,
                  'unit_code' => $salesinvoicesdataitem->unit_code,
                  'line_amount' => $salesinvoicesdataitem->line_amount,
                  'accounting_cost' => $salesinvoicesdataitem->accounting_cost,
                  'order_no' => $salesinvoicesdataitem->item_order_no,
                  'tax_amount' => $salesinvoicesdataitem->tax_amount,
                  'net_amount' => $salesinvoicesdataitem->net_amount,
                  'tax_percent' => $salesinvoicesdataitem->tax_percent,
                  'tax_name' => $salesinvoicesdataitem->tax_name,

                  'item_name' => $salesinvoicesdataitem->item_name,
                  'item_desc' => $salesinvoicesdataitem->item_desc,
                  'seller_item_id' => $salesinvoicesdataitem->seller_item_id,
                  'seller_item_schema' => $salesinvoicesdataitem->seller_item_schema,
                  'std_item_id' => $salesinvoicesdataitem->std_item_id,
                  'std_item_schema' => $salesinvoicesdataitem->std_item_schema,

                  'price' => $salesinvoicesdataitem->price,
                  'base_qty' => $salesinvoicesdataitem->base_qty
                ];
              }

              $xmlData = [
                'invoice_no' => $salesinvoicedata->invoice_no,
                'footer_note' => $salesinvoicedata->note,
                'currency_code' => $salesinvoicedata->currency_code,

                'invoice_date' => $salesinvoicedata->invoice_date, 
                'order_no' => $salesinvoicedata->order_no,

                'sender' => [
                    'website' => $salesinvoicedata->sender_website,
                    'endpoint' => $salesinvoicedata->sender_endpoint,
                    'name' => $salesinvoicedata->sender_name,
                    'street' => $salesinvoicedata->sender_street,
                    'houseno' => $salesinvoicedata->sender_houseno,
                    'city' => $salesinvoicedata->sender_city,
                    'postcode' => $salesinvoicedata->sender_postcode,
                    'email' => $salesinvoicedata->sender_email,
                    'countrycode' => $salesinvoicedata->sender_countrycode,
                    'vat_no' => $salesinvoicedata->sender_vatno,
                    'contact' => [
                      'id' => $salesinvoicedata->sender_contact_id,
                      'name' => $salesinvoicedata->sender_contact_name,
                      'telephone' => $salesinvoicedata->sender_contact_telephone,
                      'email' => $salesinvoicedata->sender_contact_email
                    ],
                  ],

                  'buyer' => [
                    'website' => $salesinvoicedata->buyer_website,
                    'endpoint' => $salesinvoicedata->buyer_endpoint,
                    'name' => $salesinvoicedata->buyer_name,
                    'street' => $salesinvoicedata->buyer_street,
                    'houseno' => $salesinvoicedata->buyer_houseno,
                    'city' => $salesinvoicedata->buyer_city,
                    'postcode' => $salesinvoicedata->buyer_postcode,
                    'email' => $salesinvoicedata->buyer_email,
                    'countrycode' => $salesinvoicedata->buyer_countrycode,
                    'vat_no' => $salesinvoicedata->buyer_vatno,
                    'contact' => [
                      'id' => $salesinvoicedata->buyer_contact_id,
                      'name' => $salesinvoicedata->buyer_contact_name,
                      'telephone' => $salesinvoicedata->buyer_contact_telephone,
                      'email' => $salesinvoicedata->buyer_contact_email
                    ]
                  ],

                  'delivery' => [
                    'date' => $salesinvoicedata->delivery_date,               
                    'street' => $salesinvoicedata->delivery_street,
                    'houseno' => $salesinvoicedata->delivery_houseno,
                    'city' => $salesinvoicedata->delivery_city,
                    'postcode' => $salesinvoicedata->delivery_postcode,                
                    'countrycode' => $salesinvoicedata->delivery_countrycode                
                  ],

                  'payment_means' => [
                    'id' => $salesinvoicedata->payment_id,
                    'branch_id' => $salesinvoicedata->payment_branch_id,
                    'due_date' => $salesinvoicedata->payment_due_date,                                  
                    'institute_name' => $salesinvoicedata->payment_institute_name,
                    
                    'type_id' => $salesinvoicedata->payment_type_id,
                    'note' => $salesinvoicedata->payment_note,
                    'discount_percent' => $salesinvoicedata->payment_discount_percent,
                    'amount' => $salesinvoicedata->payment_amount,               
                    'currencycode' => $salesinvoicedata->payment_currency_code,                
                    'settlement_date' => $salesinvoicedata->payment_settlement_date,
                    'penalty_date' => $salesinvoicedata->payment_penalty_date
                  ],

                  'allowance_charge' => $salesinvoicedata->allowance_charge, 
                  'allowance_charge_currencycode' => $salesinvoicedata->allowance_charge_currency_code, 

                  'tax_total' => [
                    'amount' => $salesinvoicedata->tax_total_amount,
                    'tax_currencycode' => $salesinvoicedata->tax_total_amount_currency_code,
                    'net_amount' => $salesinvoicedata->tax_total_net_amount,               
                    'net_currencycode' => $salesinvoicedata->tax_total_net_amount_currency_code,                
                    'percent' => $salesinvoicedata->tax_total_percent,
                    'name' => $salesinvoicedata->tax_total_name
                  ],          

                  'monetary_total' => [
                    'line_amount' => $salesinvoicedata->total_line_amount,
                    'line_currencycode' => $salesinvoicedata->total_line_currency_code,
                    'tax_excl_amount' => $salesinvoicedata->total_tax_excl_amount,               
                    'tax_excl_currencycode' => $salesinvoicedata->total_tax_excl_currency_code,                
                    'tax_incl_amount' => $salesinvoicedata->total_tax_incl_amount,
                    'tax_incl_currencycode' => $salesinvoicedata->total_tax_incl_currency_code,
                    'payable_amount' => $salesinvoicedata->total_payable_amount,
                    'payable_currencycode' => $salesinvoicedata->total_payable_currency_code
                  ],
              
                  'invoices' => $invoices
              ];
            }//from TABLE
            else
            {
              $file = $this->commonClass->getImportReconciliationFilesLazy($file_id); 

              $system = $this->commonClass->getSystemInfoLazy(); 
              $systemapi = $system->systemapi->first();  

              $downloadfile = $this->apiClass->loadFromOneDriveLazy($file, $systemapi);  

              if(isset($downloadfile->error))   
                return '';
              else              
                $xmlData = $this->commonClass->generateSalesInvoicePdfFromXml($downloadfile);
            } // from XML
          }//from TABLE
          
          //Extract converted amount from note
          if($xmlData['currency_code'] != 'NOK')
          {
            $footer_note = trim($xmlData['footer_note']);
           
            if (stripos($footer_note, " alt ") !== false) 
            {
              $arr_footer_note = explode(' ', $footer_note);

              $indexes = array_keys($arr_footer_note, 'NOK');          
              $amountsAfterNOK = array_map(fn($i) => str_replace(['.', '\'', ','], ['', '', '.'], $arr_footer_note[$i + 1]) ?? null, $indexes);
              
              $xmlData['converted_currency_code'] = 'NOK';
              $xmlData['converted_net_amount'] = $amountsAfterNOK[0];
              $xmlData['converted_vat_amount'] = $amountsAfterNOK[1];
              $xmlData['converted_total_amount'] = $amountsAfterNOK[2];
            }
          } //Extract converted amount from note

          $xmlData['sales_invoice_id'] = $invoice_id;
          $xmlData['ftp_file_id'] = $file_id;       
          $xmlData['vat_reg_id'] = $vat_reg_id; 
          $xmlData['tab_name'] = $tab_name;    
         
          $sales_invoice_datas = view('_partials._content._importreconciliation.sales-invoice-create-edit', 
              compact(
                  'xmlData'
              )
          )->render();    
        } //EDIT
        else
        {
          $sales_invoice_id = $invoice_id;
          $invoice_no = $request->invoice_no;
          $invoice_date = $request->invoice_date;
          $month_year = $request->month_year;

          $sales_invoice_datas = view('_partials._content._importreconciliation.sales-invoice-create-edit', 
              compact(
                  'vat_reg_id', 'tab_name', 'sales_invoice_id', 'invoice_no', 'invoice_date', 'month_year'
              )
          )->render();   
        } //CREATE

        return response()->json(
            [                   
                'sales_invoice_datas' => $sales_invoice_datas               
            ]
        );
      }
      catch (\Exception $e) {
        return  $e->getMessage();
      }
    }  
    /* --end GET declaration-invoice/$invoice_id/edit  -- */ 

    /* -- POST declaration-invoice/$invoice_id/edit  -- */ 
    public function invoiceEditSave(Request $request, $invoice_id)
    {
      try
      {      //dd($request);                
        $ir_file_id = $request->ftp_file_id;
        $sales_invoice_data_id = $request->sales_invoice_data_id;

        $invoice_no = $request->data['invoice_no'];
        $invoice_date = $request->data['invoice_date'];
        $order_no = $request->data['order_no'];
        $currency_code = $request->data['currency_code'];
        $note = $request->data['footer_note'];

        $sender_name = $request->data['sender']['name'];
        $sender_street = $request->data['sender']['street'];
        $sender_houseno = $request->data['sender']['houseno'];
        $sender_city = $request->data['sender']['city'];
        $sender_postcode = $request->data['sender']['postcode'];
        $sender_countrycode = $request->data['sender']['countrycode'];
        $sender_vatno = $request->data['sender']['vat_no'];
        $sender_email = $request->data['sender']['email'];
        $sender_website = $request->data['sender']['website'];
        $sender_endpoint = $request->data['sender']['endpoint'];
        $sender_contact_name = $request->data['sender']['contact']['name'];
        $sender_contact_email = $request->data['sender']['contact']['email'];
        $sender_contact_telephone = $request->data['sender']['contact']['telephone'];

        $buyer_name = $request->data['buyer']['name'];
        $buyer_street = $request->data['buyer']['street'];
        $buyer_houseno = $request->data['buyer']['houseno'];
        $buyer_city = $request->data['buyer']['city'];
        $buyer_postcode = $request->data['buyer']['postcode'];
        $buyer_countrycode = $request->data['buyer']['countrycode'];
        $buyer_vatno = $request->data['buyer']['vat_no'];
        $buyer_email = $request->data['buyer']['email'];
        $buyer_website = $request->data['buyer']['website'];
        $buyer_endpoint = $request->data['buyer']['endpoint'];
        $buyer_contact_name = $request->data['buyer']['contact']['name'];
        $buyer_contact_email = $request->data['buyer']['contact']['email'];
        $buyer_contact_telephone = $request->data['buyer']['contact']['telephone'];

        $delivery_date = $request->data['delivery']['date'];
        $delivery_street = $request->data['delivery']['street'];
        $delivery_houseno = $request->data['delivery']['houseno'];
        $delivery_city = $request->data['delivery']['city'];
        $delivery_postcode = $request->data['delivery']['postcode'];
        $delivery_countrycode = $request->data['delivery']['countrycode'];

        $payment_id = $request->data['payment_means']['id'];
        $payment_branch_id = $request->data['payment_means']['branch_id'];
        $payment_due_date = $request->data['payment_means']['due_date'];
        $payment_institute_name = $request->data['payment_means']['institute_name'];
        $payment_type_id = $request->data['payment_means']['type_id'];
        $payment_note = $request->data['payment_means']['note'];
        $payment_discount_percent = $request->data['payment_means']['discount_percent'];
        $payment_amount = $request->data['payment_means']['amount'];
        $payment_currencycode = $request->data['payment_means']['currencycode'];
        $payment_settlement_date = $request->data['payment_means']['settlement_date'];
        $payment_penalty_date = $request->data['payment_means']['penalty_date'];

        $allowance_charge = $request->data['allowance_charge'];
        $allowance_charge_currency_code = $request->data['allowance_charge_currencycode'];

        $tax_total_amount = $request->data['tax_total']['amount'];
        $tax_total_amount_currency_code = $request->data['tax_total']['tax_currencycode'];
        $tax_total_net_amount = $request->data['tax_total']['net_amount'];
        $tax_total_net_amount_currency_code = $request->data['tax_total']['net_currencycode'];
        $tax_total_percent = $request->data['tax_total']['percent'];
        $tax_total_name = $request->data['tax_total']['name'];

        $total_line_amount = $request->data['monetary_total']['line_amount'];
        $total_line_currency_code = $request->data['monetary_total']['line_currencycode'];
        $total_tax_excl_amount = $request->data['monetary_total']['tax_excl_amount'];
        $total_tax_excl_currency_code = $request->data['monetary_total']['tax_excl_currencycode'];
        $total_tax_incl_amount = $request->data['monetary_total']['tax_incl_amount'];
        $total_tax_incl_currency_code = $request->data['monetary_total']['tax_incl_currencycode'];
        $total_payable_amount = $request->data['monetary_total']['payable_amount'];
        $total_payable_currency_code = $request->data['monetary_total']['payable_currencycode'];
     
        if(!$ir_file_id)
        {                    
          $vat_reg_id = $request->vat_reg_id;
          $month_year = $request->month_year;

          $insert_irfiles = ImportReconciliationFiles::updateOrCreate(  
            [
              'vat_reg_id' => $vat_reg_id,
              'invoice_no' => $invoice_no
            ],          
            [    
              'vat_reg_id' => $vat_reg_id,
              'invoice_no' => $invoice_no,
              'month_year' => $month_year,
              'created_by' => $this->authUser->id,
              'created_at' => now()
            ]
          );

          $ir_file_id = $insert_irfiles->id;
        }

        if($ir_file_id)
        {
          $insert_salesinvoicedata = ImportReconciliationSalesInvoicesData::updateOrCreate(
            [
              'ir_file_id' => $ir_file_id
            ],
            [    
              'invoice_no' => $invoice_no,
              'invoice_date' => $invoice_date,
              'order_no' => $order_no,
              'currency_code' => $currency_code,
              'note' => $note,

              'sender_name' => $sender_name,
              'sender_street' => $sender_street,
              'sender_houseno' => $sender_houseno,
              'sender_city' => $sender_city,
              'sender_postcode' => $sender_postcode,
              'sender_countrycode' => $sender_countrycode,
              'sender_vatno' => $sender_vatno,
              'sender_email' => $sender_email,
              'sender_website' => $sender_website,
              'sender_endpoint' => $sender_endpoint,
              'sender_contact_name' => $sender_contact_name,
              'sender_contact_email' => $sender_contact_email,
              'sender_contact_telephone' => $sender_contact_telephone,

              'buyer_name' => $buyer_name,
              'buyer_street' => $buyer_street,
              'buyer_houseno' => $buyer_houseno,
              'buyer_city' => $buyer_city,
              'buyer_postcode' => $buyer_postcode,
              'buyer_countrycode' => $buyer_countrycode,
              'buyer_vatno' => $buyer_vatno,
              'buyer_email' => $buyer_email,
              'buyer_website' => $buyer_website,
              'buyer_endpoint' => $buyer_endpoint,
              'buyer_contact_name' => $buyer_contact_name,
              'buyer_contact_email' => $buyer_contact_email,
              'buyer_contact_telephone' => $buyer_contact_telephone,

              'delivery_date' => $delivery_date,
              'delivery_street' => $delivery_street,
              'delivery_houseno' => $delivery_houseno,
              'delivery_city' => $delivery_city,
              'delivery_postcode' => $delivery_postcode,
              'delivery_countrycode' => $delivery_countrycode,

              'payment_id' => $payment_id,
              'payment_branch_id' => $payment_branch_id,
              'payment_due_date' => $payment_due_date,
              'payment_institute_name' => $payment_institute_name,
              'payment_type_id' => $payment_type_id,
              'payment_note' => $payment_note,
              'payment_discount_percent' => $payment_discount_percent,
              'payment_amount' => $payment_amount,
              'payment_currency_code' => $payment_currencycode,
              'payment_settlement_date' => $payment_settlement_date,
              'payment_penalty_date' => $payment_penalty_date,

              'allowance_charge' => $allowance_charge,
              'allowance_charge_currency_code' => $allowance_charge_currency_code,
              
              'tax_total_amount' => $tax_total_amount,
              'tax_total_amount_currency_code' => $tax_total_amount_currency_code,
              'tax_total_net_amount' => $tax_total_net_amount,
              'tax_total_net_amount_currency_code' => $tax_total_net_amount_currency_code,
              'tax_total_percent' => $tax_total_percent,
              'tax_total_name' => $tax_total_name,

              'total_line_amount' => $total_line_amount,
              'total_line_currency_code' => $total_line_currency_code,
              'total_tax_excl_amount' => $total_tax_excl_amount,
              'total_tax_excl_currency_code' => $total_tax_excl_currency_code,
              'total_tax_incl_amount' => $total_tax_incl_amount,
              'total_tax_incl_currency_code' => $total_tax_incl_currency_code,
              'total_payable_amount' => $total_payable_amount,
              'total_payable_currency_code' => $total_payable_currency_code,
              
              'created_by' => ($sales_invoice_data_id) ? $insert_salesinvoicedata->created_by : $this->authUser->id,
              'updated_by' => $this->authUser->id
            ]
          );

          $items = $request->data['invoices'];
          if(count($items) > 0)
          {
            foreach($items as $item)
            {
              $item_no = $item['no'];
              $item_order_no = $item['order_no'];
              $item_name = $item['item_name'];
              $item_desc = $item['item_desc'];
              $base_qty = $item['base_qty'];
              $qty = $item['qty'];
              $unit_code = $item['unit_code'];
              $tax_name = $item['tax_name'];
              $line_amount = $item['line_amount'];
              $accounting_cost = $item['accounting_cost'];
              $tax_amount = $item['tax_amount'];
              $net_amount = $item['net_amount'];
              $tax_percent = $item['tax_percent'];
              $price = $item['price'];

              $seller_item_id = $item['seller_item_id'];
              $seller_item_schema = $item['seller_item_schema'];
              $std_item_id = $item['std_item_id'];
              $std_item_schema = $item['std_item_schema'];

              if($item_no)            
                $insert_salesinvoicedataitems = ImportReconciliationSalesInvoicesDataItems::updateOrCreate(
                  [
                    'ir_sales_invoice_data_id' => $insert_salesinvoicedata->id,
                    'item_no' => $item_no
                  ],
                  [    
                    'ir_sales_invoice_data_id' => $insert_salesinvoicedata->id,

                    'item_no' => $item_no,
                    'item_order_no' => $item_order_no,
                    'item_name' => $item_name,
                    'item_desc' => $item_desc,
                    'base_qty' => ($base_qty) ? $base_qty : 1,
                    'qty' => $qty,
                    'unit_code' => $unit_code,
                    'tax_name' => $tax_name,
                    'line_amount' => $line_amount,
                    'accounting_cost' => $accounting_cost,
                    'tax_amount' => $tax_amount,
                    'net_amount' => $net_amount,
                    'tax_percent' => $tax_percent,
                    'price' => $price,
                    'seller_item_id' => $seller_item_id,
                    'seller_item_schema' => $seller_item_schema,
                    'std_item_id' => $std_item_id,
                    'std_item_schema' => $std_item_schema,
                   
                    'created_by' => ($sales_invoice_data_id) ? $insert_salesinvoicedataitems->created_by : $this->authUser->id,
                    'updated_by' => $this->authUser->id
                  ]
                );
            }
          }
        }
          
        $vat_reg_id = $request->vat_reg_id;         
        $selected_invoices = $request->data['invoice_no'];

        $log_name_text_suffix = 'ftp-data-edit';
        
        $invoice_name_text = 'Sales Invoices';
        $log_name_text = 'sales-invoice';

        $vatreg = $this->reloadDeclarations($vat_reg_id); 

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $selected_invoices,
            'Invoice Name' => $invoice_name_text
          ]
        );

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->tab_name
          ]
        );          
      }
      catch (\Exception $e) {
        return  $e->getMessage();
      }
    }  
    /* --end POST declaration-invoice/$invoice_id/edit -- */

    /* -- POST declaration-invoice/$invoice_id/refresh (BOTH COM. INVOICE/SALES INVOICE)-- */
    public function refreshSpecificData(Request $request, $invoice_id)
    {
      try
      {       
        $invoice_name = $request->invoice_name;
        $invoice_no = $request->invoice_no;

        $log_name_text_suffix = 'specific-invoice-refresh';
        if($invoice_name == 'com')
        {
          $invoice_name_text = 'Commercial Invoices';
          $log_name_text = 'com-invoice';

          $invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();            
        }
        else if($invoice_name == 'sales')
        {
          $invoice_name_text = 'Sales Invoices';
          $log_name_text = 'sales-invoice';

          $invoice = ImportReconciliationSalesInvoices::where('id', $invoice_id)->first(); 
        }

        $vat_reg_id = $invoice->vat_reg_id;

        $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);

        if(stripos(strtolower($request->invoice_no), ", ") !== false)
        {
          $arr_invoice_nos = explode(', ', $request->invoice_no);
          foreach ($arr_invoice_nos as $invoice_no)
          {
            $from = 'specific-invoice-global-search-refresh';
            $data = $this->commonClass->loadImportReconciliationDatasFromAzureDb($this->authUser, $vatreg, $from, false, $invoice_name, str_replace(['SPG-', '-NO'], '', $invoice_no));
          }
        } 
        else
        {
          $from = 'specific-invoice-global-search-refresh';
          $data = $this->commonClass->loadImportReconciliationDatasFromAzureDb($this->authUser, $vatreg, $from, false, $invoice_name, str_replace(['SPG-', '-NO'], '', $request->invoice_no));
        }       
       
        $vatreg = $this->reloadDeclarations($vat_reg_id);   

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $invoice_no,
            'Invoice Name' => $invoice_name_text
          ]
        );        

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->tab_name
          ]
        );  
      }
      catch (\Exception $e) {
          return  $e->getMessage();
        }
    }
    /* --end POST declaration-invoice/$invoice_id/refresh (BOTH COM. INVOICE/SALES INVOICE)-- */
    
    /* --POST declaration-invoice/$vat_reg_id/convert (BOTH COM. INVOICE/SALES INVOICE)-- */
    public function convertInvoiceCurrency(Request $request, $vat_reg_id)
    {
      try
      {         
        $selected_month = $request->selected_month;
        $from_currencies = $request->from_currencies;
      
        $to_currency = $request->to_currency;

        foreach (explode(',', $from_currencies) as $from_currency)
        {
          $date_type = $request->input('chk_currency_convert_dates_' . $vat_reg_id . '_' . $from_currency);

          // 1. Create job log entry
          $joblog = JobLog::create([
              'user_id' => $this->authUser->user_id,
              'job' => ConvertImportReconcilationInvoiceCurrency::class,
              'status' => 'pending',             
              'payload' => ['vat_reg_id' => $vat_reg_id],
          ]);
          $joblog_id = $joblog->id;

          if($date_type == 'invoice date')
          {
            ConvertImportReconcilationInvoiceCurrency::dispatch($joblog_id, $selected_month, $vat_reg_id, $this->authUser->user_id, $from_currency, $to_currency)
                ->onQueue('convertinvoicecurrency'); // Dispatch the job   
          }
          else if($date_type == 'todays date')
          {
            $exchange_rate = $request->input('currency_convert_todays_rate_' . $vat_reg_id . '_' . $from_currency);
            
            ConvertImportReconcilationInvoiceCurrency::dispatch($joblog_id, $selected_month, $vat_reg_id, $this->authUser->user_id, $from_currency, $to_currency, $exchange_rate)
                  ->onQueue('convertinvoicecurrency'); // Dispatch the job          
          }
        } //for currencies    

        $vatreg = $this->reloadDeclarations($vat_reg_id);   

        $this->commonClass->addLog($this->authUser, 'importreconcilation-invoice-currency-conversion', 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Month' => $selected_month            
          ]
        );        

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->tab_name,
            'joblog_id' => $joblog_id
          ]
        );          
      }
      catch (\Exception $e) {
          return  $e->getMessage();
        }
    }
    /* --end POST declaration-invoice/$vat_reg_id/convert (BOTH COM. INVOICE/SALES INVOICE)-- */

    /* --GET job-status/$logId-- */
    public function jobStatus(Request $request, $logId)
    {
      try
      {       
        $jobLog = JobLog::findOrFail($logId);

        $vat_reg_id = $jobLog->payload['vat_reg_id'] ?? null;

        $vatreg = $this->reloadDeclarations($vat_reg_id);

        $client_id = $vatreg->client_id;
        $from_currencies = $vatreg->from_currencies;
        $currency_code = $vatreg->currency_code;
        $todays_rate = $vatreg->todays_rate;     
        $last_exchange_rates = $vatreg->last_exchange_rates;        

        $modal_currency_convert = view('_partials._modals._currency-convert-list', compact('client_id', 'vat_reg_id', 'from_currencies', 'currency_code', 'todays_rate', 'last_exchange_rates'))->render();   

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,            
            'joblog_id' => $logId,
            'job_status' => $jobLog->status,
            'modal_currency_convert' => $modal_currency_convert
          ]
        ); 
      }
      catch (\Exception $e) {
        return  $e->getMessage();
      }
    }
    /* --end GET job-status/$logId-- */

    /* -- POST declaration-invoice/$invoice_id/unmatch (COM. INVOICE)-- */
    public function invoiceUnmatch(Request $request, $invoice_id)
    {
      try
      {       
        $invoice_name = $request->invoice_name;
        $invoice_no = $request->invoice_no;

        $log_name_text_suffix = 'invoice-unmatch';
        if($invoice_name == 'com')
        {
          $invoice_name_text = 'Commercial Invoices';
          $log_name_text = 'com-invoice';

          $invoice = ImportReconciliationComInvoices::where('id', $invoice_id)->first();  

          if($invoice->data_from == 'ivf' && $invoice->doc_id)
          {
            $newRow = $invoice->replicate();

            $newRow->data_from = 'azure';
            $newRow->expo_no = NULL;
            $newRow->lope_no = NULL;
            $newRow->duties = NULL;
            $newRow->adjustment = NULL;
            $newRow->statistical_value = NULL;
            $newRow->category_type = NULL;
            $newRow->category_desc = NULL;
            $newRow->ivf_net_amount = NULL;
            $newRow->omr_kurs = NULL;
           
            $newRow->save();

            $invoice->relation_match_no = NULL;  
            $invoice->doc_id = NULL; 
            $invoice->gs_invoice_date = NULL; 
            $invoice->net_amount = NULL; 

            $invoice->unmatch = 1; 
            $invoice->rematch_com_invoice_id = NULL;           
            $invoice->save();
            
            $salesinvoices = ImportReconciliationSalesInvoices::where('com_invoice_id', $invoice->id)
                                  ->update(['com_invoice_id' => $newRow->id]);
          }
          else
          {
            $invoice->unmatch = 1;  
            $invoice->rematch_com_invoice_id = NULL;  
            $invoice->save();    
          }    
        }
       
        $vat_reg_id = $invoice->vat_reg_id;        
       
        $vatreg = $this->reloadDeclarations($vat_reg_id);   

        $this->commonClass->addLog($this->authUser, 'importreconcilation-'. $log_name_text .'-' . $log_name_text_suffix, 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $invoice_no,
            'Invoice Name' => $invoice_name_text
          ]
        );        

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'declarations' => $vatreg,
            'tab_name' => $request->tab_name
          ]
        );  
      }
      catch (\Exception $e) {
          return  $e->getMessage();
        }
    }
    /* --end POST declaration-invoice/$invoice_id/refresh (BOTH COM. INVOICE/SALES INVOICE)-- */
}
