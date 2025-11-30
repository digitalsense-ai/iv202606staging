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

class ShopifyApiClass
{    
    use DecryptTrait;

    /*Shopify API - ALL Invoices */
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
      $api_base_url = $clientapi->api_base_url;
    
      $api_secret_key = $clientapi->api_secret_key;
     
      $api_version = $clientapi->api_client_id;
      $service_start = Carbon::parse($vatreg->service_start)->format('Y-m-d');
      $country = $vatreg->country;
      $vat_reg_id = $vatreg->vat_reg_id;

      $guzzleClient = new GuzzleClient();    
      $headers = [                                   
          'X-Shopify-Access-Token' => $api_secret_key,                  
          'Content-Type' => 'application/json'          
      ];
      
                
        $end_date = $apiClass->getEndDateLazy($vatreg);                  
              
        //Sales              
        $sales_filter = "created_at_min=".$service_start."&created_at_max=".$end_date;
            
        $sales_invoice_url = "$api_base_url/admin/api/$api_version/orders.json?$sales_filter";

        $sales_invoice_response = $guzzleClient->request('GET', $sales_invoice_url, [
              'headers' => $headers,
              'verify'  => false,
          ]);       
        $sales_invoice_data = json_decode($sales_invoice_response->getBody());    

        $invoice_data = $sales_invoice_data->orders;

        
        return $invoice_data;
     
    }

    /*Shopify API - ALL VAT Account No. */
    public function getApiAllVATAccountNos($client, $authUser)
    { 
        try
        {                    
          $api_base_url = $client['api_base_url'];
          $api_version = $this->decryptValue($client->api_tenant_id);
          $api_secret_key = $this->decryptValue($client->api_secret_key);
          
          $headers = [                                   
              'X-Shopify-Access-Token' => $api_secret_key,                  
              'Content-Type' => 'application/json'          
          ];
                             
          $guzzleClient = new GuzzleClient();   
              
          $accounts_url = "$api_base_url/admin/api/$api_version/XXXXXXXXXXXX.json";

          $accounts_response = $guzzleClient->request('GET', $accounts_url, [
              'headers' => $headers,          
              'verify'  => false,
          ]);       
          $accounts_data = json_decode($accounts_response->getBody());

          $vat_account_nos = [];
          foreach ($accounts_data->collection as $key=>$account) { 
            $vat_account_nos[$key] = [
              'vat_account_no' => $account->account->accountNumber,
              'vat_account_name' => $account->name,
            ];
          }

          return $vat_account_nos;           
        }
        catch (Exception $e) {
          return  $e->getMessage();
        }
    }     
}
