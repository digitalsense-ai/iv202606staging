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

//use App\Jobs\EconomicsApi;

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
    //public function getAllInvoicesLazy($request, $vatreg, $authUser, $all = false)
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
          /*     
          //Sales      
          $sales_filter = "date\$gte:$service_start\$and:date\$lte:$end_date";

          if($country == 'GB')
            $sales_filter .= "\$and:(recipient.country\$like:United kingdom\$or:recipient.country\$like:UK\$or:recipient.country\$like:$country)";
          else if($country == 'DK')
            $sales_filter .= "\$and:(recipient.country\$like:Denmark\$or:recipient.country\$like:Danmark\$or:recipient.country\$like:$country)";
          else
            $sales_filter .= "\$and:(recipient.country\$like:$country)";
          
          //BOOKED
          $sales_invoice_url = "https://restapi.e-conomic.com/invoices/booked/?pagesize=1000&filter=$sales_filter&skippages=0";
          $sales_invoice_response = $guzzleClient->request('GET', $sales_invoice_url, [
                    'headers' => $headers,
                    'verify'  => false,
                ]);       
          $sales_invoice_data = json_decode($sales_invoice_response->getBody()); 
          $invoice_data = $sales_invoice_data->collection;

          //Pagination
          $totalResultCount = $sales_invoice_data->pagination->results;
          $maxPageSizeAllowed = $sales_invoice_data->pagination->maxPageSizeAllowed;

          if($totalResultCount > $maxPageSizeAllowed)
          {
            $pagination = $totalResultCount/$maxPageSizeAllowed;

            for($page = 1; $page<=$pagination; $page++)
            {
              $next_sales_invoice_url = "https://restapi.e-conomic.com/invoices/booked/?pagesize=1000&filter=$sales_filter&skippages=" . $page;
              $next_sales_invoice_response = $guzzleClient->request('GET', $next_sales_invoice_url, [
                  'headers' => $headers,
                  'verify'  => false,
              ]);
              $next_sales_invoice_data = json_decode($next_sales_invoice_response->getBody());             

              //Merge - Pagination
              $invoice_data = array_merge($invoice_data, $next_sales_invoice_data->collection);
            }
          }
          */
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

              // $accounting_year_filter = "(fromDate\$lte:$service_start_year\$and:toDate\$gte:$service_start_year)\$and:(fromDate\$lte:$service_end_year\$and:toDate\$gte:$service_end_year)";

              $accounting_year_filter = "(fromDate\$lte:$service_start_year\$and:toDate\$gte:$service_end_year)";

              $accounting_year_url = "https://restapi.e-conomic.com/accounts/". $accountnos[0]->acc_no ."/accounting-years?filter=$accounting_year_filter";

              $accounting_year_response = $guzzleClient->request('GET', $accounting_year_url, [
                        'headers' => $headers,
                        'verify'  => false,
                    ]);       
              $accounting_year_data = json_decode($accounting_year_response->getBody()); 

              $account_no_datas = [];
              if(count($accounting_year_data->collection) > 0)          
              {            
                $accounting_year_baseurl = $accounting_year_data->collection[0]->self;
      
                $accounting_year = $accounting_year_data->collection[0]->year;

                /* -- GET account nos. PERIODS -- */
                // $accounting_year_periods_filter = "(fromDate\$lte:$service_start_year\$and:toDate\$gte:$service_start_year)\$or:(fromDate\$lte:$service_end_year\$and:toDate\$gte:$service_end_year)";

                $accounting_year_periods_filter = "(fromDate\$gte:$service_start_year\$and:toDate\$lte:$service_end_year)";

                $accounting_year_periods_url = "$accounting_year_baseurl/periods?filter=$accounting_year_periods_filter";
                $accounting_year_periods_response = $guzzleClient->request('GET', $accounting_year_periods_url, [
                          'headers' => $headers,
                          'verify'  => false,
                      ]);       
                $accounting_year_periods_data = json_decode($accounting_year_periods_response->getBody()); 
                /* -- end GET account nos. PERIODS -- */
   
                /* -- GET account nos. PERIOD ENTRIES -- */
                //$service_start_month = Carbon::parse($service_start)->format('m') + 1;
                //$service_end_month = Carbon::parse($end_date)->format('m') + 1;
                //$entries_filter = "currency\$eq:$currencycode\$and:(text\$like:$invoice_text\$or:text\$like:$credit_text)";
                //$entries_filter = "currency\$eq:$currencycode";
                //if($client_name == "Faundit ApS" || $client_name == "Nordic Clays ApS")
                $entries_filter = "(date\$gte:$service_start_year\$and:date\$lte:$service_end_year)";

                if($client_name == "Designbysi ApS")
                  //$entries_filter = "text\$like:44241063\$or:text\$like:44240688";
                  $entries_filter .= "\$and:(currency\$eq:$currencycode\$or:currency\$eq:SEK)";

                // /*CHUNKS - JOBS*/                
                // $jobs = [];              
                // foreach ($accounting_year_periods_data->collection as $accounting_year_period) 
                // {
                //   $accounting_year_period_number = $accounting_year_period->periodNumber;

                //   $account_no_url = "$accounting_year_baseurl/periods/$accounting_year_period_number/entries?pagesize=1000&filter=$entries_filter&skippages=0";

                //   //$jobs[] = new EconomicsApi($vat_reg_id, $headers, $accountnos, $account_no_url, 0);

                //   $account_no_response = $guzzleClient->request('GET', $account_no_url, [
                //         'headers' => $headers,
                //         'verify'  => false,
                //     ]);       
                //   $account_no_data = json_decode($account_no_response->getBody()); 
                 
                //   $invoice_data = [];
                //   if(count($account_no_data->collection) > 0)
                //   {                                      
                //     foreach ($accountnos as $accountno) 
                //     {
                //       $filter_account_no_datas = array_filter($account_no_data->collection, function ($account_no_data) use($accountno) {
                //         return ($account_no_data->account->accountNumber == $accountno->acc_no);
                //       });
         
                //       $invoice_data = array_merge($invoice_data, $filter_account_no_datas);                  
                //     }            
                //   } /* --end if HAS ACCOUNT INVOICES -- */

                //   /* -- GET vat percentage -- */
                //   foreach ($invoice_data as $key => $invoice) 
                //   {
                //     if(isset($invoice->account))            
                //     {
                //       if(isset($invoice->vatAccount))            
                //       {
                //         $vat_account_url = $invoice->vatAccount->self;
                //         $vat_account_response = $guzzleClient->request('GET', $vat_account_url, [
                //               'headers' => $headers,
                //               'verify'  => false,
                //           ]);
                //         $vat_account_data = json_decode($vat_account_response->getBody()); 

                //         //$invoice->ratePercentage = $vat_account_data->ratePercentage;
                //         $invoice_data[$key]->ratePercentage = $vat_account_data->ratePercentage;
                //       }
                //     }
                //   }
                //   /* -- GET vat percentage -- */

                  

                //   //$account_no_datas = array_merge($account_no_datas, $invoice_data);

                //   // $invoice_data = [];

                //   //PAGINATION
                //   $totalResultCount = $account_no_data->pagination->results;
                //   $maxPageSizeAllowed = $account_no_data->pagination->maxPageSizeAllowed;                  

                //   if($totalResultCount > $maxPageSizeAllowed)
                //   {
                //     $pagination = ceil($totalResultCount/$maxPageSizeAllowed);
                    
                //     // Save api_call_results first page
                //     DB::table('api_call_results')->insert([
                //         'vat_reg_id' => $vat_reg_id,
                //         'page_no' => 0,
                //         'total_job' => $pagination,
                //         'status' => 'completed',
                //         'account_no_datas' => json_encode($invoice_data),
                //         'created_at' => now(),
                //         'updated_at' => now(),                        
                //     ]);

                //     for($page = 1; $page<=$pagination; $page++)
                //     {
                //       $next_account_no_url = "$accounting_year_baseurl/periods/$accounting_year_period_number/entries?pagesize=1000&filter=$entries_filter&skippages=$page";

                //       // Save api_call_results row
                //       DB::table('api_call_results')->insert([
                //           'vat_reg_id' => $vat_reg_id,
                //           'page_no' => $page,
                //           'total_job' => $pagination,
                //           'status' => 'pending',
                //           'created_at' => now()                          
                //       ]);

                //       //$jobs[] = new EconomicsApi($vat_reg_id, $headers, $accountnos, $next_account_no_url, $account_no_datas, $page);

                //       $jobs[] = new EconomicsApi($vat_reg_id, $headers, $accountnos, $next_account_no_url, $page);
                //     } /* --end for PAGINATION -- */
                //   } /* --end if RESULT COUNT > MAX PAGE SIZE -- */
                //   else
                //   {
                //     // Save api_call_results first page
                //     DB::table('api_call_results')->insert([
                //         'vat_reg_id' => $vat_reg_id,
                //         'page_no' => 0,
                //         'total_job' => 1,
                //         'status' => 'completed',
                //         'account_no_datas' => json_encode($invoice_data),
                //         'created_at' => now(),
                //         'updated_at' => now(),
                //     ]);
                //   }
                //   /* --end PAGINATION -- */
                //   //dd("first account period", $jobs);
                // }/* --end for PERIODS -- */
                
                // $batch = Bus::batch($jobs)                         
                //           ->then(function (Batch $batch) use ($commonClass, $vat_reg_id, $authUser, $api_name) {
                //               // // All jobs finished – now you can fetch the stored results
                //               // $api_call_results = DB::table('api_call_results')                                  
                //               //     ->where('vat_reg_id', $vat_reg_id)
                //               //     ->update(['batch_id' => $batch->id]);
                                                        
                //               // $api_call_result_account_no_datas = json_decode($api_call_results->account_no_datas);

                //               // /*Common class*/                            
                //               // $invoice_data = $api_call_result_account_no_datas;

                //               // $insert_invoices = $commonClass->insertInvoices($api_call_result_account_no_datas, $vat_reg_id, $authUser, $api_name);
                              
                //               // Broadcast the event                                            
                //               event(new VATReturnEvent($vat_reg_id, 'Update overview tab'));  
                //               /*Common class*/
                //           })
                //           ->catch(function (Batch $batch, Throwable $e) {
                //               Log::error("Batch failed for VAT ID $vat_reg_id: " . $e->getMessage());
                //           })
                //           ->finally(function (Batch $batch) {
                //               Log::info("Batch finished with status: " . $batch->status);

                //               // $completed = DB::table('api_call_results')
                //               //     ->where('vat_reg_id', $vat_reg_id)
                //               //     ->where('status', 'completed')
                //               //     //->pluck('page_no');
                //               //     ->update(['status' => 'finally']);

                //               // $failed = DB::table('api_call_results')
                //               //     ->where('vat_reg_id', $vat_reg_id)
                //               //     ->where('status', 'failed')
                //               //     ->pluck('page_no');
                //           })
                //           ->dispatch();
                //           //->onQueue('economicsapi'); // Dispatch the job   
                // /*CHUNKS - JOBS*/

                /* fetchWithRetry */
                foreach ($accounting_year_periods_data->collection as $accounting_year_period) 
                {
                  $accounting_year_period_number = $accounting_year_period->periodNumber;

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

                // //for($i = $service_start_month; $i <= $service_end_month; $i++)
                // foreach ($accounting_year_periods_data->collection as $accounting_year_period) 
                // {
                //   $accounting_year_period_number = $accounting_year_period->periodNumber;

                //   $account_no_url = "$accounting_year_baseurl/periods/$accounting_year_period_number/entries?pagesize=1000&filter=$entries_filter&skippages=0";

                //   $account_no_response = $guzzleClient->request('GET', $account_no_url, [
                //         'headers' => $headers,
                //         'verify'  => false,
                //     ]);       
                //   $account_no_data = json_decode($account_no_response->getBody()); 
                 
                //   $account_no_datas = array_merge($account_no_datas, $account_no_data->collection);
                              
                //   //PAGINATION
                //   $totalResultCount = $account_no_data->pagination->results;
                //   $maxPageSizeAllowed = $account_no_data->pagination->maxPageSizeAllowed;

                //   if($totalResultCount > $maxPageSizeAllowed)
                //   {
                //     $pagination = $totalResultCount/$maxPageSizeAllowed;

                //     for($page = 1; $page<=$pagination; $page++)
                //     {
                //       $next_account_no_url = "$accounting_year_baseurl/periods/$accounting_year_period_number/entries?pagesize=1000&filter=$entries_filter&skippages=$page";

                //       $next_account_no_response = $guzzleClient->request('GET', $next_account_no_url, [
                //           'headers' => $headers,
                //           'verify'  => false,
                //       ]);
                //       $next_account_no_data = json_decode($next_account_no_response->getBody());             

                //       //Merge - Pagination
                //       $account_no_datas = array_merge($account_no_datas, $next_account_no_data->collection);
                //     } /* --end for PAGINATION -- */
                //   } /* --end if RESULT COUNT > MAX PAGE SIZE -- */
                //   /* --end PAGINATION -- */
                // }/* --end for PERIODS -- */
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
         
          // // Save api_call_results first page
          // DB::table('api_call_results')->insert([
          //     'vat_reg_id' => $vat_reg_id,              
          //     'status' => 'completed',
          //     'account_no_datas' => json_encode($invoice_data),
          //     'created_at' => now(),
          //     'updated_at' => now(),
          // ]);

          return $invoice_data;

          //return $batch->id;

        }  //valid token
      }//try
      // catch (\Exception $e)
      // {
      //   if($e->getResponse())   
      //   {           
      //     //$response = $e->getResponse();          
      //     //$errorMessage = json_decode($response->getBody());  

      //     $statusCode = $e->getResponse()?->getStatusCode();

      //     if ($statusCode === 503) {
      //         Log::warning("503 Service Unavailable: Skipping this job.");
      //         return; // skip this job, do not throw
      //     }
      //     $errorMessage = json_decode($e->getResponse()?->getBody());
      //   }
      //   else
      //     $errorMessage = $e->getMessage();  

      //   return (object)['error' => $errorMessage];
      // } //catch

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
