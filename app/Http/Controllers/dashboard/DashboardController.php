<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\Client;
use App\Models\ClientApi;
use App\Models\SystemApis;
use App\Models\VATRegistrationMain;
use App\Models\VATRegistrationMainCasDdaMonths;
use App\Models\CashAccountStatement;
use App\Models\DutyDefermentAccount;
use App\Models\VATRegistration;

use \App\Classes\CommonClass;
use \App\Classes\CommercialInvoicesClass;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

use OpenAI\Laravel\Facades\OpenAI;
use HelgeSverre\ReceiptScanner\Facades\ReceiptScanner;
use HelgeSverre\ReceiptScanner\Facades\Text;
use HelgeSverre\ReceiptScanner\Enums\Model;

use Webklex\IMAP\Facades\Client as MailBoxClient;

use Storage;

use Spatie\PdfToText\Pdf as PdfExtract;
use Spatie\PdfToImage\Pdf as PdfImage;
use Aws\Textract\TextractClient;

class DashboardController extends Controller
{
	public $authUser;

    public $commonClass;
   
	public function __construct()
    {
       if (strpos(URL::full(), 'confirm-email') !== false)
          \Session::put('url.intended', URL::full());  

		$this->middleware('auth');
        $this->middleware(function ($request, $next) {        	          
            $this->commonClass = new CommonClass();
            
            $this->authUser = $this->commonClass->getAuthUser();          

            if($this->authUser->role == 'client-user')            
                $this->clientIds = $this->commonClass->getClientIdsForClientUser($this->authUser); 

        	if (strpos(\Session::get('url.intended'), 'confirm-email') !== false)
        		return \Redirect::to(\Session::get('url.intended'));
        	else
        		return $next($request);
        });      
	}

	public function index()
	{	
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);
   
        // if($this->authUser->role == 'super-admin') 
        // {
        //     // $auser = Auth::user();

        //     // // Check if user already has a token (optional)
        //     // $existingToken = $auser->tokens()->where('name', 'OCR API Token')->first();
        //     // if (!$existingToken) {
        //     //     $token = $auser->createToken('OCR API Token')->plainTextToken;

        //     //     // You can store it in session or display in dashboard for copying
        //     //     session(['ocr_api_token' => $token]);
        //     // }

        //     $user = Auth::user();

        //     // Check if user already has a valid OCR token
        //     $existingToken = $user->tokens()
        //         ->where('name', 'OCR API Token')
        //         ->where(function($q) {
        //             $q->whereNull('expires_at')
        //               ->orWhere('expires_at', '>', now());
        //         })
        //         ->first();

        //     if (!$existingToken) {
        //         // Create a new token valid for 12 hours
        //         $token = $user->createToken(
        //             'OCR API Token',
        //             ['ocr-read'],            // abilities
        //             now()->addHours(12)     // expiration
        //         )->plainTextToken;

        //         // return response()->json([
        //         //     'message' => 'OCR token created',
        //         //     'token' => $token,
        //         //     'expires_at' => now()->addHours(12)->toDateTimeString()
        //         // ]);
        //     }

        //     // return response()->json([
        //     //     'message' => 'OCR token already exists',
        //     //     'expires_at' => $existingToken->expires_at,
        //     // ]);
        // }

