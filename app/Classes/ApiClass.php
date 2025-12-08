<?php

namespace App\Classes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Carbon;
use App\Models\Client;
use App\Models\ClientApi;
use App\Models\ClientFiles;
use App\Models\ClientQAFiles;
use App\Models\SystemApis;
use App\Models\Receipt;
use App\Models\VATRegistrationMain;
use App\Models\VATRegistration;
use App\Models\Pivs;
use App\Models\Documents;
use App\Models\VATControlFiles;
use App\Models\VATControlOFiles;
use App\Models\ImportReconciliationControlFiles;
use App\Models\ImportReconciliationControlOFiles;
use App\Models\VATReturns;
use App\Models\VATReturnFiles;
use App\Models\VATReturnOFiles;
use App\Models\VATReturnComments;
use App\Models\VATReturnCommentFiles;
use App\Models\ImportVatFiles;
use App\Models\CashAccountStatement;
use App\Models\DutyDefermentAccount;
use App\Models\CargoDeclarationFiles;
use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationSalesInvoicesData;
use App\Models\AnyExcelTemplates;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use \App\Classes\CommonClass;
use \App\Classes\CommercialInvoicesClass;
use \App\Classes\DynamicsApiClass;
use \App\Classes\EconomicApiClass;
use \App\Classes\UnicontaApiClass;
use \App\Classes\ShopifyApiClass;

use App\Traits\DecryptTrait;

class ApiClass
{    
    use DecryptTrait;

