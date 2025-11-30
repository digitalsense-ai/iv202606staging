<?php

namespace App\Http\Controllers\vatregmain;

use App\Http\Controllers\Controller;
use App\Models\VATRegistrationMain;
use App\Models\VATRegistration;
use App\Models\Client;
use App\Models\ClientApi;
use App\Models\VATRegistrationMainAccNos;
use App\Models\VATRegistrationMainCasDdaMonths;
use App\Models\VATReturns;
use App\Models\VATReturnFiles;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use \App\Classes\CommonClass;
use \App\Classes\EconomicApiClass;
use \App\Classes\EmailBoxApiClass;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class VATRegistrationMainController extends Controller
{  
    public $authUser;
   
    public $commonClass;
    public $economicApiClass;
    public $emailBoxApiClass;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            
            $this->commonClass = new CommonClass();
            $this->emailBoxApiClass = new EmailBoxApiClass();
            $this->authUser = $this->commonClass->getAuthUser();  

            if($this->authUser->role == 'client-user')            
              $this->clientIds = $this->commonClass->getClientIdsForClientUser($this->authUser);        
            
            $this->economicApiClass = new EconomicApiClass();

            return $next($request);
        });
    }        

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($client_id)
    {
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser); 
       
        /* -- GET COMPANY/CLIENT -- */
        $client = $this->commonClass->getCompanyLazy($client_id);
        /* --end GET COMPANY/CLIENT -- */

        /* -- GET EXCEL COLUMNS -- */
        $excel_columns = $this->commonClass->listExcelColumns();
        /* --/ GET EXCEL COLUMNS -- */

        /* -- GET EXCEL COLUMN TEMPLATES -- */       
        $anyexcel_templates_result = $this->commonClass->getAnyExcelTemplates();
        $anyexceltemplates = $anyexcel_templates_result->filter(function ($anyexcel_template) use($client_id) {
            return ($anyexcel_template->client_id == $client_id); 
        });
        /* --/ GET EXCEL COLUMN TEMPLATES -- */ 
       
        if($this->authUser->role == 'client-user') 
        {
            // GET COMPANIES OF THE LOGGED IN USER
            $companies = Client::whereIn('id', $this->clientIds)->get();                               
            // END GET COMPANIES OF THE LOGGED IN USER  

            $client_connections = ClientApi::where('client_id', $client_id)
                  ->where('connection_status','=',  1)
                  ->get();                 
            // END GET CONNECTION     

            if(count($client_connections) == 0)                      
              $showSelectbox = "false";                     
            else                      
              $showSelectbox = "true"; 

            return view('content.vatregmain.create-lazy',
              [
                'pageConfigs' => $pageConfigs, 
                'authUser' => $this->authUser,
                'client_id' => $client_id, 
                'client_name' => $client->client_name,
                'client_connections' => $client_connections,
                'showSelectbox' => $showSelectbox,
                'title' => 'Creation',
                'excel_columns' => $excel_columns,               
                'anyexcel_templates' => $anyexceltemplates
              ]);
        }
        else  
          return view('content.vatregmain.create-lazy', 
            [
              'pageConfigs' => $pageConfigs, 
              'authUser' => $this->authUser, 
              'client_id' => $client_id, 
              'client_name' => $client->client_name, 
              'title' => 'Creation',            
              'excel_columns' => $excel_columns,             
              'anyexcel_templates' => $anyexceltemplates
            ]
          );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $vatRegMainID = $request->vat_reg_main_id;
        
        //Product Type: 
        //1 - NUF VAT Return
        //2 - Import Reconciliation
        //3 - NUF VAT Return & Import Reconciliation
        //4 - VOEC VAT Return
        //5 - VOEC VAT Return & Import Reconciliation

        $product_type = 0;
        if(isset($request->product_type))
        {
          if(count($request->product_type) == 1)       
            $product_type = (int)$request->product_type[0];       
          else if(count($request->product_type) == 2)
          {
            $choosed_product_type = (int)$request->product_type[0];

            if($choosed_product_type == 1)
              $product_type = 3;
            else
              $product_type = 5;
          }
        }
        
        if ($vatRegMainID) 
        {
            // create new one if details is unique           
            $vatReg = VATRegistrationMain::where('client_id', $request->client_id)
                    ->where('country', $request->country)                  
                    ->where('id', $vatRegMainID)
                    ->first();
           
                // update the value
                $vatRegs = VATRegistrationMain::updateOrCreate(
                  ['id' => $vatRegMainID],
                  [                
                      'country' => $request->country,                     
                      'product_type' => $product_type,
                      'cash_acc_stmt' => ($request->cash_acc_stmt) ? $request->cash_acc_stmt : 0,
                      'duty_defer_acc' => ($request->duty_defer_acc) ? $request->duty_defer_acc : 0,

                      'dda_acc_no' => ($request->dda_acc_no) ? $request->dda_acc_no : NULL,
                      'dda_acc_limit' => ($request->dda_acc_limit) ? $request->dda_acc_limit : NULL,

                      'oss' => ($request->oss) ? $request->oss : 0,
                      'excise_duty' => ($request->excise_duty) ? $request->excise_duty : 0,
                      'account_nos' => ($request->account_nos) ? 1 : 0,                   
                      'vat_no' => ($request->vat_no) ? $request->vat_no : NULL,
                      'eori_no' => ($request->eori_no) ? $request->eori_no : NULL,
                      'cash_account_no' => ($request->cash_account_no) ? $request->cash_account_no : NULL,

                      'mva_no' => ($request->mva_no) ? $request->mva_no : NULL,
                      'org_no' => ($request->org_no) ? $request->org_no : NULL,

                      'zaz_no' => ($request->zaz_no) ? $request->zaz_no : NULL,
                      'steuer_no' => ($request->steuer_no) ? $request->steuer_no : NULL,
                      'cvr_no' => ($request->cvr_no) ? $request->cvr_no : NULL,
                      'omz_no' => ($request->omz_no) ? $request->omz_no : NULL,
                      'nip_no' => ($request->nip_no) ? $request->nip_no : NULL,
                      'fo_no' => ($request->fo_no) ? $request->fo_no : NULL,
                      'siret_no' => ($request->siret_no) ? $request->siret_no : NULL,
                      'nif_no' => ($request->nif_no) ? $request->nif_no : NULL,
                      'nipc_no' => ($request->nipc_no) ? $request->nipc_no : NULL,

                      'uk_gateway_userid' => ($request->uk_gateway_userid) ? $request->uk_gateway_userid : NULL,
                      'uk_gateway_password' => ($request->uk_gateway_password) ? $request->uk_gateway_password : NULL,
                      'cds_gateway_userid' => ($request->cds_gateway_userid) ? $request->cds_gateway_userid : NULL,
                      'cds_gateway_password' => ($request->cds_gateway_password) ? $request->cds_gateway_password : NULL,

                      'anyexcel_template_id' => ($request->anyexcel_template) ? $request->anyexcel_template : NULL,
                      
                      'status' => 1                      
                  ]
                );//Draft Created (VAT reg. created)  
               
                if($request->established_connection)
                {                  
                  $clientapi = ClientApi::updateOrCreate(   
                      ['id' => $request->established_connection],                 
                      [                       
                        'vat_reg_main_id' => $vatRegs->id                              
                      ]
                    );

                    /* Client-user - Established Economic connection */
                    $established_connection_type = explode(',', $request->established_connection);
                    $erp_options =  $established_connection_type[1];
                    if($erp_options == "E-conomic")
                    {
                      if($request->account_nos)  
                      {                       
                        $vataccnos_delete = VATRegistrationMainAccNos::where('vat_reg_main_id', $vatRegs->id)->delete();
                        $selected_account_datas = explode('***', $request->selected_acc_nos);

                        foreach($selected_account_datas as $selected_account_data) 
                        {
                          if($selected_account_data != "")
                          {
                            $selected_account_details = explode('%%%', $selected_account_data);

                            $acc_no = ($selected_account_details[0]) ? $selected_account_details[0] : null;
                            $acc_name = ($selected_account_details[1]) ? $selected_account_details[1] : null;
                            $acc_type = ($selected_account_details[2]) ? $selected_account_details[2] : null;
                            $acc_reverse = ($selected_account_details[3] == "1") ? 1 : 0;
                            $acc_auto_vat_check = ($selected_account_details[4]) ? $selected_account_details[4] : 0;
                            $acc_map_column = ($selected_account_details[5]) ? $selected_account_details[5] : null;

                            $vataccnos = VATRegistrationMainAccNos::updateOrCreate(
                              [                                  
                                'vat_reg_main_id' => $vatRegs->id, 
                                'acc_no' => $acc_no,
                                'acc_name' => $acc_name,
                                'acc_type' => $acc_type,
                                'is_reverse' => $acc_reverse,
                                'is_auto_vat_check' => $acc_auto_vat_check,
                                'map_column' => $acc_map_column
                              ]
                            );
                          }
                        }
                      }
                      else                      
                        $vataccnos_delete = VATRegistrationMainAccNos::where('vat_reg_main_id', $vatRegs->id)->delete();
                    }
                    /* End Client-user - Established Economic connection */
                }
                else
                {
                  if($request->erp_options != null)
                  {
                    $currency_code = "USD";
                    if($request->country == "GB")
                      $currency_code = "GBP";
                    else if($request->country == "DK")
                      $currency_code = "DKK";
                    else if($request->country == "NO")
                      $currency_code = "NOK";
                    else if($request->country == "FR")
                      $currency_code = "EUR";
                    else if($request->country == "CH")
                      $currency_code = "CHF";
                      
                    if($request->erp_options == "Dynamics 365")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Dynamics 365",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://api.businesscentral.dynamics.com",
                          'api_tenant_id' => ($request->api_tenant_id) ? $request->api_tenant_id : null,
                          'api_client_id' => ($request->api_client_id) ? $request->api_client_id : null,
                          'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    } 
                    else if($request->erp_options == "Dynamics 365 via SmartApi")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Dynamics 365 via SmartApi",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => 'dummy',
                          'sales_invoice_url' => ($request->sales_invoice_url) ? $request->sales_invoice_url : null,
                          'purchase_invoice_url' => ($request->purchase_invoice_url) ? $request->purchase_invoice_url : null,
                          'api_client_id' => 'dummy',
                          'api_secret_key' => 'dummy',
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }                      
                    else if($request->erp_options == "E-conomic")
                    {
                      if($request->account_nos)  
                      {                       
                        $vataccnos_delete = VATRegistrationMainAccNos::where('vat_reg_main_id', $vatRegs->id)->delete();
                        $selected_account_datas = explode('***', $request->selected_acc_nos);

                        foreach($selected_account_datas as $selected_account_data) 
                        {
                          if($selected_account_data != "")
                          {
                            $selected_account_details = explode('%%%', $selected_account_data);
                          
                            $acc_no = ($selected_account_details[0]) ? $selected_account_details[0] : null;
                            $acc_name = ($selected_account_details[1]) ? $selected_account_details[1] : null;
                            $acc_type = ($selected_account_details[2]) ? $selected_account_details[2] : null;
                            $acc_reverse = ($selected_account_details[3] == "1") ? 1 : 0;
                            $acc_auto_vat_check = ($selected_account_details[4]) ? $selected_account_details[4] : 0;
                            $acc_map_column = ($selected_account_details[5]) ? $selected_account_details[5] : null;
                            
                            $vataccnos = VATRegistrationMainAccNos::updateOrCreate(
                              [                                  
                                'vat_reg_main_id' => $vatRegs->id, 
                                'acc_no' => $acc_no,
                                'acc_name' => $acc_name,
                                'acc_type' => $acc_type,
                                'is_reverse' => $acc_reverse,
                                'is_auto_vat_check' => $acc_auto_vat_check,
                                'map_column' => $acc_map_column                                  
                              ]
                            );
                          }
                        }
                      }
                      else                      
                        $vataccnos_delete = VATRegistrationMainAccNos::where('vat_reg_main_id', $vatRegs->id)->delete();

                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "E-conomic",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://restapi.e-conomic.com",
                          'api_tenant_id' => null,
                          'api_client_id' => ($request->api_client_id) ? $request->api_client_id : null,
                          'api_secret_key' => "2NBwnBEXouJc1klye2sX05tHflCaIXZObXJ0yuksRDM1",
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1,                      
                        ]
                      );
                    }                  
                    else if($request->erp_options == "Uniconta")
                    {
                      /* -- GET COMPANY NAME -- */
                      $client = $this->commonClass->getCompanyLazy($request->client_id);
                      $client_name = ($client) ? $client->client_name : '';
                      /* --end GET COMPANY NAME -- */

                      $prefix = (strlen($request->api_client_id) == 5) ? '0' : '';
                      $suffix = (strtolower($client_name) == 'alustre p/s') ? '/digitalvat' : '/intravat';
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Uniconta",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://odata.uniconta.com/odata",
                          'api_tenant_id' => null,
                          'api_client_id' => ($request->api_client_id) ? ($prefix . $request->api_client_id . $suffix) : null,
                          'api_secret_key' => 'Urges905',
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }
                    else if($request->erp_options == "Shopify")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Shopify",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://" . (($request->api_base_url) ? $request->api_base_url : 'quickstart-ad1f592d') . ".myshopify.com",
                          'api_tenant_id' => ($request->api_tenant_id) ? $request->api_tenant_id : null,
                          'api_client_id' => ($request->api_client_id) ? $request->api_client_id : null,
                          'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }   
                    else if($request->erp_options == "Billy")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Billy",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://api.billysbilling.com/v2",
                          'api_tenant_id' => null,
                          'api_client_id' => 'dummy',
                          'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }               
                    else if($request->erp_options == "FTP")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "FTP",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "165.22.78.137",
                          'api_tenant_id' => null,
                          'api_client_id' => "root",
                          'api_secret_key' => "dinV@13at",
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );

                      //HAVE TO CREATE FTP AUTOMATICALLY
                    } 
                    else if($request->erp_options == "Excel Upload")
                    {
                      //Delete Client API & relevant tables
                      $clientapi = ClientApi::where('vat_reg_main_id', $vatRegMainID)->delete();     

                      //Get vat_reg_id if any
                      $vatregs = VATRegistration::where('vat_reg_main_id', $vatRegMainID)->get();  

                      foreach($vatregs as $key=>$vatreg)
                      {
                        $vat_reg_id = $vatreg->id;

                        //Delete relevant tables                       
                        $vatreturns = VATReturns::where('vat_reg_id', $vat_reg_id)->delete();     
                        $vatreturnfiles = VATReturnFiles::where('vat_reg_id', $vat_reg_id)->delete();   
                      } 
                    } // End EXCEL UPLOAD                                    
                  }
                }

                $newVatReg = $this->commonClass->getActiveVATRegMain($request->client_id, $request->country);
                $this->commonClass->addLog($this->authUser, 'vatregmain-update', 
                  [
                    'Client Name' => (count($newVatReg) == 1) ? $newVatReg[0]->client_name : ""
                  ]
                );
                // user updated
                return response()->json('Updated');           
        } 
        else 
        {    
            // create new one if details is unique
            $vatReg = VATRegistrationMain::where('client_id', $request->vat_client_id)
                    ->where('country', $request->country)                    
                    ->first();

            if (empty($vatReg)) 
            {        
                $service_startexplode = explode('/', $request->service_start);
                $service_start = Carbon::parse($service_startexplode[1].'/'.$service_startexplode[0].'/01')->format('Y-m-d');
                
                $vatRegs = VATRegistrationMain::updateOrCreate(
                  ['id' => $vatRegMainID],
                  [                    
                    'client_id' => $request->client_id, 
                    'country' => $request->country,
                    'service_start' => $service_start,                   
                    'general_periods' => $request->general_periods,                   
                    'product_type' => $product_type,
                    'cash_acc_stmt' => ($request->cash_acc_stmt) ? $request->cash_acc_stmt : 0,
                    'duty_defer_acc' => ($request->duty_defer_acc) ? $request->duty_defer_acc : 0,

                    'dda_acc_no' => ($request->dda_acc_no) ? $request->dda_acc_no : NULL,
                    'dda_acc_limit' => ($request->dda_acc_limit) ? $request->dda_acc_limit : NULL,

                    'oss' => ($request->oss) ? $request->oss : 0,
                    'excise_duty' => ($request->excise_duty) ? $request->excise_duty : 0,
                    'account_nos' => ($request->account_nos) ? 1 : 0,                   
                    'vat_no' => ($request->vat_no) ? $request->vat_no : NULL,
                    'eori_no' => ($request->eori_no) ? $request->eori_no : NULL,
                    'cash_account_no' => ($request->cash_account_no) ? $request->cash_account_no : NULL,

                    'mva_no' => ($request->mva_no) ? $request->mva_no : NULL,
                    'org_no' => ($request->org_no) ? $request->org_no : NULL,
                   
                    'zaz_no' => ($request->zaz_no) ? $request->zaz_no : NULL,
                    'steuer_no' => ($request->steuer_no) ? $request->steuer_no : NULL,
                    'cvr_no' => ($request->cvr_no) ? $request->cvr_no : NULL,
                    'omz_no' => ($request->omz_no) ? $request->omz_no : NULL,
                    'nip_no' => ($request->nip_no) ? $request->nip_no : NULL,
                    'fo_no' => ($request->fo_no) ? $request->fo_no : NULL,
                    'siret_no' => ($request->siret_no) ? $request->siret_no : NULL,
                    'nif_no' => ($request->nif_no) ? $request->nif_no : NULL,
                    'nipc_no' => ($request->nipc_no) ? $request->nipc_no : NULL,

                    'uk_gateway_userid' => ($request->uk_gateway_userid) ? $request->uk_gateway_userid : NULL,
                    'uk_gateway_password' => ($request->uk_gateway_password) ? $request->uk_gateway_password : NULL,
                    'cds_gateway_userid' => ($request->cds_gateway_userid) ? $request->cds_gateway_userid : NULL,
                    'cds_gateway_password' => ($request->cds_gateway_password) ? $request->cds_gateway_password : NULL,
                                          
                    'anyexcel_template_id' => ($request->anyexcel_template) ? $request->anyexcel_template : NULL,
                        
                    'status' => 1 
                  ]
                );//Draft Created (VAT reg. created)  

                //Create Email
                /* -- LIST -- */
                $email_lists = $this->emailBoxApiClass->getEmailLists();
                /* --end LIST -- */            

                /* -- ADD -- */
                /* -- GET VAT REG. MAIN -- */
                $vatregmains = $this->commonClass->getVatRegMainLazy();
                /* --end GET VAT REG. MAIN -- */

                $email_created = [];
                foreach($vatregmains as $key => $vatregmain)
                {
                    $country = strtolower($vatregmain->country);

                    $client = $vatregmain->client;                
                    $client_name = str_replace(' ', '', $this->commonClass->replaceSpecialCharForFolderName(strtolower($client->client_name)));

                    $create_email = $country . '.' . $client_name . '@intravat.cloud';
                    $password = '12345678';

                    $email_exist = array_values(array_filter($email_lists, function ($email) use($create_email) {
                        return $create_email == $email;
                    }));

                    if(count($email_exist) == 0)
                    {
                        $result = $this->emailBoxApiClass->createEmailForCompany($create_email, $password);                   
                        $email_created[] = $create_email;

                        //update email in VATRegistrationMain
                        $updateEmail = VATRegistrationMain::where('id', $vatregmain->id)                            
                            ->update(
                              [
                                'email' => $create_email
                              ]
                            );
                    }
                }            
                /* --end ADD -- */
                //Create Email

                //update country and general_periods in VATRegistration
                $updateCountryGenralPeriod = VATRegistration::where('vat_reg_main_id', $vatRegMainID)                            
                            ->update(
                              [
                                'country' => $request->country,                           
                              ]
                            );

                
                if($request->established_connection)
                {                  
                  $clientapi = ClientApi::updateOrCreate(   
                      ['id' => $request->established_connection],                 
                      [                       
                        'vat_reg_main_id' => $vatRegs->id                              
                      ]
                    );

                    /* Client-user - Established Economic connection */
                    $established_connection_type = explode(',', $request->established_connection);
                    $erp_options =  $established_connection_type[1];
                    if($erp_options == "E-conomic")
                    {
                      if($request->account_nos)  
                      {                       
                        $vataccnos_delete = VATRegistrationMainAccNos::where('vat_reg_main_id', $vatRegs->id)->delete();
                        $selected_account_datas = explode('***', $request->selected_acc_nos);

                        foreach($selected_account_datas as $selected_account_data) 
                        {
                          if($selected_account_data != "")
                          {
                            $selected_account_details = explode('%%%', $selected_account_data);

                            $acc_no = ($selected_account_details[0]) ? $selected_account_details[0] : null;
                            $acc_name = ($selected_account_details[1]) ? $selected_account_details[1] : null;
                            $acc_type = ($selected_account_details[2]) ? $selected_account_details[2] : null;
                            $acc_reverse = ($selected_account_details[3] == "1") ? 1 : 0;
                            $acc_auto_vat_check = ($selected_account_details[4]) ? $selected_account_details[4] : 0;
                            $acc_map_column = ($selected_account_details[5]) ? $selected_account_details[5] : null;

                            $vataccnos = VATRegistrationMainAccNos::updateOrCreate(
                              [                                  
                              'vat_reg_main_id' => $vatRegs->id, 
                              'acc_no' => $acc_no,
                              'acc_name' => $acc_name,
                              'acc_type' => $acc_type,
                              'is_reverse' => $acc_reverse,
                              'is_auto_vat_check' => $acc_auto_vat_check,
                              'map_column' => $acc_map_column                                  
                              ]
                            );
                          }
                        }
                      }
                      else                      
                        $vataccnos_delete = VATRegistrationMainAccNos::where('vat_reg_main_id', $vatRegs->id)->delete();
                    }
                    /* End Client-user - Established Economic connection */
                }
                else
                {
                  if($request->erp_options != null)
                  {
                    $currency_code = "USD";
                    if($request->country == "GB")
                      $currency_code = "GBP";
                    else if($request->country == "DK")
                      $currency_code = "DKK";
                    else if($request->country == "NO")
                      $currency_code = "NOK";
                    else if($request->country == "FR")
                      $currency_code = "EUR";
                    else if($request->country == "CH")
                      $currency_code = "CHF";
                      
                    if($request->erp_options == "Dynamics 365")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Dynamics 365",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://api.businesscentral.dynamics.com",
                          'api_tenant_id' => ($request->api_tenant_id) ? $request->api_tenant_id : null,
                          'api_client_id' => ($request->api_client_id) ? $request->api_client_id : null,
                          'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }    
                    else if($request->erp_options == "Dynamics 365 via SmartApi")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Dynamics 365 via SmartApi",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => 'dummy',
                          'sales_invoice_url' => ($request->sales_invoice_url) ? $request->sales_invoice_url : null,
                          'purchase_invoice_url' => ($request->purchase_invoice_url) ? $request->purchase_invoice_url : null,
                          'api_client_id' => 'dummy',
                          'api_secret_key' => 'dummy',
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }                      
                    else if($request->erp_options == "E-conomic")
                    {
                      if($request->account_nos)  
                      {                       
                        $vataccnos_delete = VATRegistrationMainAccNos::where('vat_reg_main_id', $vatRegs->id)->delete();
                        $selected_account_datas = explode('***', $request->selected_acc_nos);

                        foreach($selected_account_datas as $selected_account_data) 
                        {
                          if($selected_account_data != "")
                          {
                            $selected_account_details = explode('%%%', $selected_account_data);
                           
                            $acc_no = ($selected_account_details[0]) ? $selected_account_details[0] : null;
                            $acc_name = ($selected_account_details[1]) ? $selected_account_details[1] : null;
                            $acc_type = ($selected_account_details[2]) ? $selected_account_details[2] : null;
                            $acc_reverse = ($selected_account_details[3] == "1") ? 1 : 0;
                            $acc_auto_vat_check = ($selected_account_details[4]) ? $selected_account_details[4] : 0;
                            $acc_map_column = ($selected_account_details[5]) ? $selected_account_details[5] : null;
                           
                            $vataccnos = VATRegistrationMainAccNos::updateOrCreate(
                              [                                  
                                'vat_reg_main_id' => $vatRegs->id, 
                                'acc_no' => $acc_no,
                                'acc_name' => $acc_name,
                                'acc_type' => $acc_type,
                                'is_reverse' => $acc_reverse,
                                'is_auto_vat_check' => $acc_auto_vat_check,
                                'map_column' => $acc_map_column
                              ]
                            );
                          }
                        }
                      }
                      else                      
                        $vataccnos_delete = VATRegistrationMainAccNos::where('vat_reg_main_id', $vatRegs->id)->delete();

                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "E-conomic",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://restapi.e-conomic.com",
                          'api_tenant_id' => null,
                          'api_client_id' => ($request->api_client_id) ? $request->api_client_id : null,
                          'api_secret_key' => "2NBwnBEXouJc1klye2sX05tHflCaIXZObXJ0yuksRDM1",
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1,                     
                        ]
                      );
                    }                  
                    else if($request->erp_options == "Uniconta")
                    {
                      /* -- GET COMPANY NAME -- */
                      $client = $this->commonClass->getCompanyLazy($request->client_id);
                      $client_name = ($client) ? $client->client_name : '';
                      /* --end GET COMPANY NAME -- */

                      $prefix = (strlen($request->api_client_id) == 5) ? '0' : '';
                      $suffix = (strtolower($client_name) == 'alustre p/s') ? '/digitalvat' : '/intravat';
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Uniconta",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://odata.uniconta.com/odata",
                          'api_tenant_id' => null,                          
                          'api_client_id' => ($request->api_client_id) ? ($prefix . $request->api_client_id . $suffix) : null,
                          'api_secret_key' => 'Urges905',
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }
                    else if($request->erp_options == "Shopify")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Shopify",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          
                          'api_base_url' => "https://" . (($request->api_base_url) ? $request->api_base_url : 'quickstart-ad1f592d') . ".myshopify.com",
                          'api_tenant_id' => ($request->api_tenant_id) ? $request->api_tenant_id : null,
                          'api_client_id' => ($request->api_client_id) ? $request->api_client_id : '2024-07',
                          'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }    
                    else if($request->erp_options == "Billy")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "Billy",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "https://api.billysbilling.com/v2",
                          'api_tenant_id' => null,
                          'api_client_id' => 'dummy',
                          'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }              
                    else if($request->erp_options == "FTP")
                    {
                      $clientapi = ClientApi::updateOrCreate(   
                        ['vat_reg_main_id' => $vatRegMainID],                 
                        [
                          'client_id' => $request->client_id, 
                          'vat_reg_main_id' => $vatRegs->id, 
                          'api_name' => "FTP",
                          'api_env' => ($request->api_environment) ? $request->api_environment : "Production",
                          'api_base_url' => "165.22.78.137",
                          'api_tenant_id' => null,
                          'api_client_id' => "root",
                          'api_secret_key' => "dinV@13at",
                          'api_company_id' => null,
                          'currency_code' => $currency_code,
                          'status' => 1                
                        ]
                      );
                    }                     
                  }
                }
                $newVatReg = $this->commonClass->getActiveVATRegMain($request->client_id, $request->country);

                $this->commonClass->addLog($this->authUser, 'vatregmain-add', 
                  [
                    'Client Name' => (count($newVatReg) == 1) ? $newVatReg[0]->client_name : ""
                  ]
                );
                // user created
                return response()->json('Registered');   
            } 
            else {
                // user already exist           
                $this->commonClass->addLog($this->authUser, 'vatregmain-exists', 
                  [
                    'Client Name' => $vatReg->client_name
                  ]
                );
                return response()->json(['message' => "Country already exits"], 422);
            }        
        }        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VATRegistrationMain  $vATRegistrationMain
     * @return \Illuminate\Http\Response
     */
    public function edit(VATRegistrationMain $vATRegistrationMain, $id)
    {        
      $pageConfigs = $this->commonClass->getPageConfig($this->authUser); 
      
      $vatRegMain = $this->commonClass->getVATRegMain($id);
      
      $client_id = $vatRegMain->client_id;

      /* -- GET VAT REG MAIN ACCOUNT NOS -- */
      $vatRegMain_accnos= null;
      if($vatRegMain->account_nos != 0)
        $vatRegMain_accnos = $this->commonClass->getVATRegMainAccNos($id);   
      /* --END GET VAT REG MAIN ACCOUNT NOS -- */         

      /* -- GET EXCEL COLUMNS -- */
      $excel_columns = $this->commonClass->listExcelColumns();
      /* --/ GET EXCEL COLUMNS -- */

      /* -- GET EXCEL COLUMN TEMPLATES -- */     
      $anyexcel_templates_result = $this->commonClass->getAnyExcelTemplates();
      $anyexceltemplates = $anyexcel_templates_result->filter(function ($anyexcel_template) use($client_id) {
          return ($anyexcel_template->client_id == $client_id); 
      });
      /* --/ GET EXCEL COLUMN TEMPLATES -- */ 
      
      $this->commonClass->addLog($this->authUser, 'vatregmain-edit', 
        [
          'Client Name' => $vatRegMain->client_name
        ]
      );
      // return response()->json($vatReg);

      return view('content.vatregmain.create-lazy', 
        [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser, 
          'vatRegMain' => $vatRegMain, 
          'vatRegMain_accnos'=>  $vatRegMain_accnos, 
          'title' => 'Updation',          
          'excel_columns' => $excel_columns,        
          'anyexcel_templates' => $anyexceltemplates
        ]
      );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VATRegistration  $vATRegistration
     * @return \Illuminate\Http\Response
     */
    public function destroy(VATRegistrationMain $vATRegistrationMain, $id)
    {        
        $vatReg = $this->commonClass->getVATRegMain($id);

        $this->commonClass->addLog($this->authUser, 'vatregmain-delete', 
          [
            'Client Name' => $vatReg->client_name
          ]
        );

        $VATRegistrations = VATRegistration::where('client_id', $vatReg->client_id)
                              ->where('country', $vatReg->country)
                              ->delete();
        $VATRegistrationsMain = VATRegistrationMain::where('id', $id)->delete();

        $with_vat_reg_main = [                  
          'vatregmain'
        ];  
        $where_vat_reg_main = [
          'id' => ['operator' => '=', 'value' => $vatReg->client_id]
        ]; 
        $whereHas_vat_reg_main = [];    
        $orderBy_vat_reg_main = [];         
        $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first');  
       
        return response()->json([
          'status' => 200,             
          'client' => $client,
          'message' => 'Deleted'
        ]);  
    }

    /*URL vat-registration-main/erp-fields/$erp_id */
    public function loadERPFields(Request $request, $erpId)
    {   
      $api_connection = null;
      $api_vat_acc_no = null;
      if($request->vat_reg_main_id)
      {
        //GET API Connection
        $api_connection = $this->commonClass->getVATRegMainApiConnection($request->vat_reg_main_id);           
      }

      if($erpId == "dynamics_365")      
        $view = view('_partials._content._vatregmain.dynamic-365', compact('api_connection'))->render();  
      else if($erpId == "dynamics_365_via_smartapi")       
        $view = view('_partials._content._vatregmain.dynamic-365-smartapi', compact('api_connection'))->render();
      else if($erpId == "e_conomic")       
        $view = view('_partials._content._vatregmain.e-conomic', compact('api_connection'))->render();   
      else if($erpId == "uniconta")      
        $view = view('_partials._content._vatregmain.uniconta', compact('api_connection'))->render();   
      else if($erpId == "shopify")        
        $view = view('_partials._content._vatregmain.shopify', compact('api_connection'))->render(); 
      else if($erpId == "billy")       
        $view = view('_partials._content._vatregmain.billy', compact('api_connection'))->render();     
      else if($erpId == "ftp")     
        $view = view('_partials._content._vatregmain.ftp', compact('api_connection'))->render();  
      else
        $view = "";
        
      return response()->json(
        [
          'status' => 200,          
          'view' => $view                
        ]
      );
    }

    /*URL client-vat-info/$vat_id */
    public function getClientVATInfo($vat_registration_main_id)
    {            
      $client = $this->commonClass->getVATRegMain($vat_registration_main_id);
      
      $this->commonClass->addLog($this->authUser, 'vatregmain-click', 
        [
          'Client Name' => $client->client_name
        ]
      );      
      return response()->json($client);             
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $clientid
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {    
      $vatRegMain = $this->commonClass->getVATRegMain($id);

      if($this->authUser->role != 'client-user')        
      {    
        if ($id) 
        {          
          $updateMain = VATRegistrationMain::where('id', $id)->update(
              [                                        
                'status' => ($request->status == "true") ? 1 : 0
              ]
          );         
        
          $this->commonClass->addLog($this->authUser, 'vatregmain-update-status', 
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Status Text' => $request->statustext
            ]
          );
                          
          // updated      
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];    
          $orderBy_vat_reg_main = [];         
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first');  

          return response()->json([
            'status' => 200,             
            'client' => $client,
            'message' => $request->statustext.'d'
          ]);  
        } 
        else 
        {
            // already exist
            $this->commonClass->addLog($this->authUser, 'vatregmain-error-status');
            
            return response()->json(
              [
                'message' => "cannot ".$request->statustext
              ]
            , 422);
        }
      } 
      else 
      {
          // Authuser dont have permission
          $this->commonClass->addLog($this->authUser, 'vatregmain-error-auth-status',
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Status Text' => $request->statustext
            ]
          );
                 
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];   
          $orderBy_vat_reg_main = [];          
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first'); 
         
          return response()->json(
            [
              'status' => 422,   
              'client' => $client,
              'message' => "Don't have permission to ".$request->statustext
            ]
          , 200);
      }     
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $clientid
     * @return \Illuminate\Http\Response
     */
    public function updateCashAccountStatement(Request $request, $id)
    {      
      $vatRegMain = $this->commonClass->getVATRegMain($id);

      if($this->authUser->role != 'client-user')        
      {    
        if ($id) 
        {   
          $cas = ($request->cash_account_statement == "true") ? 1 : 0;           
          $updateMain = VATRegistrationMain::where('id', $id)->update(
              [                                        
                'cash_acc_stmt' => $cas
              ]
          );         
        
          if($cas)
          {
            $vatRegMainCasDdaMonths = VATRegistrationMainCasDdaMonths::updateOrCreate(
              [
                'vat_reg_main_id' => $id, 
                'month_year' => (Carbon::now()->format('m-Y'))
              ],
              [                
                'vat_reg_main_id' => $id,                
                'month_year' => (Carbon::now()->format('m-Y'))
              ]
            );
          }

          $this->commonClass->addLog($this->authUser, 'vatregmain-update-cash-account-statement', 
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Cash Account Statement Text' => $request->cash_account_statement_text
            ]
          );
                          
          // updated          
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];   
          $orderBy_vat_reg_main = [];          
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first');  

          return response()->json([
            'status' => 200,             
            'client' => $client,
            'message' => $request->cash_account_statement_text.'ed'
          ]);     
        } 
        else 
        {
            // already exist
            $this->commonClass->addLog($this->authUser, 'vatregmain-error-cash-account-statement');
            
            return response()->json(
              [
                'message' => "cannot ".$request->cash_account_statement_text
              ]
            , 422);
        }
      } 
      else 
      {
          // Authuser dont have permission
          $this->commonClass->addLog($this->authUser, 'vatregmain-error-auth-cash-account-statement',
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Cash Account Statement Text' => $request->cash_account_statement_text
            ]
          );
                  
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];   
          $orderBy_vat_reg_main = [];          
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first'); 
         
          return response()->json(
            [
              'status' => 422,   
              'client' => $client,
              'message' => "Don't have permission to ".$request->cash_account_statement_text
            ]
          , 200);
      }     
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $clientid
     * @return \Illuminate\Http\Response
     */
    public function updateDutyDefermentAccount(Request $request, $id)
    {      
      $vatRegMain = $this->commonClass->getVATRegMain($id);

      if($this->authUser->role != 'client-user')        
      {    
        if ($id) 
        {     
          $dda = ($request->duty_deferment_account == "true") ? 1 : 0;     
          $updateMain = VATRegistrationMain::where('id', $id)->update(
              [                                        
                'duty_defer_acc' => $dda
              ]
          );         
        
          if($dda)
          {
            $vatRegMainCasDdaMonths = VATRegistrationMainCasDdaMonths::updateOrCreate(
              [
                'vat_reg_main_id' => $id, 
                'month_year' => (Carbon::now()->format('m-Y'))
              ],
              [                
                'vat_reg_main_id' => $id,                
                'month_year' => (Carbon::now()->format('m-Y'))
              ]
            );
          }
          
          $this->commonClass->addLog($this->authUser, 'vatregmain-update-duty-deferment-account', 
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Duty Deferment Account Text' => $request->duty_deferment_account_text
            ]
          );
                          
          // updated     
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];
          $orderBy_vat_reg_main = [];             
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first');  

          return response()->json([
            'status' => 200,             
            'client' => $client,
            'message' => $request->duty_deferment_account_text.'ed'
          ]);  
        } 
        else 
        {
            // already exist
            $this->commonClass->addLog($this->authUser, 'vatregmain-error-duty-deferment-account');
            
            return response()->json(
              [
                'message' => "cannot ".$request->duty_deferment_account_text
              ]
            , 422);
        }
      } 
      else 
      {
          // Authuser dont have permission
          $this->commonClass->addLog($this->authUser, 'vatregmain-error-auth-duty-deferment-account',
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Duty Deferment Account Text' => $request->duty_deferment_account_text
            ]
          );
                  
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];   
          $orderBy_vat_reg_main = [];          
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first'); 
         
          return response()->json(
            [
              'status' => 422,   
              'client' => $client,
              'message' => "Don't have permission to ".$request->duty_deferment_account_text
            ]
          , 200);
      }     
    }

    public function updateOSS(Request $request, $id)
    {      
      $vatRegMain = $this->commonClass->getVATRegMain($id);

      if($this->authUser->role != 'client-user')        
      {    
        if ($id) 
        {                    
          $updateMain = VATRegistrationMain::where('id', $id)->update(
              [                                        
                'oss' => ($request->oss == "true") ? 1 : 0
              ]
          );         
        
          $this->commonClass->addLog($this->authUser, 'vatregmain-update-oss', 
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Oss Text' => $request->oss_text
            ]
          );
                          
          // updated         
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];   
          $orderBy_vat_reg_main = [];          
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first');  

          return response()->json([
            'status' => 200,             
            'client' => $client,
            'message' => $request->oss_text.'ed'
          ]);     
        } 
        else 
        {
            // already exist
            $this->commonClass->addLog($this->authUser, 'vatregmain-error-oss');
            
            return response()->json(
              [
                'message' => "cannot ".$request->oss_text
              ]
            , 422);
        }
      } 
      else 
      {
          // Authuser dont have permission
          $this->commonClass->addLog($this->authUser, 'vatregmain-error-auth-oss',
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Oss Text' => $request->oss_text
            ]
          );
          
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];   
          $orderBy_vat_reg_main = [];          
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first'); 
         
          return response()->json(
            [
              'status' => 422,   
              'client' => $client,
              'message' => "Don't have permission to ".$request->oss_text
            ]
          , 200);
      }     
    }

    public function updateExciseDuty(Request $request, $id)
    {      
      $vatRegMain = $this->commonClass->getVATRegMain($id);

      if($this->authUser->role != 'client-user')        
      {    
        if ($id) 
        {                   
          $updateMain = VATRegistrationMain::where('id', $id)->update(
              [                                        
                'excise_duty' => ($request->excise_duty == "true") ? 1 : 0
              ]
          );         
        
          $this->commonClass->addLog($this->authUser, 'vatregmain-update-excise-duty', 
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Excise Duty Text' => $request->excise_duty_text
            ]
          );
                          
          // updated         
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];   
          $orderBy_vat_reg_main = [];          
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first');  

          return response()->json([
            'status' => 200,             
            'client' => $client,
            'message' => $request->excise_duty_text.'ed'
          ]);     
        } 
        else 
        {
            // already exist
            $this->commonClass->addLog($this->authUser, 'vatregmain-error-excise-duty');
            
            return response()->json(
              [
                'message' => "cannot ".$request->excise_duty_text
              ]
            , 422);
        }
      } 
      else 
      {
          // Authuser dont have permission
          $this->commonClass->addLog($this->authUser, 'vatregmain-error-auth-excise-duty',
            [
              'Client Name' => $vatRegMain->client_name,
              'Country' => $vatRegMain->country,
              'Excise Duty Text' => $request->excise_duty_text
            ]
          );
                   
          $with_vat_reg_main = [                  
            'vatregmain'
          ];  
          $where_vat_reg_main = [
            'id' => ['operator' => '=', 'value' => $vatRegMain->client_id]
          ]; 
          $whereHas_vat_reg_main = [];   
          $orderBy_vat_reg_main = [];          
          $client = $this->commonClass->getLazy('client', $with_vat_reg_main, $where_vat_reg_main, $whereHas_vat_reg_main, $orderBy_vat_reg_main, 'first'); 
         
          return response()->json(
            [
              'status' => 422,   
              'client' => $client,
              'message' => "Don't have permission to ".$request->excise_duty_text
            ]
          , 200);
      }     
    }

    /* -- GET /accountnos/{client_id} -- */     
    public function loadAccountNos(Request $request, $client_id)
    {
      try {   
        $api_name = $request->api_name; 
        $api_client_id = $request->api_client_id;
        $acc_vat_reg_main_id = $request->acc_vat_reg_main_id;        

        if($api_name == "E-conomic")    
        {                                   
          $accountnos = $this->economicApiClass->getApiAllAccountNos($api_client_id);          
        }

        return response()->json(
          [                
            'allaccountnos' => $accountnos,           
          ]
        , 200);   
      }
      catch (Exception $e) {      
        return response()->json(
          [                
            'error' => $e->error          
          ]
        , 400); 
      }
    }
    /* --end GET /accountnos/{client_id} -- */     

    /* -- GET /editaccountnos -- */     
    public function loadEditAccountNos(Request $request)
    {
      try {         
        $api_name = $request->api_name;             
        $acc_vat_reg_main_id = $request->acc_vat_reg_main_id;

        if($api_name == "e_conomic")  
        {              
          /* -- GET VAT REG MAIN ACCOUNT NOS -- */
          $vatRegMain_accnos = $this->commonClass->getVATRegMainAccNos($acc_vat_reg_main_id);               
          /* --END GET VAT REG MAIN ACCOUNT NOS -- */
        }

        return response()->json($vatRegMain_accnos);
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }
    /* --end GET /editaccountnos -- */
}
