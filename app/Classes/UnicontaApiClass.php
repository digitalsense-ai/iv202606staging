<?php

namespace App\Classes;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Carbon;

use App\Models\Client;
use App\Models\ClientApi;
use App\Models\VATRegistration;
use App\Models\VATReturns;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use Illuminate\Support\Facades\Log;

use App\Traits\DecryptTrait;

class UnicontaApiClass
{    
    use DecryptTrait;

    /*Uniconta API - ALL VAT Account No. */
    public function getApiAllVATAccountNos($client, $authUser)
    { 
        try
        {                    
          $api_base_url = $client['api_base_url'];
          $api_client_id = $this->decryptValue($client->api_client_id);
          $api_secret_key = $this->decryptValue($client->api_secret_key);
          
          $headers = [                                   
              'X-Shopify-Access-Token' => $api_secret_key,                  
              'Content-Type' => 'application/json'          
          ];
                             
          $guzzleClient = new GuzzleClient();   
                     
          $accounts_url = "$api_base_url/GLAccountClient";

          $accounts_response = $guzzleClient->request('GET', $accounts_url, [
              'auth' => [$api_client_id, $api_secret_key],    
              'verify'  => false,
          ]);       
          $accounts_data = json_decode($accounts_response->getBody());

          $vat_account_nos = [];
          foreach ($accounts_data->value as $key=>$account) { 
            $vat_account_nos[$key] = [
              'vat_account_no' => $account->Account,
              'vat_account_name' => $account->Name,
            ];
          }

          return $vat_account_nos;           
        }
        catch (Exception $e) {
          return  $e->getMessage();
        }
    } 

    /*Uniconta API - ALL Invoices */
    public function getAllInvoicesLazy($request, $vatreg, $authUser, $all = false)
    {   
      try {                        
        $apiClass = new ApiClass();
        $commonClass = new CommonClass();
                 
        $vatregmain = $vatreg->vatregmain;
        $clientapi = $vatregmain->clientapi;
        $client = $vatreg->client;
                  
        $client_id = $client->client_id;
        $client_name = $client->client_name;
        $vat_no = $client->vatno;
        $api_base_url = $clientapi->api_base_url;
        $api_secret_key = $clientapi->api_secret_key;
        $api_client_id = $clientapi->api_client_id; 
        $service_start = Carbon::parse($vatreg->service_start)->format('Y-m-d');
        $country = $vatreg->country;
        $vat_reg_id = $vatreg->vat_reg_id;

        $guzzleClient = new GuzzleClient();    
                    
          $end_date = $apiClass->getEndDateLazy($vatreg);                  
          
          //Sales    
          if($vatreg->country == 'GB')     
            $sales_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00' and DeliveryCountry eq 'UnitedKingdom'";
          else if($vatreg->country == 'DK')     
            $sales_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00' and DeliveryCountry eq 'Denmark'";
          else if($vatreg->country == 'FR')
          {    
            if(strtolower($client_name) == 'alustre p/s' || strtolower($client_name) == 'alûstre p/s' || strtolower($client_name) == 'alÛstre p/s')
              $sales_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00' and (DeliveryCountry eq 'France' or DeliveryCountry eq 'Monaco' or startswith(Vat, 'MC'))";
            else
              $sales_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00' and (DeliveryCountry eq 'France' or DeliveryCountry eq 'Monaco')";
          }
          else
            $sales_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00'";
          
          $sales_invoice_url = "$api_base_url/DebtorInvoiceClient?\$filter=$sales_filter";
            
          $sales_invoice_response = $guzzleClient->request('GET', $sales_invoice_url, [
                    'auth' => [$api_client_id, $api_secret_key], 
                    'verify'  => false,
                ]);       
          $sales_invoice_data = json_decode($sales_invoice_response->getBody());    

          //Purchase  
          if($vatreg->country == 'GB')   
            $purchase_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00' and DeliveryCountry eq 'UnitedKingdom'";
          else if($vatreg->country == 'DK')   
            $purchase_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00' and DeliveryCountry eq 'Denmark'";
          else if($vatreg->country == 'FR')
          {   
            if(strtolower($client_name) == 'alustre p/s' || strtolower($client_name) == 'alûstre p/s' || strtolower($client_name) == 'alÛstre p/s')
              $purchase_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00' and (DeliveryCountry eq 'France' or DeliveryCountry eq 'Monaco' or startswith(Vat, 'MC'))";
            else
              $purchase_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00' and (DeliveryCountry eq 'France' or DeliveryCountry eq 'Monaco')";
          }
          else              
            $purchase_filter =  "Date ge datetime'".$service_start."T00:00:00' and Date le datetime'".$end_date."T00:00:00'";
           
          $purchase_invoice_url = "$api_base_url/CreditorInvoiceClient?\$filter=$purchase_filter";
            
          $purchase_invoice_response = $guzzleClient->request('GET', $purchase_invoice_url, [
                    'auth' => [$api_client_id, $api_secret_key], 
                    'verify'  => false,
                ]);       
          $purchase_invoice_data = json_decode($purchase_invoice_response->getBody());    

          $invoice_data = array_merge($sales_invoice_data->value, $purchase_invoice_data->value);

        
          return $invoice_data;
       
        } 
        catch (\Exception $e) {         
          return [];
        }
    }      
}