    public function getSqlWithBindings($query)
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }

    public function getEndDate($client)
    {
        $end_date = Carbon::now()->format('Y-m-d');        
        if($client['general_periods'] == 'monthly')        
            $end_date = Carbon::parse($client['service_start'])->endOfMonth()->format('Y-m-d');  
        else if($client['general_periods'] == 'bi-monthly')                  
            $end_date = Carbon::parse($client['service_start'])->addMonths(1)->endOfMonth()->format('Y-m-d');        
        else if($client['general_periods'] == 'quarterly')      
            $end_date = Carbon::parse($client['service_start'])->addMonths(2)->endOfMonth()->format('Y-m-d');   
        else if($client['general_periods'] == 'half-yearly')      
            $end_date = Carbon::parse($client['service_start'])->addMonths(5)->endOfMonth()->format('Y-m-d');   
        else if($client['general_periods'] == 'yearly')      
            $end_date = Carbon::parse($client['service_start'])->addMonths(11)->endOfMonth()->format('Y-m-d');

        return $end_date;     
    }    

    public function xmlExtract($file = NULL, $month_year = NULL)
    {  
      $commonClass = new CommonClass();
          
      //$xmlString = file_get_contents(public_path('911997134_20230401_20230430.xml'));
      $xmlString = file_get_contents($file);
      $xmlObject = simplexml_load_string($xmlString);
      $json = json_encode($xmlObject);
      $phpArray = json_decode($json, true);         
     
      $fee = 0;
      $fee_ex = 0;
      $statvalue = 0;
      $statvalue_ex = 0;
      $adjustment = 0;
      $invoice_total = 0;

      $box_85 = 0;

      $category_type = '';
      $category_desc = '';
     
      $expedition_list = [];

      if(isset($phpArray['Deklarasjoner']))
      {
        // Ensure that 'item' is an array and check if it's an associative array
        if (isset($phpArray['Deklarasjoner']['Deklarasjon'])) {
          if (is_array($phpArray['Deklarasjoner']['Deklarasjon'])) {
            // Check if it's an associative array
            if ($commonClass->is_associative_array($phpArray['Deklarasjoner']['Deklarasjon'])) {
              // It's a single associative array (single <item>)
              $phpArray['Deklarasjoner']['Deklarasjon'] = [$phpArray['Deklarasjoner']['Deklarasjon']]; // Wrap it in an array
            }
          }
        }

        
        foreach($phpArray['Deklarasjoner']['Deklarasjon'] as $main_key=> $Deklarasjon)
        {          
          // if(is_array($Deklarasjon))
          // {
            $allow_sumpup = true;
            $eksp_type = '';      
            
            //$same_month = true;
            $allow_box85_sumpup = true;     
            foreach($Deklarasjon as $key=>$value)
            {             
              if($key == 'Ekspedisjon')
              {
                foreach($value as $ex_key=>$Ekspedisjon)
                {
                  if($ex_key == 'EkspType')
                  {
                    foreach($Ekspedisjon as $exname_key=>$EkspTypeNavn)
                    {
                      // if($exname_key == 'EkspTypeNavn')
                      // {                        
                      //   if(stripos($EkspTypeNavn, " Utførsel") !== false) 
                      //   {
                      //     $allow_sumpup = false;
                      //     break;
                      //   }
                      // }

                      //Ordinær utførsel, Midlertidig innførsel med gjenutførselsfrist
                      if($exname_key == 'EkspTypeNr')
                      {
                        $eksp_type = $EkspTypeNavn;
                        if($EkspTypeNavn == 1 || $EkspTypeNavn == 3) 
                        {
                          $allow_sumpup = false;
                          break;
                        } //1
                        else if($EkspTypeNavn == 5) 
                        {
                          //Check Fakturadato belongs to the month
                          // $faktura_month = Carbon::parse($Deklarasjon['Fakturareferanser']['Fakturareferanse']['Fakturadato'])->format('m');

                          // // Ensure that 'item' is an array and check if it's an associative array
                          // if (isset($Deklarasjon['Fakturareferanser']['Fakturareferanse'])) {
                          //   if (is_array($Deklarasjon['Fakturareferanser']['Fakturareferanse'])) {
                          //     // Check if it's an associative array
                          //     if ($commonClass->is_associative_array($Deklarasjon['Fakturareferanser']['Fakturareferanse'])) {
                          //       // It's a single associative array (single <item>)
                          //       $Deklarasjon['Fakturareferanser']['Fakturareferanse'] = [$Deklarasjon['Fakturareferanser']['Fakturareferanse']]; // Wrap it in an array
                          //     }
                          //   }
                          // }

                          // foreach($Deklarasjon['Fakturareferanser']['Fakturareferanse'] as $d_fakturareferanse)
                          // {  
                          //   foreach($d_fakturareferanse as $d_faktur_key=>$d_fakturvalue)
                          //   {
                          //     if($d_faktur_key == 'Fakturadato')
                          //     {
                          //       $faktura_date = Carbon::parse($d_fakturvalue)->format('d');

                          //       $compare_month = Carbon::parse('01-' . $month_year)->format('m');

                          //       if($compare_month == $faktura_date)
                          //       {

                          //       }
                          //       else
                          //       {
                          //         $allow_sumpup = false;
                          //         break;
                          //       }
                          //     }
                          //   }
                          // }

                          if(isset($Deklarasjon['Ekspedisjon']['EkspDato']))
                          {
                            //$faktura_date = Carbon::parse($Deklarasjon['Ekspedisjon']['EkspDato'])->format('d');
                            $faktura_year_month = Carbon::parse($Deklarasjon['Ekspedisjon']['EkspDato'])->format('Ym');
                            
                            $compare_year_month = Carbon::parse('01-' . $month_year)->format('Ym');                            

                            // $faktura_month = Carbon::parse($Deklarasjon['Ekspedisjon']['EkspDato'])->format('m');
                            //$compare_month = Carbon::parse('01-' . $month_year)->format('m');
                            
                            // if($compare_month != $faktura_month)
                            //   $same_month = false;

                            if($compare_year_month == $faktura_year_month)
                            {
                              if(isset($Deklarasjon['GjenutFrist']))
                              {                                
                                // $expiry_month = Carbon::parse($Deklarasjon['GjenutFrist'])->format('m');

                                // if($expiry_month == $compare_month)
                                // {

                                // }
                                // else
                                // {
                                //   $allow_sumpup = false;
                                //   $allow_box85_sumpup = false;
                                //   break;
                                // }
                              }
                              else
                              {
                                $allow_sumpup = false;
                                $allow_box85_sumpup = false;
                                break;
                              }
                            }
                            else
                            {                                 
                              $allow_sumpup = false;
                              $allow_box85_sumpup = false;
                              break;
                            }
                          } //has EkspDato
                          // else
                          // {
                          //   dd($Deklarasjon['Fakturareferanser']['Fakturareferanse']);
                          // }
                        } //5
                      }
                    }
                  } // if EkspType
                  else if($ex_key == 'EkspedisjonsId')
                  {
                    $expo_no = '';
                    $run_no = '';
                    foreach($Ekspedisjon as $exid_key=>$EkspedisjonsId)
                    {
                      if($exid_key == 'EkspNr')
                        $expo_no = $EkspedisjonsId;
                      else if($exid_key == 'LopeNr')
                        $run_no = $EkspedisjonsId;
                    } // for EkspedisjonsId

                    if(array_key_exists($main_key, $expedition_list))
                    {
                      $expedition_list[$main_key]['expo_no'] = $expo_no;
                      $expedition_list[$main_key]['run_no'] = $run_no;
                      $expedition_list[$main_key]['com_invoices'] = [];                      
                    }
                    else
                      $expedition_list[$main_key] = ['expo_no' => $expo_no, 'run_no' => $run_no, 'com_invoices' => []];                  
                  } // else EkspedisjonsId
                  else if($ex_key == 'Kategori')
                  {
                    foreach($Ekspedisjon as $category_key=>$kategori)
                    {
                      if($category_key == 'KategoriType')
                        $category_type = $kategori;
                      else if($category_key == 'KategoriBeskrivelse')
                        $category_desc = $kategori;
                    } //for Kategori
                    
                    if(array_key_exists($main_key, $expedition_list))
                    {                      
                      $expedition_list[$main_key]['category_type'] = $category_type;
                      $expedition_list[$main_key]['category_desc'] = $category_desc;
                    }
                    else
                      $expedition_list[$main_key] = ['expo_no' => '', 'run_no' => '', 'com_invoices' => [], 'category_type' => $category_type, 'category_desc' => $category_desc]; 
                  } // else Kategori
                }
              } //if Ekspedisjon

              if($key == 'Fakturareferanser')              
              {
                // Ensure that 'item' is an array and check if it's an associative array
                if (isset($value['Fakturareferanse'])) {
                  if (is_array($value['Fakturareferanse'])) {
                    // Check if it's an associative array
                    if ($commonClass->is_associative_array($value['Fakturareferanse'])) {
                      // It's a single associative array (single <item>)
                      $value['Fakturareferanse'] = [$value['Fakturareferanse']]; // Wrap it in an array
                    }
                  }
                }
              
                $arr_index = 0;
                foreach($value['Fakturareferanse'] as $fakturareferanse)
                {                  
                  foreach($fakturareferanse as $faktur_key=>$fakturvalue)
                  {
                    if($faktur_key == 'Fakturadato')
                    {                      
                      if(array_key_exists($arr_index, $expedition_list[$main_key]['com_invoices']))
                        $expedition_list[$main_key]['com_invoices'][$arr_index]['com_invoice_date'] = $fakturvalue;
                      else
                        $expedition_list[$main_key]['com_invoices'][$arr_index] =  ['com_invoice_date' => $fakturvalue];
                    }
                    else if($faktur_key == 'Fakturanummer')
                    {                      
                      if(array_key_exists($arr_index, $expedition_list[$main_key]['com_invoices']))
                        $expedition_list[$main_key]['com_invoices'][$arr_index]['com_invoice_no'] = $fakturvalue;
                      else
                        $expedition_list[$main_key]['com_invoices'][$arr_index] =  ['com_invoice_no' => $fakturvalue];
                    }
                  }
                  $arr_index = $arr_index + 1;
                }                
              } //Fakturareferanser

              if($key == 'Avgift')
              {
                if($allow_sumpup)  
                {   
                  // if($same_month && $eksp_type == 5) 
                  //   $fee_ex += $value;
                  if($allow_box85_sumpup && $eksp_type == 5)
                    $fee_ex += $value;
                  else                
                    $fee += $value;
                }
                else  
                {  
                  //if($same_month)
                  if($allow_box85_sumpup && $eksp_type == 5)
                    $fee_ex += $value;                  
                }

                if(array_key_exists($main_key, $expedition_list))               
                  $expedition_list[$main_key]['duties'] = $value;                  
                else
                  $expedition_list[$main_key] = ['duties' => $value];
              }

              if($key == 'StatistiskVerdi')
              {
                if($allow_sumpup)   
                {   
                  //IM. STATISTICAL VALUE SHOULD HAVE EkspTypeNr. 4
                  if($eksp_type != 5)           
                    $statvalue += $value;                  
                }
                else
                {
                  //EX. STATISTICAL VALUE SHOULD HAVE EkspTypeNr. 1
                  if($eksp_type == 1 || $eksp_type == 3) //3 included for BOLDLIGHT 
                    $statvalue_ex += $value;       
                }

                //BOX 85 SHOULD HAVE EkspTypeNr. 5 with SAME MONTH and Duties and tax
                if($allow_box85_sumpup && $eksp_type == 5)
                  $box_85 += $value;

                if(array_key_exists($main_key, $expedition_list))               
                  $expedition_list[$main_key]['statistical_value'] = $value;                  
                else
                  $expedition_list[$main_key] = ['statistical_value' => $value];             
              }

              if($key == 'Justering') 
              {             
                $adjustment += $value;              
              
                if(array_key_exists($main_key, $expedition_list))               
                  $expedition_list[$main_key]['adjustment'] = $value;                  
                else
                  $expedition_list[$main_key] = ['adjustment' => $value];
              }

              if($key == 'FakturaValutaBelop')
              {                            
                if($category_type == 'RE')
                {                  
                  if(stripos($expedition_list[$main_key]['statistical_value'], "-") !== false)
                    $invoice_total -= $value['Fakturasum']; 
                  else      
                  {
                    if($expedition_list[$main_key]['statistical_value'] == 0)
                    {

                    }              
                    else
                      $invoice_total += $value['Fakturasum'];
                  }
                } //RE
                else if($category_type == 'EB')
                {                  
                  $invoice_total += $expedition_list[$main_key]['statistical_value'];
                } //EB
                else
                {
                  if($value['OmrKurs'] == 100)
                    $invoice_total += $value['Fakturasum']; 
                  else
                  {                   
                    $currency_value = $value['Fakturavaluta'];  

                    $OmrKurs_value = 1;
                    if($currency_value == 'DKK')   
                    {  
                      $OmrKurs_value = str_replace([",", "."], "", $value['OmrKurs']);
                      $OmrKurs_value = substr_replace($OmrKurs_value, ".", 1, 0);  // Insert dot after the first character
                    }
                    else if($currency_value == 'USD' || $currency_value == 'EUR')   
                    {
                      $OmrKurs_value = str_replace([",", "."], "", $value['OmrKurs']);
                      $OmrKurs_value = substr_replace($OmrKurs_value, ".", 2, 0);  // Insert dot after the second character
                    }     
                    
                    $omr_invoice_amount = $value['Fakturasum'] * $OmrKurs_value;

                    $invoice_total += $omr_invoice_amount;
                  } 
                } //FU                 

                if(array_key_exists($main_key, $expedition_list)) 
                {                   
                  $expedition_list[$main_key]['com_invoice_currency_code'] = $value['Fakturavaluta'];                      
                  $expedition_list[$main_key]['com_invoice_net_amount'] = $value['Fakturasum'];  
                  $expedition_list[$main_key]['com_invoice_omr_kurs'] = $value['OmrKurs'];                
                }
                else
                {
                  $expedition_list[$main_key] = ['com_invoice_currency_code' => $value['Fakturavaluta']];
                  $expedition_list[$main_key] = ['com_invoice_net_amount' => $value['Fakturasum']];
                  $expedition_list[$main_key] = ['com_invoice_omr_kurs' => $value['OmrKurs']];                 
                }                
              } // FakturaValutaBelop              
            } //for         
        }
      }
      $xmlvalue = [
        'fee' => $fee, 
        'fee_ex' => $fee_ex, 
        'statvalue' => $statvalue,          
        'statvalue_ex' => $statvalue_ex,
        'adjustment' => $adjustment,
        'invoice_total' => $invoice_total,        
        'expedition_list' => $expedition_list,

        'box_85' => $box_85
      ];
 
      return $xmlvalue;
    }

    public function xmlExtractByLine($importvatfile, $file = NULL)
    {      
      //$xmlString = file_get_contents(public_path('911997134_20230401_20230430.xml'));
      $xmlString = file_get_contents($file);
      $xmlObject = simplexml_load_string($xmlString);
      $json = json_encode($xmlObject);
      $phpArray = json_decode($json, true); 

      $commonClass = new commonClass();    

      if(isset($phpArray['Deklarasjoner']))
      {
        foreach($phpArray['Deklarasjoner'] as $Deklarasjon)
        { 
          foreach($Deklarasjon as $key=>$value)
          {
            if(is_numeric($key))
            {
              $importvatcomments = $commonClass->getVatReturnImportVatComments($importvatfile->id, ($key+1));                       
              $phpArray['Deklarasjoner']['Deklarasjon'][$key]['comment'] = $importvatcomments;
            }
            else
            {             
              $importvatcomments = $commonClass->getVatReturnImportVatComments($importvatfile->id, 1);                       
              $phpArray['Deklarasjoner']['Deklarasjon']['comment'] = $importvatcomments;   

              $returnvalue[] = $phpArray['Deklarasjoner']['Deklarasjon'];
              
              return $returnvalue;              
            }
          }
        }
      }

      return (isset($phpArray['Deklarasjoner'])) ? $phpArray['Deklarasjoner']['Deklarasjon'] : [];
    }

    public function rssExtractExchangeRate($url, $from_currency = NULL, $to_currency = NULL)
    {             
      $xmlString = file_get_contents($url);
      $xmlObject = simplexml_load_string($xmlString);
      $json = json_encode($xmlObject);
      $phpArray = json_decode($json, true); 

      $commonClass = new commonClass();    

      $exchange_rates = [];
      $to_currency_rate = 0;
      if($from_currency)
      {   
        foreach($phpArray['dailyrates']['currency'] as $key=>$item)
        { 
          if(strpos($to_currency, $item['@attributes']['code']) !== false)
          { 
            $per_rate = $item['@attributes']['rate']/100;
            $to_currency_rate = $per_rate;
          }
        }

        foreach($phpArray['dailyrates']['currency'] as $key=>$item)
        {             
          if(strpos($from_currency, $item['@attributes']['code']) !== false)
          { 
            $per_rate = $item['@attributes']['rate']/100;
            if($to_currency_rate == 0)
              $exchange_rates[$item['@attributes']['code']] = 0;
            else
              $exchange_rates[$item['@attributes']['code']] = number_format($per_rate/$to_currency_rate,2);
          }
        }
      }
      else
      {
        foreach($phpArray['channel']['item'] as $key=>$item)
        { 
          dd($item);
        }
      }

      return $exchange_rates;
    }
    
    /*Microsoft Graph API - One Drive - Delete - NEED TO WORKOUT*/    
    public function deleteFromOneDrive($client, $authUser, $system)   
    { 
        $api_base_url = $system->api_base_url;
        $apiUserId = $this->decryptValue($system->api_user_id);

        if(isset($client))
        {          
          $item_id = $client->file_id;
          $id = $client->id;
          $vat_reg_id = $client->vat_reg_id;
          try
          {    
            $access_token = $this->getMicrosoftGraphAccessToken($system); 
            $access_token = ($access_token == "not expired") ?  $this->decryptValue($system->api_token) : $access_token;

            $headers = [                         
                'Content-Type' => 'application/json',           
                'Authorization' => 'Bearer ' . $access_token      
            ];        

            $guzzleClient = new GuzzleClient();   
            
            $url = $api_base_url .'/v1.0/users/'.$apiUserId.'/drive/items/'.$item_id; 
            
            $response = $guzzleClient->request('DELETE', $url, [              
                'headers' => $headers
            ]);

            $data = json_decode($response->getBody());        
                       
            return response()->json([
              'status'          => "deleted",
              'vat_reg_id' => $vat_reg_id,
              'id' => $id,
            ]);
          }
          catch (\Exception $e) {            
            if($e->getResponse())   
            {           
              $response = $e->getResponse();
              $errorMessage = json_decode($response->getBody());  
            }
            else
              $errorMessage = $e->getMessage();  

            return response()->json([
              'status'          => "error",
              'error' => $errorMessage,
              'vat_reg_id' => $vat_reg_id,
              'id' => $id,
            ]);
          }
        }
    }

    public function createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $folderId, $folderName, $type = NULL)   
    {
      $returnfolderId = '';
      try
      {        
        $postData = [        
            "name" => $folderName,
            "folder" => [ "@odata.type" => "microsoft.graph.folder" ],
            "@microsoft.graph.conflictBehavior" => "fail"      
        ];

        $headers = [                         
            'Content-Type' => 'application/json',           
            'Authorization' => 'Bearer ' . $access_token      
        ];        

        $guzzleClient = new GuzzleClient();   
        
        $url = $api_base_url .'/v1.0/users/'.$apiUserId.'/drive/items/'.$folderId.'/children'; 
        
        $response = $guzzleClient->request('POST', $url, [
            'body' => json_encode($postData),
            'headers' => $headers
        ]);

        $folder = json_decode($response->getBody());        
        $returnfolderId = $folder->id; 

        return $returnfolderId;
      }
      catch (\Exception $e) {   
        
        $response = $e->getResponse();
        $errorMessage = json_decode($response->getBody());  

        if($errorMessage->error->code == "nameAlreadyExists")
        {
          if($returnfolderId == '')
          {
            $filter = "?\$filter=name eq '".$folderName."'";  

            $response = $guzzleClient->request('GET', $url.$filter, [                
                'headers' => $headers
            ]);
            
            $folders = json_decode($response->getBody());
            foreach($folders->value as $key=>$folder)
            {   
              $returnfolderId = $folder->id;          
            }
           
            return $returnfolderId; 
          }
        } 
      }

      return $returnfolderId;
    }
       
    /* - NEED TO WORKOUT*/   
    public function uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $folderId, $files, $ftp = false, $extension = null)   
    {
      $returnfileId = 'Error in upload';
      try
      {  
        $result = [];
        foreach($files as $key => $file)
        {
          $dateFileName = Carbon::now()->format('d-m-Y-H-i-s'); 
          if($extension)
            $fileName = $dateFileName . '-' . uniqid() . '.' . $extension;
          else  
            $fileName = $dateFileName . '-' . uniqid() . '.' . (($ftp) ? "xlsx" : $file->getClientOriginalExtension());
        
          $headers = [                         
              'Content-Type' => 'application/json',           
              'Authorization' => 'Bearer ' . $access_token      
          ];        

          if($extension)
            $body = $file;
          else
            $body = ($ftp) ? $file : file_get_contents($file);            

          $guzzleClient = new GuzzleClient();   
          
          $url = $api_base_url .'/v1.0/users/'.$apiUserId.'/drive/items/'.$folderId.':/'.$fileName.':/content'; 
          
          $response = $guzzleClient->request('PUT', $url, [            
              'body' => $body,
              'headers' => $headers
          ]);

          $file_data = json_decode($response->getBody());        
 
          $returnfileId = $file_data->id; 
          $returnfileSize = $file_data->size; 

          $result[$key] = ['fileId' => $returnfileId, 'fileName' => $fileName, 'fileSize' => $returnfileSize];   
        }  
        return $result;       
      }
      catch (\Exception $e) {         
        $response = $e->getResponse();
        $errorMessage = json_decode($response->getBody());  
       
        return $errorMessage->error;
      }

      return $returnfileId;
    }  

    /*Microsoft Graph API - One Drive - Upload */    
    public function uploadCommentWithFilesToOneDrive($request, $client, $authUser, $system)   
    {          
      $commonClass = new CommonClass();

      $fileValue = $request->file('attach-comment-file');

      $vat_reg_id = $client->vat_reg_id;
     
      $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;        

      $api_base_url = $system->api_base_url;
      $apiUserId = $this->decryptValue($system->api_user_id);
      $oneDriveRootId = $this->decryptValue($system->one_drive_root_id);

      $access_token = $this->getMicrosoftGraphAccessToken($system); 

      $access_token = ($access_token == "not expired") ?  $this->decryptValue($system->api_token) : $access_token;
           
      $clientFolderId = '';
      if($oneDriveRootId != "")
      {
        $clientFolderName = $commonClass->replaceSpecialCharForFolderName($client->client_name);
        $clientFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $oneDriveRootId, $clientFolderName);
      }

      $countryFolderId = '';
      if($clientFolderId != "")
      {
        $countryFolderName = $client->country;        
        $countryFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $clientFolderId, $countryFolderName);
      }

      $dateFolderId = '';
      if($countryFolderId != "")
      {
        $dateFolderName = Carbon::parse($client->service_start)->format('M-Y');       
        $dateFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $countryFolderId, $dateFolderName);
      }

      $commentfilesFolderId = '';
      if($dateFolderId != "")
      {
        $commentfilesFolderName = 'Comment';
        $commentfilesFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $dateFolderId, $commentfilesFolderName);
      }

      if($commentfilesFolderId != "")
      {
        $comments = VATReturnComments::create(             
              [
                'vat_reg_id' => $vat_reg_id,
                'subject' => "Re-open folder",
                'comment' => $request->comment_quill,  
                'created_by' => $authUser->user_id,                
              ]
            );

        if($fileValue === null)   
        {
          $commonClass->addLog($authUser, 'comment-add', 
            [
              'Client Name' => $client->client_name,
              'VAT Reg' => $vatRegHeading
            ]
          );
         
          return $comments;   
        }
        else
        {         
          $files = $fileValue;        
          $fileDetails = $this->uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $commentfilesFolderId, $files);

          $commentfiles = [];
          foreach($fileDetails as $key => $fileDetail)
          {                      
            $commentfiles[$key] = VATReturnCommentFiles::create(              
              [
                'vat_reg_id' => $vat_reg_id,
                'comment_id' => $comments->id,
                'folder_id' => $dateFolderId,
                'file_id' => $fileDetail['fileId'],
                'file_name' => $fileDetail['fileName'],
                'file_size' => $fileDetail['fileSize']                
              ]
            );            
          }
                           
          $commonClass->addLog($authUser, 'comment-files-upload', 
            [
              'Client Name' => $client->client_name,
              'VAT Reg' => $vatRegHeading
            ]
          );

          return $commentfiles;   
        } 
      }
    }

    public function getMicrosoftGraphAccessToken($system)
    {           
        if($system->api_token == null)           
            return $this->accessMicrosoftGraphApi($system);                   
        else
        {   
            if(Carbon::parse($this->decryptValue($system->api_token_expire))->addHour(1) <= Carbon::now())            
                return $this->accessMicrosoftGraphApi($system);           
            else            
                return "not expired";            
        }
    }

    public function accessMicrosoftGraphApi($system)
    {     
        $api_base_url = $system->api_base_url;
        $tenant_id = $this->decryptValue($system->api_tenant_id);
        $client_id = $this->decryptValue($system->api_client_id);
        $client_secret = $this->decryptValue($system->api_secret_key);
        
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

        $systemapis = SystemApis::where('id', $system->id)->first();
        $systemapis->api_token = $access_token->access_token;
        $systemapis->api_token_expire = Carbon::now();
        $systemapis->access_token = json_encode($access_token);
        $systemapis->save();                   
       
        return $access_token->access_token;
    } 
    
    /*Microsoft Graph API - One Drive - Upload */    
    public function uploadClientFilesToOneDrive($request, $client, $authUser, $system)   
    {          
      $commonClass = new CommonClass();  

      $fileValue = $request->file('file');

      $client_id = $client->client_id;
           
      $api_base_url = $system->api_base_url;
      $apiUserId = $this->decryptValue($system->api_user_id);
      $oneDriveRootId = $this->decryptValue($system->one_drive_root_id);

      $access_token = $this->getMicrosoftGraphAccessToken($system); 

      $access_token = ($access_token == "not expired") ?  $this->decryptValue($system->api_token) : $access_token;
              
      $clientFolderId = '';
      if($oneDriveRootId != "")
      {       
        $clientFolderName = $commonClass->replaceSpecialCharForFolderName($client->client_name);
        $clientFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $oneDriveRootId, $clientFolderName);
      }
      
      if($clientFolderId != "")
      {
        $files = $fileValue;             
        $fileDetails = $this->uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $clientFolderId, $files);

        $clientfiles = [];
        foreach($fileDetails as $key => $fileDetail)
        {
          $clientfiles[$key] = ClientFiles::create([
              'client_id' => $client_id,
              'folder_id' => $clientFolderId,
              'file_id' => $fileDetail['fileId'],
              'file_name' => $fileDetail['fileName'],
              'file_size' => $fileDetail['fileSize'],
          ]);
        }
              
        $commonClass->addLog($authUser, 'client-file-upload', 
          [
            'Client Name' => $client->client_name
          ]
        );

        return $clientfiles;    
      }
    }

    /*Lazy*/
    public function getEndDateLazy($vatreg)
    {
        $end_date = Carbon::now()->format('Y-m-d');

        $service_start = $vatreg->service_start;  
        $general_periods = $vatreg->general_periods; 

        if($general_periods == 'monthly')        
            $end_date = Carbon::parse($service_start)->endOfMonth()->format('Y-m-d');  
        else if($general_periods == 'bi-monthly')                  
            $end_date = Carbon::parse($service_start)->addMonths(1)->endOfMonth()->format('Y-m-d');        
        else if($general_periods == 'quarterly')      
            $end_date = Carbon::parse($service_start)->addMonths(2)->endOfMonth()->format('Y-m-d');   
        else if($general_periods == 'half-yearly')      
            $end_date = Carbon::parse($service_start)->addMonths(5)->endOfMonth()->format('Y-m-d');   
        else if($general_periods == 'yearly')      
            $end_date = Carbon::parse($service_start)->addMonths(11)->endOfMonth()->format('Y-m-d');

        return $end_date;     
    }

    /*Microsoft Graph API - One Drive - Load*/           
    public function loadFromOneDriveLazy($file, $system, $original_file = false)   
    { 
        $api_base_url = $system->api_base_url;
        $apiUserId = $system->api_user_id;         

          $fileId = $file->file_id;
             
          try
          {  
            $access_token = $this->getMicrosoftGraphAccessTokenLazy($system);
            $access_token = ($access_token == "not expired") ?  $system->api_token : $access_token;

            $headers = [                         
                'Content-Type' => 'application/json',           
                'Authorization' => 'Bearer ' . $access_token      
            ];        

            $guzzleClient = new GuzzleClient();   
            
            $filter = '?select=name,file,@microsoft.graph.downloadUrl';
            $url = $api_base_url .'/v1.0/users/'.$apiUserId.'/drive/items/'.$fileId.$filter; 
           
            $response = $guzzleClient->request('GET', $url, [              
                'headers' => $headers,
                'verify'  => false
            ]);

            $data = json_decode($response->getBody());        
            
            $return_value = [];           
            foreach ($data as $key => $value) 
            {
              if($key == '@microsoft.graph.downloadUrl')
              {               
                $return_value['download_url'] = $value;  
                $return_value['file'] = file_get_contents($value);                  
                $return_value['original_file'] = $original_file;
              }                            
              else if($key == 'name')  
              {
                $return_value['name'] = $value;

                $file_extension = explode('.',$value);
                $file_extension_length = count($file_extension);
                $return_value['file_extension'] = ($file_extension_length > 0) ? $file_extension[$file_extension_length-1] : ''; 
              }
              else if($key == 'file')  
              {                
                $return_value['mime_type'] = $value->mimeType; 

                if($return_value['file_extension'] == '')
                {
                  if($value->mimeType == "application/vnd.ms-excel")   
                    $return_value['file_extension'] = ".csv";     
                  else if($value->mimeType == "text/xml")   
                    $return_value['file_extension'] = ".xml";   
                  else if($value->mimeType == "application/pdf")   
                    $return_value['file_extension'] = ".pdf";      
                }                           
              }
            }  

            return $return_value;
          }
          catch (\Exception $e) {     
          dd($e);
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

    public function getMicrosoftGraphAccessTokenLazy($system)
    {       
        if($system->api_token == null)           
            return $this->accessMicrosoftGraphApiLazy($system);                   
        else
        {   
            if(Carbon::parse($system->api_token_expire)->addHour(1) <= Carbon::now())            
                return $this->accessMicrosoftGraphApiLazy($system);           
            else            
                return "not expired";            
        }
    }

    public function accessMicrosoftGraphApiLazy($system)
    {          
        $api_base_url = $system->api_base_url;
        $tenant_id = $system->api_tenant_id;
        $client_id = $system->api_client_id;
        $client_secret = $system->api_secret_key;
        
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

        $systemapis = SystemApis::where('id', $system->id)->first();
        $systemapis->api_token = $access_token->access_token;
        $systemapis->api_token_expire = Carbon::now();
        $systemapis->access_token = json_encode($access_token);
        $systemapis->save();                   
       
        return $access_token->access_token;
    } 

    public function uploadSystemFileInOneDrive($api_base_url, $access_token, $apiUserId, $folderId, $files, $filename_instorage)   
    {
      $returnfileId = 'Error in upload';
      try
      {  
        $result = [];
        foreach($files as $key => $file)
        {          
          $fileName = $filename_instorage;
         
          $headers = [                         
              'Content-Type' => 'application/json',           
              'Authorization' => 'Bearer ' . $access_token      
          ];        

          $body = $file;

          $guzzleClient = new GuzzleClient();   
          
          $url = $api_base_url .'/v1.0/users/'.$apiUserId.'/drive/items/'.$folderId.':/'.$fileName.':/content'; 
          
          $response = $guzzleClient->request('PUT', $url, [            
              'body' => $body,
              'headers' => $headers
          ]);

          $file_data = json_decode($response->getBody());        
  
          $returnfileId = $file_data->id; 
          $returnfileSize = $file_data->size; 

          $result[$key] = ['fileId' => $returnfileId, 'fileName' => $fileName, 'fileSize' => $returnfileSize];   
        }  
        return $result;       
      }
      catch (\Exception $e) {         
        $response = $e->getResponse();
        $errorMessage = json_decode($response->getBody());  
       
        return $errorMessage->error;
      }

      return $returnfileId;
    }

    /*Microsoft Graph API - One Drive - Upload */    
    public function uploadCompanyFilesToOneDriveLazy($request, $client, $authUser, $system, $file_type = null)   
    {          
      $commonClass = new CommonClass();  

      if($file_type) 
      {        
        if($file_type == 'name')
          $fileValue = $request['director_file_name'];
        else if($file_type == 'address')
          $fileValue = $request['director_file_address'];     
      }
      else
        $fileValue = $request->file('file');

      $client_id = $client->id;
          
      $api_base_url = $system->api_base_url;
      $apiUserId = $system->api_user_id;
      $oneDriveRootId = $system->one_drive_root_id;

      $access_token = $this->getMicrosoftGraphAccessTokenLazy($system); 

      $access_token = ($access_token == "not expired") ?  $system->api_token : $access_token;
              
      $clientFolderId = '';
      if($oneDriveRootId != "")
      {       
        $clientFolderName = $commonClass->replaceSpecialCharForFolderName($client->client_name);
        $clientFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $oneDriveRootId, $clientFolderName);
      }

      $qaFolderId = '';
      if($file_type)
      {        
        if($oneDriveRootId != "")
        {         
          $qaFolderName = $commonClass->replaceSpecialCharForFolderName('QA');
          $qaFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $clientFolderId, $qaFolderName);
        }
      }
      
      if($clientFolderId != "")
      {
        if($file_type)
          $clientFolderId = $qaFolderId;

        $files = $fileValue;             
        $fileDetails = $this->uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $clientFolderId, $files);

        if($file_type)
        {
          //insert into qa files
          foreach($fileDetails as $key => $fileDetail)
          {
            $clientfiles[$key] = ClientQAFiles::create([
              'qa_id' => $request['qa_id'],
              'file_type' => $file_type,
              'folder_id' => $clientFolderId,
              'file_id' => $fileDetail['fileId'],
              'file_name' => $fileDetail['fileName'],
              'file_size' => $fileDetail['fileSize'],
              'o_file_name' => $fileValue[$key]->getClientOriginalName(),
              
              'created_by' => $authUser->user_id
            ]);
          }
        }
        else
        {
          $clientfiles = [];
          $uploads = array_values($request->uploads);
          foreach($fileDetails as $key => $fileDetail)
          {
            $clientfiles[$key] = ClientFiles::create([
              'client_id' => $client_id,
              'folder_id' => $clientFolderId,
              'file_id' => $fileDetail['fileId'],
              'file_name' => $fileDetail['fileName'],
              'file_size' => $fileDetail['fileSize'],
              'o_file_name' => $fileValue[$key]->getClientOriginalName(),

              'file_for' => $uploads[$key]['file_for'],
              'subject' => $uploads[$key]['subject'],
              'is_locked' => isset($uploads[$key]['chk_lock']) ? 1 : 0,
              'created_by' => $authUser->user_id
            ]);
          }
              
          $commonClass->addLog($authUser, 'client-file-upload', 
            [
              'Client Name' => $client->client_name
            ]
          );

          return $clientfiles; 
        }   
      }
    }

    /*Microsoft Graph API - One Drive - Upload */    
    public function uploadAnyExcelTemplateFilesToOneDrive($request, $authUser, $system)   
    {        
      $commonClass = new CommonClass();  

      $fileValue = $request->file('file');
      
      $api_base_url = $system->api_base_url;
      $apiUserId = $system->api_user_id;
      $oneDriveRootId = $system->one_drive_root_id;

      $access_token = $this->getMicrosoftGraphAccessTokenLazy($system); 

      $access_token = ($access_token == "not expired") ?  $system->api_token : $access_token;
              
      $anyexcelFolderId = '';
      if($oneDriveRootId != "")
      {       
        $anyexcelFolderName = "Any Excel Templates";
        $anyexcelFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $oneDriveRootId, $anyexcelFolderName);
      }
      
      if($anyexcelFolderId != "")
      {
        $files[0] = $fileValue;             
        $fileDetails = $this->uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $anyexcelFolderId, $files, false, 'xlsx');

        $anyexcelfiles = [];
        foreach($fileDetails as $key => $fileDetail)
        {
          $anyexcelfiles[$key] = AnyExcelTemplates::create([
            'client_id' => $request->client_id,
            'name' => $request->template_name,
            'folder_id' => $anyexcelFolderId,
            'file_id' => $fileDetail['fileId'],
            'file_name' => $fileDetail['fileName'],
            'file_size' => $fileDetail['fileSize'],
            'o_file_name' => $fileValue->getClientOriginalName(),
            'status' => 0,
            'created_by' => $authUser->id
          ]);
        }
              
        $commonClass->addLog($authUser, 'anyexceltemplate-file-upload', 
          [
            'Template Name' => $request->template_name
          ]
        );

        return $anyexcelfiles;    
      }
    }

    /*Microsoft Graph API - One Drive - Upload */    
    public function uploadFileToOneDriveLazy($request, $vatreg, $authUser, $system, $uploadtype = null)   
    {          
      $commonClass = new CommonClass();
      $commercialInvoicesClass = new CommercialInvoicesClass();

      $client = $vatreg->client;
      $vat_reg_id = $vatreg->id;
      $vat_reg_main_id = isset($vatreg->vat_reg_main_id) ? $vatreg->vat_reg_main_id : NULL;

      if($uploadtype == null)
      {        
        if(is_array($request))
        {
          if(isset($request['file']))
          {
            $fileValue = $request['file'];
            $file_type = $request['file_type'];
            $file_type_title = $request['file_type_title'];

            //$month_year = '';
            $month_year = isset($request['month_year']) ? $request['month_year'] : '';

            $file_extension = (isset($request['file_type_title'])) ? $request['file_type_title'] : 'xlsx';
          }
          else
          {
            $fileValue = $request[0];
            $file_type = 'vatreturn';
            $file_type_title = 'VATReturn';

            $month_year = '';

            $file_extension = 'xlsx';
          }
        }
        else
        {
          $fileValue = ($request->file('file')) ? $request->file('file') : $request->file('vatreturn_file');
       
          $file_type = ($request->file_type) ? $request->file_type : 'vatreturn';
          $file_type_title = ($request->file_type_title) ? $request->file_type_title : 'VATReturn';

          $month_year = ($request->month_year) ? $request->month_year : '';

          $fileValue = ($file_type == 'swiss_import_reconciliation' || $file_type == 'documents') ? $fileValue[0] : $fileValue;
          $file_extension = ($file_type == 'receipt') ? NULL : $fileValue->getClientOriginalExtension();
        }
      }
      else if($uploadtype == 'bulk' || $uploadtype == 'mailbox' || $uploadtype == 'cargo_mailbox' || $uploadtype == 'import_reconciliation')
      {
        if($uploadtype == 'import_reconciliation')
        {
          $fileValue = $request['filecontent'];
          
          $file_type = $uploadtype;
          $file_type_title = 'Import Reconciliation';

          $month_year = $request['month_year'];

          $file_extension = 'xml';
        }
        else
        {
          $fileValue = $request['file'];
          
          $file_type = $request['file_type'];
          $file_type_title = $request['file_type_title'];

          $month_year = ($uploadtype == 'bulk') ? $request['month_year'] : '';

          if($uploadtype == 'bulk')
            $file_extension = $fileValue->getClientOriginalExtension();
          else
          {
            $arr_file_extension = explode('.',$request['file_name']);
            $file_extension_length = count($arr_file_extension);

            $file_extension = ($file_extension_length > 0) ? $arr_file_extension[$file_extension_length-1] : '';          
          }
        }
      }      
     
      $vatRegHeading = Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods;        
      $api_base_url = $system->api_base_url;
      $apiUserId = $system->api_user_id;
      $oneDriveRootId = $system->one_drive_root_id;

      $access_token = $this->getMicrosoftGraphAccessTokenLazy($system); 

      $access_token = ($access_token == "not expired") ?  $system->api_token : $access_token;
      
      $clientFolderId = '';
      if($oneDriveRootId != "")
      {
        $clientFolderName = $commonClass->replaceSpecialCharForFolderName($client->client_name);
        $clientFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $oneDriveRootId, $clientFolderName);
      }

      $countryFolderId = '';
      if($clientFolderId != "")
      {
        $countryFolderName = $vatreg->country;        
        $countryFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $clientFolderId, $countryFolderName);
      }

      if($uploadtype == 'mailbox' || $uploadtype == 'cargo_mailbox')
      {
        $fileFolderId = '';
        if($countryFolderId != "")
        {
          $fileFolderName = ($file_type == 'documents') ? $file_type_title : strtoupper($file_type);
          $fileFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $countryFolderId, $fileFolderName);
        }
      }
      else
      {
        $dateFolderId = '';
        if($countryFolderId != "")
        {
          $dateFolderName = Carbon::parse($vatreg->service_start)->format('M-Y');       
          $dateFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $countryFolderId, $dateFolderName);
        }

        $fileFolderId = '';
        if($dateFolderId != "")
        {
          $fileFolderName = ($file_type == 'documents' || $file_type == 'import_reconciliation' || $file_type == 'swiss_import_reconciliation') ? $file_type_title : strtoupper($file_type);
          $fileFolderId = $this->createFolderInOneDrive($api_base_url, $access_token, $apiUserId, $dateFolderId, $fileFolderName);
        }
      }

      if($fileFolderId != "")
      {
        if($fileValue === null)
          return false;
        else
        {
          if($file_type == 'receipt')
            $files = $fileValue;
          else
            $files[0] = $fileValue;
          if($uploadtype == 'mailbox' || $uploadtype == 'cargo_mailbox' || $uploadtype == 'import_reconciliation' || $uploadtype == 'swiss_import_reconciliation')
            $fileDetails = $this->uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $fileFolderId, $files, false, $file_extension);
          else
          {
            if(is_array($request)) 
            {
              if($uploadtype == 'bulk')
                $fileDetails = $this->uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $fileFolderId, $files);
              else
                $fileDetails = $this->uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $fileFolderId, $files, true);
            }
            else
              $fileDetails = $this->uploadFileInOneDrive($api_base_url, $access_token, $apiUserId, $fileFolderId, $files);
          }

          $file = [];
          foreach($fileDetails as $key => $fileDetail)
          {   
            $file_table = "";  

            $query = "";   
            if($month_year == '')  
              $whereUpdate = [
                'vat_reg_id' => $vat_reg_id               
              ]; 
            else
              $whereUpdate = [
                'vat_reg_id' => $vat_reg_id,   
                'month_year' => Carbon::parse('01-'.$month_year)->format('m-Y')
              ];  
            $number = 0; 
              
            $file_table = $commonClass->queryTableForFile($file_type);

            if($file_type == 'pivs' || $file_type == 'c79')                          
              $number = $commercialInvoicesClass->extractFromPdfText($file_type, $fileValue);               
            else if($file_type == 'ci') 
            {
              //$number = $commonClass->extractTextViaOpenAi($file_type, $fileValue); 
              $number = $commercialInvoicesClass->extractFromPdfText($file_type, $fileValue);              
            }
            else if($file_type == 'ivf')   
            {
              if($file_extension == 'xml')
                $xmlvalue = $this->xmlExtract($fileValue, $month_year);          
            }  
            else if($file_type == 'swiss_import_reconciliation')   
            {
              if($file_extension == 'pdf')
              {
                $swissImportReconciliationClass = new SwissImportReconciliationClass();
                $readswissfiles = $swissImportReconciliationClass->readSwissFile($fileValue);                  
              }
            }          

            $query = $file_table;
            
            $whereCondition = $query->where('vat_reg_id', $vat_reg_id);            
            if($file_type != 'ci') 
            {
              if($file_type == 'mailbox') 
                $whereCondition = $query->where('vat_reg_main_id', $vat_reg_main_id);              
              else
              {
                if($month_year != '')
                  $whereCondition = $whereCondition->where('month_year', Carbon::parse('01-'.$month_year)->format('m-Y'));
              }
            }           
            
            if(is_array($request)) 
            {              
              $updateFields = [
                'vat_reg_id' => $vat_reg_id,
                'folder_id' => $fileFolderId,
                'file_id' => $fileDetail['fileId'],
                'file_name' => $fileDetail['fileName'],
                'file_size' => $fileDetail['fileSize']                
              ];
            }
            else
            {                                
                $updateFields = [
                  'vat_reg_id' => $vat_reg_id,
                  'folder_id' => $fileFolderId,
                  'file_id' => $fileDetail['fileId'],
                  'file_name' => $fileDetail['fileName'],
                  'file_size' => $fileDetail['fileSize']                
                ];  
            }

            if($file_type == 'pivs' || $file_type == 'cas' || $file_type == 'dda')
            {
              $updateFields['month_year'] = Carbon::parse('01-'.$month_year)->format('m-Y');
              $updateFields['month_total'] = $number;
            }
            else if($file_type == 'documents')
            {              
              $whereUpdate = [];

              $updateFields['o_file_name'] = $fileValue->getClientOriginalName();
              $updateFields['doc_type'] = $request->documents_type;
              $updateFields['doc_numbers'] = 0;              
            }     
            else if($file_type == 'c79')
            {            
              $updateFields['month_year'] = Carbon::parse('01-'.$month_year)->format('m-Y');
              $updateFields['doc_type'] = $file_type_title;
              $updateFields['doc_numbers'] = $number;
            }  
            else if($file_type == 'ivf')
            {     
              $whereUpdate = [
                'vat_reg_id' => $vat_reg_id,   
                'month_year' => Carbon::parse('01-'.$month_year)->format('m-Y'),
                'file_type' => $file_extension
              ];

              $updateFields['month_year'] = Carbon::parse('01-'.$month_year)->format('m-Y');
              $updateFields['upload_type'] = $uploadtype;
              if($file_extension == 'xml')
              {
                $updateFields['fee_number'] = $xmlvalue['fee'];
                $updateFields['e_fee_number'] = $xmlvalue['fee_ex'];
                $updateFields['statistical_number'] = $xmlvalue['statvalue'];
                $updateFields['e_statistical_number'] = $xmlvalue['statvalue_ex'];
                $updateFields['adjustment_no'] = $xmlvalue['adjustment'];
                $updateFields['invoice_total'] = $xmlvalue['invoice_total'];

                $updateFields['box_85'] = $xmlvalue['box_85'];
              }
            } 
            else if($file_type == 'receipt')
            {
              $whereUpdate = [];              
              $updateFields['o_file_name'] = $fileValue[$key]->getClientOriginalName();
            }
            else if($file_type == 'vatreturn' || $file_type == 'vatcontrol')
            {
              if(is_array($request)) 
              { 
                if($file_type == 'vatreturn')
                { 
                  if(isset($request['mailbox']))
                  {
                    $whereUpdate = [];

                    if($request['original_file'])                          
                      $updateFields = [
                        'vat_reg_id' => $vat_reg_id,
                        'folder_id' => NULL,
                        'file_id' => NULL,
                        'file_name' => NULL,
                        'file_size' => NULL,                     
                        'anyexcel_template_id' => $vatreg->anyexcel_template_id
                      ];
                    else
                      $updateFields = [
                        'vat_reg_id' => $vat_reg_id,                      
                        'folder_id' => $fileFolderId,
                        'file_id' => $fileDetail['fileId'],
                        'file_name' => $fileDetail['fileName'],
                        'file_size' => $fileDetail['fileSize'],                        
                        'anyexcel_template_id' => $vatreg->anyexcel_template_id
                      ];
                  }
                  else
                  {
                    $vatreturn_files = $vatreg->vatreturnfiles;
                    $vatreturnfile = $vatreturn_files->filter(function ($vatreturn_file, $key) {
                      return $vatreturn_file->file_id == NULL;
                    })->first();

                    $whereUpdate = [
                      'id' => $vatreturnfile->id,
                      'vat_reg_id' => $vat_reg_id
                    ]; 
                  }
                }

                if($file_type == 'vatcontrol')
                {
                  $vatcontrol_files = $vatreg->vatcontrolfiles;
                  $vatcontrolfile = $vatcontrol_files->filter(function ($vatcontrol_file, $key) {
                    return $vatcontrol_file->file_id == NULL;
                  })->first();

                  if($vatcontrolfile)
                    $whereUpdate = [
                      'id' => $vatcontrolfile->id,
                      'vat_reg_id' => $vat_reg_id
                    ];
                  
                  if(isset($request['file_ids']))
                  {
                    foreach ($request['file_ids'] as $file_id) 
                    {
                      $whereUpdate = [
                        'id' => $file_id,
                        'vat_reg_id' => $vat_reg_id
                      ];

                      $file_table->updateOrCreate(
                        $whereUpdate,
                        $updateFields
                      );
                    }
                  }                  
                }                  
              }
              else  
              {
                $whereUpdate = [];    

                if($request->original_file)
                { 
                  if($request->original_file == "1")                          
                    $updateFields = [
                      'vat_reg_id' => $vat_reg_id,
                      'folder_id' => NULL,
                      'file_id' => NULL,
                      'file_name' => NULL,
                      'file_size' => NULL,
                      
                      'anyexcel_template_id' => $vatreg->anyexcel_template_id
                    ];
                  else
                    $updateFields = [
                      'vat_reg_id' => $vat_reg_id,                      
                      'folder_id' => $fileFolderId,
                      'file_id' => $fileDetail['fileId'],
                      'file_name' => $fileDetail['fileName'],
                      'file_size' => $fileDetail['fileSize'],
                  
                      'anyexcel_template_id' => $vatreg->anyexcel_template_id
                    ]; 
                }                 
              }                             
            }   
            else if($file_type == 'ci')
            {        
              $whereUpdate = [];

              $updateFields['sale_invoice_nos'] = $number['sale_invoice_nos'];
              $updateFields['invoice_count'] = $number['invoice_count'];             
              $updateFields['created_by'] = $authUser->user_id;
            }
            else if($file_type == 'mailbox')
            {
              $whereUpdate = [];

              $updateFields = [
                'vat_reg_main_id' => $vat_reg_main_id,
                'email_datetime' => $request['email_datetime'],
                'email_subject' => $request['email_subject'],
                'email_id' => $request['email_id'],
                'folder_id' => $fileFolderId,
                'file_id' => $fileDetail['fileId'],
                'file_name' => $fileDetail['fileName'],
                'file_size' => $fileDetail['fileSize'],
                'o_file_name' => $request['file_name'],
                'status' => 2,                
              ];
            } 
            else if($file_type == 'cargo_mailbox')
            {                           
              $expo_run_no = $request['expo_no'] . $request['lope_no'];
              
              $whereCondition = $query->where('expo_run_no', $expo_run_no);

              $whereUpdate = [];
              if($expo_run_no != '')
                $whereUpdate = [
                  'expo_run_no' => $expo_run_no
                ];

              $updateFields = [                 
                'cargo_date' => $request['cargo_date'],

                'cargo_com_invoice_nos' => $request['com_invoice_no'],
                'cargo_com_invoice_dates' => $request['com_invoice_date'],

                'expo_no' => $request['expo_no'],
                'run_no' => $request['lope_no'],
                'expo_run_no' => $expo_run_no,

                'email_datetime' => $request['email_datetime'],
                'email_subject' => $request['email_subject'],
                'email_id' => $request['email_id'],
                'folder_id' => $fileFolderId,
                'file_id' => $fileDetail['fileId'],
                'file_name' => $fileDetail['fileName'],
                'file_size' => $fileDetail['fileSize'],
                'o_file_name' => $request['file_name'],
                'status' => 2,                
              ];
            }
            else if($file_type == 'import_reconciliation')  
            {                      
              $whereUpdate = [
                'vat_reg_id' => $vat_reg_id,   
                'month_year' => Carbon::parse('01-'.$month_year)->format('m-Y'),
                'o_file_name' => $request['o_filename']
              ]; 

              $invoice_row = $request['invoice_row'];
              $updateFields['credit_note'] = $invoice_row['invoice_credit_note'];

              $updateFields['o_file_name'] = $request['o_filename'];
              $updateFields['month_year'] = $request['month_year'];
              $updateFields['invoice_no'] = $request['invoice_no'];
              $updateFields['created_by'] = ($authUser->user_id) ? $authUser->user_id : $authUser->user_id;
            }
            else if($file_type == 'swiss_import_reconciliation')  
            {                        
              $whereUpdate = [
                'vat_reg_id' => $vat_reg_id,   
                'month_year' => Carbon::parse($readswissfiles['com_invoice_date'])->format('m-Y'),
                'o_file_name' => $fileValue->getClientOriginalName()
              ]; 

              $updateFields['o_file_name'] = $fileValue->getClientOriginalName();
              $updateFields['month_year'] = Carbon::parse($readswissfiles['com_invoice_date'])->format('m-Y');
              $updateFields['invoice_no'] = $readswissfiles['com_invoice_no'];
              $updateFields['created_by'] = ($authUser->user_id) ? $authUser->user_id : $authUser->user_id;
            }
            elseif($file_type == 'ircontrol')
            {
              $ircontrol_files = $vatreg->ircontrolfiles;
              $ircontrolfile = $ircontrol_files->filter(function ($ircontrol_file, $key) {
                return $ircontrol_file->file_id == NULL;
              })->first();

              $whereUpdate = [
                'id' => $ircontrolfile->id,
                'vat_reg_id' => $vat_reg_id
              ];

              
              if(isset($request['file_ids']))
              {
                foreach ($request['file_ids'] as $file_id) 
                {
                  $whereUpdate = [
                    'id' => $file_id,
                    'vat_reg_id' => $vat_reg_id
                  ];

                  $file_table->updateOrCreate(
                    $whereUpdate,
                    $updateFields
                  );
                }
              }                  
            } //importreconciliationcontrol
            else if($file_type == 'iranyexcel') // any excel documents like in VATreturn
            {
              /*
              if(is_array($request)) 
              {                
                $vatreturn_files = $vatreg->vatreturnfiles;
                $vatreturnfile = $vatreturn_files->filter(function ($vatreturn_file, $key) {
                  return $vatreturn_file->file_id == NULL;
                })->first();

                $whereUpdate = [
                  'id' => $vatreturnfile->id,
                  'vat_reg_id' => $vat_reg_id
                ];                   
              }
              else  
              {
                $whereUpdate = [];    

                if($request->original_file)
                {        
                  if($request->original_file == "1")                          
                    $updateFields = [
                      'vat_reg_id' => $vat_reg_id,
                      'folder_id' => NULL,
                      'file_id' => NULL,
                      'file_name' => NULL,
                      'file_size' => NULL,
                      'excel_column_template_id' => $vatreg->excel_column_template_id
                    ];
                  else
                    $updateFields = [
                      'vat_reg_id' => $vat_reg_id,                      
                      'folder_id' => $fileFolderId,
                      'file_id' => $fileDetail['fileId'],
                      'file_name' => $fileDetail['fileName'],
                      'file_size' => $fileDetail['fileSize'],
                      'excel_column_template_id' => $vatreg->excel_column_template_id
                    ]; 
                }                 
              } 
              */                            
            }  

            if($file_type != 'ivf' && $file_type != 'vatreturn' && $file_type != 'vatcontrol' && $file_type != 'receipt' && $file_type != 'mailbox' && $file_type != 'cargo_mailbox' && $file_type != 'import_reconciliation' && $file_type != 'swiss_import_reconciliation' && $file_type != 'ircontrol' && $file_type != 'iranyexcel')
              $updateFields['status'] = 0;

            $fileexists = $whereCondition->count();
            
             
            if(empty($whereUpdate))
              $file[$key] = $file_table->updateOrCreate(              
                $updateFields
              );
            else
              $file[$key] = $file_table->updateOrCreate(
                $whereUpdate,
                $updateFields
              );
           
            //update receipt fields in VAT reg.
            if($file_type == 'receipt')
            { 
              $updateStatus = VATRegistration::where('id', $vat_reg_id)
                            ->where('status', 4)
                            ->orWhere('status', 5)
                            ->update(
                              [
                                    'status' => 5, 
                                    'receipt_by' => $authUser->user_id, 
                                    'receipt_at' => now()                                   
                              ]
                            );//From 'Ready to Submit' to 'Submitted' (after team user uploaded receipts) 
            }

            //insert into Import VAT File - cargo files
            if($file_type == 'ivf')
            {   
              if($file_extension == 'xml')
              {           
                $import_vat_id = $file[$key]->id;

                $expedition_list = $xmlvalue['expedition_list'];  
                foreach($expedition_list as $key => $expedition)
                {
                  $ivf_com_invoice_nos = '';
                  $ivf_com_invoice_dates = '';
                  foreach($expedition['com_invoices'] as $com_invoice)
                  {                   
                    $commercial_invoice_no = isset($com_invoice['com_invoice_no']) ? $com_invoice['com_invoice_no'] : null;
                    $com_invoice_date = isset($com_invoice['com_invoice_date']) ? $com_invoice['com_invoice_date'] : null;
                       
                    if($ivf_com_invoice_nos == '')
                    {
                        $ivf_com_invoice_nos = $commercial_invoice_no;
                        $ivf_com_invoice_dates = $com_invoice_date;
                    }
                    else
                    {
                        $ivf_com_invoice_nos .= ',' . $commercial_invoice_no;
                        $ivf_com_invoice_dates .= ',' . $com_invoice_date;
                    }
                                                                                                                  
                    $com_invoice_net_amount = isset($expedition['com_invoice_net_amount']) ? $expedition['com_invoice_net_amount'] : null;
                    $com_invoice_omr_kurs = isset($expedition['com_invoice_omr_kurs']) ? $expedition['com_invoice_omr_kurs'] : null;
                    $com_invoice_currency_code = isset($expedition['com_invoice_currency_code']) ? $expedition['com_invoice_currency_code'] : 'NOK';

                    //INSERT INTO COM. INVOICE TABLE
                    $already_exists_cominvoice = ImportReconciliationComInvoices::where('vat_reg_id', $vat_reg_id)
                                            ->where('invoice_no', $commercial_invoice_no)
                                            ->where('lope_no', $expedition['run_no'])
                                            ->first();

                    if($already_exists_cominvoice)
                    {
                        $already_exists_cominvoice->data_from = 'ivf';
                        $already_exists_cominvoice->month_year = $month_year;
                        $already_exists_cominvoice->invoice_date = Carbon::parse($com_invoice_date)->format('Y-m-d');
                        $already_exists_cominvoice->expo_no = ($already_exists_cominvoice->expo_no == NULL) ? $expedition['expo_no'] : $already_exists_cominvoice->expo_no;
                        $already_exists_cominvoice->lope_no = ($already_exists_cominvoice->lope_no == NULL) ? $expedition['run_no'] : $already_exists_cominvoice->lope_no;
                        $already_exists_cominvoice->duties = $expedition['duties'];
                        $already_exists_cominvoice->adjustment = $expedition['adjustment'];
                        $already_exists_cominvoice->statistical_value = $expedition['statistical_value'];
                        $already_exists_cominvoice->category_type = $expedition['category_type'];
                        $already_exists_cominvoice->category_desc = $expedition['category_desc'];
                        $already_exists_cominvoice->ivf_net_amount = $com_invoice_net_amount;
                        $already_exists_cominvoice->omr_kurs = $com_invoice_omr_kurs;
                        $already_exists_cominvoice->currency_code = $com_invoice_currency_code;
                        $already_exists_cominvoice->updated_by = $authUser->id;

                        $already_exists_cominvoice->save();
                    }
                    else
                    {
                      $insert_cominvoice = ImportReconciliationComInvoices::updateOrCreate(
                        [
                          'vat_reg_id' => $vat_reg_id,
                          'invoice_no' => $commercial_invoice_no,
                          'lope_no' => $expedition['run_no']
                        ],
                        [                
                          'vat_reg_id' => $vat_reg_id,

                          'data_from' => 'ivf',
                          'month_year' => $month_year,
                          
                          'invoice_no' => $commercial_invoice_no,                                             
                          'invoice_date' => Carbon::parse('01-'.$month_year)->format('Y-m-d'),
                          'expo_no' => $expedition['expo_no'],
                          'lope_no' => $expedition['run_no'],
                          'duties' => $expedition['duties'],
                          'adjustment' => $expedition['adjustment'],
                          'statistical_value' => $expedition['statistical_value'],
                          'category_type' => $expedition['category_type'],
                          'category_desc' => $expedition['category_desc'],
                          'doc_status' => ($commercial_invoice_no) ? 'Validated' : 'Validation',
                          'country' => $vatreg->country,
                          'currency_code' => $com_invoice_currency_code,
                          'ivf_net_amount' => $com_invoice_net_amount,
                          'omr_kurs' => $com_invoice_omr_kurs,
                          'created_by' => $authUser->id
                        ]
                      );
                    }
                  } //for commercial_invoice_no
                  
                  $already_exists_cargo_file = CargoDeclarationFiles::where('expo_no', $expedition['expo_no'])
                                                  ->where('run_no', $expedition['run_no'])
                                                  ->first();
                                                                      
                  if($already_exists_cargo_file)
                  {                    
                    $already_exists_cargo_file->import_vat_id = $import_vat_id;
                    $already_exists_cargo_file->ivf_com_invoice_nos = $ivf_com_invoice_nos;
                    $already_exists_cargo_file->ivf_com_invoice_dates = $ivf_com_invoice_dates;                   
                    $already_exists_cargo_file->status = 2;
                    $already_exists_cargo_file->updated_by = ($authUser->user_id) ? $authUser->user_id : $authUser->user_id;

                    $already_exists_cargo_file->save();
                  }
                  else
                  {                    
                    $cargo_file = CargoDeclarationFiles::updateOrCreate(
                      [                     
                        'expo_no' => $expedition['expo_no'],
                        'run_no' => $expedition['run_no']
                      ],
                      [           
                        'import_vat_id' => $import_vat_id,
                        'ivf_com_invoice_nos' => $ivf_com_invoice_nos,
                        'ivf_com_invoice_dates' => $ivf_com_invoice_dates,
                        'expo_no' => $expedition['expo_no'],
                        'run_no' => $expedition['run_no'],
                        'expo_run_no' => $expedition['expo_no'] . $expedition['run_no'],
                        'status' => 2,
                        'created_by' => ($authUser->user_id) ? $authUser->user_id : $authUser->user_id
                      ]
                    );
                  }
                } //for Cargo Files
              } // XML
            } // IVF - Cargo Files
            
            if($file_type == 'vatreturn' || $file_type == 'vatcontrol')
            {
              if(is_array($request)) 
              {
                if(isset($request['mailbox']))
                {                  
                  if($request['original_file']) 
                  {
                    if($file_type == 'vatreturn')
                    {
                      $updateFields = [
                        'vatreturn_file_id' => $file[$key]->id,
                        'vat_reg_id' => $vat_reg_id,
                        'folder_id' => $fileFolderId,
                        'file_id' => $fileDetail['fileId'],
                        'file_name' => $fileDetail['fileName'],
                        'file_size' => $fileDetail['fileSize'],

                        'o_file_name' => $request['o_file_name']
                      ];
      
                      //Insert into Original Files
                      $_o_files = VATReturnOFiles::updateOrCreate(
                        $updateFields
                      );
                    } //VAT Return
                  }
                } 
              }//is array
              else
              {
                if($request->original_file)
                {
                  if($request->original_file == "1")
                  {
                    if($file_type == 'vatreturn')
                    {
                      $updateFields = [
                        'vatreturn_file_id' => $file[$key]->id,
                        'vat_reg_id' => $vat_reg_id,
                        'folder_id' => $fileFolderId,
                        'file_id' => $fileDetail['fileId'],
                        'file_name' => $fileDetail['fileName'],
                        'file_size' => $fileDetail['fileSize'],

                        'o_file_name' => $fileValue->getClientOriginalName()
                      ];
      
                      //Insert into Original Files
                      $_o_files = VATReturnOFiles::updateOrCreate(
                        $updateFields
                      );
                    } //VAT Return                    

                    if($file_type == 'vatcontrol')
                    {
                      $updateFields = [
                        'vatcontrol_file_id' => $file[$key]->id,
                        'vat_reg_id' => $vat_reg_id,
                        'folder_id' => $fileFolderId,
                        'file_id' => $fileDetail['fileId'],
                        'file_name' => $fileDetail['fileName'],
                        'file_size' => $fileDetail['fileSize'],

                        'o_file_name' => $fileValue->getClientOriginalName()
                      ];
      
                      //Insert into Original Files
                      $_o_files = VATControlOFiles::updateOrCreate(
                        $updateFields
                      );
                    } //VAT Control
                  }
                }
              } //not array
            }

            //insert into Sales Invoice Data table
            if($file_type == 'import_reconciliation')
            {
              $invoice_row = $request['invoice_row'];
              $ir_file_id = $file[$key]->id;

              $already_exists_sales_invoice_data = ImportReconciliationSalesInvoicesData::where('ir_file_id', $ir_file_id)
                                                      ->where('invoice_no', $invoice_row['invoice_no'])
                                                      ->first();

              if($already_exists_sales_invoice_data)              
                $sales_invoice_data_id = $already_exists_sales_invoice_data->id;
             
             try {
                             
                                       
              $insert_salesinvoicedata = ImportReconciliationSalesInvoicesData::updateOrCreate(
                [
                  'ir_file_id' => $ir_file_id,
                  'invoice_no' => $invoice_row['invoice_no']
                ],
                [    
                  'ir_file_id' => $ir_file_id,
                  'invoice_no' => $invoice_row['invoice_no'],
                  'invoice_date' => $invoice_row['invoice_date'],
                
                  'currency_code' => $invoice_row['invoice_currency'],
                  'converted_note' => $invoice_row['note'],
                  'credit_note' => $invoice_row['invoice_credit_note'],
                 
                  'buyer_name' => $invoice_row['account_name'],
                  'buyer_street' => $invoice_row['client_street'],
                  'buyer_houseno' => $invoice_row['client_houseno'],
                  'buyer_city' => $invoice_row['client_city'],
                  'buyer_postcode' => $invoice_row['client_postcode'],
                  'buyer_countrycode' => $invoice_row['client_countrycode'],
                  'buyer_vatno' => $invoice_row['vat_no'],
                  'buyer_contact_name' => $invoice_row['account_name'],
                  
                  'tax_total_amount' => $invoice_row['invoice_vat_amount'],
                  'tax_total_amount_currency_code' => $invoice_row['invoice_currency'],
                  'tax_total_net_amount' => $invoice_row['invoice_net_amount'],
                  'tax_total_net_amount_currency_code' => $invoice_row['invoice_currency'],
                  'tax_total_percent' => ($invoice_row['invoice_net_amount'] > 0) ? round((($invoice_row['invoice_vat_amount']/$invoice_row['invoice_net_amount']) * 100)) : 0,
                  
                  'created_by' => isset($sales_invoice_data_id) ? $already_exists_sales_invoice_data->created_by : $authUser->id,
                  'updated_by' => $authUser->id
                ]
              );  
               } catch (Exception $e) {
                             dd($e);
                           }            
            }
            //END insert into Sales Invoice Data table

            //insert into Com. Invoice Data table
            if($file_type == 'swiss_import_reconciliation')
            {
              $commercial_invoice_no = $readswissfiles['com_invoice_no'];
              $commercial_invoice_date = $readswissfiles['com_invoice_date'];
              $lope_no = $readswissfiles['decalration_no'];
              $category_type = $readswissfiles['category_type'];
              $currency_code = $readswissfiles['currency_code'];
              $net_amount = $readswissfiles['com_invoice_net_amount'];
              $vat_amount = $readswissfiles['com_invoice_vat_amount'];
              $total_amount = $net_amount + $vat_amount;
                          
              $check_already_exist_cominvoice = ImportReconciliationComInvoices::where('vat_reg_id', $vat_reg_id)
                                                      ->where('invoice_no', $commercial_invoice_no)      
                                                      ->where('lope_no', $lope_no)         
                                                      ->first();
             
              if($check_already_exist_cominvoice)              
                $com_invoice_id = $check_already_exist_cominvoice->id;
             
             try {
                             
                                       
              $insert_cominvoice = ImportReconciliationComInvoices::updateOrCreate(
                  [
                    'vat_reg_id' => $vat_reg_id,
                    'invoice_no' => $commercial_invoice_no,
                    'lope_no' => $lope_no
                  ],
                  [                
                    'vat_reg_id' => $vat_reg_id,

                    'data_from' => 'swiss',
                    'month_year' => Carbon::parse($commercial_invoice_date)->format('m-Y'),
                    
                    'invoice_no' => $commercial_invoice_no,
                    'invoice_date' => $commercial_invoice_date,
                    'gs_invoice_date' => $commercial_invoice_date,
                    
                    'doc_status' => 'Validated',
                    'lope_no' => $lope_no,

                    'category_type' => $category_type,
                    'category_desc' => ($category_type) ? (($category_type == 1) ? 'Declaration' : 'Correction') : NULL,
                    
                    'country' => 'CH',
                    'currency_code' => $currency_code,
                    'statistical_value' => $net_amount,
                    'net_amount' => $net_amount,
                    'vat_amount' => $vat_amount,
                    'total_amount' => $total_amount,
                   
                    'created_by' => $authUser->id                    
                  ]
                ); 
               } catch (Exception $e) {
                             dd($e);
                           }            
            }
            //END insert into Com. Invoice Data table

            if($file_type == 'ircontrol')
            {              
              if($request->original_file)
              {
                if($request->original_file == "1")
                {                     
                  $updateFields = [
                    'ircontrol_file_id' => $file[$key]->id,
                    'vat_reg_id' => $vat_reg_id,
                    'folder_id' => $fileFolderId,
                    'file_id' => $fileDetail['fileId'],
                    'file_name' => $fileDetail['fileName'],
                    'file_size' => $fileDetail['fileSize'],

                    'o_file_name' => $fileValue->getClientOriginalName()
                  ];
  
                  //Insert into Original Files
                  $_o_files = ImportReconciliationControlOFiles::updateOrCreate(
                    $updateFields
                  );
                }
              }
            }

            $file[$key]['file_type'] = $file_type;
            $file[$key]['file_type_title'] = $file_type_title;

            if($file_type != 'ci')
            {
              if($fileexists == 0)
                $updateFileTable = $whereCondition               
                                ->update(
                                  [
                                        'created_by' => ($authUser->user_id) ? $authUser->user_id : $authUser->user_id
                                  ]
                                );
              else
                $updateFileTable = $whereCondition                
                                ->update(
                                  [
                                        'updated_by' => ($authUser->user_id) ? $authUser->user_id : $authUser->user_id
                                  ]
                                );      
            }           

            //Update NEXT Date
            if($file_type == 'pivs' || $file_type == 'c79')
            {                                      
              $next_date = Carbon::parse('01-'.$month_year)->addMonth(1)->format('m-Y');
              $updateDate = $commonClass->updateNextDate($vat_reg_id, $next_date, $month_year, $number, $file_type);  
            }          
          }
                        
          if($file_type == 'import_reconciliation')           
            $commonClass->addLog($authUser, 'file-upload', 
              [
                'Client Name' => $client->client_name,
                'VAT Reg' => $vatRegHeading,
                'month' => ($month_year != '') ? Carbon::parse('01-'.$month_year)->format('M Y') : '',
                'file_type_title' => $file_type_title,
                'file_name' => $request['o_filename']
              ]
            ); 
          else if($file_type == 'receipt')        
            $commonClass->addLog($authUser, 'receipt-upload', 
              [
                'Client Name' => $client->client_name,
                'VAT Reg' => $vatRegHeading
              ]
            );           
          else               
            $commonClass->addLog($authUser, 'file-upload', 
              [
                'Client Name' => $client->client_name,
                'VAT Reg' => $vatRegHeading,
                'month' => ($month_year != '') ? Carbon::parse('01-'.$month_year)->format('M Y') : '',
                'file_type_title' => $file_type_title
              ]
            );

          return $file;   
        } 
      }
    }

    /*Microsoft Graph API - One Drive - Delete*/    
    public function deleteFromOneDriveLazy($file, $system, $file_type = NULL)   
    { 
        $api_base_url = $system->api_base_url;
        $apiUserId = $system->api_user_id;

        if(isset($file))
        {          
          $item_id = $file->file_id;
          $id = $file->id;
          $vat_reg_id = $file->vat_reg_id;
          try
          {    
            $access_token = $this->getMicrosoftGraphAccessTokenLazy($system); 
            $access_token = ($access_token == "not expired") ?  $system->api_token : $access_token;

            $headers = [                         
                'Content-Type' => 'application/json',           
                'Authorization' => 'Bearer ' . $access_token      
            ];        

            $guzzleClient = new GuzzleClient();   
            
            if($file->o_file_id)
            {
              $url = $api_base_url .'/v1.0/users/'.$apiUserId.'/drive/items/'.$file->o_file_id; 

              $response = $guzzleClient->request('DELETE', $url, [              
                  'headers' => $headers
              ]);

              $data = json_decode($response->getBody()); 
            }
            
            $url = $api_base_url .'/v1.0/users/'.$apiUserId.'/drive/items/'.$item_id; 
            
            $response = $guzzleClient->request('DELETE', $url, [              
                'headers' => $headers
            ]);

            $data = json_decode($response->getBody());        
                       
            return response()->json([
              'status'          => "deleted",
              'vat_reg_id' => $vat_reg_id,
              'id' => $id,
              'file_type' => $file_type
            ]);
          }
          catch (\Exception $e) {            
            if($e->getResponse())   
            {           
              $response = $e->getResponse();
              $errorMessage = json_decode($response->getBody());  
            }
            else
              $errorMessage = $e->getMessage();  

            return response()->json([
              'status'          => "error",
              'error' => $errorMessage,
              'vat_reg_id' => $vat_reg_id,
              'id' => $id,
              'file_type' => $file_type
            ]);
          }
        }
    }
}