        if($this->authUser->role == 'client-user') 
        {
            // GET COMPANIES OF THE LOGGED IN USER
            $companies = Client::whereIn('id', $this->clientIds)->get();                       
            // END GET COMPANIES OF THE LOGGED IN USER
           
            //  GET CONNECTION
            $client_connections = Client::with(['vatregmain','clientapi'])
                                    ->whereHas('clientapi', function ($query) {
                                        $query->whereIn('client_id', $this->clientIds);                      
                                    })
                                    ->get();  
            // END GET CONNECTION
            return view('content.dashboard.dashboard', 
                [
                    'pageConfigs' => $pageConfigs, 
                    'authUser' => $this->authUser, 
                    'companies' => $companies, 
                    'clientconnection' => $client_connections
                ]
            );
        }
        else
            return view('content.dashboard.dashboard', 
                [
                    'pageConfigs' => $pageConfigs, 
                    'authUser' => $this->authUser
                ]
            );
	}

    public function store(Request $request)
    {        
        if($request->erp_options != null)
        {
            $currency_code = "USD";
            
            /* -- DYNAMIC 365 -- */
            if($request->erp_options == "Dynamics 365")
            {
                try
                {                    
                    /* -- GET ACCESS TOKEN FOR DYNAMIC 365 -- */
                    $api_base_url = "https://api.businesscentral.dynamics.com";
                    $tenant_id =  $request->api_tenant_id;
                    $api_client_id = $request->api_client_id;
                    $client_secret = $request->api_secret_key;

                    $params = [            
                        'scope' => "$api_base_url/.default",            
                        'grant_type' => "client_credentials",                        
                        'client_secret' => $client_secret,
                        'client_id' => $api_client_id,          
                    ];

                    $guzzleClient = new GuzzleClient();
                    $url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";

                    $headers = [               
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ];

                    $response = $guzzleClient->request('POST', $url, [
                        'headers' => $headers,
                        'form_params' => $params,
                        'verify'  => false,
                    ]);

                    $access_token = json_decode($response->getBody());  
                    /* --end GET ACCESS TOKEN FOR DYNAMIC 365 -- */

                    if($access_token)
                    {                        
                        /* -- GET DYNAMIC 365 API DATAS USING ACCESS TOKEN -- */                       
                        $auth_bearer = ($access_token->token_type . ' ' . $access_token->access_token); 
                        $client_name = $request->client_id;                           
                        
                        $api_environment = "Production";

                        $headers = [               
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',           
                            'Authorization' => $auth_bearer           
                        ];

                        $guzzleClient = new GuzzleClient();   

                        $client_url = "$api_base_url/v2.0/$tenant_id/$api_environment/api/v2.0/companies"; 
                        $client_response = $guzzleClient->request('GET', $client_url, [
                            'headers' => $headers,
                            'verify'  => false,
                        ]);

                        $testconnection_dynamic_data = json_decode($client_response->getBody());
                        /* --end GET DYNAMIC 365 API DATAS USING ACCESS TOKEN -- */

                        if($testconnection_dynamic_data != null)
                        {
                            /* -- CHECK CLIENT CONNECTION ALREADY EXISTS -- */
                            $clientApidatas = ClientApi::where('client_id', $request->client_id)
                                                ->where('api_name', "Dynamics 365")                                   
                                                ->get();     

                            if(count($clientApidatas) > 0)                    
                                $clientApidata_filter = $clientApidatas->filter(function($clientApidata, $key) use($api_client_id) {
                                    return $clientApidata->api_client_id == $api_client_id;
                                });
                            else
                                $clientApidata_filter = null;
                            /* --end CHECK CLIENT CONNECTION ALREADY EXISTS -- */

                            if (empty($clientApidata_filter)) 
                            {
                                /* -- INSERT NEW CONNECTION -- */
                                $clientapi = ClientApi::updateOrCreate(                                   
                                    [
                                        'client_id' => $request->client_id, 
                                        'vat_reg_main_id' => null, 
                                        'api_name' => "Dynamics 365",
                                        'api_env' => "Production",
                                        'api_base_url' => "https://api.businesscentral.dynamics.com",
                                        'api_tenant_id' => ($request->api_tenant_id) ? $request->api_tenant_id : null,
                                        'api_client_id' => ($request->api_client_id) ? $request->api_client_id : null,
                                        'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                                        'api_company_id' => null,
                                        'currency_code' => $currency_code,
                                        'status' => 1 ,
                                        'connection_name' => $request->connection_name,  
                                        'connection_status' => 1, 
                                        'connection_remarks' => "Established Connection",               
                                    ]
                                );
                                /* --end INSERT NEW CONNECTION -- */
                            }
                            else
                            {
                                /* -- GET CLIENT CONNECTIONS -- */    
                                $client_connections = Client::with(['vatregmain','clientapi'])
                                                        ->whereHas('clientapi', function ($query) {
                                                            $query->whereIn('client_id', $this->clientIds);                      
                                                        })
                                                        ->get(); 
                                /* --end GET CLIENT CONNECTIONS -- */

                                /* -- RETURN JSON -- */ 
                                return response()->json(
                                    [
                                        'status' => 401,                        
                                        'message' => 'Connection Already Established.',
                                        'clientconnection' => $client_connections
                                    ]
                                );
                                /* --end RETURN JSON -- */ 
                            }

                            /* -- GET CLIENT CONNECTIONS -- */
                            $client_connections = Client::with(['vatregmain','clientapi'])
                                                    ->whereHas('clientapi', function ($query) {
                                                        $query->whereIn('client_id', $this->clientIds);                      
                                                    })
                                                    ->get();
                            /* --end GET CLIENT CONNECTIONS -- */                        
                        } /* --end if TEST CONNECTION FOR DYNAMIC 365 -- */

                        /* -- RETURN JSON -- */ 
                        return response()->json(
                            [
                                'status' => 200,                        
                                'message' => 'Connection Established',
                                'clientconnection' => $client_connections
                            ]
                        );
                        /* --end RETURN JSON -- */ 
                        //End Get Dynamic 365 api datas using access token
                    } /* --end if ACCESS TOKEN FOR DYNAMIC 365 -- */
                } /* --end try DYNAMIC 365 -- */
                catch (\Exception $e)        
                {
                    if($e->getResponse())  
                    {
                        $response = $e->getResponse();

                        $errorMessage = json_decode($response->getBody()); 

                        list($errpart, $extraerrmessage) = explode(' Trace ID: ', $errorMessage->error_description);

                        /* -- INSERT NEW CONNECTION WITH FAILURE REASON -- */                            
                        $clientapi = ClientApi::updateOrCreate(                                               
                            [
                                'client_id' => $request->client_id, 
                                'vat_reg_main_id' => null, 
                                'api_name' => "Dynamics 365",
                                'api_env' => "Production",
                                'api_base_url' => "https://api.businesscentral.dynamics.com",
                                'api_tenant_id' => ($request->api_tenant_id) ? $request->api_tenant_id : null,
                                'api_client_id' => ($request->api_client_id) ? $request->api_client_id : null,
                                'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                                'api_company_id' => null,
                                'currency_code' => $currency_code,
                                'status' => 0 ,
                                'connection_name' => $request->connection_name,  
                                'connection_status' => 0, 
                                'connection_remarks' => $errpart,              
                            ]
                        );
                        /* --end INSERT NEW CONNECTION WITH FAILURE REASON -- */

                        /* -- GET CLIENT CONNECTIONS -- */   
                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();  
                        /* --end GET CLIENT CONNECTIONS -- */ 
                                                
                        /* -- RETURN JSON -- */                        
                        return response()->json(
                            [
                                'status' => 402,                        
                                'message' => $errpart,
                                'clientconnection' => $client_connections
                            ]
                        );
                        /* --end RETURN JSON -- */
                    } /* --end catch RESPONSE -- */
                } /* --end catch DYNAMIC 365 -- */
            } /* --end if DYNAMIC 365 -- */
       
            /* -- DYNAMIC 365 via SMART API -- */
            if($request->erp_options == "Dynamics 365 via SmartApi")
            {
                $sales_invoice_url = $request->sales_invoice_url;
                $purchase_invoice_url = $request->purchase_invoice_url;

                try
                {
                    if($sales_invoice_url != ""  )
                    {
                        $service_start = date("Y-m-01");
                        $end_date  = date('Y-m-t');

                        $sales_filter = "&filtertext=posting date=filter($service_start..$end_date)";
                        $sales_invoice_url_withfilter = $sales_invoice_url . '' . $sales_filter; 

                        $guzzleClient = new GuzzleClient(); 

                        $sales_invoice_client  = new \GuzzleHttp\Client(['verify' =>false]); //ssl verifyication 
                        $sales_invoice_request = new \GuzzleHttp\Psr7\Request('GET', $sales_invoice_url_withfilter);
                        $sales_invoice_response = $guzzleClient->sendAsync($sales_invoice_request)->then(function ($response) {            
                            return $response->getBody()->getContents();
                        });
                        $sales_invoice_xml = $sales_invoice_response->wait(); 
                        $xmlObject = simplexml_load_string(str_replace('s:', '',$sales_invoice_xml));
                        $json = json_encode($xmlObject);
                        $sales_invoice_data = json_decode($json, true); 

                        if($sales_invoice_data != null)
                        {
                            if (array_key_exists("message", $sales_invoice_data))
                            {
                                // Insert New connection with failure reason                              
                                $json = Arr::get($sales_invoice_data, 'message');
                                $jsoncontent = json_encode($json);
                                                               
                                $my_array = explode(":", $jsoncontent);                                   
                                $lasttwoerrMessage = array_slice($my_array, -2, 2);
                                $string = str_replace('}', ' ', $lasttwoerrMessage);                                
                                $errorMessage = implode(".",$string);

                                $clientapi = ClientApi::updateOrCreate(                                               
                                    [
                                        'client_id' => $request->client_id,                                
                                        'api_name' => "Dynamics 365 via SmartApi",
                                        'api_env' => "Production",
                                        'api_base_url' => 'dummy',
                                        'sales_invoice_url' => ($request->sales_invoice_url) ? $request->sales_invoice_url : null,
                                        'purchase_invoice_url' => ($request->purchase_invoice_url) ? $request->purchase_invoice_url : null,
                                        'api_client_id' => 'dummy',
                                        'api_secret_key' => 'dummy',
                                        'currency_code' => $currency_code,
                                        'status' => 0 ,
                                        'connection_name' => $request->connection_name,  
                                        'connection_status' => 0, 
                                        'connection_remarks' => $errorMessage,              
                                    ]
                                );
                                // End Insert New connection with failure reason
                                $client_connections = Client::with(['vatregmain','clientapi'])
                                                        ->whereHas('clientapi', function ($query) {
                                                            $query->whereIn('client_id', $this->clientIds);                      
                                                        })
                                                        ->get();  

                                return response()->json(
                                    [
                                        'status' => 402,                        
                                        'message' => $errorMessage,
                                        'clientconnection' => $client_connections
                                    ]
                                );
                            }//if data contains message means failure connection
                            else // data not null existing api for the logged in client 
                            {
                                $clientApidatas = ClientApi::where('client_id', $request->client_id)
                                                ->where('api_name', "Dynamics 365 via SmartApi")                                   
                                                ->get();    

                                if(count($clientApidatas) > 0)                
                                    $clientApidata_filter = $clientApidatas->filter(function($clientApidata, $key) use($sales_invoice_url) {
                                        return $clientApidata->sales_invoice_url == $sales_invoice_url;
                                    });
                                else
                                    $clientApidata_filter = null;

                                if (count($clientApidata_filter) > 0)
                                {
                                    $client_connections = Client::with(['vatregmain','clientapi'])
                                                            ->whereHas('clientapi', function ($query) {
                                                                $query->whereIn('client_id', $this->clientIds);                      
                                                            })
                                                            ->get();  

                                    return response()->json(
                                        [
                                            'status' => 401,                        
                                            'message' => 'Connection Already Established.',
                                            'clientconnection' => $client_connections
                                        ]
                                    );
                                } // end if client data not  null
                                else // else insert new connection for client
                                {
                                    $clientapi = ClientApi::updateOrCreate(                                               
                                        [
                                            'client_id' => $request->client_id,                                
                                            'api_name' => "Dynamics 365 via SmartApi",
                                            'api_env' => "Production",
                                            'api_base_url' => 'dummy',
                                            'sales_invoice_url' => ($request->sales_invoice_url) ? $request->sales_invoice_url : null,
                                            'purchase_invoice_url' => ($request->purchase_invoice_url) ? $request->purchase_invoice_url : null,
                                            'api_client_id' => 'dummy',
                                            'api_secret_key' => 'dummy',
                                            'currency_code' => $currency_code,
                                            'status' => 1,
                                            'connection_name' => $request->connection_name,  
                                            'connection_status' => 1, 
                                            'connection_remarks' => "Established Connection",                
                                        ]
                                    );
                                } //end else insert new connection for client
                                $client_connections = Client::with(['vatregmain','clientapi'])
                                                        ->whereHas('clientapi', function ($query) {
                                                            $query->whereIn('client_id', $this->clientIds);                      
                                                        })
                                                        ->get();
                            } // data not null existing api for the logged in client 
                            return response()->json(
                                [
                                    'status' => 200,                        
                                    'message' => 'Connection Established',
                                    'clientconnection' => $client_connections
                                ]
                            );
                        }// end if invoice data not null

                    }// end if url not null
                }// end of try
                catch (\Exception $e)        
                {
                    dd($e);
                    if($e->getResponse())  
                    {
                        $response = $e->getResponse();                
                        $errorMessage = json_decode($response->getBody());
                    }
                } /* --end catch DYNAMIC 365 via SMART API -- */
            } /* --end if DYNAMIC 365 via SMART API -- */

            if($request->erp_options == "E-conomic")
            {
                try
                {
                    $api_use_base_currency_amount = $request->api_use_base_currency_amount;

                    $api_client_id = $request->api_client_id;
                    $api_secret_key = "2NBwnBEXouJc1klye2sX05tHflCaIXZObXJ0yuksRDM1";

                    $guzzleClient = new GuzzleClient();    
                    $headers = [                                   
                        'X-AppSecretToken' => $api_secret_key,
                        'X-AgreementGrantToken' => $api_client_id,
                        'Content-Type' => 'application/json'          
                    ];
                    $testconnection_economic_url = "https://restapi.e-conomic.com/self";
                    $testconnection_economic_response = $guzzleClient->request('GET', $testconnection_economic_url, [
                        'headers' => $headers,
                        'verify'  => false,
                    ]);       
                    $testconnection_economic_data = json_decode($testconnection_economic_response->getBody()); 

                    if($testconnection_economic_data != null)
                    {
                        $clientApidatas = ClientApi::where('client_id', $request->client_id)
                                            ->where('api_name', "E-conomic")                                   
                                            ->get();    

                        if(count($clientApidatas) > 0)
                            $clientApidata_filter = $clientApidatas->filter(function($clientApidata, $key) use($api_client_id) {
                                return $clientApidata->api_client_id == $api_client_id;
                            });
                        else
                            $clientApidata_filter = null;

                        if (empty($clientApidata_filter)) 
                        {
                            // Insert new connection success connection
                            $clientapi = ClientApi::updateOrCreate(                                               
                                [
                                    'client_id' => $request->client_id, 
                                    'vat_reg_main_id' =>null, 
                                    'api_name' => "E-conomic",
                                    'api_env' => "Production",
                                    'api_base_url' => "https://restapi.e-conomic.com",
                                    'api_tenant_id' => null,
                                    'api_client_id' => $api_client_id ? $api_client_id : null,
                                    'api_secret_key' => $api_secret_key,
                                    'api_company_id' => null,
                                    'currency_code' => $currency_code,
                                    'status' => 1 ,
                                    //'is_reverse' => $request->api_reverse ? 1 : 0,
                                    'connection_name' => $request->connection_name,  
                                    'connection_status' => 1, 
                                    'connection_remarks' => "Established Connection", 
                                    'use_base_currency_amount' => ($api_use_base_currency_amount) ? 1 : 0,             
                                ]
                            );
                            //End  Insert new connection success connection
                        }
                        else
                        {
                            $client_connections = Client::with(['vatregmain','clientapi'])
                                                    ->whereHas('clientapi', function ($query) {
                                                        $query->whereIn('client_id', $this->clientIds);                      
                                                    })
                                                    ->get();  


                            return response()->json(
                                [
                                    'status' => 401,                        
                                    'message' => 'Connection Already Established.',
                                    'clientconnection' => $client_connections
                                ]
                            );
                        }

                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();  

                    }
                    /* -- RETURN JSON -- */
                    return response()->json(
                        [
                            'status' => 200,                        
                            'message' => 'Connection Established',
                            'clientconnection' => $client_connections
                        ]
                    );
                } // end of try
                catch (\Exception $e)        
                {

                    if($e->getResponse())  
                    {
                        $response = $e->getResponse();

                        $errorMessage = json_decode($response->getBody()); 

                        // Insert New connection with failure reason
                        $clientapi = ClientApi::updateOrCreate(                                               
                            [
                                'client_id' => $request->client_id, 
                                'vat_reg_main_id' =>null, 
                                'api_name' => "E-conomic",
                                'api_env' => "Production",
                                'api_base_url' => "https://restapi.e-conomic.com",
                                'api_tenant_id' => null,
                                'api_client_id' => $api_client_id ? $api_client_id : null,
                                'api_secret_key' => $api_secret_key,
                                'api_company_id' => null,
                                'currency_code' => $currency_code,
                                'status' => 0 ,                             
                                'connection_name' => $request->connection_name,  
                                'connection_status' => 0, 
                                'connection_remarks' => $errorMessage->errorCode . '-' . $errorMessage->message,
                                'use_base_currency_amount' => ($api_use_base_currency_amount) ? 1 : 0,               
                            ]
                        );
                        // End Insert New connection with failure reason

                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();  

                        return response()->json(
                            [
                                'status' => 402,                        
                                'message' => $errorMessage->errorCode . '-' . $errorMessage->message,
                                'clientconnection' => $client_connections
                            ]
                        );
                    } 
                } 

            } // end of if economic

            // UNICONTA
            if($request->erp_options == "Uniconta")
            {
                $api_client_id = $request->api_client_id;
                $api_secret_key = $request->api_secret_key;
                $api_base_url = "https://odata.uniconta.com/odata";

                $service_start = date("Y-m-01");
                $end_date  = date('Y-m-t');                 
                $guzzleClient = new GuzzleClient();  

                $sales_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00'";
                $sales_invoice_url = "$api_base_url/DebtorInvoiceClient?\$filter=$sales_filter";                

                try
                {                    
                    $sales_invoice_response = $guzzleClient->request('GET', $sales_invoice_url, [
                        'auth' => [$api_client_id, $api_secret_key], 
                        'verify'  => false,
                    ]);       
                    $sales_invoice_data = json_decode($sales_invoice_response->getBody());    

                    if($sales_invoice_data != null)
                    {
                        $clientApidatas = ClientApi::where('client_id', $request->client_id)
                                            ->where('api_name', "Uniconta")                                  
                                            ->get();     

                        if(count($clientApidatas) > 0)                    
                            $clientApidata_filter = $clientApidatas->filter(function($clientApidata, $key) use($api_client_id) {
                                return $clientApidata->api_client_id == $api_client_id;
                            });
                        else
                            $clientApidata_filter = null;

                        if (count($clientApidata_filter) > 0)
                        {                                    
                            $client_connections = Client::with(['vatregmain','clientapi'])
                                                    ->whereHas('clientapi', function ($query) {
                                                        $query->whereIn('client_id', $this->clientIds);                      
                                                    })
                                                    ->get();  

                            //dd('already exist');
                            return response()->json(
                                [
                                    'status' => 401,                        
                                    'message' => 'Connection Already Established.',
                                    'clientconnection' => $client_connections
                                ]
                            );
                        } // end if client data not  null
                        else // else insert new connection for client
                        {   
                            /* -- GET COMPANY NAME -- */
                            $client = $this->commonClass->getCompanyLazy($request->client_id);
                            $client_name = ($client) ? $client->client_name : '';
                            /* --end GET COMPANY NAME -- */

                            $prefix = (strlen($request->api_client_id) == 5) ? '0' : '';
                            $suffix = (strtolower($client_name) == 'alustre p/s') ? '/digitalvat' : '/intravat';  
                            $clientapi = ClientApi::updateOrCreate(                                               
                                [
                                    'client_id' => $request->client_id,                                 
                                    'api_name' => "Uniconta",
                                    'api_env' =>  "Production",
                                    'api_base_url' => "https://odata.uniconta.com/odata",
                                    'api_tenant_id' => null,                                    
                                    'api_client_id' => ($request->api_client_id) ? ($prefix . $request->api_client_id . $suffix) : null,
                                    'api_secret_key' => 'Urges905',
                                    'api_company_id' => null,
                                    'currency_code' => $currency_code,
                                    'status' => 1,
                                    'connection_name' => $request->connection_name,  
                                    'connection_status' => 1, 
                                    'connection_remarks' => "Established Connection",                
                                ]
                            );
                        } //end else insert new connection for client

                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();

                    } // end if data not null

                    return response()->json(
                        [
                            'status' => 200,                        
                            'message' => 'Connection Established',
                            'clientconnection' => $client_connections
                        ]
                    );
                }// end try
                catch (\Exception $e)        
                {
                    if($e->getResponse())  
                    {
                        $response = $e->getResponse();

                        $errorMessage = $response->getStatusCode() . '-' . $response->getreasonPhrase();

                        /* -- GET COMPANY NAME -- */
                        $client = $this->commonClass->getCompanyLazy($request->client_id);
                        $client_name = ($client) ? $client->client_name : '';
                        /* --end GET COMPANY NAME -- */

                        $prefix = (strlen($request->api_client_id) == 5) ? '0' : '';
                        $suffix = (strtolower($client_name) == 'alustre p/s') ? '/digitalvat' : '/intravat';
                        // Insert New connection with failure reason
                        $clientapi = ClientApi::updateOrCreate(                                       
                            [
                                'client_id' => $request->client_id,                                 
                                'api_name' => "Uniconta",
                                'api_env' =>  "Production",
                                'api_base_url' => "https://odata.uniconta.com/odata",
                                'api_tenant_id' => null,
                                'api_client_id' => ($request->api_client_id) ? ($prefix . $request->api_client_id . $suffix) : null,
                                'api_secret_key' => 'Urges905',
                                'api_company_id' => null,
                                'currency_code' => $currency_code,
                                'status' => 0,
                                'connection_name' => $request->connection_name,  
                                'connection_status' => 0, 
                                'connection_remarks' => $errorMessage,             
                            ]
                        );
                        // End Insert New connection with failure reason

                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();  

                        return response()->json(
                            [
                                'status' => 402,                        
                                'message' => $errorMessage,
                                'clientconnection' => $client_connections
                            ]
                        );
                    }
                }//end catch
            } //END  UNICONTA  

            /* -- SHOPIFY -- */
            if($request->erp_options == "Shopify")
            {
                $api_base_url = "https://". (($request->api_base_url) ? $request->api_base_url : 'quickstart-ad1f592d') .".myshopify.com";
                $api_version = ($request->api_client_id) ? $request->api_client_id : '2024-07';
                $api_secret_key = $request->api_secret_key;
                //$api_client_id = $request->api_client_id; 
                
                $guzzleClient = new GuzzleClient();    
                $headers = [                                   
                    'X-Shopify-Access-Token' => $api_secret_key,                  
                    'Content-Type' => 'application/json'          
                ];
                 
                $sales_invoice_url = "$api_base_url/admin/api/$api_version/shop.json";

                try
                {
                    /* -- GET DATAS FOR SHOPIFY -- */
                    $sales_invoice_response = $guzzleClient->request('GET', $sales_invoice_url, [
                        'headers' => $headers,
                        'verify'  => false,
                    ]);       
                    $sales_invoice_data = json_decode($sales_invoice_response->getBody());    

                    
                    if($sales_invoice_data != null)
                    {
                        /* -- CHECK CLIENT CONNECTION ALREADY EXISTS -- */  
                        $clientApidatas = ClientApi::where('client_id', $request->client_id)
                                            ->where('api_name', "Shopify")                                  
                                            ->get();  

                         if(count($clientApidatas) > 0)                        
                            $clientApidata_filter = $clientApidatas->filter(function($clientApidata, $key) use($api_secret_key) {
                                return $clientApidata->api_secret_key == $api_secret_key;
                            });                        
                        else                        
                            $clientApidata_filter = null;                            
                        /* --end  CHECK CLIENT CONNECTION ALREADY EXISTS -- */ 

                        if (count($clientApidata_filter) > 0)
                        {     
                             /* -- GET CLIENT CONNECTIONS -- */                                  
                            $client_connections = Client::with(['vatregmain','clientapi'])
                                                    ->whereHas('clientapi', function ($query) {
                                                        $query->whereIn('client_id', $this->clientIds);                      
                                                    })
                                                    ->get();  

                            /* -- end GET CLIENT CONNECTIONS -- */  
                             /* -- RETURN JSON -- */   
                            return response()->json(
                                [
                                    'status' => 401,                        
                                    'message' => 'Connection Already Established.',
                                    'clientconnection' => $client_connections
                                ]
                            );
                             /* -- RETURN JSON -- */  
                        } 
                        else 
                        {    
                             /* -- INSERT NEW CONNECTION -- */  
                            $clientapi = ClientApi::updateOrCreate(                                               
                                [
                                 'client_id' => $request->client_id,                                         
                                 'api_name' => "Shopify",
                                 'api_env' => "Production",
                                 'api_base_url' => "https://" . (($request->api_base_url) ? $request->api_base_url : 'quickstart-ad1f592d') . ".myshopify.com",
                                 'api_tenant_id' => ($request->api_tenant_id) ? $request->api_tenant_id : null,
                                 'api_client_id' => ($request->api_client_id) ? $request->api_client_id : '2024-07',
                                 'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                                 'api_company_id' => null,
                                 'currency_code' => $currency_code,
                                 'status' => 1,
                                 'connection_name' => $request->connection_name,  
                                 'connection_status' => 1, 
                                 'connection_remarks' => "Established Connection",                
                                ]
                            );
                             /* -- INSERT NEW CONNECTION -- */
                        } 

                        /* -- GET CLIENT CONNECTIONS -- */  
                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();
                        /* -- end GET CLIENT CONNECTIONS -- */ 

                    }/* --end if DATAS  FOR SHOPIFY -- */

                    /* -- RETURN JSON -- */  
                    return response()->json(
                        [
                            'status' => 200,                        
                            'message' => 'Connection Established',
                            'clientconnection' => $client_connections
                        ]
                    );
                    /* -- end RETURN JSON -- */  
                    /* --end GET DATAS FOR SHOPIFY -- */    
                }/* --end try SHOPIFY -- */
                catch (\Exception $e)                      
                {
                   $response = [];                                               
                   $response = $e->getMessage();
                   if($e->getMessage())
                   {
                        $json = response()->json($response);
                        $jsoncontent = $json->content();
                                            
                        $my_array = explode(":", $jsoncontent);                    
                        $lasterrorMsg = end($my_array);                    
                        $string = str_replace('\n', ' ', $lasterrorMsg); 
                   
                        $errorMessage = preg_replace('/[^A-Za-z0-9 ]/', '', $string);
                        
                        /* -- INSERT NEW CONNECTION WITH FAILURE REASON -- */ 
                        $clientapi = ClientApi::updateOrCreate(                                       
                            [
                                'client_id' => $request->client_id,                                         
                                'api_name' => "Shopify",
                                'api_env' => "Production",
                                'api_base_url' => "https://" . (($request->api_base_url) ? $request->api_base_url : 'quickstart-ad1f592d') . ".myshopify.com",
                                 'api_tenant_id' => ($request->api_tenant_id) ? $request->api_tenant_id : null,
                                 'api_client_id' => ($request->api_client_id) ? $request->api_client_id : '2024-07',
                                 'api_secret_key' => ($request->api_secret_key) ? $request->api_secret_key : null,
                                'api_company_id' => null,
                                'currency_code' => $currency_code,
                                'status' => 0,
                                'connection_name' => $request->connection_name,  
                                'connection_status' => 0, 
                                'connection_remarks' => $errorMessage,             
                            ]
                        );
                        /* -- end INSERT NEW CONNECTION WITH FAILURE REASON -- */ 
                    
                        /* -- GET CLIENT CONNECTIONS -- */  
                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();  
                        /*  -- end GET CLIENT CONNECTIONS -- */       

                        /* -- RETURN JSON -- */                       
                        return response()->json(
                            [
                                'status' => 402,                        
                                'message' => $errorMessage,
                                'clientconnection' => $client_connections
                            ]
                        );
                        /* -- end RETURN JSON -- */  
                    }/* --end catch RESPONSE -- */
                }//* --end catch SHOPIFY -- */
            } /* --end if SHOPIFY -- */ 

            /* -- BILLY -- */
            if($request->erp_options == "Billy")
            {
                try
                {
                    $api_secret_key = $request->api_secret_key;
                    
                    $guzzleClient = new GuzzleClient();    
                    $headers = [                                   
                        'X-Access-Token' => $api_secret_key,                       
                        'Content-Type' => 'application/json'          
                    ];
                    $testconnection_billy_url = "https://api.billysbilling.com/v2/organization";
                    $testconnection_billy_response = $guzzleClient->request('GET', $testconnection_billy_url, [
                        'headers' => $headers,
                        'verify'  => false,
                    ]);       
                    $testconnection_billy_data = json_decode($testconnection_billy_response->getBody()); 

                    if($testconnection_billy_data != null)
                    {
                        $clientApidatas = ClientApi::where('client_id', $request->client_id)
                                            ->where('api_name', "Billy")
                                            ->get();    

                        if(count($clientApidatas) > 0)
                            $clientApidata_filter = $clientApidatas->filter(function($clientApidata, $key) use($api_client_id) {
                                return $clientApidata->api_client_id == $api_client_id;
                            });
                        else
                            $clientApidata_filter = null;

                        if (empty($clientApidata_filter)) 
                        {
                            // Insert new connection success connection
                            $clientapi = ClientApi::updateOrCreate(                                               
                                [
                                    'client_id' => $request->client_id, 
                                    'vat_reg_main_id' =>null, 
                                    'api_name' => "Billy",
                                    'api_env' => "Production",
                                    'api_base_url' => "https://api.billysbilling.com/v2",
                                    'api_tenant_id' => null,
                                    'api_client_id' => 'dummy',
                                    'api_secret_key' => $api_secret_key,
                                    'api_company_id' => null,
                                    'currency_code' => $currency_code,
                                    'status' => 1 ,
                                    'connection_name' => $request->connection_name,  
                                    'connection_status' => 1, 
                                    'connection_remarks' => "Established Connection",              
                                ]
                            );
                            //End  Insert new connection success connection
                        }
                        else
                        {
                            $client_connections = Client::with(['vatregmain','clientapi'])
                                                    ->whereHas('clientapi', function ($query) {
                                                        $query->whereIn('client_id', $this->clientIds);                      
                                                    })
                                                    ->get();  


                            return response()->json(
                                [
                                    'status' => 401,                        
                                    'message' => 'Connection Already Established.',
                                    'clientconnection' => $client_connections
                                ]
                            );
                        }

                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();  

                    }
                    /* -- RETURN JSON -- */
                    return response()->json(
                        [
                            'status' => 200,                        
                            'message' => 'Connection Established',
                            'clientconnection' => $client_connections
                        ]
                    );
                } // end of try
                catch (\Exception $e)        
                {

                    if($e->getResponse())  
                    {
                        $response = $e->getResponse();

                        $errorMessage = json_decode($response->getBody()); 

                        // Insert New connection with failure reason
                        $clientapi = ClientApi::updateOrCreate(                                               
                            [
                                'client_id' => $request->client_id, 
                                'vat_reg_main_id' =>null, 
                                'api_name' => "Billy",
                                'api_env' => "Production",
                                'api_base_url' => "https://api.billysbilling.com/v2",
                                'api_tenant_id' => null,
                                'api_client_id' => 'dummy',
                                'api_secret_key' => $api_secret_key,
                                'api_company_id' => null,
                                'currency_code' => $currency_code,
                                'status' => 0 ,
                                'connection_name' => $request->connection_name,  
                                'connection_status' => 0, 
                                'connection_remarks' => $errorMessage->errorCode . '-' . $errorMessage->errorMessage,              
                            ]
                        );
                        // End Insert New connection with failure reason

                        $client_connections = Client::with(['vatregmain','clientapi'])
                                                ->whereHas('clientapi', function ($query) {
                                                    $query->whereIn('client_id', $this->clientIds);                      
                                                })
                                                ->get();  

                        return response()->json(
                            [
                                'status' => 402,                        
                                'message' => $errorMessage->errorCode . '-' . $errorMessage->errorMessage,
                                'clientconnection' => $client_connections
                            ]
                        );
                    } 
                } 

            } // end of if Billy
        }     
    }
}
