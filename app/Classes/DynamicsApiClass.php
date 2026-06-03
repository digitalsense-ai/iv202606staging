<?php

namespace App\Classes;

use App\Models\Client;
use App\Models\ClientApi;
use App\Models\VATRegistration;
use App\Models\VATReturns;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use GuzzleHttp\Client as GuzzleClient;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

use App\Traits\DecryptTrait;

class DynamicsApiClass
{    
    use DecryptTrait;

    public function getAccessToken($client)
    {                
        if($client['api_token'] == null)           
            return $this->accessApi($client);                   
        else
        {   
            if(Carbon::parse($this->decryptValue($client['api_token_expire']))->addHour(1) <= Carbon::now())            
                return $this->accessApi($client);           
            else
                return "not expired";  
        }
    }

    public function accessApi($client)
    {            
        $api_base_url = $client['api_base_url'];
        $tenant_id = $this->decryptValue($client['api_tenant_id']);
        $client_id = $this->decryptValue($client['api_client_id']);
        $client_secret = $this->decryptValue($client['api_secret_key']);
        
        $params = [            
            'scope' => "$api_base_url/.default",            
            'grant_type' => "client_credentials",                        
            'client_secret' => $client_secret,
            'client_id' => $client_id,          
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
                
        $clientapi = ClientApi::where('client_id', $client['client_id'])
                        ->where('vat_reg_main_id', $client['vat_reg_main_id'])
                        ->first();
        $clientapi->api_token = $access_token->access_token;
        $clientapi->api_token_expire = Carbon::now();        
        $clientapi->save();                   
       
        return $access_token;  
    }  
    
    /*Dynamics 365 API - Client */
    public function getApiClient($auth_bearer, $client)
    {
      try
      {
        $client_name = $client->client_name;
        $vat_no = $client->vatno;
        $api_base_url = $client->api_base_url;
        $tenant_id = $this->decryptValue($client->api_tenant_id);
        $api_environment = $client->api_env;

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

        $client_data = json_decode($client_response->getBody());
        $i =0;     
        foreach($client_data->value as $key=>$client)
        {                    
            if(strtolower($client_name) == strtolower($client->name) || strtolower($client_name) == strtolower($client->displayName))
            {
              $i++;
              return $client;
            }
        }

        if($i == 0)       
          return "";
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*Dynamics 365 API - ALL VAT Account No. */
    public function getApiAllVATAccountNos($client, $authUser)
    { 
        try
        {          
          $api_base_url = $client['api_base_url'];
          $tenant_id = $this->decryptValue($client['api_tenant_id']);
          $api_environment = $client['api_env'];
          $api_company_id = $this->decryptValue($client['api_company_id']);
         
          $access_token = $this->getAccessToken($client);
          $api_token = $this->decryptValue($client['api_token']);
          $auth_bearer = ($access_token == "not expired") ? ('Bearer ' . $api_token) : ($access_token->token_type . ' ' . $access_token->access_token); 

          $headers = [               
              'Accept' => 'application/json',
              'Content-Type' => 'application/json',           
              'Authorization' => $auth_bearer           
          ];
          $guzzleClient = new GuzzleClient();   
        
          $accounts_url = "$api_base_url/v2.0/$tenant_id/$api_environment/api/v2.0/companies($api_company_id)/accounts";

          $accounts_response = $guzzleClient->request('GET', $accounts_url, [
              'headers' => $headers,          
              'verify'  => false,
          ]);       
          $accounts_data = json_decode($accounts_response->getBody());

          $vat_account_nos = [];
          foreach ($accounts_data->value as $key=>$account) { 
            $vat_account_nos[$key] = [
              'vat_account_no' => $account->number,
              'vat_account_name' => $account->displayName,
            ];
          }

          return $vat_account_nos;

          return $accounts_data->value; 
        }
        catch (Exception $e) {
          return  $e->getMessage();
        }
    }

    /*LAZY*/
    public function getAccessTokenLazy($vatreg)
    {               
      $vatregmain = $vatreg->vatregmain;
      $clientapi = $vatregmain->clientapi;

      if($clientapi->api_token == null)           
          return $this->accessApiLazy($vatreg);                   
      else
      {   
          if(Carbon::parse($clientapi->api_token_expire)->addHour(1) <= Carbon::now())            
              return $this->accessApiLazy($vatreg);           
          else
              return "not expired";  
      }
    }

    public function accessApiLazy($vatreg)
    {         
        $vatregmain = $vatreg->vatregmain;
        $clientapi = $vatregmain->clientapi;

        $api_base_url = $clientapi->api_base_url;
        $tenant_id = $clientapi->api_tenant_id;
        $client_api_id = $clientapi->api_client_id;
        $client_secret = $clientapi->api_secret_key;

        $params = [            
            'scope' => "$api_base_url/.default",            
            'grant_type' => "client_credentials",                        
            'client_secret' => $client_secret,
            'client_id' => $client_api_id,          
        ];

        $guzzleClient = new GuzzleClient();
        $url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";

        $headers = [               
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        try
        {
            $response = $guzzleClient->request('POST', $url, [
                'headers' => $headers,
                'form_params' => $params,
                'verify'  => false,
            ]);

            $access_token = json_decode($response->getBody());  
                  
            $clientapi = ClientApi::where('client_id', $vatreg->client_id)
                          ->where('vat_reg_main_id', $vatreg->vat_reg_main_id)
                          ->first();
            $clientapi->api_token = $access_token->access_token;
            $clientapi->api_token_expire = Carbon::now();        
            $clientapi->save();                   
         
            return $access_token;  
        }        
        catch (\Exception $e) 
        {        
          if($e->getResponse())  
          {
            $response = $e->getResponse();
            $errorMessage = json_decode($response->getBody()); 
          }
          else
            $errorMessage = $e->getMessage(); 

          return $errorMessage;      
        }
    }  
    
    /*Dynamics 365 API - Client */
    public function getApiClientLazy($auth_bearer, $vatreg)
    {
      try
      {
        $vatregmain = $vatreg->vatregmain;
        $clientapi = $vatregmain->clientapi;
        $client = $vatreg->client;

        $client_name = $client->client_name;
        $vat_no = $client->vatno;
        $api_base_url = $clientapi->api_base_url;
        $tenant_id = $clientapi->api_tenant_id;
        $api_environment = $clientapi->api_env;

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

        $client_data = json_decode($client_response->getBody());
        $i =0;     
        foreach($client_data->value as $key=>$client)
        {                    
            if(strtolower($client_name) == strtolower($client->name) || strtolower($client_name) == strtolower($client->displayName))
            {
              $i++;
              return $client;
            }
        }

        if($i == 0)  
        {     
          //return "";
          foreach($client_data->value as $key=>$client)
          {                    
            return $client;
          }
        }
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*Dynamics 365 API - ALL Invoices */
    public function getAllInvoicesLazy($request, $vatreg, $authUser, $all = false)
    {    
      $apiClass = new ApiClass();
      $commonClass = new CommonClass();
      
      $vatregmain = $vatreg->vatregmain;
      $clientapi = $vatregmain->clientapi;
      $client = $vatreg->client;

      $client_id = $client->client_id;
      $client_name = $client->client_name;
      $vat_no = $client->vatno;

      $api_name = $clientapi->api_name;
      $api_base_url = $clientapi->api_base_url;
      $tenant_id = $clientapi->api_tenant_id;
      $api_environment = $clientapi->api_env;
      $api_company_id = $clientapi->api_company_id;

      $service_start = Carbon::parse($vatreg->service_start)->format('Y-m-d');
      $country = $vatreg->country;
      $vat_reg_id = $vatreg->vat_reg_id;
                            
        $access_token = $this->getAccessTokenLazy($vatreg);
        $api_token = $clientapi->api_token;
        $auth_bearer = ($access_token == "not expired") ? ('Bearer ' . $api_token) : ($access_token->token_type . ' ' . $access_token->access_token); 
        $headers = [               
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',           
            'Authorization' => $auth_bearer           
        ];
        
        $end_date = $apiClass->getEndDateLazy($vatreg);            

        //Sales
        if($service_start == '2026-01-01' && $end_date == '2026-03-31' && strtolower($client_name) == 'fairpoint outdoors a/s')         
          $sales_filter = "((sellToCountry eq '".$country."') and (invoiceDate ge $service_start and invoiceDate le $end_date and invoiceDate ne 2026-03-27))";         
        else
          $sales_filter = "((sellToCountry eq '".$country."') and (invoiceDate ge $service_start and invoiceDate le $end_date))";
        
        if($api_environment == "Sandbox")
          $sales_expand = "salesInvoiceLines,customer,pdfDocument";
        else
          $sales_expand = "salesInvoiceLines,customer";

        $sales_invoice_url = "$api_base_url/v2.0/$tenant_id/$api_environment/api/v2.0/companies($api_company_id)/salesInvoices?\$expand=$sales_expand&\$filter=$sales_filter"; 

        $guzzleClient = new GuzzleClient();  
        try 
        {
          $sales_invoice_response = $guzzleClient->request('GET', $sales_invoice_url, [
                  'headers' => $headers,
                  'verify'  => false,
              ]);
          $sales_invoice_data = json_decode($sales_invoice_response->getBody());   
        } 
        catch (\Exception $e) 
        {           
          if($e->getResponse())  
          {
            $response = $e->getResponse();
            $errorMessage = json_decode($response->getBody()); 
          } 
        } 
                
        //Sales Credit Memos
        $sales_credit_memos_filter = "((sellToCountry eq '".$country."') and (creditMemoDate ge $service_start and creditMemoDate le $end_date))";
        
        $sales_credit_memos_expand = "customer";

        $sales_credit_memos_invoice_url = "$api_base_url/v2.0/$tenant_id/$api_environment/api/v2.0/companies($api_company_id)/salesCreditMemos?\$expand=$sales_credit_memos_expand&\$filter=$sales_credit_memos_filter"; 

        $guzzleClient = new GuzzleClient();  
        try 
        {
          $sales_credit_memos_invoice_response = $guzzleClient->request('GET', $sales_credit_memos_invoice_url, [
                  'headers' => $headers,
                  'verify'  => false,
              ]);
          $sales_credit_memos_invoice_data = json_decode($sales_credit_memos_invoice_response->getBody());   
        } 
        catch (\Exception $e) 
        {           
          if($e->getResponse())  
          {
            $response = $e->getResponse();
            $errorMessage = json_decode($response->getBody()); 
          } 
        } 
                     
        //Purchase
        $purchase_filter = "((buyFromCountry eq '".$country."') and (invoiceDate ge $service_start and invoiceDate le $end_date))";
            
        $purchase_expand = "purchaseInvoiceLines,vendor";

        $purchase_invoice_url = "$api_base_url/v2.0/$tenant_id/$api_environment/api/v2.0/companies($api_company_id)/purchaseInvoices?\$expand=$purchase_expand&\$filter=$purchase_filter"; 

        $guzzleClient = new GuzzleClient();  
        try 
        {
            $purchase_invoice_response = $guzzleClient->request('GET', $purchase_invoice_url, [
                  'headers' => $headers,
                  'verify'  => false,
              ]);       
            $purchase_invoice_data = json_decode($purchase_invoice_response->getBody()); 
        } 
        catch (\Exception $e) 
        {
          if($e->getResponse())  
          {
            $response = $e->getResponse();
            $errorMessage = json_decode($response->getBody());  
          }
        } 
       
        if(isset($errorMessage))        
          $invoice_data = $errorMessage;                        
        else
          $invoice_data = array_merge($sales_invoice_data->value, $sales_credit_memos_invoice_data->value, $purchase_invoice_data->value);          
        
        return $invoice_data;     
    }

    
}
