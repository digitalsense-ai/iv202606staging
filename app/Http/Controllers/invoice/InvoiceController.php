<?php

namespace App\Http\Controllers\invoice;

use App\Http\Controllers\Controller;
use App\Models\VATRegistration;
use App\Models\VATReturns;
use App\Models\Client;
use App\Models\System;
use App\Models\InvoiceColumnSettings;
use App\Models\Invoices;

use App\Jobs\ConvertInvoiceCurrency;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Str;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;
use \App\Classes\DynamicsApiClass;
use \App\Classes\EconomicApiClass;
use \App\Classes\UnicontaApiClass;
use \App\Classes\ShopifyApiClass;
use \App\Classes\BillyApiClass;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Worksheet;

use App\Events\InvoiceCurrencyEvent;

class InvoiceController extends Controller
{  
    public $authUser;
    public $clientIds;

    public $commonClass;
    public $apiClass;
    public $dynamicsApiClass;
    public $economicApiClass;
    public $unicontaApiClass;
    public $shopifyApiClass;
    public $billyApiClass;
   
    public $invoiceColumnNames;
   
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
            $this->dynamicsApiClass = new DynamicsApiClass();
            $this->economicApiClass = new EconomicApiClass();
            $this->unicontaApiClass = new UnicontaApiClass();
            $this->shopifyApiClass = new ShopifyApiClass();
            $this->billyApiClass = new BillyApiClass();
           
            $this->invoiceColumnNames = $this->commonClass->invoiceColumnNamesData();   

