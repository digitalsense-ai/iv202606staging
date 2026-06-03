<?php

namespace App\Classes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Carbon;

use App\Models\Client;
use App\Models\ClientApi;
use App\Models\VATRegistration;
use App\Models\VATReturns;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

use App\Events\VATReturnEvent;

class EconomicApiClass
{        
    /*Economics API - ALL Invoices */  
    public function getAllInvoicesLazy($request, $vatreg, $authUser, $check_accountnos = [])
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
      $api_client_id = $clientapi->api_client_id; 
      $service_start = Carbon::parse($vatreg->service_start)->format('Y-m-d');
      $country = $vatreg->country;
      $vat_reg_id = $vatreg->vat_reg_id;

      $guzzleClient = new GuzzleClient();    
      $headers = [                                   
          'X-AppSecretToken' => $api_secret_key,
          'X-AgreementGrantToken' => $api_client_id,
          'Content-Type' => 'application/json'          
      ];
      
      try
      {
        //SELF CHECK
        $self_check_url = "$api_base_url/self";
        $self_check_response = $guzzleClient->request('GET', $self_check_url, [
            'headers' => $headers,
            'verify'  => false,
        ]);       
        $self_check_data = json_decode($self_check_response->getBody());       
  
        if(!isset($self_check_data->errorCode))       
        {                  
          $end_date = $apiClass->getEndDateLazy($vatreg);                  
         
          $invoice_data = [];
       
          /* -- GET account nos. INVOICES -- */
          if($vatregmain->accnos)
          {          
            $accountnos = $vatregmain->accnos;
            
            if(count($accountnos) > 0)
            {
              $currencycode = '';     
              if($country == "DK")     
                $currencycode = "DKK";          
              elseif($country == "NO")
                $currencycode = "NOK";
              elseif($country == "SE") 
                $currencycode = "SEK";
              elseif($country == "GB")
                $currencycode = "GBP";          
              elseif($country == "IN")  
                $currencycode = "INR";          
              elseif($country == "FR")      
                $currencycode = "EUR";
              elseif($country == "CH")      
                $currencycode = "CHF";

              $invoice_text = 'Invoice:';
              $credit_text = 'Credit:';

              $service_start_year = Carbon::parse($service_start)->format('Y-m-d');
              $service_end_year = Carbon::parse($end_date)->format('Y-m-d');
             
              //$accounting_year_filter = "(fromDate\$lte:$service_start_year\$and:toDate\$gte:$service_end_year)";

              // $service_start_year = '2026-01-01';
              // $service_end_year = '2026-01-31';
              //$accounting_year_filter = "(fromDate\$lte:2026-01-05\$and:toDate\$gte:2026-01-01)";        

              //overlap a date range
              $accounting_year_filter = "(fromDate\$lte:$service_end_year\$and:toDate\$gte:$service_start_year)";
              
              $accounting_year_url = "https://restapi.e-conomic.com/accounts/". $accountnos[0]->acc_no ."/accounting-years?filter=$accounting_year_filter";

              $accounting_year_response = $guzzleClient->request('GET', $accounting_year_url, [
                        'headers' => $headers,
                        'verify'  => false,
                    ]);       
              $accounting_year_data = json_decode($accounting_year_response->getBody()); 

              $account_no_datas = [];
              if(count($accounting_year_data->collection) > 0)          
              {   
                $period_index = 0;
                $arr_period_number = [];
                foreach ($accounting_year_data->collection as $accounting_year) 
                {         
                  //$accounting_year_baseurl = $accounting_year_data->collection[0]->self;        
                  //$accounting_year = $accounting_year_data->collection[0]->year;

                  $accounting_year_baseurl = $accounting_year->self;        
                  
                  /* -- GET account nos. PERIODS -- */               
                  $accounting_year_periods_filter = "(fromDate\$gte:$service_start_year\$and:toDate\$lte:$service_end_year)";

                  $accounting_year_periods_url = "$accounting_year_baseurl/periods?filter=$accounting_year_periods_filter";
                  $accounting_year_periods_response = $guzzleClient->request('GET', $accounting_year_periods_url, [
                            'headers' => $headers,
                            'verify'  => false,
                        ]);       
                  $accounting_year_periods_data = json_decode($accounting_year_periods_response->getBody()); 

                  foreach ($accounting_year_periods_data->collection as $accounting_year_period) 
                  {
                    $accounting_year_period_number = $accounting_year_period->periodNumber;

                    $arr_period_number[$period_index] = [
                      'baseUrl' => $accounting_year_baseurl,
                      'periodNumber' => $accounting_year_period_number
                    ];
                    $period_index++;
                  }
                  /* -- end GET PERIOD NUMBERS -- */
                }/* --end for PERIODS -- */ 

                  /* -- GET account nos. PERIOD ENTRIES -- */                
                  $entries_filter = "(date\$gte:$service_start_year\$and:date\$lte:$service_end_year)";

                  if($client_name == "Designbysi ApS")                
                    $entries_filter .= "\$and:(currency\$eq:$currencycode\$or:currency\$eq:SEK)";

                  /* fetchWithRetry */
                  // foreach ($accounting_year_periods_data->collection as $accounting_year_period) 
                  // {
                  //   $accounting_year_period_number = $accounting_year_period->periodNumber;
                  foreach ($arr_period_number as $period_number) 
                  {
                    $accounting_year_baseurl = $period_number['baseUrl'];
                    $accounting_year_period_number = $period_number['periodNumber'];

                    $account_no_url = "$accounting_year_baseurl/periods/$accounting_year_period_number/entries?pagesize=1000&filter=$entries_filter&skippages=0";
                    
                    $data = $this->fetchWithRetry($guzzleClient, $account_no_url, $headers);

                    if (!$data) continue; // Skip if failed

                    $account_no_datas = array_merge($account_no_datas, $data->collection ?? []);
                    
                    //PAGINATION
                    $totalResultCount = $data->pagination->results ?? 0;
                    $maxPageSizeAllowed = $data->pagination->maxPageSizeAllowed ?? 1000;

                    if($totalResultCount > $maxPageSizeAllowed)
                    {
                      $pagination = ceil($totalResultCount/$maxPageSizeAllowed);

                      for($page = 1; $page<=$pagination; $page++)
                      {
                        $next_account_no_url = "$accounting_year_baseurl/periods/$accounting_year_period_number/entries?pagesize=1000&filter=$entries_filter&skippages=$page";
                        
                        $pageData = $this->fetchWithRetry($guzzleClient, $next_account_no_url, $headers);

                        if (!$pageData) continue;

                        $account_no_datas = array_merge($account_no_datas, $pageData->collection ?? []);
                      } /* --end for PAGINATION -- */
                    } /* --end if RESULT COUNT > MAX PAGE SIZE -- */
                    /* --end PAGINATION -- */
                  }/* --end for PERIODS -- */
                  /* fetchWithRetry */                
              } /* --end if SELF COLLECTION EXISTS -- */
         
              if(count($account_no_datas) > 0)
              {
                if(count($check_accountnos) > 0)
                  $accountnos = $check_accountnos;
                
                foreach ($accountnos as $accountno) 
                {
                  $filter_account_no_datas = array_filter($account_no_datas, function ($account_no_data) use($accountno) {
                    return ($account_no_data->account->accountNumber == $accountno->acc_no);
                  });
     
                  $invoice_data = array_merge($invoice_data, $filter_account_no_datas);                  
                }     
              } /* --end if HAS ACCOUNT INVOICES -- */
              /* -- GET account nos. PERIOD ENTRIES -- */

              /* -- GET vat percentage -- */
              $vat_cache = [];
              foreach ($invoice_data as $invoice) 
              {
                if(isset($invoice->account))            
                {
                  if(isset($invoice->vatAccount))            
                  {
                    $vat_account_url = $invoice->vatAccount->self;
                    
                    if (!isset($vat_cache[$vat_account_url])) {
                        $vat_account_data = $this->fetchWithRetry($guzzleClient, $vat_account_url, $headers);
                        $vat_cache[$vat_account_url] = $vat_account_data->ratePercentage ?? null;
                    }

                    $invoice->ratePercentage = $vat_cache[$vat_account_url];              
                  }
                }
              }
              /* -- GET vat percentage -- */
            } /* --end if account nos. CHECKED -- */
          } /* --end if GET account nos. -- */
          //Log::info("invoice data.", ['datas' => $invoice_data]);                
          return $invoice_data;

          //return $batch->id;

        }  //valid token
      }//try  
      catch (RequestException $e) {
          // this block can safely use $e->getResponse()
          if ($e->hasResponse()) {
              $response = $e->getResponse();
              $statusCode = $response->getStatusCode();

              if ($statusCode === 503) {
                  Log::warning("503 Service Unavailable: Skipping this job.");
                  return; // or handle accordingly
              }

              $errorMessage = json_decode($response->getBody()->getContents());
          } else {
              $errorMessage = $e->getMessage();
          }

          return (object)['error' => $errorMessage];
      } catch (\Exception $e) {
          // fallback for non-Guzzle errors
          return (object)['error' => $e->getMessage()];
      }
    } 

    function fetchWithRetry(GuzzleClient $client, string $url, array $headers, int $maxRetries = 3): ?object
    {
        $delay = 1; // Start with 1 second

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $client->request('GET', $url, [
                    'headers' => $headers,
                    'verify' => false,
                ]);

                usleep(200000); // 200ms between successful calls
                return json_decode($response->getBody());
            } catch (RequestException $e) {
                $status = $e->getResponse()?->getStatusCode();

                if ($status === 503 && $attempt < $maxRetries) {
                    // Log and retry after delay
                    Log::warning("503 received. Retry #$attempt after {$delay}s for URL: $url");
                    sleep($delay);
                    $delay *= 2; // Exponential backoff
                } else {
                    // Final failure or non-503 error
                    Log::error("Request failed for URL: $url", [
                        'status' => $status,
                        'message' => $e->getMessage()
                    ]);
                    return null; // Or throw $e; to stop execution
                }
            }
        }

        return null; // All retries failed
    }


    // /*Economics API - ALL VAT Account No. */
    // public function getApiAllVATAccountNos($client, $authUser)
    // { 
    //     try
    //     {          
    //       $api_secret_key = $this->decryptValue($client->api_secret_key);
    //       $api_client_id = $this->decryptValue($client->api_client_id); 
                   
    //       $headers = [                                   
    //           'X-AppSecretToken' => $api_secret_key,
    //           'X-AgreementGrantToken' => $api_client_id,
    //           'Content-Type' => 'application/json'          
    //       ];
    //       $guzzleClient = new GuzzleClient();   
     
    //       $accounts_url = "https://restapi.e-conomic.com/vat-accounts/?pagesize=1000";

    //       $accounts_response = $guzzleClient->request('GET', $accounts_url, [
    //           'headers' => $headers,          
    //           'verify'  => false,
    //       ]);       
    //       $accounts_data = json_decode($accounts_response->getBody());

    //       $vat_account_nos = [];
    //       foreach ($accounts_data->collection as $key=>$account) { 
    //         $vat_account_nos[$key] = [
    //           'vat_account_no' => $account->account->accountNumber,
    //           'vat_account_name' => $account->name,
    //         ];
    //       }

    //       return $vat_account_nos;           
    //     }
    //     catch (Exception $e) {         
    //       if($e->getResponse())   
    //       {           
    //         $response = $e->getResponse();
    //         $errorMessage = json_decode($response->getBody());  
    //       }
    //       else
    //         $errorMessage = $e->getMessage();  

    //       return (object)['error' => $errorMessage];
    //     }
    // }

    /*Economics API - ALL Account No. */
    public function getApiAllAccountNos($api_client_id)
    { 
        try
        {          
          $api_secret_key = "2NBwnBEXouJc1klye2sX05tHflCaIXZObXJ0yuksRDM1";
                  
          $headers = [                                   
              'X-AppSecretToken' => $api_secret_key,
              'X-AgreementGrantToken' => $api_client_id,
              'Content-Type' => 'application/json'          
          ];
          $guzzleClient = new GuzzleClient();   
     
          $accounts_url = "https://restapi.e-conomic.com/accounts/?pagesize=1000";

          $accounts_response = $guzzleClient->request('GET', $accounts_url, [
              'headers' => $headers,          
              'verify'  => false,
          ]);       
          $accounts_data = json_decode($accounts_response->getBody());

          $account_nos = [];
          foreach ($accounts_data->collection as $key=>$account) { 
            $account_nos[$key] = [
              'account_no' => $account->accountNumber,
              'account_name' => $account->name,             
              'account_type' => $account->debitCredit
            ];
          }

          return $account_nos;
        }
        catch (\Exception $e) {
          //return  $e->getMessage();

          if($e->getResponse())   
          {           
            $response = $e->getResponse();
            $errorMessage = json_decode($response->getBody());  
          }
          else
            $errorMessage = $e->getMessage();  

          return (object)['error' => $errorMessage];

        }
    }
}
