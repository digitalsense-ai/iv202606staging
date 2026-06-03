<?php

namespace App\Http\Controllers\tasks;

use App\Http\Controllers\Controller;
use App\Models\VATRegistration;
use App\Models\ImportVatFiles;
use App\Models\VATReturnNotes;
use App\Models\ImportReconciliationNotes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Classes\CommonClass;
use App\Classes\ApiClass;

use \NumberFormatter;

use App\Events\VATReturnNotesEvent;
use App\Events\ImportReconciliationNotesEvent;

class TasksController extends Controller
{
    public $authUser;
    
    public $commonClass;
    public $apiClass;

    public $clientIds;

    public $pageSize;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                     
            $this->commonClass = new CommonClass();
            $this->apiClass = new ApiClass();
            $this->authUser = $this->commonClass->getAuthUser();     

            if($this->authUser->role == 'client-user')            
              $this->clientIds = $this->commonClass->getClientIdsForClientUser($this->authUser);  
                     
            $this->pageSize = 250;

            return $next($request);
        });
    }

    /* -- GET /uploads/{upload_file_type} -- */
    public function Uploads($upload_file_type = NULL)
    {        
        try
        {
            /* -- PAGE CONFIG -- */
            $pageConfigs = $this->commonClass->getPageConfig($this->authUser); 
            /* --end PAGE CONFIG -- */

            /* -- GET EXCEL COLUMNS -- */
            $excel_columns = $this->commonClass->listExcelColumns();
            /* -- GET EXCEL COLUMNS -- */
           
            /* -- GET ANYEXCEL TEMPLATES -- */
            $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
            /* --/ GET ANYEXCEL TEMPLATES -- */   

            //if($upload_file_type)
                //$this->pageSize = 100;
            /* -- GET ALL VAT REG. -- */            
            $vatregs_result = $this->commonClass->getAllVatRegQuery($this->authUser, NULL, true, $this->pageSize); //10

            $title_text = '';
            if($upload_file_type)
            {
                if($upload_file_type == 'pivs')
                {
                    // $result = $vatregs_result->filter(function ($vatreg, $key) {         
                    //     return ($vatreg->country == 'GB') && ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
                    // });

                    $title_text = ' - Postponed import VAT statement';
                }
                else if($upload_file_type == 'cas')
                {
                    // $result = $vatregs_result->filter(function ($vatreg, $key) {         
                    //     return ($vatreg->country == 'GB' && $vatreg->vatregmain->cash_acc_stmt === 1) && ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
                    // });

                    $title_text = ' - Cash Account Statement';
                }
                else if($upload_file_type == 'dda')
                {
                    // $result = $vatregs_result->filter(function ($vatreg, $key) {         
                    //     return ($vatreg->country == 'NO' && $vatreg->vatregmain->duty_defer_acc === 1) && ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
                    // });

                    $title_text = ' - Duty Deferment Account';
                }
            }
            // else
            // {
                $result = $vatregs_result->filter(function ($vatreg, $key) {         
                    return ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
                });                
            //}              
            /* --end GET ALL VAT REG. -- */

            /* -- GET ALL TASK DATES. -- */
            $system = $this->commonClass->getSystemInfoLazy();
            $systemtaskdates = $system->systemtaskdate;
            /* -- end GET ALL TASK DATES. -- */

            /* -- RETURN VIEW -- */
            return view('content.tasks.uploads', 
                [
                    'pageConfigs' => $pageConfigs, 
                    'authUser' => $this->authUser, 
                    'title' => 'Uploads' . $title_text, 
                    'upload_file_type' => $upload_file_type,            
                    'result' => $result,
                    'systemtaskdates' => $systemtaskdates,
                    'excel_columns' => $excel_columns,                  
                    'anyexcel_templates' => $anyexcel_templates
                ]         
            );
            /* --end RETURN VIEW -- */
        }      
        catch (\Exception $e) 
        {           
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Tasks Controller',
                'method' => 'Uploads',
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
    /* --end GET /uploads -- */

    /* -- GET /all-tasks -- */
    public function AllTasks()
    {  
        try
        {     
            /* -- PAGE CONFIG -- */
            $pageConfigs = $this->commonClass->getPageConfig($this->authUser);  
            /* --end PAGE CONFIG -- */

            /* -- GET EXCEL COLUMNS -- */
            $excel_columns = $this->commonClass->listExcelColumns();
            /* -- GET EXCEL COLUMNS -- */
            
            /* -- GET ANYEXCEL TEMPLATES -- */
            $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
            /* --/ GET ANYEXCEL TEMPLATES -- */   

            /* -- GET ALL VAT REG. -- */            
            $vatregs_result = $this->commonClass->getAllVatRegQuery($this->authUser, NULL, true, $this->pageSize); //10
            $result = $vatregs_result->filter(function ($vatreg, $key) {         
                return ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
            });          
            /* --end GET ALL VAT REG. -- */

            /* -- GET ALL TASK DATES. -- */
            $system = $this->commonClass->getSystemInfoLazy();
            $systemtaskdates = $system->systemtaskdate;
            /* -- end GET ALL TASK DATES. -- */

            /* -- RETURN VIEW -- */           
            return view('content.tasks.alltasks', 
                [
                    'pageConfigs' => $pageConfigs, 
                    'authUser' => $this->authUser, 
                    'title' => ($this->authUser->role == 'team-user') ? 'My Tasks' : 'All Tasks',             
                    'result' => $result,
                    'systemtaskdates' => $systemtaskdates,
                    'excel_columns' => $excel_columns,                   
                    'anyexcel_templates' => $anyexcel_templates
                ]         
            );
            /* --end RETURN VIEW -- */
        }      
        catch (\Exception $e) 
        {           
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Tasks Controller',
                'method' => 'AllTasks',
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
    /* --end GET /all-tasks -- */

    /* -- GET /clientuser-tasks -- */
    public function clientUserTasks()
    {  
        try
        {
            /* -- PAGE CONFIG -- */
            $pageConfigs = $this->commonClass->getPageConfig($this->authUser);   
            /* --end PAGE CONFIG -- */

            /* -- GET EXCEL COLUMNS -- */
            $excel_columns = $this->commonClass->listExcelColumns();
            /* -- GET EXCEL COLUMNS -- */
                        
            /* -- GET ANYEXCEL TEMPLATES -- */
            $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
            /* --/ GET ANYEXCEL TEMPLATES -- */

            /* -- GET ALL VAT REG. -- */           
            $vatregs_result = $this->commonClass->getAllVatRegQuery($this->authUser, $this->clientIds, true, $this->pageSize); //10
            $result = $vatregs_result->filter(function ($vatreg, $key) {         
                return ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
            });
            /* --end GET ALL VAT REG. -- */
            
            /* -- GET ALL TASK DATES. -- */
            $system = $this->commonClass->getSystemInfoLazy();
            $systemtaskdates = $system->systemtaskdate;
            /* -- end GET ALL TASK DATES. -- */

            /* -- RETURN VIEW -- */      
            return view('content.tasks.clientusertasks', 
                [
                    'pageConfigs' => $pageConfigs, 
                    'authUser' => $this->authUser, 
                    'title' => 'Tasks',             
                    'result' => $result,
                    'systemtaskdates' => $systemtaskdates,
                    'excel_columns' => $excel_columns,                  
                    'anyexcel_templates' => $anyexcel_templates
                ]         
            );
            /* --end RETURN VIEW -- */      
        }      
        catch (\Exception $e) 
        {           
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Tasks Controller',
                'method' => 'clientUserTasks',
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
    /* --end GET /clientuser-tasks -- */

    /* -- GET /all-tasks/more/{$page} -- */
    public function AllTasksMore(Request $request, $morepage)
    {      
        $pagename = $request->pagename;

        /* -- GET ALL TASK DATES. -- */
        $system = $this->commonClass->getSystemInfoLazy();
        $systemtaskdates = $system->systemtaskdate;
        /* -- end GET ALL TASK DATES. -- */

        $authUser = $this->authUser;

        //if($pagename == 'uploadspivs' || $pagename == 'uploadscas' || $pagename == 'uploadsdda')
            //$this->pageSize = 100;

        if($this->clientIds)
            $vatregs_result = $this->commonClass->getAllVatRegQuery($this->authUser, $this->clientIds, true, $this->pageSize, $morepage); //10
        else
            $vatregs_result = $this->commonClass->getAllVatRegQuery($this->authUser, NULL, true, $this->pageSize, $morepage); //10

        $result = $vatregs_result->filter(function ($vatreg, $key) {         
            return ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
        });

        if($pagename == 'uploadspivs')
        {
            $upload_tasks_pivs = view('_partials._content._tasks.upload-tasks-pivs', 
                compact(
                    'result', 'morepage', 'authUser', 'systemtaskdates'
                )
            )->render();

            $upload_tasks_cas = '';

            $upload_tasks_dda = '';
        }
        elseif($pagename == 'uploadscas')
        {
            $upload_tasks_pivs = '';

            $upload_tasks_cas = view('_partials._content._tasks.upload-tasks-cas', 
                compact(
                    'result', 'morepage', 'authUser', 'systemtaskdates'
                )
            )->render();

            $upload_tasks_dda = '';
        }
        elseif($pagename == 'uploadsdda')
        {
            $upload_tasks_pivs = '';

            $upload_tasks_cas = '';

            $upload_tasks_dda = view('_partials._content._tasks.upload-tasks-dda', 
                compact(
                    'result', 'morepage', 'authUser', 'systemtaskdates'
                )
            )->render();
        }
        elseif($pagename == 'uploads')
        {
            $upload_tasks_pivs = view('_partials._content._tasks.upload-tasks-pivs', 
                compact(
                    'result', 'morepage', 'authUser', 'systemtaskdates'
                )
            )->render();

            $upload_tasks_cas = view('_partials._content._tasks.upload-tasks-cas', 
                compact(
                    'result', 'morepage', 'authUser', 'systemtaskdates'
                )
            )->render();

            $upload_tasks_dda = view('_partials._content._tasks.upload-tasks-dda', 
                compact(
                    'result', 'morepage', 'authUser', 'systemtaskdates'
                )
            )->render();
        }
             
        /* -- GET ANYEXCEL TEMPLATES -- */
        $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
        /* --/ GET ANYEXCEL TEMPLATES -- */

        $title = ($pagename == 'uploads') ? 'Uploads' : (($this->authUser->role == 'team-user') ? 'My Tasks' : 'All Tasks'); 

        $full_result = $result;
        $vatreturn_tasks = "";
        //if($pagename != 'uploads')
        if($pagename == 'uploads' || $pagename == 'uploadspivs' || $pagename == 'uploadscas' || $pagename == 'uploadsdda')
        {

        }
        else
        {
            $check_product_type = 1;
            $accordion_name = 'All';
            
            $filtered_result = $full_result->filter(function ($vatreg, $key) {                   
              return ($vatreg->vatregmain->product_type != 4);
            });
            $result = $filtered_result;

            $vatreturn_tasks = view('_partials._content._vatreturn.vatreturns-all-tasks-lazy', 
                compact(                
                    'result', 'morepage', 'authUser', 'anyexcel_templates', 'title', 'check_product_type', 'accordion_name'
                )
            )->render();

            //For VOEC
            if(count($result) < $this->pageSize) //10
            {       
                $check_product_type = 4;
                            
                $filtered_result = $full_result->filter(function ($vatreg, $key) {                   
                  return ($vatreg->vatregmain->product_type == 4);
                });
                $result = $filtered_result;

                $voec_vatreturn_tasks = view('_partials._content._vatreturn.vatreturns-all-tasks-lazy', 
                    compact(                       
                        'result', 'morepage', 'authUser', 'anyexcel_templates', 'title', 'check_product_type', 'accordion_name'
                    )
                )->render();

                if($voec_vatreturn_tasks != '')
                    $vatreturn_tasks .= $voec_vatreturn_tasks;
            }
        }

        return response()->json(
            [   
                'result_count' => count($full_result),
                'upload_tasks_pivs' => $upload_tasks_pivs,
                'upload_tasks_cas' => $upload_tasks_cas,
                'upload_tasks_dda' => $upload_tasks_dda,
                'vatreturn_tasks' => $vatreturn_tasks
            ]
        );
    }
    /* --end GET /all-tasks/more/{$page} -- */
    
    /* -- GET vat-returns-tab/{vat_reg_id}*/
    public function loadVatReturnsTab(Request $request, $client_id)
    {       
        try    
        {    
            /* -- GET VAT REG.s -- */
            $vatregs_result = $this->commonClass->getAllVatRegQuery($this->authUser, $client_id, false); 
            $result = $vatregs_result->filter(function ($vatreg, $key) {         
                return ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
            });
            /* --end GET VAT REG.s -- */
            
            /* -- AUTH USER -- */
            $authUser = $this->authUser;
            /* --end AUTH USER -- */
            
            /* -- GET ANYEXCEL TEMPLATES -- */
            $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
            /* --/ GET ANYEXCEL TEMPLATES -- */

            $full_result = $result;

            $check_product_type = 1;
            $accordion_name = 'All';
            
            $filtered_result = $full_result->filter(function ($vatreg, $key) {                   
              return ($vatreg->vatregmain->product_type != 4);
            });
            $result = $filtered_result;

            $view = view('_partials._content._vatreturn.vatreturns-all-tasks-lazy', 
                compact(                   
                    'result', 'authUser', 'anyexcel_templates', 'check_product_type', 'accordion_name'
                )
            )->render();

            //For VOEC
            if(count($result) < $this->pageSize) //10
            {       
                $check_product_type = 4;
                            
                $filtered_result = $full_result->filter(function ($vatreg, $key) {                   
                  return ($vatreg->vatregmain->product_type == 4);
                });
                $result = $filtered_result;

                $voec_vatreturn_tasks = view('_partials._content._vatreturn.vatreturns-all-tasks-lazy', 
                    compact(                       
                        'result', 'authUser', 'anyexcel_templates', 'check_product_type', 'accordion_name'
                    )
                )->render();

                if($voec_vatreturn_tasks != '')
                    $view .= $voec_vatreturn_tasks;
            }
            
            /* -- RETURN JSON -- */
            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
            );                            
            /* --end RETURN JSON -- */
        }      
        catch (\Exception $e) 
        {   
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
                [
                'status' => 'Error',
                'controller' => 'Task Controller',
                'method' => 'loadVatReturnsTab',
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
    /* --end GET vat-returns-tab/{vat_reg_id}*/

    /*-- GET vat-return-overview-tab/{vat_reg_id}*/
    public function loadOverviewTabLazy(Request $request, $vat_reg_id)
    {    
        $authUser = $this->authUser;
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();

        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
        
        if(empty($vatreg))
            return false;
        
        $refresh = (isset($request->refresh)) ? $request->refresh : false;

        if($refresh == "true")
        {
            $data = $this->commonClass->loadApiDatas($this->authUser, $vatreg, $systemapi, $refresh);        
            $result = json_decode($data->getContent());            
            
            $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
        }

        $tab_name = (isset($request->tab_name)) ? $request->tab_name : 'overview';
        $page_type = 'my-tasks';
       
        $client = $vatreg->client;
        $client_name = $client->client_name;
        $client_id = $client->client_id;
        $client_users = $client->userclient;  

        $vat_reg_main = $vatreg->vatregmain;
        $vatregmain_status = $vat_reg_main->status;
        $client_api = isset($vat_reg_main->clientapi) ? $vat_reg_main->clientapi : null;

        $vatreturns = $vatreg->vatreturns;
        $vatreturnfiles = ($vatreg->vatreturnfiles) ? $vatreg->vatreturnfiles : [];         

        $show_vatreturn = 0;            
        if(($client_api === null)) 
        {
            if((count($vatreturnfiles) > 0) || count($vatreturns) > 0)
                $show_vatreturn = 1;
            else
            {
                if (stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
                stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
                stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
                ) 
                {       
                    $importreconciliationfiles = ($vatreg->importreconciliationfiles) ? $vatreg->importreconciliationfiles : [];
                                 
                    if((count($importreconciliationfiles) > 0))
                        $show_vatreturn = 1;   
                    else
                        $show_vatreturn = 0;   
                }                
            }
        } 
        else
        {
            if(count($vatreturns) == 0)
                $show_vatreturn = 0;
            else
                $show_vatreturn = 1;  
        } 

        $currencycode = ''; 
        $currencylocale = 'en_US';        
        if($vatreg->country == "DK")
        {
            $currencycode = "DKK";
            $currencylocale = 'da_DK';
        }
        elseif($vatreg->country == "NO") 
        { 
            $currencycode = "NOK";           
            $currencylocale = 'da_DK';
        }
        elseif($vatreg->country == "SE") 
        { 
            $currencycode = "SEK";
            $currencylocale = 'sv_SE';
        }
        elseif($vatreg->country == "GB")
        {
            $currencycode = "GBP";
            $currencylocale = 'en_GB';
        }
        elseif($vatreg->country == "IN")  
        {
            $currencycode = "INR";
            $currencylocale = 'en_IN';
        }
        elseif($vatreg->country == "FR")  
        {
            $currencycode = "EUR";
            $currencylocale = 'fr_FR';
        }
        elseif($vatreg->country == "CH")  
        {
            $currencycode = "CHF";
            $currencylocale = 'fr_FR';
        }
       
        $currencyFormatter = new NumberFormatter($currencylocale, NumberFormatter::DECIMAL);  
        $currencyFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);     
        $currencySymbol = $currencycode;

        $totalnet = 0;
        $purchasetotalnet = 0;
        $salestotalnet = 0;

        $totalvat = 0;
        $purchasetotalvat = 0;
        $salestotalvat = 0;       

        if($vatreg->country == 'NO')
        {
            $sales_standard_totalvat = 0; 
            $sales_medium_totalvat = 0; 
            $sales_low_totalvat = 0;
            $sales_zero_totalvat = 0;
            $sales_fish_totalvat = 0;

            $sales_standard_totalnet = 0;
            $sales_medium_totalnet = 0;
            $sales_low_totalnet = 0;
            $sales_zero_totalnet = 0;
            $sales_fish_totalnet = 0;

            $purchases_standard_totalvat = 0; 
            $purchases_medium_totalvat = 0; 
            $purchases_low_totalvat = 0;
            $purchases_zero_totalvat = 0;
            $purchases_fish_totalvat = 0;

            $purchases_standard_totalnet = 0;
            $purchases_medium_totalnet = 0;
            $purchases_low_totalnet = 0;
            $purchases_zero_totalnet = 0;
            $purchases_fish_totalnet = 0;

            $view = view('_partials._content._vatreturn.vatreturn-overview-lazy', 
                compact(
                    'authUser', 
                    'page_type', 
                    'tab_name', 
                    'vat_reg_id',
                    'vatregmain_status',
                    'client',
                    'show_vatreturn',                
                    'vatreturns',
                    'vatreg',
                    'client_users',
                    'client_api',
                   
                    'totalnet',
                    'purchasetotalnet',
                    'salestotalnet',
                    'totalvat',
                    'purchasetotalvat',
                    'salestotalvat',

                    'sales_standard_totalvat',
                    'sales_medium_totalvat',
                    'sales_low_totalvat',
                    'sales_zero_totalvat',
                    'sales_fish_totalvat',

                    'sales_standard_totalnet',
                    'sales_medium_totalnet',
                    'sales_low_totalnet',
                    'sales_zero_totalnet',
                    'sales_fish_totalnet',

                    'purchases_standard_totalvat',
                    'purchases_medium_totalvat',
                    'purchases_low_totalvat',
                    'purchases_zero_totalvat',
                    'purchases_fish_totalvat',

                    'purchases_standard_totalnet',
                    'purchases_medium_totalnet',
                    'purchases_low_totalnet',
                    'purchases_zero_totalnet',
                    'purchases_fish_totalnet',

                    'currencyFormatter',
                    'currencySymbol',
                    'currencycode',
                    'currencylocale'
                )
            )->render();
        }
        else
            $view = view('_partials._content._vatreturn.vatreturn-overview-lazy', 
                compact(
                    'authUser', 
                    'page_type', 
                    'tab_name', 
                    'vat_reg_id',
                    'vatregmain_status',
                    'client',
                    'show_vatreturn',                
                    'vatreturns',
                    'vatreg',
                    'client_users',
                    'client_api',

                    'totalnet',
                    'purchasetotalnet',
                    'salestotalnet',
                    'totalvat',
                    'purchasetotalvat',
                    'salestotalvat',

                    'currencyFormatter',
                    'currencySymbol',
                    'currencycode',
                    'currencylocale'
                )
            )->render();  

        if(isset($result->error))
        {
            return response()->json(
                [
                    'status' => 400,             
                    'error' => $result->error,
                    'view' => $view,
                    'vatreturn_file_id' => isset($result->vatreturn_file_id) ? $result->vatreturn_file_id : null
                ]
            );  
        }
        else        
            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
            );                  
    }
    /*--end GET vat-return-overview-tab/{vat_reg_id}*/

    /* -- GET vat-return-importvat-tab/{vat_reg_id}*/
    public function loadImportVatTabLazy(Request $request, $vat_reg_id)
    {
        $import_vat_file_id = $request->import_vat_file_id;
        $import_vat_file = ImportVatFiles::with(['vatreg' => function ($query) {
                                    $query->select(['id',
                                      'id AS vat_reg_id',//foreign_key -DON'T REMOVE
                                      'client_id'
                                    ]);                      
                                }
                            ])
                            ->select(['id',
                                'vat_reg_id',//foreign_key -DON'T REMOVE 
                                'file_type',
                                'folder_id',
                                'file_id',
                                'file_name',
                                'file_size',
                                'month_year',
                                'fee_number',
                                'statistical_number',
                                'e_fee_number',
                                'e_statistical_number',
                                'adjustment_no',
                                'invoice_total',
                                'box_85'
                            ])
                            ->where('vat_reg_id', $vat_reg_id)
                            ->where('file_type', 'xml')
                            ->where('id', $import_vat_file_id)                            
                            ->first();

        $client_id = $import_vat_file->vatreg->client_id;                   
        if($import_vat_file->file_id != NULL)
        {
            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first(); 

            $importvatfileName = $this->apiClass->loadFromOneDriveLazy($import_vat_file, $systemapi);
            if(isset($importvatfileName->error))   
            {

            } 
            else    
            {
                if($import_vat_file->file_type == 'xml') 
                    $import_vat_file->xml = $this->apiClass->xmlExtractByLine($import_vat_file, $importvatfileName['download_url']);    
            }
        }

        $view = view('_partials._content._vatreturn.import-vat-file-overview-lazy', 
            compact(
                'client_id',
                'vat_reg_id',
                'import_vat_file'            
            )
        )->render();  

        return response()->json(
            [
              'status' => 200,             
              'view' => $view
            ]
          );    
    } 
    /* --end GET vat-return-importvat-tab/{vat_reg_id}*/  

    /* -- GET vat-return-history-tab/{vat_reg_id}*/
    public function loadHistoryTabLazy(Request $request, $vat_reg_id)
    {       
        $client = VATRegistration::with([
                                'client'                                    
                            ])   
                            ->select(['id',       
                                'client_id'
                            ])                         
                            ->where('id', $vat_reg_id)                           
                            ->first();

        if($client)                    
        {
            $client_id = $client->client_id;
                                
            $histories = $this->commonClass->getVatReturnTimeline($vat_reg_id);
            
            $view = view('_partials._content._vatreturn.history-lazy', 
                compact(
                    'client_id',
                    'vat_reg_id',
                    'histories'            
                )
            )->render();  

            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
              );
        }
        else
            return response()->json(
                [
                  'status' => 200,             
                  'view' => ""
                ]
              );    
    } 
    /* --end GET vat-return-history-tab/{vat_reg_id}*/

    /* -- GET vat-return-notes-tab/{vat_reg_id}*/
    public function loadVatReturnNotesTab(Request $request, $vat_reg_id)
    {                               
        $client_id = $request->client_id;

        $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id, [], []);
        $vatreg_country = $vatreg->country;

        $general_notes = $this->commonClass->getVatReturnNotes('general', $client_id);
        $specific_notes = $this->commonClass->getVatReturnNotes('specific', $vat_reg_id);

        $vatreturn_notes = $general_notes->merge($specific_notes)->sortByDesc('created_at')->values();
       
        $authUser = $this->authUser;

        $view = view('_partials._content._vatreturn.notes', 
            compact(
                'authUser',
                'vat_reg_id',
                'vatreturn_notes',
                'vatreg_country'
            )
        )->render();  

        return response()->json(
            [
              'status' => 200,             
              'view' => $view
            ]
        );        
    } 
    /* --end GET vat-return-notes-tab/{vat_reg_id}*/

    /* -- POST vat-return-notes-tab/{vat_reg_id}*/
    public function postVatReturnNotes(Request $request, $vat_reg_id)
    {                               
        $vatreg = VATRegistration::with([
                                'client', 'vatregmain'                                   
                            ])                                                   
                            ->where('id', $vat_reg_id)                           
                            ->first();

        if($vatreg)                    
        {            
            $vatregmain_status = $vatreg->vatregmain->status;

            $client_id = $vatreg->client_id;
            if($vatregmain_status)
            {                
                $note_id = $request->note_id;
                $countries = implode(', ', $request->vatreturn_selectedCountries);
       
                if($note_id)            
                    $vatreturn_note = VATReturnNotes::updateOrCreate(    
                        ['id' => $note_id],
                        [
                            'client_id' => $client_id, 
                            'vat_reg_id' => $vat_reg_id, 
                            'type' => $request->vatreturn_note_type,                             
                            'notes' => $request->vatreturn_note_quill,
                            'countries' => $countries,
                            'created_by' => $this->authUser->user_id
                        ]
                    );
                else         
                    $vatreturn_note = VATReturnNotes::updateOrCreate(                    
                    [
                        'client_id' => $client_id, 
                        'vat_reg_id' => $vat_reg_id, 
                        'type' => $request->vatreturn_note_type,                             
                        'notes' => $request->vatreturn_note_quill,
                        'countries' => $countries,
                        'created_by' => $this->authUser->user_id
                    ]
                );

                $this->commonClass->addLog($this->authUser, 'vatreturn-notes-'. (($note_id) ? 'update' : 'add'), 
                    [
                      'Client Name' => $vatreg->client->client_name,
                      'type' => $request->vatreturn_note_type,
                      'month' => Carbon::parse($vatreg->service_start)->format('M Y')
                    ]
                );

                event(new VATReturnNotesEvent($vat_reg_id, $request->vatreturn_note_type . ' note has been '. (($note_id) ? 'updat' : 'add') .'ed.'));  

                return response()->json(
                    [
                      'status' => 200,
                      'message' => 'Created',
                      'client_id' => $client_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                ); 
            }  //Active VAT reg. 
            else
            {
                return response()->json(
                    [
                      'status' => 200,
                      'message' => 'You cannot create note for inactive VAT Reg.',
                      'client_id' => $client_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                ); 
            }   
        }     
    }
    /* --end POST vat-return-notes-tab/{vat_reg_id}*/
    
    /* -- DELETE vat-return-notes-tab/{note_id}*/
    public function deleteVatReturnNotes(Request $request, $note_id)
    {       
        $vat_reg_id = $request->vat_reg_id;
        $vatreg = VATRegistration::with([
                                'client', 'vatregmain'
                            ])                                                   
                            ->where('id', $vat_reg_id)                           
                            ->first();

        if($vatreg)                    
        {
            $vatregmain_status = $vatreg->vatregmain->status;
            $client_id = $vatreg->client_id;

            if($vatregmain_status)
            {                
                $vatreturn_note = VATReturnNotes::where('id', $note_id)->first();
                $vatreturn_note_type = $vatreturn_note->type;

                $vatreturn_note->delete();

                $this->commonClass->addLog($this->authUser, 'vatreturn-notes-delete', 
                    [
                      'Client Name' => $vatreg->client->client_name,
                      'type' => $vatreturn_note_type,
                      'month' => Carbon::parse($vatreg->service_start)->format('M Y')
                    ]
                );

                event(new VATReturnNotesEvent($vat_reg_id, $vatreturn_note_type . ' note has been deleted.'));  

                return response()->json(
                    [
                      'status' => 200,
                      'message' => 'Deleted',
                      'client_id' => $client_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                );
            }
            else
            {
                return response()->json(
                    [
                      'status' => 400,
                      'message' => 'You cannot delete note for inactive VAT Reg.',
                      'client_id' => $client_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                );
            }
        }      
    }
    /* --end DELETE vat-return-notes-tab/{vat_reg_id}*/

    /* -- GET /vat-return-submittingfields-tab/{vat_reg_id} -- */
    public function loadSubmittingFieldsTab(Request $request, $vat_reg_id)
    {   
      try
      {            
        /* -- GET VAT REG. -- */
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
        
        $vatregmain_status = $vatreg->vatregmain->status;
        $vatreg_status = $vatreg->status;
        $vatreg_is_disregard = $vatreg->is_disregard;

        $salestotalnet = 0; 
        $salestotalvat = 0;

        $purchasetotalnet = 0;
        $purchasetotalvat = 0;

        /* -- NO -- */
        if($vatreg->country == 'NO')
        {
            $import_vat_files = $vatreg->importvatfiles;
         
            // foreach($import_vat_files as $importvatfile)       
            // {       
            //     if($importvatfile->file_type == 'xml')
            //     {
            //         if($importvatfile->file_id != NULL)
            //         {              
            //             $importvatfileName = $this->apiClass->loadFromOneDriveLazy($importvatfile, $systemapi);              
            //             if(isset($importvatfileName->error))   
            //             {

            //             } 
            //             else    
            //                 $importvatfile->xml = $this->apiClass->xmlExtractByLine($importvatfile,$importvatfileName['download_url']);    
            //         } 
            //     }                   
            // }

            $submitting_fields = $vatreg->submittingfieldsNO;

            $sales_standard_totalnet = 0;
            $sales_standard_totalvat = 0;

            $sales_medium_totalnet = 0;
            $sales_medium_totalvat = 0;

            $sales_low_totalnet = 0;
            $sales_low_totalvat = 0;

            $sales_zero_totalnet = 0;
            $sales_zero_totalvat = 0;

            $sales_fish_totalnet = 0;
            $sales_fish_totalvat = 0;

            $purchases_standard_totalnet = 0;
            $purchases_standard_totalvat = 0;

            $purchases_medium_totalnet = 0;
            $purchases_medium_totalvat = 0;

            $purchases_low_totalnet = 0;
            $purchases_low_totalvat = 0;

            $purchases_zero_totalnet = 0;
            $purchases_zero_totalvat = 0;

            $purchases_fish_totalnet = 0;
            $purchases_fish_totalvat = 0;
        }
        /* --end NO -- */

        /* -- GB -- */
        if($vatreg->country == 'GB')
        {
            $pivs_files = $vatreg->pivs;
            $c79_documents = $vatreg->c79;
            $submitting_fields = $vatreg->submittingfields;            
        }
        /* --end GB -- */

        /* -- CH -- */
        if($vatreg->country == 'CH')
        {                        
            $sales_standard_totalnet = 0;
            $sales_standard_totalvat = 0;

            $sales_reduced_totalnet = 0;
            $sales_reduced_totalvat = 0;

            $submitting_fields = $vatreg->submittingfieldsCH;            
        }
        /* --end CH -- */

        /* -- VATRETUNS  -- */
        $vatreturns = $vatreg->vatreturns;
        
        foreach ($vatreturns as $key => $vatreturn)
        {
            $vat_percentage = str_replace('.00', '', $vatreturn->vat_percentage) . '%';
            
            if($vatreturn->invoice_type == 'sale')
            {                               
                // $salestotalnet += $vatreturn->net_amount;
                // $salestotalvat += $vatreturn->vat_amount;

                if($vatreg->country == 'NO')
                {
                    if($vat_percentage == "25%")
                    {
                        $sales_standard_totalnet += $vatreturn->net_amount;
                        $sales_standard_totalvat += $vatreturn->vat_amount;
                    } /* --end if 25% -- */
                    else if($vat_percentage == "15%")
                    {
                        $sales_medium_totalnet += $vatreturn->net_amount;
                        $sales_medium_totalvat += $vatreturn->vat_amount;
                    } /* --end if 15% -- */
                    else if($vat_percentage == "12%")
                    {
                        $sales_low_totalnet += $vatreturn->net_amount;
                        $sales_low_totalvat += $vatreturn->vat_amount;
                    } /* --end if 12% -- */
                    else if($vat_percentage == "0%")
                    {
                        $sales_zero_totalnet += $vatreturn->net_amount;
                        $sales_zero_totalvat += $vatreturn->vat_amount;
                    } /* --end if 0% -- */
                    else if($vat_percentage == "11.11%")
                    {
                        $sales_fish_totalnet += $vatreturn->net_amount;
                        $sales_fish_totalvat += $vatreturn->vat_amount;
                    } /* --end if 11.11% -- */
                } /* --end if NO -- */
                else if($vatreg->country == 'CH')
                {
                    if($vat_percentage == "8.10%")
                    {
                        $sales_standard_totalnet += $vatreturn->net_amount;
                        $sales_standard_totalvat += $vatreturn->vat_amount;
                    } /* --end if 8.10% -- */
                    else if($vat_percentage == "2.60%")
                    {
                        $sales_reduced_totalnet += $vatreturn->net_amount;
                        $sales_reduced_totalvat += $vatreturn->vat_amount;
                    } /* --end if 2.60% -- */
                } /* --end if CH -- */
                else if($vatreg->country == 'GB')
                {
                    if($vatreturn->currency_code == 'GBP')
                    {
                        $salestotalnet += $vatreturn->net_amount;
                        $salestotalvat += $vatreturn->vat_amount;
                    }
                } /* --end if GB -- */
                else
                {
                    $salestotalnet += $vatreturn->net_amount;
                    $salestotalvat += $vatreturn->vat_amount;
                }  /* --end if OTHER -- */
            } /* --end if SALE -- */

            if($vatreturn->invoice_type == 'purchase')
            {
                $purchasetotalnet += $vatreturn->net_amount;
                $purchasetotalvat += $vatreturn->vat_amount;

                if($vatreg->country == 'NO')
                {
                    if($vat_percentage == "25%")
                    {
                        $purchases_standard_totalnet += $vatreturn->net_amount;
                        $purchases_standard_totalvat += $vatreturn->vat_amount;
                    } /* --end if 25% -- */
                    else if($vat_percentage == "15%")
                    {
                        $purchases_medium_totalnet += $vatreturn->net_amount;
                        $purchases_medium_totalvat += $vatreturn->vat_amount;
                    } /* --end if 15% -- */
                    else if($vat_percentage == "12%")
                    {
                        $purchases_low_totalnet += $vatreturn->net_amount;
                        $purchases_low_totalvat += $vatreturn->vat_amount;
                    } /* --end if 12% -- */
                    else if($vat_percentage == "0%")
                    {
                        $purchases_zero_totalnet += $vatreturn->net_amount;
                        $purchases_zero_totalvat += $vatreturn->vat_amount;
                    } /* --end if 0% -- */
                    else if($vat_percentage == "11.11%")
                    {
                        $purchases_fish_totalnet += $vatreturn->net_amount;
                        $purchases_fish_totalvat += $vatreturn->vat_amount;
                    } /* --end if 11.11% -- */
                } /* --end if NO -- */                                                   
            } /* --end if PURCHASE -- */
        } /* --end for -- */
        /* --end VATRETUNS  -- */            
        /* --end GET VAT REG. -- */

        /* -- RENDER VIEW -- */
        $view = '';
        if($vatreg->country == 'GB')
            $view = view('_partials._content._vatreturn.submitting-fields-lazy', 
                compact(  
                    'vatregmain_status',
                    'vatreg_status',
                    'vatreg_is_disregard',

                    'vat_reg_id',
                    'pivs_files',
                    'c79_documents',
                    'submitting_fields',

                    'salestotalnet',
                    'salestotalvat',

                    'purchasetotalnet',
                    'purchasetotalvat'
                )
            )->render();  
        else if($vatreg->country == 'NO')
            $view = view('_partials._content._vatreturn.submitting-fields-NO-lazy', 
                compact(
                    'vatregmain_status',
                    'vatreg_status',
                    'vatreg_is_disregard',

                    'vat_reg_id',
                    'import_vat_files',
                    'submitting_fields',

                    'sales_standard_totalnet',
                    'sales_standard_totalvat',

                    'sales_medium_totalnet',
                    'sales_medium_totalvat',

                    'sales_low_totalnet',
                    'sales_low_totalvat',

                    'sales_zero_totalnet',
                    'sales_zero_totalvat',

                    'sales_fish_totalnet',
                    'sales_fish_totalvat',

                    'purchases_standard_totalnet',
                    'purchases_standard_totalvat',

                    'purchases_medium_totalnet',
                    'purchases_medium_totalvat',

                    'purchases_low_totalnet',
                    'purchases_low_totalvat',

                    'purchases_zero_totalnet',
                    'purchases_zero_totalvat',

                    'purchases_fish_totalnet',
                    'purchases_fish_totalvat',

                    'salestotalnet',
                    'salestotalvat',

                    'purchasetotalnet',
                    'purchasetotalvat'
                )
            )->render();
        else if($vatreg->country == 'CH')
            $view = view('_partials._content._vatreturn.submitting-fields-CH', 
                compact(
                    'vatregmain_status',
                    'vatreg_status',
                    'vatreg_is_disregard',
                    
                    'vat_reg_id',                   
                   
                    'salestotalnet',
                    'purchasetotalvat',
                   
                    'sales_standard_totalnet',
                    'sales_standard_totalvat',

                    'sales_reduced_totalnet',
                    'sales_reduced_totalvat',

                    'submitting_fields'                    
                )
            )->render();
        /* --end RENDER VIEW -- */

        /* -- RETURN JSON -- */
        return response()->json(
            [
                'status' => 200,
                'view' => $view
            ]
        );
        /* --end RETURN JSON -- */         
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Task Controller',
            'method' => 'loadSubmittingFieldsTab',
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
    /* --end GET /vat-return-submittingfields-tab/{vat_reg_id} -- */

    /*-- GET vat-return-control-tab/{vat_reg_id}*/
    public function loadVatReturnControlTab(Request $request, $vat_reg_id)
    {    
        $authUser = $this->authUser;
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();

        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
        
        if(empty($vatreg))
            return false;        

        $data = $this->commonClass->loadControlFiles($this->authUser, $vatreg, $systemapi);   

        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 

        $vat_reg_main = $vatreg->vatregmain;
        $vatregmain_status = $vat_reg_main->status;

        $files = $vatreg->vatcontrolfiles;
        $client = $vatreg->client;
        $client_id = $client->id;
        $file_type = 'vatcontrol';
        $file_type_title = 'VAT Control';

        $client_users = $this->commonClass->getClientUsersForEmail($client_id, $file_type);        

        $view = view('_partials._content._vatreturn.file-list-lazy', compact('files', 'authUser', 'client', 'client_users', 'client_id', 'vat_reg_id', 'file_type_title', 'file_type', 'vatreg', 'vat_reg_main', 'vatregmain_status'))->render();


        if(isset($data['message']))
        {
            return response()->json(
                [
                    'status' => 400,  
                    'view' => $view,           
                    'error' => $data['message']
                ]
            ); 
        }
        else
        {            
            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
            );  
        }                 
    }
    /*--end GET vat-return-control-tab/{vat_reg_id}*/

    /* -- GET import-reconciliation-tab/{vat_reg_id}*/
    public function loadImportReconciliationTab(Request $request, $client_id)
    {       
        try    
        {    
            /* -- GET VAT REG.s -- */
            $vatregs_result = $this->commonClass->getAllVatRegQuery($this->authUser, $client_id, false); 
            $result = $vatregs_result->filter(function ($vatreg, $key) {         
                return ($vatreg->client->status === 1 && $vatreg->vatregmain->status === 1); 
            });
            /* --end GET VAT REG.s -- */
            
            /* -- AUTH USER -- */
            $authUser = $this->authUser;
            /* --end AUTH USER -- */
            
            $check_product_type = 2;
            $accordion_name = 'ImportReconciliation';

            /* -- RENDER VIEW -- */
            $view = view('_partials._content._vatreturn.vatreturns-all-tasks-lazy', 
                    compact(
                        'authUser', 
                        'result',

                        'check_product_type',
                        'accordion_name'
                    )
                )->render();
            /* --end RENDER VIEW -- */

            /* -- RETURN JSON -- */
            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
            );                            
            /* --end RETURN JSON -- */
        }      
        catch (\Exception $e) 
        {           
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
                [
                    'status' => 'Error',
                    'controller' => 'Task Controller',
                    'method' => 'loadImportReconciliationTab',
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
    /* --end GET import-reconciliation-tab/{vat_reg_id}*/

    /*-- GET import-reconciliation-overview-tab/{vat_reg_id}*/
    public function loadImportReconciliationOverviewTabLazy(Request $request, $vat_reg_id)
    {                  
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();

        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
            
        $view = null;
        if($vatreg)        
        {
            $vatregmain_status = $vatreg->vatregmain->status;

            if($vatregmain_status)
            {
                $refresh = (isset($request->refresh)) ? $request->refresh : false;

                if($refresh == "true")
                {                                
                    //From AZURE
                    $data = $this->commonClass->loadImportReconciliationDatasFromAzureDb($this->authUser, $vatreg); 
                    
                    $result = $data;        

                    $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
                }
                          
                $vatregmain_status = $vatreg->vatregmain->status;

                $client = ($vatreg->client) ? $vatreg->client : [];        
                $client_users = ($client) ? $client->userclient : [];  

                $import_vat_files = [];
                if($vatreg->country == 'NO')
                {
                    $import_vat_files = ($vatreg->importvatfiles) ? $vatreg->importvatfiles : [];

                    if($import_vat_files)
                    {
                      $filtered_import_vat_files = $import_vat_files->filter(function ($import_vat_file, $key) {         
                          return $import_vat_file->file_type == 'xml'; 
                      });

                      $import_vat_files = $filtered_import_vat_files;          
                    }
                }
                
                $importreconciliationcominvoices = $vatreg->importreconciliationcominvoices;  
                $importreconciliationsalesinvoices = $vatreg->importreconciliationsalesinvoices;  

                $show_importreconciliation = 0;            
                if(count($import_vat_files) == 0 && count($importreconciliationsalesinvoices) == 0)
                    $show_importreconciliation = 0;            
                else
                    $show_importreconciliation = 1; 

                $view = view('_partials._content._importreconciliation.importreconciliation-overview',
                    compact(
                        'vatreg',
                        'vat_reg_id',
                        'vatregmain_status',
                        'client_users',
                        'import_vat_files',
                       
                        'importreconciliationcominvoices',
                        'importreconciliationsalesinvoices',
                        'show_importreconciliation'
                    )
                )->render(); 
            } 
            else
            {
                return response()->json(
                    [
                        'status' => 400,             
                        'error' => "Inactive VAT Reg.",
                        'view' => $view
                    ]
                );
            }   
        } // has vatreg

        if($view)
        {
            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
            );            
        }
        else        
            return response()->json(
                [
                    'status' => 400,             
                    'error' => "Azure DB Connection failed",
                    'view' => $view
                ]
            );      
                          
    }
    /*--end GET import-reconciliation-overview-tab/{vat_reg_id}*/

    /*-- GET import-reconciliation-overview-tab-sales-invoice-vat-amount/{vat_reg_id}*/
    public function loadImportReconciliationOverviewTabSalesInvoiceVatAmount(Request $request, $vat_reg_id)
    {                         
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id);         
        
        if($vatreg)      
        {
            $vatregmain_status = $vatreg->vatregmain->status;

            if($vatregmain_status)
            {
                $importreconciliationsalesinvoices = $vatreg->importreconciliationsalesinvoices;  
               
                $sales_invoice_vat_amount = [];
                if(count($importreconciliationsalesinvoices) > 0)
                {         
                    for ($i = 0; $i < $vatreg->frequency; $i++) 
                    {
                        $month_year = \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('m-Y');
                    
                        $filtered_importreconciliationsalesinvoices = $importreconciliationsalesinvoices->filter(function ($importreconciliationsalesinvoice, $key) use ($month_year) {         
                            return \Carbon\Carbon::parse($importreconciliationsalesinvoice->invoice_date)->format('m-Y') == $month_year;
                        });

                        $sales_invoice_vat_amount[$month_year] = $filtered_importreconciliationsalesinvoices->sum('vat_amount');
                    }
                }        
               
                if($sales_invoice_vat_amount)
                {
                    return response()->json(
                        [
                          'status' => 200,             
                          'sales_invoice_vat_amount' => $sales_invoice_vat_amount
                        ]
                    );            
                }
                else        
                    return response()->json(
                        [
                            'status' => 400,             
                            'error' => "Sales Invoices VAT Amount failed"
                        ]
                    );
            } 
            return response()->json(
                        [
                            'status' => 400,             
                            'error' => "Inactive VAT Reg."
                        ]
                    );     
        }                
    }
    /*--end GET import-reconciliation-overview-tab-sales-invoice-vat-amount/{vat_reg_id}*/

    /* -- GET import-reconciliation-history-tab/{vat_reg_id}*/
    public function loadImportReconciliationHistoryTab(Request $request, $vat_reg_id)
    {       
        $client = VATRegistration::with([
                                'client'                                    
                            ])   
                            ->select(['id',       
                                'client_id'
                            ])                         
                            ->where('id', $vat_reg_id)                           
                            ->first();

        if($client)                    
        {
            $client_id = $client->client_id;
                                
            $histories = $this->commonClass->getImportReconciliationTimeline($vat_reg_id);
            
            $view = view('_partials._content._importreconciliation.importreconciliation-history', 
                compact(
                    'client_id',
                    'vat_reg_id',
                    'histories'            
                )
            )->render();  

            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
              );
        }
        else
            return response()->json(
                [
                  'status' => 200,             
                  'view' => ""
                ]
              );    
    }
    /* --end GET import-reconciliation-history-tab/{vat_reg_id}*/ 

    /* -- GET import-reconciliation-notes-tab/{vat_reg_id}*/
    public function loadImportReconciliationNotesTab(Request $request, $vat_reg_id)
    {                               
        $client_id = $request->client_id;

        $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id, [], []);
        $vatreg_country = $vatreg->country;

        $general_notes = $this->commonClass->getImportReconciliationNotes('general', $client_id);
        $specific_notes = $this->commonClass->getImportReconciliationNotes('specific', $vat_reg_id);

        $importreconciliation_notes = $general_notes->merge($specific_notes)->sortByDesc('created_at')->values();
        
        $authUser = $this->authUser;

        $view = view('_partials._content._importreconciliation.notes', 
            compact(
                'authUser',
                'vat_reg_id',
                'importreconciliation_notes',
                'vatreg_country'
            )
        )->render();  

        return response()->json(
            [
              'status' => 200,             
              'view' => $view
            ]
        );        
    } 
    /* --end GET import-reconciliation-notes-tab/{vat_reg_id}*/

    /* -- POST import-reconciliation-notes-tab/{vat_reg_id}*/
    public function postImportReconciliationNotes(Request $request, $vat_reg_id)
    {                               
        $vatreg = VATRegistration::with([
                                'client', 'vatregmain'                                    
                            ])                                               
                            ->where('id', $vat_reg_id)                           
                            ->first();

        if($vatreg)                    
        {
            $client_id = $vatreg->client_id;
            $vatregmain_status = $vatreg->vatregmain->status;

            if($vatregmain_status)
            {
                $note_id = $request->ir_note_id;     
                $countries = implode(', ', $request->importreconciliation_selectedCountries);

                if($note_id)            
                    $importreconciliation_note = ImportReconciliationNotes::updateOrCreate(    
                        ['id' => $note_id],
                        [
                            'client_id' => $client_id, 
                            'vat_reg_id' => $vat_reg_id, 
                            'type' => $request->importreconciliation_note_type,                             
                            'notes' => $request->importreconciliation_note_quill,
                            'countries' => $countries,
                            'created_by' => $this->authUser->user_id
                        ]
                    );
                else         
                    $importreconciliation_note = ImportReconciliationNotes::updateOrCreate(                    
                    [
                        'client_id' => $client_id, 
                        'vat_reg_id' => $vat_reg_id, 
                        'type' => $request->importreconciliation_note_type,                             
                        'notes' => $request->importreconciliation_note_quill,
                        'countries' => $countries,
                        'created_by' => $this->authUser->user_id
                    ]
                );

                $this->commonClass->addLog($this->authUser, 'importreconciliation-notes-'. (($note_id) ? 'update' : 'add'), 
                    [
                      'Client Name' => $vatreg->client->client_name,
                      'type' => $request->importreconciliation_note_type,
                      'month' => Carbon::parse($vatreg->service_start)->format('M Y')
                    ]
                );

                event(new ImportReconciliationNotesEvent($vat_reg_id, $request->importreconciliation_note_type . ' note has been '. (($note_id) ? 'updat' : 'add') .'ed.'));  

                return response()->json(
                    [
                      'status' => 200,
                      'message' => 'Created',
                      'client_id' => $client_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                );
            }   
            else
            {
                return response()->json(
                    [
                      'status' => 400,
                      'message' => 'You cannot create note for inactive VAT Reg.',
                      'client_id' => $client_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                );
            }
        }     
    }
    /* --end POST import-reconciliation-notes-tab/{vat_reg_id}*/
    
    /* -- DELETE import-reconciliation-notes-tab/{note_id}*/
    public function deleteImportReconciliationNotes(Request $request, $note_id)
    {       
        $vat_reg_id = $request->vat_reg_id;
        $vatreg = VATRegistration::with([
                                'client', 'vatregmain'                                   
                            ])                              
                            ->where('id', $vat_reg_id)                           
                            ->first();

        if($vatreg)                    
        {
            $client_id = $vatreg->client_id;
            $vatregmain_status = $vatreg->vatregmain->status;

            if($vatregmain_status)
            {
                $importreconciliation_note = ImportReconciliationNotes::where('id', $note_id)->first();
                $importreconciliation_note_type = $importreconciliation_note->type;

                $importreconciliation_note->delete();

                $this->commonClass->addLog($this->authUser, 'importreconciliation-notes-delete', 
                    [
                      'Client Name' => $vatreg->client->client_name,
                      'type' => $importreconciliation_note_type,
                      'month' => Carbon::parse($vatreg->service_start)->format('M Y')
                    ]
                );

                event(new ImportReconciliationNotesEvent($vat_reg_id, $importreconciliation_note_type . ' note has been deleted.'));  

                return response()->json(
                    [
                      'status' => 200,
                      'message' => 'Deleted',
                      'client_id' => $client_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                );
            }
            else
            {
                return response()->json(
                    [
                      'status' => 400,
                      'message' => 'You cannot delete note for inactive VAT Reg.',
                      'client_id' => $client_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                );
            }
        }      
    }
    /* --end DELETE import-reconciliation-notes-tab/{vat_reg_id}*/

    /*-- GET import-reconciliation-control-tab/{vat_reg_id}*/
    public function loadImportReconciliationControlTab(Request $request, $vat_reg_id)
    {    
        $authUser = $this->authUser;
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();

        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
        
        if(empty($vatreg))
            return false;        

        $data = $this->commonClass->loadControlFiles($this->authUser, $vatreg, $systemapi, 'ircontrol');   

        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 

        $vat_reg_main = $vatreg->vatregmain;
        $vatregmain_status = $vat_reg_main->status;

        $files = $vatreg->ircontrolfiles;
        $client = $vatreg->client;
        $client_id = $client->id;
        $file_type = 'ircontrol';
        $file_type_title = 'Import Reconciliation Control';

        $client_users = $this->commonClass->getClientUsersForEmail($client_id, $file_type);        

        $view = view('_partials._content._importreconciliation.file-list-lazy', compact('files', 'authUser', 'client', 'client_users', 'client_id', 'vat_reg_id', 'file_type_title', 'file_type', 'vatreg', 'vat_reg_main', 'vatregmain_status'))->render();


        if(isset($data['message']))
        {
            return response()->json(
                [
                    'status' => 400,  
                    'view' => $view,           
                    'error' => $data['message']
                ]
            ); 
        }
        else
        {            
            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
            );  
        }                 
    }
    /*--end GET import-reconciliation-control-tab/{vat_reg_id}*/
}
