<?php

namespace App\Classes;

use Illuminate\Support\Carbon;

use GuzzleHttp\Client as GuzzleClient;

class DynamicsSmartApiClass
{       
    /*Dynamics 365 SmartAPI - ALL Invoices */
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

      $sales_invoice_url = $clientapi->sales_invoice_url;
      $purchase_invoice_url = $clientapi->purchase_invoice_url;
      
      $service_start = Carbon::parse($vatreg->service_start)->format('Y-m-d');
      $country = $vatreg->country;
      $vat_reg_id = $vatreg->vat_reg_id;
                  
      $end_date = $apiClass->getEndDateLazy($vatreg);            

      if($sales_invoice_url != ""  )
      {
        //Sales        
        $sales_filter = "&filtertext=posting date=filter($service_start..$end_date),Bill-to Country/Region Code=CONST($country)";        
        
        $sales_invoice_url = $sales_invoice_url . '' . $sales_filter; 

        $guzzleClient = new GuzzleClient();  
        try 
        {        
          $sales_invoice_client  = new \GuzzleHttp\Client(['verify' =>false]); //ssl verifyication 
          $sales_invoice_request = new \GuzzleHttp\Psr7\Request('GET', $sales_invoice_url);

          $sales_invoice_response = $guzzleClient->sendAsync($sales_invoice_request)->then(function ($response) {            
            return $response->getBody()->getContents();
          });
          $sales_invoice_xml = $sales_invoice_response->wait();  

          $xmlObject = simplexml_load_string($sales_invoice_xml);
          $json = json_encode($xmlObject);
          $sales_invoice_data = json_decode($json, true);           
        } 
        catch (\Exception $e) 
        { 
          if($e->getMessage())
            $errorMessage = "error";
          else
          {
            if($e->getResponse())  
            {
              $response = $e->getMessage();
              $errorMessage = json_decode($response->getBody()); 
            } 
          }
        } 
      }        
      
      if(isset($errorMessage))        
        $invoice_data = $errorMessage;                        
      else
        $invoice_data = array_merge($sales_invoice_data['salesinvoices']);

      return $invoice_data;
     
    }

    
}
