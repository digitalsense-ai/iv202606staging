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
use Illuminate\Http\Request;

class BillyApiClass
{        
    /*Billy API - ALL Invoices */
    public function getAllInvoicesLazy($request, $vatreg, $authUser, $all = false)
    {           
      try
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
        $api_secret_key = $clientapi->api_secret_key;
       
        $service_start = Carbon::parse($vatreg->service_start)->format('Y-m-d');
        $country = $vatreg->country;
        $vat_reg_id = $vatreg->vat_reg_id;

        $guzzleClient = new GuzzleClient();    
        $headers = [                                   
            'X-Access-Token' => $api_secret_key,       
            'Content-Type' => 'application/json'          
        ];
                  
        $end_date = $apiClass->getEndDateLazy($vatreg);                  
            
        $organization_url = "$api_base_url/organization?pageSize=1000&page=0";
        $organization_response = $guzzleClient->request('GET', $organization_url, [
                  'headers' => $headers,
                  'verify'  => false,
              ]);       
        $organization_data = json_decode($organization_response->getBody()); 
        $organization_id = ($organization_data->organization) ? $organization_data->organization->id : '';
             
        if($organization_id != '')      
        {
          //Sales      
          $sales_filter = "&entryDatePeriod=dates:$service_start...$end_date";
          
          //BOOKED
          $sales_invoice_url = "$api_base_url/invoices?organizationId=$organization_id&pageSize=1000$sales_filter&page=1";
          $sales_invoice_response = $guzzleClient->request('GET', $sales_invoice_url, [
            'headers' => $headers,
            'verify'  => false,
          ]);       
          $sales_invoice_data = json_decode($sales_invoice_response->getBody()); 
          $invoice_data = $sales_invoice_data->invoices;

          //Pagination
          $totalResultCount = $sales_invoice_data->meta->paging->total;
          $maxPageSizeAllowed = $sales_invoice_data->meta->paging->pageSize;

          if($totalResultCount > $maxPageSizeAllowed)
          {
            $pagination = $totalResultCount/$maxPageSizeAllowed;

            for($page = 2; $page<=$pagination; $page++)
            {
              $next_sales_invoice_url = "$api_base_url/invoices?organizationId=$organization_id&pageSize=1000$sales_filter&page=" . $page;
              $next_sales_invoice_response = $guzzleClient->request('GET', $next_sales_invoice_url, [
                'headers' => $headers,
                'verify'  => false,
              ]);
              $next_sales_invoice_data = json_decode($next_sales_invoice_response->getBody());             

              //Merge - Pagination
              $invoice_data = array_merge($invoice_data, $next_sales_invoice_data->collection);
            }
          }
        }

        return $invoice_data;    
      }
      catch (\Exception $e) {        
        if($e->getResponse())   
        {           
          $response = $e->getResponse();
          $errorMessage = json_decode($response->getBody());  
          
          $errorMessage = isset($errorMessage->errorMessage) ? $errorMessage->errorMessage : $errorMessage;
        }
        else
          $errorMessage = $e->getMessage();  

        return (object)['error' => $errorMessage];

      }  
  
    } 
}
