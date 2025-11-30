<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

use \App\Classes\HMRCApiClass;

use Storage;

use GuzzleHttp\Client as GuzzleClient;

class VatNoExportJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $start_no;
    protected $systemapi;
    protected $access_token;    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($start_no, $systemapi, $access_token = NULL)
    {       
        $this->start_no = $start_no;
        $this->systemapi = $systemapi;
        $this->access_token = $access_token;       
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
      $start_no = $this->start_no;

      $systemapi = $this->systemapi;
      $access_token = $this->access_token;            

      $hmcrapiClass =  new HMRCApiClass();

      $guzzleClient = new GuzzleClient(); 
      $headers = [                         
        'Content-Type' => 'application/json',   
        'Accept'  => 'application/vnd.hmrc.1.0+json',
        //'Accept'  => 'application/vnd.hmrc.2.0+json',
        //'Authorization' => 'Bearer ' . $access_token      
      ]; 

      $matchedValue =  false; 
      $deadend_count = 0;             
      for ($j=0; $j<=99; $j++)  
      {                                       
        $value = strlen($j);
        if($value==1)
          $k = "0".$j;       
        else        
          $k=$j;
                       
        $vatno = $start_no . $k;                   
        try
        {
          $baseurl = str_replace('test-api', 'api', $systemapi->api_base_url);

          $url =  $baseurl. '/organisations/vat/check-vat-number/lookup/' . $vatno; 
          $response = $guzzleClient->request('GET', $url, [              
            'headers' => $headers,
            'verify'  => false
          ]);   

          $response_data = json_decode($response->getBody(),true);                              
          // key exists                                          
          if (array_key_exists("target", $response_data)) 
          { 
            // key isn't in this array, go deeper                       
            if(isset($response_data['target']['name']))
            {
              $matchedValue = true;                          

              $response_datas[] = $response_data['target'];

              $excel_export = $hmcrapiClass->exportToExcelVatNo($response_data['target']);                    
              $client_name = $response_data['target']['name'];                            
              $result = $hmcrapiClass->getClientDetails($client_name);

              if($result != null)
              {
                //update vatno                                                
                $model = Client::where('dv_clients.client_name', $client_name)->first();

                $model->update([
                  'vatno' =>  $vatno                               
                ]);                          
              }                          
              break;
            }
          }                                                                                
        }
        catch (\Exception $e) {  
          $response = [];   
          $response_data =[];                                            
          $response = $e->getMessage();

          if($e->getMessage())
          {
            $json = response()->json($response);
            $jsoncontent = $json->content();

            $my_array = explode(":", $jsoncontent);                    
            $lasterrorMsg = end($my_array);                    
            $string = str_replace('\n', ' ', $lasterrorMsg); 

            $errorMessage = preg_replace('/[^A-Za-z0-9 ]/', '', $string);                                    
          }
          
          $errMsg = $errorMessage;                                 
          $response_data['vatNumber'] = $start_no;
          $response_data['name'] = $errMsg;
          $response_data['address'] = "";
            
          if($deadend_count <= 99)         
            $deadend_count++;
          
          if($deadend_count == 99)         
            $excel_export = $hmcrapiClass->exportToExcelVatNo($response_data); 
          
          continue;
        }
      } // end of for loop
    }
}
   
   