            return $next($request);
        });
    }

    /**
    * Redirect to index view.
    *
    */
    public function InvoiceController(Request $request, $vat_reg_id)
    {        
      $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id, false); 
      
      $show = true;
      if(($this->authUser->role == 'client-user') && !in_array($vatreg->client_id, $this->clientIds))
        $show = false;      

      if($show)                          
      {
        if($vatreg)
        {
          $invoices = $this->commonClass->loadInvoicesFromPartition($vatreg);

          $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'invoice');
        
          $invoice_column_settings = InvoiceColumnSettings::where('client_id', $vatreg->client_id)
                                        ->orWhere('vat_reg_main_id', $vatreg->vat_reg_main_id)
                                        ->get();
        
          if($vatreg && count($invoices) == 0)
          {         
            return abort(420, 'Loading invoices...');              
          }
          else
          {          
            $from_currencies = $invoices->unique('currency_code');
              
            //Get todays Exchange rate
            $vatregmain = $vatreg->vatregmain;
            $client = $vatreg->client;
            $clientapi = $vatregmain->clientapi;
            $currency_code = ($clientapi) ? $clientapi->currency_code : '';

            $filtered_currency = $from_currencies->filter(function ($currency, $key) use($currency_code) {         
                return $currency_code != $currency->currency_code; 
            }); 
            $fetch_currency = $filtered_currency->pluck('currency_code')->implode(',');
                           
            $todays_rate = [];
            if($fetch_currency != '') 
              $todays_rate = $this->apiClass->rssExtractExchangeRate('https://www.nationalbanken.dk/api/currencyratesxml?lang=en', $fetch_currency, $currency_code);
            
            $invoice_details = [];
            if($request->has('type') && $request->has('percentage') && $request->has('currency'))
            {
              $invoice_details = [
                  'type' => $request->get('type'),
                  'percentage' => $request->get('percentage'),
                  'currency' => $request->get('currency')
              ];
            }
            $this->commonClass->addLog($this->authUser, 'invoice-view', 
              [
                'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
                'Client Name' => $client->client_name,
                'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,
                'Invoice Details' => $invoice_details
              ]
            );

            return view('content.invoice.index-lazy', 
              [
                'pageConfigs' => $pageConfigs, 
                'authUser' => $this->authUser, 
                'vatreg' => $vatreg, 
                'invoices' => $invoices, 
                'invoice_column_names' => $this->invoiceColumnNames, 
                'invoice_column_settings' => $invoice_column_settings, 
                'from_currencies' => $fetch_currency,        
                'todays_rate' => $todays_rate
              ]
            );        
          }
        }
        else
          return abort(404, 'Page not found.');
      }
      else
        return abort(403, 'User does not have the right to access this page.');
    }        
       
    //GET invoices/{$vat_reg_id}/current
    public function currentInvoices($vat_reg_id)
    {
      try
      {
        //$invoices = Invoice::latest()->where('vat_reg_id', $vat_reg_id)->take(200)->get();

        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id, false);

        $totalExpected = 0;
        foreach ($vatreg->vatreturns as $vatreturn)
          $totalExpected += $vatreturn->invoice_count;

        $invoices = $this->commonClass->loadInvoicesFromPartition($vatreg);

        return response()->json([
            'count' => count($invoices),
            'totalExpected' => $totalExpected,
            'invoices' => $invoices,
        ]);

        return $invoices;
      }
      catch (\Exception $e) {
        return  $e->getMessage();
      }
    }

    //GET invoices/{$vat_reg_id}/refresh
    public function refreshInvoices($vat_reg_id)
    {
      $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id, false); 

      $vatregmain = $vatreg->vatregmain;
      $vatregmain_status = $vatregmain->status;

      if($vatregmain_status)
      { 
        $clientapi = ($vatregmain->clientapi) ? $vatregmain->clientapi : null;
        $api_name = ($clientapi) ? $clientapi->api_name: null;

        if($api_name == "Dynamics 365")      
          $specificClass = new DynamicsApiClass();
        else if($api_name == "Dynamics 365 via SmartApi")      
          $specificClass = new DynamicsSmartApiClass();   
        else if($api_name == "E-conomic")   
          $specificClass = new EconomicApiClass();
        else if($api_name == "Shopify")      
          $specificClass = new ShopifyApiClass(); 
        else if($api_name == "Uniconta")
          $specificClass = new UnicontaApiClass();
        else if($api_name == "Billy")
          $specificClass = new BillyApiClass();
        else if($api_name == "FTP")                               
          $specificClass = new FtpClass();
        
        $account_data = $specificClass->getAllInvoicesLazy(null, $vatreg, $this->authUser);

        if($account_data == "error")    
        {            
           
        } /* --end if ACCOUNT DATA ERROR -- */
        else if(isset($account_data->error))
        {
          
        } /* --end else ACCOUNT DATA ERROR -- */
        else
        { 
          if(count($account_data) > 0) 
          {    
            if($api_name != null)              
            {
              if($api_name != "FTP")             
                $insert_invoices = $this->commonClass->insertInvoices($account_data, $vat_reg_id, $this->authUser, $api_name);
            }            
          }
        } /* --end else ACCOUNT DATA -- */
      }//active vat reg
    }

    // public function loadInvoicesFromPartition($vatreg)
    // {
    //   try
    //   { 
    //     $vat_reg_id = $vatreg->id;

    //     //Get start year
    //     $start_year = Carbon::parse($vatreg->service_start)->format('Y');

    //     //Get end year
    //     $end_date = $this->apiClass->getEndDateLazy($vatreg); 
    //     $end_year = Carbon::parse($end_date)->format('Y');

    //     //Get partition      
    //     $partitions = ($start_year == $end_year) ? ['invoice_'.$start_year] : ['invoice_'.$start_year, 'invoice_'.$end_year];

    //     //Get invoices  
    //     $search_by = null;
    //     $invoices = $this->commonClass->getInvoicesLazy($vat_reg_id, $search_by, $partitions);

    //     return $invoices;
    //   }
    //   catch (Exception $e) 
    //   {
    //     return  $e->getMessage();
    //   }
    // }            

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VATRegistration  $vATRegistration
     * @return \Illuminate\Http\Response
     */   
    public function show(Request $request, $id)
    {
        try {
        $access_token = $this->getDigitalVATAccessToken($api_base_url);
        $access_token = ($access_token == "not expired") ?  $this->decryptValue($system->api_token) : $access_token->token;//ERP Plus Excel
        
        $headers = [                         
            'Content-Type' => 'application/json',           
            'Authorization' => 'Bearer ' . $access_token      
        ];        

        $guzzleClient = new GuzzleClient();   
                                      
        $url = $api_base_url .'/api/saft'; //ERP Plus Excel   
        
        $postData = [        
            "invoice_list" => $invoices->getData()->data,
            "invoice_period" => $request->invoice_period,
            "invoice_year" => $request->invoice_year
        ];
        
        $response = $guzzleClient->request('POST', $url, [       
            'body' => json_encode($postData),       
            'headers' => $headers,
            'verify'  => false,
        ]);

        $data = $response->getBody()->getContents();

        $fileName = "saft.xls";
        $fileheaders = [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
        ];
        return response( $data, 200, $fileheaders); 
      }
      catch (Exception $e) {dd("error");
        return  $e->getMessage();
      }              
    }   

    public function getDigitalVATAccessToken($api_base_url)
    {        
        $params = [                        
            'grant_type' => "password",                        
            'username' => "admin",  //ERP Plus Excel     
            'password' => "admin",  //ERP Plus Excel   
            //'username' => "DigitalAdminVat",  //Interop Excel 
            //'password' => "Y6%9ld3\$u",  //Interop Excel       
        ];

        $guzzleClient = new GuzzleClient();
        $url = "$api_base_url/api/login"; //ERP Plus Excel   
        //$url = "$api_base_url/token";//Interop Excel
        

        $headers = [               
            'Accept' => 'application/json', //ERP Plus Excel, Interop Excel
            'Content-Type' => 'application/json', //ERP Plus Excel   
            //'Content-Type' => 'application/x-www-form-urlencoded',//Interop Excel          
        ];

        $response = $guzzleClient->request('POST', $url, [
            'headers' => $headers,
            'body' => json_encode($params), //ERP Plus Excel   
            //'form_params' => $params,//'Content-Type' => 'application/json',
            'verify'  => false,
        ]);

        $access_token = json_decode($response->getBody());  
        
        return $access_token;      
    } 

    //POST invoice/download/$vat_reg_id
    public function downloadInvoice(Request $request, $vat_reg_id)
    {
      $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);

      $vatregmain = $vatreg->vatregmain;
      $clientapi = $vatregmain->clientapi;
      $api_name = ($clientapi) ? $clientapi->api_name: null;
      $client = $vatreg->client;

      if($api_name == "Dynamics 365")  
      {     
        $api_base_url = $clientapi->api_base_url;
        $tenant_id = $clientapi->api_tenant_id;
        $api_environment = $clientapi->api_env;
        $api_company_id = $clientapi->api_company_id;

        $invoice_id = $request->invoice_id;
              
        $access_token = $this->dynamicsApiClass->getAccessTokenLazy($vatreg);
        $api_token = $clientapi->api_token;
        $auth_bearer = ($access_token == "not expired") ? ('Bearer ' . $api_token) : ($access_token->token_type . ' ' . $access_token->access_token); 

        try
        {
          //PDF   
          $invoicename = 'salesInvoices';         
          $pdf_url = "$api_base_url/v2.0/$tenant_id/$api_environment/api/v2.0/companies($api_company_id)/$invoicename($invoice_id)/pdfDocument/pdfDocumentContent"; 
          $guzzleClient = new GuzzleClient();    
          $headers = [               
              'Accept' => 'application/json',
              'Content-Type' => 'application/json',           
              'Authorization' => $auth_bearer           
          ]; 

          $pdf_response = $guzzleClient->request('GET', $pdf_url, [
                            'headers' => $headers,
                            'verify'  => false,
                        ]);       
          $pdf_data = $pdf_response->getBody()->getContents();

          $fileName = "invoice.pdf";
          $fileheaders = [
              'Content-Type'        => 'application/octet-stream',
              'Content-Disposition' => 'attachment; filename=' . $fileName,
          ];
          $response = response( $pdf_data, 200, $fileheaders);
          return $response; 
        }
        catch (\Exception $e) {
          //return  $e->getMessage();
          return abort(404, 'Not found'); 
        }      
      } //Dynamics 365
      else if($api_name == "E-conomic")  
      {
        $api_secret_key = $clientapi->api_secret_key;
        $api_client_id = $clientapi->api_client_id; 
        try
        {        
          $pdf_url = "https://restapi.e-conomic.com/invoices/booked/".$request->invoice_id."/pdf"; 
          $guzzleClient = new GuzzleClient();    
          $headers = [                                   
              'X-AppSecretToken' => $api_secret_key,
              'X-AgreementGrantToken' => $api_client_id,
              'Content-Type' => 'application/json'          
          ];

          $pdf_response = $guzzleClient->request('GET', $pdf_url, [
                    'headers' => $headers,
                    'verify'  => false,
                ]);       
          $pdf_data = $pdf_response->getBody()->getContents();

          $fileName = "invoice.pdf";
          $fileheaders = [
              'Content-Type'        => 'application/octet-stream',
              'Content-Disposition' => 'attachment; filename=' . $fileName,
          ];
          $response = response( $pdf_data, 200, $fileheaders);
          return $response; 
        }
        catch (\Exception $e) {
          //return  $e->getMessage();
          return abort(404, 'Not found');       
        }   
      }//E-conomic
    }

    //POST invoices/$vat_reg_id/convert
    public function convertInvoiceCurrency(Request $request, $vat_reg_id)
    {
      try
      {   
        $from_currencies = $request->from_currencies;
        $selected_invoices = json_decode($request->selected_invoices, true);
        $to_currency = $request->to_currency;

        foreach (explode(',', $from_currencies) as $from_currency)
        {
          $date_type = $request->input('chk_currency_convert_dates_' . $vat_reg_id . '_' . $from_currency);

          if($date_type == 'invoice date')
          {
            ConvertInvoiceCurrency::dispatch($selected_invoices, $vat_reg_id, $this->authUser->user_id, $from_currency, $to_currency);
          }
          else if($date_type == 'todays date')
          {
            $exchange_rate = $request->input('currency_convert_todays_rate_' . $vat_reg_id . '_' . $from_currency);
                        
            ConvertInvoiceCurrency::dispatch($selected_invoices, $vat_reg_id, $this->authUser->user_id, $from_currency, $to_currency, $exchange_rate);
                  //->onQueue('convertinvoicecurrency'); // Dispatch the job          
          }
        } //for currencies    
       
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id, false); 
        $invoices = $this->commonClass->loadInvoicesFromPartition($vatreg);
              
        $this->commonClass->addLog($this->authUser, 'invoice-currency-conversion', 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg.' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods,           
            'Selected Invoices' => $selected_invoices
          ]
        );

        // Broadcast the event          
        event(new InvoiceCurrencyEvent($vat_reg_id, 'Currency converted for the invoice'));

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'invoices' => $invoices,
          ]
        );  
      }
      catch (\Exception $e) {
          return  $e->getMessage();
        }
    }

    //POST invoice/$invoice_id/disregard (DISREGARD/ENABLE)
    public function invoiceDisregard(Request $request, $invoice_id)
    {
      try
      { 
        $vat_reg_id = ($request->invoice_vat_reg_id) ? $request->invoice_vat_reg_id : $request->vat_reg_id;     

        $is_disregard = $request->is_disregard;
        
        $is_enable = $request->is_enable;
        
        $selected_invoice_ids = ($invoice_id == '0') ? $request->invoice_id : $invoice_id;
        $selected_invoices = $request->invoice_no;

        $log_name_text_suffix = '';
        foreach (explode(',', $selected_invoice_ids) as $invoice_id)
        {                    
          $invoice_name_text = 'Invoices';
          $log_name_text = 'invoice';

          $invoice = Invoices::where('id', $invoice_id)->first();       

          if($is_disregard == "1")
          {
            $log_name_text_suffix = 'disregard';

            $invoice->disregard_invoice = 1;            
            $invoice->disregard_comment = $request->invoice_disregard_quill;

            //VAT returns 
            $from_vatreturn = VATReturns::where('vat_reg_id', $vat_reg_id)
                                    ->where('invoice_type', $invoice->invoice_type)      
                                    ->where('vat_percentage', $invoice->vat_rate)
                                    ->where('currency_code', $invoice->currency_code)
                                    ->first();

            if($from_vatreturn)                
            {
                if($from_vatreturn->invoice_count - 1 == 0 || $from_vatreturn->net_amount == 0)  
                    $from_vatreturn->delete();  
                else
                {
                    $from_vatreturn->vat_amount = $from_vatreturn->vat_amount - $invoice->total_vat;
                    $from_vatreturn->net_amount = $from_vatreturn->net_amount - $invoice->total_net;
                    $from_vatreturn->invoice_count = $from_vatreturn->invoice_count - 1;
                    $from_vatreturn->updated_by = $this->authUser->id;
                    $from_vatreturn->save();    
                }     
            }
            //VAT returns 
          } // invoice disregard
          else if($is_enable == "1")
          {
            $log_name_text_suffix = 'enable';

            $invoice->disregard_invoice = 0;         
            $invoice->disregard_comment = NULL;  

            //VAT returns 
            $to_vatreturn = VATReturns::where('vat_reg_id', $vat_reg_id)
                                    ->where('invoice_type', $invoice->invoice_type)      
                                    ->where('vat_percentage', $invoice->vat_rate)
                                    ->where('currency_code', $invoice->currency_code)
                                    ->first();   

            if($to_vatreturn)
            {                    
                $to_vatreturn->vat_amount = $to_vatreturn->vat_amount + $invoice->total_vat;
                $to_vatreturn->net_amount = $to_vatreturn->net_amount + $invoice->total_net;
                $to_vatreturn->invoice_count = $to_vatreturn->invoice_count + 1;
                $to_vatreturn->updated_by = $this->authUser->id;
                $to_vatreturn->save();   
            }   
            else
            {               
                $to_vatreturn_insert = VATReturns::create(
                    [
                        'vat_reg_id' => $vat_reg_id,
                        'invoice_type' => $invoice->invoice_type,
                        'vat_percentage' => $invoice->vat_rate,
                        'vat_amount' => $total_vat_exchange_rate,
                        'net_amount' => $total_net_exchange_rate,
                        'currency_code' => $to_currency,
                        'invoice_count' => 1,
                        'created_by' => $authUserId,
                    ]
                );               
            }
            //VAT returns 
          } // invoice enable          
          
          $invoice->save(); 
        }     
        
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id, false); 
        $invoices = $this->commonClass->loadInvoicesFromPartition($vatreg);

        $this->commonClass->addLog($this->authUser, $log_name_text .'-' . $log_name_text_suffix, 
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
          event(new InvoiceCurrencyEvent($vat_reg_id, 'Disregarded the invoice'));
        }

        return response()->json(
          [
            'status' => 200,             
            'message' => "success",
            'invoices' => $invoices,
            'tab_name' => $request->tab_name
          ]
        );  
      }
      catch (\Exception $e) {
          return  $e->getMessage();
        }
    }

    /* -- POST /invoices/invoice-column-settings -- */   
    public function postInvoiceColumnSettings(Request $request)
    {
      try
      {
        $client_id = $request->client_id; 
        $vat_reg_main_id = $request->vat_reg_main_id; 

        /* -- if USER ID -- */
        if ($client_id && $vat_reg_main_id)
        {
          /* -- UPDATE INVOICE COLUMN SETTINGS -- */
          $invoiceColumnNames = $this->invoiceColumnNames;

          foreach($invoiceColumnNames as $key => $columnNames)
          {            
            $notification = InvoiceColumnSettings::updateOrCreate(
              [
                'client_id' => $client_id,
                'vat_reg_main_id' => $vat_reg_main_id,
                'column_name' => $key
              ],
              [                                  
                'status' => ($request->has('chk_invoice_column_'.$key)) ? 1 : 0
              ]
            );
          }
          /* --end UPDATE INVOICE COLUMN SETTINGS -- */
         
          /* -- GET COMPANY NAME -- */
          $client = $this->commonClass->getCompanyLazy($client_id);
          /* --end GET COMPANY NAME -- */

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'invoice-column-settings',
            [
              'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,
              'Client Name' => $client->client_name,
              'VAT Reg.' => Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods              
            ]
          );
          /* --end LOG -- */          
          
          /* -- RETURN JSON -- */
          return response()->json([
            'status' => 200,                        
            'message' => 'Updated'
          ]);
          /* --end RETURN JSON -- */
        }  /* -- else USER ID -- */
        else
        {                    
          /* -- RETURN JSON -- */
          return response()->json([                      
            'message' => 'Error'
          ]); 
          /* --end RETURN JSON -- */ 
        }  /* --end if USER ID -- */
      }
      catch (\Exception $e) 
      {          
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Invoice Controller',
            'method' => 'postInvoiceColumnSettings',
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
    /* --end POST /invoices/invoice-column-settings -- */
}
