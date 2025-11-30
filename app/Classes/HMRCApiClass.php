<?php

namespace App\Classes;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use App\Models\SystemApis;
use App\Models\SystemFiles;
use App\Models\Client;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

use GuzzleHttp\Client as GuzzleClient;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Storage;

use App\Traits\DecryptTrait;


use App\Jobs\VatNoExportJob;

class HMRCApiClass
{    
    use DecryptTrait;

    public function getAccessToken($system)
    {            
        if($system->api_token == null)           
            return $this->accessApi($system);                   
        else
        {   
            if(Carbon::parse($this->decryptValue($system->api_token_expire))->addHour(1) <= Carbon::now())            
                return $this->accessApi($system);           
            else            
                return "not expired";            
        }
    }

    public function accessApi($system)
    {     
        $api_base_url = $system->api_base_url;
        $tenant_id = $this->decryptValue($system->api_tenant_id);
        $client_id = $this->decryptValue($system->api_client_id);
        $client_secret = $this->decryptValue($system->api_secret_key);
        
        $params = [            
            'scope' => "read:vat+write:vat+write:sent-invitations",            
            'grant_type' => "client_credentials",                        
            'client_secret' => $client_secret,
            'client_id' => $client_id        
        ];

        $guzzleClient = new GuzzleClient();
        $url = "$api_base_url/oauth/token";

        $headers = [               
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $response = $guzzleClient->request('POST', $url, [            
            'form_params' => $params,
            'verify'  => false,
        ]);

        $access_token = json_decode($response->getBody());  
                     
        $systemapis = SystemApis::where('id', $system->id)->first();
        $systemapis->api_token = $access_token->access_token;
        $systemapis->api_token_expire = Carbon::now();
        $systemapis->access_token = json_encode($access_token);
        $systemapis->save();                   
       
        return $access_token->access_token;             
    } 

    /*HMRC - POST - Submitting VAT Returns */    
    public function postSubmittingFields($request, $client, $authUser, $system)   
    {                  
      $commonClass = new CommonClass();

      $box1 = $request->input('submittingfields_box_1');
      $box2 = $request->input('submittingfields_box_2');
      $box3 = $request->input('submittingfields_box_3');
      $box4 = $request->input('submittingfields_box_4');
      $box5 = $request->input('submittingfields_box_5');
      $box6 = $request->input('submittingfields_box_6');
      $box7 = $request->input('submittingfields_box_7');
      $box8 = $request->input('submittingfields_box_8');
      $box9 = $request->input('submittingfields_box_9');

      $period_key_year = Carbon::parse($client->service_start)->format('y');
      $month = Carbon::parse($client->service_start)->format('m');
      $period_key_month = '';
      if($client->general_periods == 'quarterly')
      {
        if($month >= 2 && $month <= 4)
          $period_key_month = 'A1';
        elseif($month >= 5 && $month <= 7)
          $period_key_month = 'A2';
        elseif($month >= 8 && $month <= 10)
          $period_key_month = 'A3';
        elseif($month >= 11 && $month <= 12)
          $period_key_month = 'A4';
        elseif($month == 1)
        {
          $period_key_month = 'A4';
          $period_key_year = $period_key_year - 1;
        }       
      }
      elseif($client->general_periods == 'monthly')
      {
        $alphabet = range('A', 'L');

        $period_key_month = 'A'.$alphabet[$month-1];      
      }
      
      if($period_key_month != '')
      {
        $api_base_url = $system->api_base_url;        
        $vat_reg_id = $client->vat_reg_id;        
          
        try
        {    
          $access_token = $this->getAccessToken($system); 
          $access_token = ($access_token == "not expired") ?  $this->decryptValue($system->api_token) : $access_token;

          $headers = [                         
              'Content-Type' => 'application/json',   
              'Accept'  => 'application/vnd.hmrc.1.0+json',
              'Authorization' => 'Bearer ' . $access_token      
          ];  

          $postData = [        
              "periodKey" => $period_key_year.$period_key_month,
              "vatDueSales" => $box1,
              "vatDueAcquisitions" => $box2,
              "totalVatDue" => $box3,
              "vatReclaimedCurrPeriod" => $box4,
              "netVatDue" => $box5,
              "totalValueSalesExVAT" => $box6,
              "totalValuePurchasesExVAT" => $box7,
              "totalValueGoodsSuppliedExVAT" => $box8,
              "totalAcquisitionsExVAT" => $box9,
              "finalised" => ($request->input('submittingfields_declaration')) ? (($request->input('submittingfields_declaration') == 'on') ? true : false) : false
          ];    

          $guzzleClient = new GuzzleClient();   
          
          $url = $api_base_url .'/organisations/vat/'.$client->vatno.'/returns'; 
             
          $response = $guzzleClient->request('POST', $url, [
              'body' => json_encode($postData),
              'headers' => $headers
          ]);

          $response_data = json_decode($response->getBody());        
          
          $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;

          $commonClass = new CommonClass();
          $insertSubmittingFields = $commonClass->createUpdateSubmittingFields($authUser, $client, $postData, $response_data);
          if($insertSubmittingFields)
          {
            $this->commonClass->addLog($authUser, 'vatreturn-submitting-fields-hmrc', 
              [
                'Client Name' => $client->client_name, 
                'VAT Reg' => $vatRegHeading
              ]
            ); 

            return response()->json([
              'status'          => $response->getStatusCode(),
              'message'          => "VAT Return Submitted",
              'vat_reg_id' => $vat_reg_id,
              'data' => $response_data
            ]);
          }
          else
          {
            $this->commonClass->addLog($authUser, 'vatreturn-submitting-fields-error', 
              [
                'Client Name' => $client->client_name, 
                'VAT Reg' => $vatRegHeading
              ]
            ); 

            return response()->json([
              'status'          => $response->getStatusCode(),
              'message'          => "VAT Return Submitted. But error in saving datas.",
              'vat_reg_id' => $vat_reg_id,
              'data' => $response_data
            ]);
          }
        }
        catch (\Exception $e) {             
          $response = $e->getResponse();
          $errorMessage = json_decode($response->getBody());  

          return response()->json([
            'status'          => $response->getStatusCode(),
            'message' => $errorMessage,            
          ]);
        }
      }
      
    }

    public function getAccessTokenLazy($system)
    {            
        if($system->api_token == null)           
            return $this->accessApiLazy($system);                   
        else
        {   
            if(Carbon::parse($system->api_token_expire)->addHour(1) <= Carbon::now())            
                return $this->accessApiLazy($system);           
            else            
                return "not expired";            
        }
    }

    public function accessApiLazy($system)
    {     
        $api_base_url = $system->api_base_url;
        $tenant_id = $system->api_tenant_id;
        $client_id = $system->api_client_id;
        $client_secret = $system->api_secret_key;
        
        $params = [            
            //'scope' => "read:vat+write:vat+write:sent-invitations",            
          'scope' => "read:vat",            
            'grant_type' => "client_credentials",                        
            'client_secret' => $client_secret,
            'client_id' => $client_id        
        ];

        $guzzleClient = new GuzzleClient();
        $url = "$api_base_url/oauth/token";

        $headers = [               
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $response = $guzzleClient->request('POST', $url, [            
            'form_params' => $params,
            'verify'  => false,
        ]);

        $access_token = json_decode($response->getBody());  
                     
        $systemapis = SystemApis::where('id', $system->id)->first();
        $systemapis->api_token = $access_token->access_token;
        $systemapis->api_token_expire = Carbon::now();
        $systemapis->access_token = json_encode($access_token);
        $systemapis->save();                   
       
        return $access_token->access_token;             
    } 

    /*HMRC - GET - Check VAT Number by batch*/    
    public function checkVatNumberByBatch($chunkSize = 100, $maxRow = 250)
    {
      $response_datas = [];

      $commonClass = new CommonClass();

      $system = $commonClass->getSystemInfoLazy('HMRC - GOV.UK', 'Sandbox');         
      $systemapi = $system->systemapi->first();           
      
      $startRow = $systemapi->vat_start_no;     
      $highestRow = $startRow + $maxRow;
      
      do {
        $endRow = min($startRow + $chunkSize - 1, $highestRow);        
        $batch = Bus::batch([])->dispatch(); 
        // Process chunk of rows
        for ($row = $startRow; $row <= $endRow; $row++) {                 
          $batch->add(new VatNoExportJob($row,$systemapi));
        }

        $startRow = $endRow + 1;

      } while ($startRow < $highestRow);

      /* update the next start number */          
      $systemapi->update([
        'vat_start_no' =>  $endRow + 1                      
      ]);   
      /* update the next start number */
    }

    /*HMRC - GET - Check VAT Number by batch*/    
    public function recheckVatNumbers($startRow = 1, $execute_batch = false)
    {
      try
      {  
        $commonClass = new CommonClass();

        $system = $commonClass->getSystemInfoLazy('HMRC - GOV.UK', 'Sandbox');         
        $systemapi = $system->systemapi->first();

        $filename_instorage = "RecheckVatNo.xlsx";
        $storage_path = storage_path('app/public/');
        $inputFileName = $storage_path.$filename_instorage;
      
        /**  Identify the type of $inputFileName  **/
        $inputFileType = IOFactory::identify($inputFileName);

        $reader = IOFactory::createReader($inputFileType);     
        $reader->setReadDataOnly(true);

        $worksheetData = $reader->listWorksheetInfo($inputFileName);  
        foreach ($worksheetData as $worksheet) 
        {
          $sheetName = $worksheet['worksheetName'];

          $reader->setLoadSheetsOnly($sheetName);   
          $reader->setReadDataOnly(FALSE);           
          $spreadsheet = $reader->load($inputFileName);

          $worksheet = $spreadsheet->getActiveSheet();
          
          $highestRow = $worksheet->getHighestRow(); 
          $highestColumn = $worksheet->getHighestColumn();
                                       
          $chunkSize = 1000; // Adjust as needed
          
          do {
            $endRow = min($startRow + $chunkSize - 1, $highestRow);            
            $batch = Bus::batch([])->dispatch();

            // Process chunk of rows
            for ($row = $startRow; $row <= $endRow; $row++)           
            {
              $rowData = $worksheet->rangeToArray('A' . $row . ':D' . $row);

              $address = trim($rowData[0][2]);
              $i = trim($rowData[0][0]);
              if($address == '')
              {       
                echo $i . "<br>";   
                if($execute_batch)
                  $batch->add(new VatNoExportJob($i,$systemapi));                
              }//if address null              
            }//chunk for          
            $startRow = $endRow + 1;                              
          } while ($startRow <= $highestRow);
        } //for Worksheet        
      }
      catch (\Exception $e) 
      {  dd($e);
        if($e->getResponse())   
        {           
          $response = $e->getResponse();
          $errorMessage = json_decode($response->getBody());  
        }
        else
          $errorMessage = $e->getMessage();         
      }
    }

    /*HMRC - GET - Check VAT Number */    
    public function checkVATNumber()   
    {
      try
      {   
        $response_datas = [];

        $commonClass = new CommonClass();

        $system = $commonClass->getSystemInfoLazy('HMRC - GOV.UK', 'Sandbox');         
        $systemapi = $system->systemapi->first();

        $headers = [                         
          'Content-Type' => 'application/json',   
          'Accept'  => 'application/vnd.hmrc.1.0+json',        
        ]; 
        
        $guzzleClient = new GuzzleClient();   

        //4745529 has value upto 12.09.2024
        //4746806 has value upto 13.09.2024 11.50AM
        $i = 4745807;  //recheck- 4745807, 808 and 809
        echo $i . "<br>";
        for ($j=0; $j<=99; $j++)  
        {                                       
          $value = strlen($j);

          if($value==1)        
            $k = "0".$j;        
          else        
            $k=$j;
                           
          $vatno = $i . $k;   

          try
          {
            $baseurl = str_replace('test-api', 'api', $systemapi->api_base_url);
            $url =  $baseurl. '/organisations/vat/check-vat-number/lookup/' . $vatno; 
            $response = $guzzleClient->request('GET', $url, [              
              'headers' => $headers,
              'verify'  => false
            ]);   
           
            $response_data = json_decode($response->getBody(),true);
            dd($response_data);
          }
          catch (\Exception $e) { 
            continue;
          }
        } 
        /* Loop the start number with inner loop from 00 to 99  */
      }
      catch (\Exception $e) 
      {  dd($e);
        if($e->getResponse())   
        {           
          $response = $e->getResponse();
          $errorMessage = json_decode($response->getBody());  
        }
        else
          $errorMessage = $e->getMessage();         
      }
    }

    //GET Client Details
    public function getClientDetails($client_name)
    {      
      $client = Client::where('dv_clients.client_name', $client_name)->first();

      return $client;                
    }

    public function exportToExcelVatNo($response_data)
    {      
       try 
        {           
          $commonClass = new CommonClass();
          $apiClass = new apiClass();

          $system = $commonClass->getSystemInfoLazy();
          $systemapi = $system->systemapi->first();             
          $api_base_url = $systemapi->api_base_url;
          $apiUserId = $systemapi->api_user_id;
          $oneDriveRootId = $systemapi->one_drive_root_id;

          $systemfiles = $system->systemfiles;
          $file_type = 'vatnocheck';           
          $system_file = $systemfiles->filter(function ($systemfile, $key) use($file_type) {
            return $file_type == $systemfile->file_type;
          })->first();  

          //Check file exists
          $existing_file = false;

          $filename_instorage = "VATNoCheck.xlsx";
          $storage_path = storage_path('app/public/');
          $filename = $storage_path.$filename_instorage;

          if($existing_file)
          {
            //Store in public folder                
            $url = $existing_file['download_url'];
            $contents = (strpos($url, "https://") !== false) ? file_get_contents($url) : $url;  
            file_put_contents($filename, $contents);

            //Open existing file
            $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
            $sheet = $spreadSheet->getActiveSheet();
          }
          else
          {            
            //Open existing file
            $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
            $sheet = $spreadSheet->getActiveSheet();

            //Create header
            $sheet->setCellValue('A1', 'VAT No.');
            $sheet->setCellValue('B1', 'Name');
            $sheet->setCellValue('C1', 'Address');               
            $sheet->setCellValue('D1', 'DateTime');   

            //Header style
            $range = 'A1:D1';       
            $style = [
              'font'  => [
                  'bold'  => true,
                  'color' => array('rgb' => 'FFFFFF'),                
              ],
              'alignment' => [
                  'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                  'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                  'wrapText' => true,
              ],
              'fill' => [
                  'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                  'startColor' => ['argb' => '4F81BD']
              ],
            ];
            $sheet->getStyle($range)->applyFromArray($style);       

            //Cell width
            foreach (range('A', 'D') as $letra) {            
              $sheet->getColumnDimension($letra)->setAutoSize(false);
              $sheet->getColumnDimension($letra)->setWidth(20);
            }
          }  

          //Get row height
          $row = $sheet->getHighestRow()+1;

          $var_deadend = [];
          // //Response Datas         
            if(is_array($response_data))
            {
              $company_vatno = $response_data['vatNumber'];                 
              $company_name = $response_data['name'];    
              $company_address = '';
              if($response_data['address'])
              {             
                if(array_key_exists('line1', $response_data['address']))
                  $company_address .= (($company_address == '') ? '' : ', ') . $response_data['address']['line1'];

                if(array_key_exists('line2', $response_data['address']))
                  $company_address .= (($company_address == '') ? '' : ', ') . $response_data['address']['line2'];

                if(array_key_exists('line3', $response_data['address']))
                  $company_address .= (($company_address == '') ? '' : ', ') . $response_data['address']['line3'];

                if(array_key_exists('line4', $response_data['address']))
                  $company_address .= (($company_address == '') ? '' : ', ') . $response_data['address']['line4'];
              }
            }
           
            
            $sheet->setCellValue('A'.$row, $company_vatno);
            $sheet->setCellValue('B'.$row, $company_name);
            $sheet->setCellValue('C'.$row, $company_address);   
            $sheet->setCellValue('D'.$row, Carbon::now()->format('d-m-Y H:i:s'));  
              
          
          $cellValue = $sheet->getCellByColumnAndRow(2, $row)->getValue();            
          if (str_contains($cellValue, "404:")) 
          {              
            $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray([
                'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'FF8D33',
                        ]           
                ],
            ]);
          }

          //Save file 
          $Excel_writer = new Xlsx($spreadSheet);         
          $Excel_writer->save($filename);              
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }              
    }    
}
