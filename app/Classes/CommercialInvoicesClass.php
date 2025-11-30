<?php

namespace App\Classes;

use Spatie\PdfToText\Pdf as PdfExtract;
use Spatie\PdfToImage\Pdf as PdfImage;
//use Org_Heigl\Ghostscript\Ghostscript;
use Aws\Textract\TextractClient;
use Storage;

use App\Classes\CommonClass;

class CommercialInvoicesClass
{        
    /* -- EXTRACT From PDF Text -- */
    public function extractFromPdfText($file_type, $file = NULL)
    {   
        if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
        {
          $exepath = 'c:/Program Files/Git/mingw64/bin/pdftotext';               
          $pdftext = PdfExtract::getText($file, $exepath); 
        }
        else
          $pdftext = PdfExtract::getText($file); 
      
        if($file_type == 'ci')
        {
          if($pdftext == "")
          {
            // Ghostscript::setGsPath("C:/Program Files/gs/gs10.03.0/bin/gswin64c.exe");
            $pdf = new \Spatie\PdfToImage\Pdf($file);
            $storage_path = storage_path('app/public/');
            //$pdf->saveImage($storage_path);
            $noofpages = $pdf->saveAllPagesAsImages($storage_path);

            $textractClient = new TextractClient([
                'version' => 'latest',
                'region' => 'us-east-1',//'eu-north-1', // pass your region
                'credentials' => [
                    'key'    => config('services.ses.key'),
                    'secret' => config('services.ses.secret')
                ]
            ]);
            // The file in this project.
            //$filename = $storage_path ."1.jpg";
            for($i = 0; $i < count($noofpages); $i++)
            {              
              $page_arr = explode("//", $noofpages[$i]);             
              //$filename = $noofpages[$i];
              $page_name = $page_arr[count($page_arr) - 1];
              if (stripos($page_name, ".") !== false)  
                $filename = $storage_path . $page_name;

              $file = fopen($filename, "rb");
              $contents = fread($file, filesize($filename));
              fclose($file);
              $options = [
                  'Document' => [
                  'Bytes' => $contents
                  ],
                  'FeatureTypes' => ['FORMS'], // REQUIRED
              ];
              $result = $textractClient->analyzeDocument($options);
              
              //echo "Page: ". print_r($i+1, true) . "<br>-----------<br>";
              // If debugging:
              // echo print_r($result, true);
              $blocks = $result['Blocks'];
              // Loop through all the blocks:
              foreach ($blocks as $key => $value) {
                if (isset($value['BlockType']) && $value['BlockType']) {
                  $blockType = $value['BlockType'];
                  if (isset($value['Text']) && $value['Text']) {
                    $text = $value['Text'];

                    if ($blockType == 'LINE')
                    {
                      if($pdftext == '')
                        $pdftext .= $text;
                      else
                        $pdftext .= "\n" . $text;
                    }
                    
                    // if ($blockType == 'WORD') {
                    //   echo "Word: ". print_r($text, true) . "<br>";
                    // } else if ($blockType == 'LINE') {
                    //   echo "Line: ". print_r($text, true) . "<br>";
                    // }
                  }
                }
              }
              Storage::disk('public')->delete($page_name);
            }//for noofpages                    
          }

          $ci_details = [];
          if($pdftext)       
          {
            $arraytext = explode("\n", $pdftext);
          //dd($arraytext);
            $sale_invoice_nos = '';
            $which_condition = '';
            foreach($arraytext as $key => $text)
            {                 
              /* -- SALES INVOICES -- */
              //Format 1 - ***CommercialInvoice_.pdf                           
              if (stripos($text, "Commercial Invoice based on the Sale Invoices: ") !== false)  
              {                
                $which_condition .= 'S-1 ';
                $sale_invoice_text = $text;               
                if (stripos($text, "and the Delivery ") !== false)  
                {
                  $sale_invoice_with_delivery = explode(' and ', $text); 
                  $sale_invoice_text = (count($sale_invoice_with_delivery) == 0) ? '' : $sale_invoice_with_delivery[0];
                }

                $sale_invoice_nos = str_replace(
                                    array("Commercial Invoice based on the Sale Invoices: ", "\r", "\n", "\r\n")
                                    , "", $sale_invoice_text);                
              }
              
              //Format 2 - AUBO - ESCANI1680187f26c10c4-8d15-4da6-a59e-7a267e6d42a2.pdf
              if (stripos($text, "Omfatter ") !== false && stripos($text, " faktura:") !== false)  
              {       
                $which_condition .= 'S-2 ';  

                $sales_invoice_line = $arraytext[$key + 1]; 
                if (stripos($sales_invoice_line, "..") !== false)                  
                  $sale_invoice_nos = str_replace('..', '-', $sales_invoice_line);               
              }
            
              //Format 3 - BECKSONDERGAARD ApS - NIC00924.pdf
              if ((stripos($text, "Samlefakturaen ") !== false && stripos($text, " salgsfaktura ") !== false))
              { // || stripos($text, "Additional Comments") !== false
                //"Omfatter følgende faktura:"
                //"Samlefakturaen dækker salgsfaktura "
                $which_condition .= 'S-3 ';
                $sale_invoice_text = $text;  
                
                if (preg_match('/\d+/', $sale_invoice_text)) 
                {                
                  preg_match_all('/\d+/', $sale_invoice_text, $matches);
                
                  if(count($matches[0]) == 2)
                    $sale_invoice_nos = $matches[0][0] . '-' . $matches[0][1];
                }
              }
              
              //Format 4 - BERENDSOHN AG - 983799620MVA_CI_0000041790_END.pdf
              if (str_replace(array("\r", "\n", "\r\n"), "", $text) == "CN")  
              {                  
                if(isset($exepath))
                {
                  $which_condition .= 'S-4 ';                        
                  $sales_invoice_line = $arraytext[$key - 2];
                  $star_line = $arraytext[$key + 2];
                  if (stripos($star_line, "*****") !== false)  
                  {            
                    $which_condition .= 'S-4* ';       
                    $sale_invoice_no = str_replace(array("\r", "\n", "\r\n"), "", $sales_invoice_line);
                    if($sale_invoice_nos == '')
                      $sale_invoice_nos = $sale_invoice_no;
                    else
                      $sale_invoice_nos .= ',' . $sale_invoice_no;
                  }
                }  //localhost
                else
                {    
                  if($sale_invoice_nos == '')
                  {              
                    $start_pos = 0;                
                    $end_pos = $key - 2;

                    for($i = $end_pos; $i >= 0; $i--)
                    {
                      if (stripos($arraytext[$i], "SAMPLE") !== false)  
                      {
                        $start_pos = $i + 2;
                        break;
                      }  
                    }

                    if($start_pos != 0)
                    {
                      $which_condition .= 'S-4 ';
                      $y = 0;
                      for($i = $start_pos; $i <= $end_pos; $i++)
                      {
                        $sales_invoice_line = $arraytext[$i];
                        if (preg_match('/\d+/', $sales_invoice_line)) 
                        {
                          if(strlen($sales_invoice_line) == 8)
                          {
                            $sale_invoice_no = str_replace(array("\r", "\n", "\r\n"), "", $sales_invoice_line);
                            if($sale_invoice_nos == '')
                              $sale_invoice_nos = $sale_invoice_no;
                            else
                              $sale_invoice_nos .= ',' . $sale_invoice_no;
                          }
                        }
                      }
                    }                   
                  } //sales invoice nos null
                } //server
              } //CN
              //Format 4-1          
              if (stripos($text, "_______C_N_______") !== false)  
              {        
                $which_condition .= 'S-4-1 ';                  
                $sales_invoice_array = explode('_______', $text);
                foreach($sales_invoice_array as $keyi => $sales_invoice_text)
                {
                  $sales_invoice_line = str_replace(array("_", "\r", "\n", "\r\n"), "", $sales_invoice_text);
                  if ($sales_invoice_line == "CN") 
                  {
                    $sale_invoice_no = str_replace(array("_", "\r", "\n", "\r\n"), "", $sales_invoice_array[$keyi - 1]);
                    if($sale_invoice_nos == '')
                      $sale_invoice_nos = $sale_invoice_no;
                    else
                      $sale_invoice_nos .= ',' . $sale_invoice_no;  
                  }
                }               
              }
             
              //Format 5 - BESSIE - Samlefaktura 22-03-24.pdf
              if(stripos($text, "Antal kolli Netto") !== false && stripos($text, " Faktura") !== false)  
              {    
                $which_condition .= 'S-5 ';
                $sales_invoice_line = $arraytext[$key + 2];
                $sales_invoice_array = explode(' ', $sales_invoice_line);
                
                if(count($sales_invoice_array) >= 3)
                {
                  $length = count($sales_invoice_array);
                  $sale_invoice_nos = $sales_invoice_array[$length-3] . '-' . $sales_invoice_array[$length-1];
                }
              }

              //Format 6 - BLACK COLOUR - NO-337.pdf, HORN BORDPLADER - 2024-02-01 Proformafaktura 6205.pdf, LYNGSOE RAINWEAR - NOS-003113 SHIPMENT - SF-1300197.pdf
              if(stripos($text, "Salgsfaktura:") !== false)  
              {       
                $which_condition .= 'S-6 ';
                if(stripos($text, "Track no:") !== false)  
                {
                  $start_pos = strpos($text, "Salgsfaktura:")+13;      
                  $end_pos = strpos($text, "Track no:"); 
                  $diff_pos = $end_pos - $start_pos;
                  $sales_invoice_text = substr($text, $start_pos, $diff_pos);
                  
                  $sale_invoice_nos = trim($sales_invoice_text);      
                } 
                else
                {
                  // //$start_pos = strpos($text, "salgsfaktura:")+13;                
                  // $start_pos = strlen("Samlefaktura vedr. Salgsfaktura: "); 
                  // $end_pos = strlen($text); 
                  // $diff_pos = $end_pos - $start_pos;
                  // $sales_invoice_text = substr($text, $start_pos, $diff_pos);

                  // $sales_invoice_text = str_replace(array(".", "\r", "\n", "\r\n"), "", $sales_invoice_text);
                  // $sale_invoice_nos = trim($sales_invoice_text); 

                  $start_pos = $key;                
                  $end_pos = count($arraytext) - 1;

                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {
                    if (stripos($arraytext[$i], "Track no: ") !== false || stripos($arraytext[$i], "Horn Bordplader") !== false)                      
                    {
                      $end_pos = $i -1;
                      break;
                    }
                  }

                  $sale_invoice_nos = '';
                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {
                    $sales_invoice_line = $arraytext[$i];
                    if (stripos($sales_invoice_line, "Samlefaktura vedr. Salgsfaktura: ") !== false)                      
                    {
                      $diff_pos = strlen($sales_invoice_line) - strlen("Samlefaktura vedr. Salgsfaktura: ");
                      $sales_invoice_text = substr($text, strlen("Samlefaktura vedr. Salgsfaktura: "), $diff_pos);

                      $sales_invoice_text = str_replace(array(".", "\r", "\n", "\r\n"), "", $sales_invoice_text);

                      if($sale_invoice_nos == '')
                        $sale_invoice_nos = trim($sales_invoice_text);
                      else
                        $sale_invoice_nos .= trim($sales_invoice_text); 
                    }
                    else if (stripos($sales_invoice_line, "Samlefaktura dækker over følgende salgsfaktura: ") !== false)
                    {
                      $diff_pos = strlen($sales_invoice_line) - strlen("Samlefaktura dækker over følgende salgsfaktura: ");
                      $sales_invoice_text = substr($text, strlen("Samlefaktura dækker over følgende salgsfaktura: "), $diff_pos);

                      $sales_invoice_text = str_replace(array(".", "\r", "\n", "\r\n"), "", $sales_invoice_text);

                      if($sale_invoice_nos == '')
                        $sale_invoice_nos = trim($sales_invoice_text);
                      else
                        $sale_invoice_nos .= trim($sales_invoice_text); 
                    }
                    // else if (stripos($sales_invoice_line, "Samlefakturaen dækker salgsfaktura:") !== false)
                    // {
                    // }
                    else                   
                    {
                      $sales_invoice_text = $sales_invoice_line;
                      $sales_invoice_text = str_replace(array(".", "\r", "\n", "\r\n"), "", $sales_invoice_text);

                      if (stripos($sales_invoice_line, "Samlefakturaen dækker salgsfaktura:") !== false)
                      {

                      }
                      else
                      {
                        if($sale_invoice_nos == '')
                          $sale_invoice_nos = trim($sales_invoice_text);
                        else
                          $sale_invoice_nos .= trim($sales_invoice_text);  
                      }
                    } 
                  }//for
                }                     
              }

              //Format 7 - BODO MOLLER CHEMIE - DK618744.pdf
              //if(stripos($text, "Denne faktura") !== false || stripos($text, "Norway faktura") !== false)  
              if(stripos($text, "Norway faktura") !== false)  
              {      
                $which_condition .= 'S-7 ';
                $sales_invoice_array = explode(' ', $text);
                foreach($sales_invoice_array as $sales_invoice_text)
                {
                  if (preg_match('/\d+/', $sales_invoice_text)) 
                    $sale_invoice_nos = trim($sales_invoice_text);
                }                                        
              }

              //Format 8 - BYIC - consolidated-invoice-9895-2024-03-22-14-01-11.pdf
              if(stripos($text, "References:") !== false)  
              {       
                $which_condition .= 'S-8 ';
                if(isset($exepath))
                {
                  $sales_invoice_line = $arraytext[$key + 3];
                  $sales_invoice_text = str_replace(array("\r", "\n", "\r\n"), "", $sales_invoice_line);
                 
                  $sale_invoice_nos = str_replace("-", "***", $sales_invoice_text);
                }//localhost
                else
                {
                  $sales_invoice_line = $arraytext[$key + 5];
                  $sales_invoice_text = str_replace(array("\r", "\n", "\r\n"), "", $sales_invoice_line);
                 
                  $sale_invoice_nos = str_replace("-", "***", $sales_invoice_text);
                }//server
              }

              //Format 9 - CM DISTRIBUTION DENMARK - Invoice 100677.pdf
              if(stripos($text, "Order No.: ") !== false)  
              {       
                $which_condition .= 'S-9 ';

                if (preg_match('/\d+/', $text)) 
                {                
                  preg_match_all('/\d+/', $text, $matches);
                
                  if(count($matches[0]) > 0)
                    $sale_invoice_nos = $matches[0][0];                 
                }                
              }

              //Format 10 - GUARDIAN PROTECTION PRODUCTS - 1448.pdf, MILLARCO - Millarco_PROFORMA_faktura_NOPF52071.pdf
              if(stripos($text, "salgsfakturaer") !== false)  
              {       
                $which_condition .= 'S-10 ';

                if(stripos($text, ":") !== false)  
                { 
                  $sale_invoice_array = explode(':', $text);
                  if(count($sale_invoice_array) > 1)
                    $sale_invoice_array = explode(' ', $sale_invoice_array[1]);
                  
                  foreach($sale_invoice_array as $sale_invoice_text)
                  {
                    if (preg_match('/\d+/', $sale_invoice_text)) 
                    {                
                      preg_match_all('/\d+/', $sale_invoice_text, $matches);
                   
                      if(count($matches[0]) == 2)
                        $sale_invoice_nos = $matches[0][0] . '-' . $matches[0][1];
                    }   
                  } 
                }  
                else
                {
                  if(isset($exepath))
                  {
                    $start_pos = strpos($text, " samlefaktura ")+13;                              
                    $end_pos = strlen($text); 
                    $diff_pos = $end_pos - $start_pos;
                    $sales_invoice_text = substr($text, $start_pos, $diff_pos);

                    $sales_invoice_text = str_replace(array(".", "\r", "\n", "\r\n"), "", $sales_invoice_text);
                    $sale_invoice_nos = trim($sales_invoice_text); 
                  }//localhost
                  else
                  {
                    //MILLARCO - Millarco_PROFORMA_faktura_NOPF52071.pdf
                    $sales_invoice_line = $arraytext[$key + 1];
                    $sales_invoice_text = str_replace(array(".", "\r", "\n", "\r\n"), "", $sales_invoice_line);
                    $sale_invoice_nos = trim($sales_invoice_text); 

                    
                    $invoice_total_line = $arraytext[$key - 2];
                    if (preg_match('/\d+/', $invoice_total_line)) 
                    {
                      $invoice_total_text = str_replace(
                                        array(":", "\r", "\n", "\r\n")
                                        , "", $invoice_total_line);
                      
                      $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);  
                      $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                    }

                  }//server
                }                         
              }

              //Format 11 - HALO DESIGN - SAMLEFAKTURA  NORGE 259.pdf
              if(stripos($text, "Faktura Nummer") !== false)  
              {       
                $which_condition .= 'S-11 ';
              
                if(isset($exepath))
                {
                  $startkey = $key + 6;                
                  $endkey_line = array_keys((array)$arraytext, "Kundenavn\r");
                  $endkey = $endkey_line[0] - 6;

                  for($i = $startkey; $i <= $endkey; $i++)
                  {                  
                    $sale_invoice_no = str_replace(array("_", "\r", "\n", "\r\n"), "", $arraytext[$i]);
                    if($sale_invoice_nos == '')
                      $sale_invoice_nos = $sale_invoice_no;
                    else
                      $sale_invoice_nos .= ',' . $sale_invoice_no;  

                    $i += 5;
                  } 
                } //localhost
                else
                {
                  $startkey = $key;
                  $endkey = count($arraytext) - 1;
                  for($i = $startkey; $i <= $endkey; $i++)
                  {    
                    $sale_invoice_line = $arraytext[$i];
                    if(strpos($sale_invoice_line, "N10") !== false)  
                    {              
                      $sale_invoice_no = str_replace(array("_", "\r", "\n", "\r\n"), "", $sale_invoice_line);
                      if($sale_invoice_nos == '')
                        $sale_invoice_nos = $sale_invoice_no;
                      else
                        $sale_invoice_nos .= ',' . $sale_invoice_no;  
                    }  
                  }
                }//server
              }

              //Format 12 - IMERCO - Samlefaktura NO 11-03-24 SFB8005619.pdf
              if(stripos($text, "Sales Invoice:") !== false)  
              {       
                $which_condition .= 'S-12 ';
              
                if(isset($exepath))
                  $sales_invoice_line = $arraytext[$key + 3]; 
                else
                  $sales_invoice_line = $arraytext[$key + 4]; 
                $sale_invoice_no = str_replace(array("_", "\r", "\n", "\r\n"), "", $sales_invoice_line);
                $sale_invoice_nos = $sale_invoice_no;
              }

              //Format 13 - JUST SUPREME - Economic_ConsolidatedInvoice_2024-03-21_2024-03-22.pdf
              if(stripos($text, "Invoice numbers in this delivery") !== false)  
              {       
                $which_condition .= 'S-13 ';
              
                if(isset($exepath))
                  $sales_invoice_line = $arraytext[$key + 1]; 
                else
                  $sales_invoice_line = $arraytext[$key + 2]; 
                $sale_invoice_nos = str_replace(array("_", "\r", "\n", "\r\n"), "", $sales_invoice_line);               
              }

              //Format 14 - NOSCOMED - 14948FakturaNOSAM2925_250324_094043.pdf
              if ((stripos($text, "Samlefaktura ") !== false && stripos($text, " salgsfaktura ") !== false))
              { 
                $which_condition .= 'S-14 ';
                $sale_invoice_text = $text;  
                
                if(isset($exepath))
                {
                  if (preg_match('/\d+/', $sale_invoice_text)) 
                  {                
                    preg_match_all('/\d+/', $sale_invoice_text, $matches);
                 
                    if(count($matches[0]) > 0)
                      $sale_invoice_nos = $matches[0][0];
                  }
                }//localhost
                else
                {
                  $sales_invoice_line = $arraytext[$key + 1]; 
                  if (preg_match('/\d+/', $sales_invoice_line)) 
                  {
                    $sale_invoice_nos = $sales_invoice_line;
                  }
                }//server
              }

              //Format 15 - OUR UNITS - 220324.pdf
              if (stripos($text, "Document Nos.") !== false)
              { 
                $which_condition .= 'S-15 ';

                if(isset($exepath))
                {
                  $start_pos = strpos($text, "Document Nos. ")+14;                              
                  $end_pos = strlen($text); 
                  $diff_pos = $end_pos - $start_pos;
                  $sales_invoice_text = substr($text, $start_pos, $diff_pos);
                  
                  $sales_invoice_text = str_replace(array(".", "\r", "\n", "\r\n"), "", $sales_invoice_text);
                  $sale_invoice_nos = trim($sales_invoice_text); 
                }//localhost
                else
                {
                  $start_pos = $key + 1;                
                  $end_pos = count($arraytext) - 1;

                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {
                    if (stripos($arraytext[$i], "The exporter of the products") !== false)
                    {
                      $end_pos = $i -1;
                      break;
                    }
                  }//for

                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {    
                    $sale_invoice_line = $arraytext[$i];
                    if(strpos($sale_invoice_line, "NOSIN") !== false)  
                    {              
                      $sale_invoice_no = str_replace(array("_", "\r", "\n", "\r\n"), "", $sale_invoice_line);
                      if($sale_invoice_nos == '')
                        $sale_invoice_nos = $sale_invoice_no;
                      else
                        $sale_invoice_nos .= ',' . $sale_invoice_no;  
                    }  
                  }//for                
                }//server
              }

              //Format 16 - PANDORA KITCHEN LIVING - Proforma 5 07-03-2024.pdf
              if (stripos($text, "Reference til salgsfaktura-nr. :") !== false)
              { 
                $which_condition .= 'S-16 ';

                $sales_invoice_line = $arraytext[$key + 2];                 
                if (preg_match('/\d+/', $sales_invoice_line)) 
                {                
                  preg_match_all('/\d+/', $sales_invoice_line, $matches);
               
                  if(count($matches[0]) > 0)
                  {
                    foreach($matches[0] as $sale_invoice_no)
                    {
                      if($sale_invoice_nos == '')
                        $sale_invoice_nos = $sale_invoice_no;
                      else
                        $sale_invoice_nos .= ',' . $sale_invoice_no;  
                    }
                  }
                }

                $invoice_subtotal_shipping_line = $arraytext[$key + 4];   
                if (preg_match('/\d+/', $invoice_subtotal_shipping_line)) 
                {
                  $invoice_subtotal_shipping_array = explode(' ', $invoice_subtotal_shipping_line);

                  $subtotal_text = str_replace(
                                    array("NOK ", "\r", "\n", "\r\n")
                                    , "", $invoice_subtotal_shipping_array[0]);
                  $ci_details['invoice_amount'] = $this->convertToNumber($subtotal_text);

                  $shipping_text = str_replace(
                                    array("NOK ", "\r", "\n", "\r\n")
                                    , "", $invoice_subtotal_shipping_array[2]);
                  $ci_details['invoice_shipping'] = $this->convertToNumber($shipping_text);
                }

                $invoice_total_line = $arraytext[$key + 5];  
                if (preg_match('/\d+/', $invoice_total_line)) 
                {
                  $total_text = str_replace(
                                    array("NOK ", "\r", "\n", "\r\n")
                                    , "", $invoice_total_line);
                  $ci_details['invoice_total'] = $this->convertToNumber($total_text);
                }    
              }

              //Format 17 - REX HOLM (ID) - Proformafaktura PROF02421.pdf
              if (stripos($text, "Faktura (Ordre)") !== false)
              { 
                $which_condition .= 'S-17 ';

                if(isset($exepath))
                {
                  $start_pos = strpos($text, "Faktura (Ordre) ")+16;                              
                  $end_pos = strlen($text); 
                  $diff_pos = $end_pos - $start_pos;
                  $sales_invoice_text = substr($text, $start_pos, $diff_pos);
                  
                  $sales_invoice_text = str_replace(array(".", ",", "\r", "\n", "\r\n"), "", $sales_invoice_text);
                  $sales_invoice_text = str_replace(")", "),", $sales_invoice_text);
                  $sales_invoice_text = rtrim($sales_invoice_text, ",");
                  $sale_invoice_nos = trim($sales_invoice_text); 
                }//localhost
                else
                {
                  $start_pos = $key + 1;                
                  $end_pos = count($arraytext) - 1;

                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {
                    if (stripos($arraytext[$i], "Faktura (Pakker)") !== false)
                    {
                      $end_pos = $i -1;
                      break;
                    }
                  }//for

                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {    
                    $sale_invoice_line = $arraytext[$i];
                    $sale_invoice_no = str_replace(array("_", "\r", "\n", "\r\n"), "", $sale_invoice_line);
                    if($sale_invoice_nos == '')
                      $sale_invoice_nos = $sale_invoice_no;
                    else
                      $sale_invoice_nos .= ',' . $sale_invoice_no;  
                  }//for  
                }//server
              }

              //Format 18 - RIEKER - 980827682MVA_CI_24802686-24803079_END.pdf
              if ($text == "code number\r" || stripos($text, "page 1 from") !== false)
              { 
                $which_condition .= 'S-18 ';

                $sales_invoice_line = $arraytext[$key - 2];    
                
                $sales_invoice_text = str_replace(array(".", ",", "\r", "\n", "\r\n"), "", $sales_invoice_line);
               
                $sale_invoice_nos = trim($sales_invoice_text);                
              }

              //Format 19 - SECOND FEMALE - IC5118.pdf              
              if (stripos($text, "Opr. fakturanr.") !== false)
              { 
                $which_condition .= 'S-19 ';

                $sales_invoice_line = $arraytext[$key + 1];    
                $sales_invoice_text = str_replace(array(".", ",", "\r", "\n", "\r\n"), "", $sales_invoice_line);

                $sales_invoice_text = str_replace(" ", ",", $sales_invoice_text);
                              
                if($sale_invoice_nos == '')
                  $sale_invoice_nos = $sales_invoice_text;
                else
                  $sale_invoice_nos .= ',' . $sales_invoice_text;  
              }

              //Format 20 - VILLY JENSEN - Report50022.pdf         
              if (stripos($text, "Denne Proforma samlefaktura indeholder ") !== false)
              { 
                $which_condition .= 'S-20 ';

                if(isset($exepath))
                {
                  $start_pos = strpos($text, " fakturanr.: ")+13;                              
                  $end_pos = strlen($text); 
                  $diff_pos = $end_pos - $start_pos;
                  $sales_invoice_text = substr($text, $start_pos, $diff_pos);
                 
                  $sale_invoice_nos = str_replace(array("\r", "\n", "\r\n"), "", $sales_invoice_text);
                }//localhost
                else
                {
                  $start_pos = $key;                
                  $end_pos = count($arraytext) - 1;

                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {
                    if (stripos($arraytext[$i], "Brugstarif") !== false)
                    {
                      $end_pos = $i -1;
                      break;
                    }
                  }//for

                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {    
                    $sale_invoice_line = $arraytext[$i];
                    if (stripos($sale_invoice_line, ":") !== false)
                    {
                      $invoice_nos_text = explode(':', $sale_invoice_line);
                      $sale_invoice_line = $invoice_nos_text[1];
                    }
                    $sale_invoice_no = str_replace(array("_", "\r", "\n", "\r\n"), "", $sale_invoice_line);
                    if($sale_invoice_nos == '')
                      $sale_invoice_nos = $sale_invoice_no;
                    else
                      $sale_invoice_nos .= ',' . $sale_invoice_no;  
                  }//for  
                }//server                                        
              }

              //Format 21 - STOF - Samlefaktura 638 Norge.pdf         
              if (stripos($text, "Denne samlefaktura omhandler faktura numre") !== false)
              { 
                $which_condition .= 'S-21 ';

                $sales_invoice_line = $arraytext[$key + 2];    
                $sales_invoice_text = str_replace(array("\r", "\n", "\r\n"), "", $sales_invoice_line);
                          
                $sale_invoice_nos = $sales_invoice_text;                
              }

              //Format 22 - VERNON SPORT (COMMERCIAL INVOICE ON PAGE 3) - EX0414 -doc56197220240206144006.pdf         
              if (stripos($text, "Samlefakturaen ") !== false && stripos($text, " af folgende fakturaer:") !== false)
              { 
                $which_condition .= 'S-22 ';

                $sales_invoice_line = $arraytext[$key + 1];    
                $sales_invoice_text = str_replace(array("\r", "\n", "\r\n"), "", $sales_invoice_line);
                          
                $sale_invoice_nos = $sales_invoice_text;                
              }

              //Common - sale invoices extract
              if($sale_invoice_nos != '')
              {
                if (stripos($sale_invoice_nos, ",") !== false)
                { 
                  if(stripos($which_condition, "Mixed ") === false)
                  {
                    $which_condition .= 'Mixed ';
                    $invoice_nos = explode(',', $sale_invoice_nos);
                    $sale_invoice_nos = '';
                    foreach($invoice_nos as $invoice_no)
                    {
                      if (stripos($invoice_no, "-") !== false)
                      {
                        $invoice_nos = explode('-', $invoice_no);                      
                        for($i = trim($invoice_nos[0]); $i <= trim($invoice_nos[1]); $i++)
                        {
                          if($sale_invoice_nos == '')
                            $sale_invoice_nos = $i;
                          else
                            $sale_invoice_nos .= ',' . $i;  
                        }
                      }
                      else
                      {
                        if($sale_invoice_nos == '')
                          $sale_invoice_nos = $invoice_no;
                        else
                          $sale_invoice_nos .= ',' . $invoice_no;
                      }
                    }
                  }
                }

                if (stripos($sale_invoice_nos, "-") !== false)
                {    
                  $which_condition .= 'Common- ';

                  $invoice_nos = explode('-', $sale_invoice_nos);
                  $sale_invoice_nos = '';
                  for($i = trim($invoice_nos[0]); $i <= trim($invoice_nos[1]); $i++)
                  {
                    if($sale_invoice_nos == '')
                      $sale_invoice_nos = $i;
                    else
                      $sale_invoice_nos .= ',' . $i;  
                  }
                }                
                
                $invoice_count = explode(',', $sale_invoice_nos);

                $ci_details['sale_invoice_nos'] = $sale_invoice_nos;
                $ci_details['invoice_count'] = count($invoice_count);
              }
              /* --/ SALES INVOICES -- */

              /* -- TOTAL AMOUNT -- */
              // //Format 1 - ***CommercialInvoice_.pdf  
              // if (stripos($text, "T. Price NOK") !== false)                
              // {                
              //   $which_condition .= 'T-1 ';
              //   if (preg_match('/\d+/', $text)) 
              //   {
              //     $total_amount_text = str_replace(
              //                       array("T. Price NOK ", "\r", "\n", "\r\n")
              //                       , "", $text);

              //     $total_amount_array = explode(' ', $total_amount_text);

              //     if(count($total_amount_array) == 3)
              //     {
              //       $ci_details['invoice_amount'] = $this->convertToNumber($total_amount_array[0]);
              //       $ci_details['invoice_shipping'] = $this->convertToNumber($total_amount_array[1]);
              //       $ci_details['invoice_total'] = $this->convertToNumber($total_amount_array[2]);
              //     }
              //   }
              // }
              //Format 1 - ***CommercialInvoice_.pdf 
              if (stripos($text, "Commercial Invoice based on the Sale Invoices: ") !== false)  
              { 
                $which_condition .= 'T-1 ';

                $start_pos = 0;                
                $end_pos = $key - 1;

                for($i = $end_pos; $i >= 0; $i--)
                {
                  if (stripos($arraytext[$i], "T. Price NOK") !== false)  
                  {
                    $start_pos = $i;
                    break;
                  }  
                }

                $y = 0;
                $_total_key = 0;
                for($i = $start_pos; $i <= $end_pos; $i++)
                {
                  $invoice_total_shipping_line = $arraytext[$i];
                  if ($invoice_total_shipping_line == "Total")
                    $_total_key = $i;

                  if (preg_match('/\d+/', $invoice_total_shipping_line)) 
                  {
                    $total_shipping_text = str_replace(
                                      array("T. Price NOK ", "\r", "\n", "\r\n")
                                      , "", $invoice_total_shipping_line);

                    $total_amount_array = explode(' ', $total_shipping_text);

                    if(count($total_amount_array) == 1)
                    {
                      if($y == 0)
                        $ci_details['invoice_amount'] = $this->convertToNumber($total_amount_array[0]);
                      else if($y == 1)
                        $ci_details['invoice_shipping'] = $this->convertToNumber($total_amount_array[0]);
                      else if($y == 2)
                        $ci_details['invoice_total'] = $this->convertToNumber($total_amount_array[0]);

                      $y++;
                    }
                    else if(count($total_amount_array) == 2)
                    {
                      $ci_details['invoice_shipping'] = $this->convertToNumber($total_amount_array[0]);
                      $ci_details['invoice_total'] = $this->convertToNumber($total_amount_array[1]);
                    }
                    else if(count($total_amount_array) == 3)
                    {
                      $ci_details['invoice_amount'] = $this->convertToNumber($total_amount_array[0]);
                      $ci_details['invoice_shipping'] = $this->convertToNumber($total_amount_array[1]);
                      $ci_details['invoice_total'] = $this->convertToNumber($total_amount_array[2]);
                    }                    
                  }
                }

                if($ci_details['invoice_amount'] == 0)
                {
                  if($_total_key != 0)
                  {
                    $invoice_subtotal_line = $arraytext[$_total_key - 3];
                    $ci_details['invoice_amount'] = $this->convertToNumber($invoice_subtotal_line);

                    $invoice_shipping_line = $arraytext[$_total_key - 2];
                    $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_shipping_line);

                    $ci_details['invoice_total'] = $ci_details['invoice_amount'] + $ci_details['invoice_shipping'];
                  }
                }

                //$diff_pos = $end_pos - $start_pos;
                //$total_amount_text = substr($text, $start_pos, $diff_pos);
              }

              //Format 2 - BECKSONDERGAARD ApS - NIC00924.pdf, CM DISTRIBUTION DENMARK - Invoice 100677.pdf
              if (stripos($text, "Replacement statements serving as proof of preferential origin") !== false)  
              {
                $which_condition .= 'T-2 ';
                $total_amount_line = $arraytext[$key - 2];
                if (preg_match('/\d+/', $total_amount_line)) 
                {
                  $total_amount_text = str_replace(
                                      array("\r", "\n", "\r\n", ",")
                                      , "", $total_amount_line);

                  $ci_details['invoice_amount'] = (float)trim($total_amount_text);
                  $ci_details['invoice_total'] = $ci_details['invoice_amount'];   
                }
              }

              if (stripos($text, "Invoice Total") !== false)  
              {                
                // if(count($arraytext) >= ($key+8))
                // {
                //   $which_condition .= 'T-2 ';
                //   //BECKSONDERGAARD ApS - NIC00924.pdf
                //   $total_amount_line = $arraytext[$key+8];
                //   if (preg_match('/\d+/', $total_amount_line)) 
                //   {
                //     $total_amount_text = str_replace(
                //                       array("\r", "\n", "\r\n", ",")
                //                       , "", $total_amount_line);
                    
                //     $ci_details['invoice_amount'] = (float)trim($total_amount_text);
                //     $ci_details['invoice_total'] = $ci_details['invoice_amount'];                                
                //   }
                // }
                // else
                // {
                  //CM DISTRIBUTION DENMARK - Invoice 100677.pdf                 
                  $which_condition .= 'T-8-2 ';  
                  if(isset($exepath)) 
                    $invoice_total_line = $arraytext[$key + 1];       
                  else
                    $invoice_total_line = $arraytext[$key + 4];       
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {                  
                    $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_total_line);
                    
                    $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);                    
                  }
                //}
              }

              //Format 3 - BERENDSOHN AG - 983799620MVA_CI_0000041790_END.pdf
              if (stripos($text, "Samples of no commercial value") !== false)  
              {
                $which_condition .= 'T-3 ';
                if(isset($exepath))                
                  $total_amount_line = $arraytext[$key - 2];
                else                
                  $total_amount_line = $arraytext[$key - 6];
                
                if (preg_match('/\d+/', $total_amount_line)) 
                {
                  $total_amount_text = str_replace(
                                    array("\r", "\n", "\r\n", "*")
                                    , "", $total_amount_line);
                
                  $total_amount_array = explode(' ', $total_amount_text);
  
                  if(count($total_amount_array) == 1)
                  {
                    $ci_details['invoice_amount'] = $this->convertToNumber($total_amount_array[0]);                    
                    $ci_details['invoice_total'] = $ci_details['invoice_amount'];
                  }
                  else if(count($total_amount_array) == 3)
                  {
                    $ci_details['invoice_amount'] = $this->convertToNumber($total_amount_array[1]);                   
                    $ci_details['invoice_total'] = $ci_details['invoice_amount'];
                  }                
                }
              }
              
              //Format 4 - BESSIE - Samlefaktura 22-03-24.pdf
              if (stripos($text, "FOR RIGTIGHEDEN ") !== false)  
              {
                if(isset($exepath))
                { 
                  $which_condition .= 'T-4 ';
                  $total_amount_line = $arraytext[$key - 2];
                  if (preg_match('/\d+/', $total_amount_line)) 
                  {
                    $total_amount_text = str_replace(
                                      array("\r", "\n", "\r\n", "*")
                                      , "", $total_amount_line);
                                                      
                    $ci_details['invoice_total'] = $this->convertToNumber($total_amount_text);                     
                    if($ci_details['invoice_shipping'])       
                      $ci_details['invoice_amount'] = $ci_details['invoice_total'] - $ci_details['invoice_shipping'];
                  }
                } //localhost
                else
                {
                  $which_condition .= 'S-5 ';
                  $sales_invoice_line = $arraytext[$key - 2];
                  $sales_invoice_array = explode(' ', $sales_invoice_line);
                  
                  if(count($sales_invoice_array) >= 3)
                  {
                    $length = count($sales_invoice_array);
                    $sale_invoice_nos = $sales_invoice_array[$length-3] . '-' . $sales_invoice_array[$length-1];
                  }

                  $total_amount_line = $arraytext[$key - 14];
                  if (preg_match('/\d+/', $total_amount_line)) 
                  {
                    $total_amount_text = str_replace(
                                      array("\r", "\n", "\r\n", "*")
                                      , "", $total_amount_line);
                                                      
                    $ci_details['invoice_total'] = $this->convertToNumber($total_amount_text);                     
                    if($ci_details['invoice_shipping'])       
                      $ci_details['invoice_amount'] = $ci_details['invoice_total'] - $ci_details['invoice_shipping'];
                  }
                  
                }//server
              }
              //Format 4-1
              if (stripos($text, "Fragt GLS Norge") !== false)  
              {
                $which_condition .= 'T-4-1 ';
                if(isset($exepath))
                { 
                  $shipping_amount_array = explode(' ', $text);

                  if(count($shipping_amount_array) > 0)
                  {
                    foreach($shipping_amount_array as $keyi => $shipping_amount_text)
                    {
                      if (preg_match('/\d+/', $shipping_amount_text)) 
                      {
                        $shipping_amount = str_replace(
                                      array("\r", "\n", "\r\n")
                                      , "", $shipping_amount_text);  
                        $ci_details['invoice_shipping'] = $this->convertToNumber($shipping_amount);                      
                      }
                    }
                  }      
                } //localhost
                else
                {
                  $shipping_amount_text = $arraytext[$key - 4];
                  if (preg_match('/\d+/', $shipping_amount_text)) 
                  {
                    $shipping_amount = str_replace(
                                  array("\r", "\n", "\r\n")
                                  , "", $shipping_amount_text);
                    $ci_details['invoice_shipping'] = $this->convertToNumber($shipping_amount);                      
                  }
                }//server
              }

              //Format 5 - BLACK COLOUR - NO-337.pdf
              if (stripos($text, "Subtotal:") !== false)  
              {         
                $which_condition .= 'T-5 ';
                if(isset($exepath))
                {
                  if (preg_match('/\d+/', $text)) 
                  {
                    $invoice_amount_text = str_replace(
                                      array("Subtotal: ", "NOK", ",", "\r", "\n", "\r\n")
                                      , "", $text);
                    
                    $ci_details['invoice_amount'] = (float)trim($invoice_amount_text);                  
                  }
                } //localhost
                else
                {
                  $invoice_amount_line = $arraytext[$key + 2];         
                  if (preg_match('/\d+/', $invoice_amount_line)) 
                  {
                    $invoice_amount_text = str_replace(
                                      array(",", "NOK", "\r", "\n", "\r\n")
                                      , "", $invoice_amount_line);
                                      
                    $ci_details['invoice_amount'] = (float)trim($invoice_amount_text);              
                  }
                }//server
              }
              //Format 5-1
              if (stripos($text, "Shipping:") !== false)  
              {       
                $which_condition .= 'T-5-1 ';     
                if(isset($exepath))
                {
                  if (preg_match('/\d+/', $text)) 
                  {
                    $shipping_amount_text = str_replace(
                                      array("Shipping: ", "NOK", ",", "\r", "\n", "\r\n")
                                      , "", $text);
                    
                    $ci_details['invoice_shipping'] = (float)trim($shipping_amount_text);                  
                  }
                } //localhost
                else
                {
                  $shipping_amount_line = $arraytext[$key + 2];         
                  if (preg_match('/\d+/', $shipping_amount_line)) 
                  {
                    $shipping_amount_text = str_replace(
                                      array(",", "NOK", "\r", "\n", "\r\n")
                                      , "", $shipping_amount_line);
                                      
                    $ci_details['invoice_shipping'] = (float)trim($shipping_amount_text);              
                  }
                }//server  
              }
              //Format 5-2
              if (stripos($text, "Total price ex. vat:") !== false)  
              {         
                $which_condition .= 'T-5-2 ';   
                if(isset($exepath))
                { 
                  if (preg_match('/\d+/', $text)) 
                  {
                    $total_amount_text = str_replace(
                                      array("Total price ex. vat:", ",", "NOK", "\r", "\n", "\r\n")
                                      , "", $text);
                                      
                    $ci_details['invoice_total'] = (float)trim($total_amount_text);              
                  }
                } //localhost
                else
                {
                  $total_amount_line = $arraytext[$key + 2];         
                  if (preg_match('/\d+/', $total_amount_line)) 
                  {
                    $total_amount_text = str_replace(
                                      array(",", "NOK", "\r", "\n", "\r\n")
                                      , "", $total_amount_line);
                                      
                    $ci_details['invoice_total'] = (float)trim($total_amount_text);              
                  }
                } //server   
              }
              //Format 5-3
              if (stripos($text, "Handling fee:") !== false)  
              {       
                $which_condition .= 'T-5-3 ';
                $handling_fee_line = $arraytext[$key + 2];         
                if (preg_match('/\d+/', $handling_fee_line)) 
                {
                  $handling_fee_text = str_replace(
                                    array(",", "NOK", "\r", "\n", "\r\n")
                                    , "", $handling_fee_line);
                                    
                  $ci_details['invoice_handling_fee'] = (float)trim($handling_fee_text);              
                }
              }

              //Format 6 - BODO MOLLER CHEMIE - DK618744.pdf
              if (stripos($text, "We hereby contradict by order given purchase and delivery terms") !== false)  
              {    
                $which_condition .= 'T-6 ';  
                if(isset($exepath))
                {
                  $invoice_total_line = $arraytext[$key - 2];       
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {
                    $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_total_line);
                    
                    $ci_details['invoice_amount'] = $this->convertToNumber($invoice_total_text);    
                    $ci_details['invoice_total'] = $ci_details['invoice_amount'];               
                  }
                }//localhost
                else
                {
                  $invoice_total_line = $arraytext[count($arraytext)-1];       
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {
                    $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_total_line);
                    
                    $ci_details['invoice_amount'] = $this->convertToNumber($invoice_total_text);    
                    $ci_details['invoice_total'] = $ci_details['invoice_amount'];               
                  }
                }//server
              }

              //Format 7 - BYIC - consolidated-invoice-9895-2024-03-22-14-01-11.pdf
              if (stripos($text, "Total invoice value in ") !== false)  
              {    
                $which_condition .= 'T-7 ';  
                if(isset($exepath))
                {
                  $invoice_total_line = $arraytext[$key + 4];       
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {
                    $total_amount_array = explode(' ', $invoice_total_line);

                    $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $total_amount_array[1]);
                    
                    $ci_details['invoice_amount'] = (float)trim($invoice_total_text);    
                    $ci_details['invoice_total'] = $ci_details['invoice_amount'];               
                  }
                }//localhost
                else
                {
                  $invoice_total_line = $arraytext[$key + 3];       
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {                    
                    $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_total_line);
                    
                    $ci_details['invoice_amount'] = (float)trim($invoice_total_text);    
                    $ci_details['invoice_total'] = $ci_details['invoice_amount'];               
                  }
                }//server
              }

              //Format 8 - CM DISTRIBUTION DENMARK - Invoice 100677.pdf
              if (stripos($text, "Amount") !== false && stripos($text, "Total Amount") === false)  
              {    
                $which_condition .= 'T-8 '; 
                if(isset($exepath))
                { 
                  $invoice_amount_line = $arraytext[$key + 1];       
                  if (preg_match('/\d+/', $invoice_amount_line)) 
                  {                  
                    $invoice_amount_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_amount_line);
                    
                    $ci_details['invoice_amount'] = $this->convertToNumber($invoice_amount_text);                      
                  }
                }//localhost
                else
                {
                  if(!array_key_exists('invoice_amount', $ci_details))  
                  {
                    $invoice_amount_line = $arraytext[$key + 6];       
                    if (preg_match('/\d+/', $invoice_amount_line)) 
                    {                  
                      $invoice_amount_text = str_replace(
                                        array(":", "\r", "\n", "\r\n")
                                        , "", $invoice_amount_line);
                      
                      $ci_details['invoice_amount'] = $this->convertToNumber($invoice_amount_text);                      
                    }
                  }
                }//server
              }
              //Format 8-1
              if (stripos($text, "Freight") !== false)  
              {    
                $which_condition .= 'T-8-1 ';  
                if(isset($exepath))
                {
                  $invoice_shipping_line = $arraytext[$key + 1];       
                  if (preg_match('/\d+/', $invoice_shipping_line)) 
                  {                  
                    $invoice_shipping_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_shipping_line);
                    
                    $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_shipping_text);                      
                  }
                }//localhost
                else
                {
                  if(!array_key_exists('invoice_shipping', $ci_details))  
                  {
                    if (stripos($text, "Freight cost") !== false || $text == "Freight") 
                    {      
                      if ($text == "Freight Cost") 
                        $invoice_shipping_line = $arraytext[$key + 4];       
                      else         
                        $invoice_shipping_line = $arraytext[$key + 2];       
                    }
                    else
                      $invoice_shipping_line = $arraytext[$key + 6];       
                    if (preg_match('/\d+/', $invoice_shipping_line)) 
                    {                  
                      $invoice_shipping_text = str_replace(
                                        array(":", "\r", "\n", "\r\n")
                                        , "", $invoice_shipping_line);
                      
                      $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_shipping_text);                      
                    }
                  }

                  if(!array_key_exists('invoice_amount', $ci_details))  
                  {
                    if (stripos($text, "Freight cost") !== false || $text == "Freight")                
                      $invoice_subtotal_line = $arraytext[$key - 2];       
                        
                    if (preg_match('/\d+/', $invoice_subtotal_line)) 
                    {                  
                      $invoice_subtotal_text = str_replace(
                                        array(":", "\r", "\n", "\r\n")
                                        , "", $invoice_subtotal_line);
                      
                      $ci_details['invoice_amount'] = $this->convertToNumber($invoice_subtotal_text);                      
                    }
                  }
                }//server
              }
             
              //Format 9 - GUARDIAN PROTECTION PRODUCTS - 1448.pdf, MILLARCO - Millarco_PROFORMA_faktura_NOPF52071.pdf, SEBRA INTERIOR - Samlefaktura 11231.pdf, OUR UNITS - 220324.pdf
              if (stripos($text, "Total NOK") !== false && stripos($text, "Subtotal NOK") === false)  
              {    
                $which_condition .= 'T-9 ';  

                //GUARDIAN PROTECTION PRODUCTS - 1448.pdf
                if (stripos($text, ":") !== false)  
                {
                  if(isset($exepath))
                    $invoice_total_line = $arraytext[$key + 1];   
                  else    
                    $invoice_total_line = $arraytext[$key + 2];       
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {                  
                    $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_total_line);
                    
                    $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);                      
                  }

                  if(isset($exepath))
                    $invoice_shipping_line = $arraytext[$key - 1];       
                  else
                    $invoice_shipping_line = $arraytext[$key - 9];       
                  if (preg_match('/\d+/', $invoice_shipping_line)) 
                  {                  
                    $invoice_shipping_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_shipping_line);
                    
                    $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_shipping_text);                      
                  }

                  $ci_details['invoice_amount'] = $ci_details['invoice_total'] - $ci_details['invoice_shipping']; 
                }
                else
                {
                  //MILLARCO - Millarco_PROFORMA_faktura_NOPF52071.pdf, OUR UNITS - 220324.pdf
                  $invoice_total_line = $arraytext[$key + 2];
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {
                    $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_total_line);
                    
                    $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);  
                    $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                  }
                  
                  if(array_key_exists('invoice_total', $ci_details))  
                  {
                    if($ci_details['invoice_total'] == 0)
                    {
                      //SEBRA INTERIOR - Samlefaktura 11231.pdf
                      $invoice_total_line = $arraytext[$key + 4]; 
                      if (preg_match('/\d+/', $invoice_total_line)) 
                      {
                        $invoice_total_text = str_replace(
                                          array(":", "\r", "\n", "\r\n")
                                          , "", $invoice_total_line);
                        
                        $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);  
                        $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                      }
                    }
                  }
                  else
                  {
                    //SEBRA INTERIOR - Samlefaktura 11231.pdf
                    $invoice_total_line = $arraytext[count($arraytext) - 1]; 
                    if (preg_match('/\d+/', $invoice_total_line)) 
                    {
                      $invoice_total_text = str_replace(
                                        array(":", "\r", "\n", "\r\n")
                                        , "", $invoice_total_line);
                      
                      $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);  
                      $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                    }
                  }
                }
              }
              //Format 9-1
              if (stripos($text, "Subtotal NOK") !== false)  
              {    
                $which_condition .= 'T-9-1 '; 

                $invoice_subtotal_line = $arraytext[$key + 2];
                if (preg_match('/\d+/', $invoice_subtotal_line)) 
                {
                  $invoice_subtotal_text = str_replace(
                                    array(":", "\r", "\n", "\r\n")
                                    , "", $invoice_subtotal_line);
                  
                  $ci_details['invoice_amount'] = $this->convertToNumber($invoice_subtotal_text);                   
                }
              }

              //Format 10 - HALO DESIGN - SAMLEFAKTURA  NORGE 259.pdf
              if (stripos($text, "Samlet") !== false)  
              {    
                $which_condition .= 'T-10 ';  
                if(isset($exepath))
                {
                  $invoice_total_line = $arraytext[$key - 5];       
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {       
                    $total_amount_array = explode(' ', $invoice_total_line);

                    $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $total_amount_array[0]);
                   
                    $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);                      
                    $ci_details['invoice_amount'] = $ci_details['invoice_total'];  
                  }
                }//localhost
                else
                {
                  $start_pos = $key;                
                  $end_pos = count($arraytext) - 1; 

                  for($i = $start_pos; $i <= $end_pos; $i++)
                  {
                    $invoice_total_line = $arraytext[$i];
                    if (preg_match('/\d+/', $invoice_total_line)) 
                    {
                      if (stripos($invoice_total_line, ",") !== false) 
                      {
                        $invoice_total_text = str_replace(
                                      array(":", "\r", "\n", "\r\n")
                                      , "", $invoice_total_line);
                   
                        $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);                      
                        $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                      }
                    }
                  }
                }//server
              }

              //Format 11 - HORN BORDPLADER - 2024-02-01 Proformafaktura 6205.pdf
              if (stripos($text, "Total bel") !== false)  
              {    
                $which_condition .= 'T-11 ';  

                if(isset($exepath))
                {
                  $start_pos = strpos($text, "Total bel")+12;                
                  $end_pos = strlen($text); 
                  $diff_pos = $end_pos - $start_pos;
                  $total_amount_text = substr($text, $start_pos, $diff_pos);

                  $ci_details['invoice_total'] = $this->convertToNumber($total_amount_text); 
                  $ci_details['invoice_amount'] = $ci_details['invoice_total'];     
                } //localhost
                else
                {
                  $invoice_total_line = $arraytext[$key + 1];
                  $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_line); 
                  $ci_details['invoice_amount'] = $ci_details['invoice_total'];    
                }//server
              }
              //Format 11-1
              if (stripos($text, "Fragt ") !== false)  
              {    
                //if(!$ci_details['invoice_shipping'])
                if(!array_key_exists('invoice_shipping', $ci_details))
                {
                  $which_condition .= 'T-11-1 ';  

                  if(isset($exepath))                  
                    $invoice_shipping_text = $arraytext[$key + 2];       
                  else                 
                    $invoice_shipping_text = $arraytext[$key + 3];       
                 
                  $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_shipping_text);       
                }           
              }

              //Format 12 - IMERCO - Samlefaktura NO 11-03-24 SFB8005619.pdf, JUST SUPREME - Economic_ConsolidatedInvoice_2024-03-21_2024-03-22.pdf
              if (stripos($text, "Total Freight cost") !== false || stripos($text, "Total invoice value") !== false)  
              {    
                $which_condition .= 'T-12 ';  

                if (stripos($text, "Subtotal Freight cost Total invoice value") !== false)  
                {
                  //JUST SUPREME - Economic_ConsolidatedInvoice_2024-03-21_2024-03-22.pdf
                  $invoice_subtotal_shipping_line = $arraytext[$key + 2];  
                  if (preg_match('/\d+/', $invoice_subtotal_shipping_line)) 
                  {
                    $invoice_subtotal_shipping_array = explode(' ', $invoice_subtotal_shipping_line);

                    if(count($invoice_subtotal_shipping_array) > 1)
                    {
                      $ci_details['invoice_amount'] = $this->convertToNumber($invoice_subtotal_shipping_array[0]); 
                      $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_subtotal_shipping_array[1]); 
                    }
                  }

                  $invoice_total_line = $arraytext[$key + 3]; 
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {
                    $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_line); 
                  } 
                }
                else if (stripos($text, "Total invoice value") !== false)  
                {
                  //JUST SUPREME - Economic_ConsolidatedInvoice_2024-03-21_2024-03-22.pdf
                  $invoice_total_line = $arraytext[$key + 2];  
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {
                    $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_line); 
                  }
                }
                else
                {
                  //IMERCO - Samlefaktura NO 11-03-24 SFB8005619.pdf
                  if(isset($exepath))
                  {
                    $invoice_total_shipping_line = $arraytext[$key + 3];       
                    if (preg_match('/\d+/', $invoice_total_shipping_line)) 
                    {                        
                      $invoice_total_shipping_text = str_replace(
                                        array(":", "\r", "\n", "\r\n")
                                        , "", $invoice_total_shipping_line);
                      
                      $total_shipping_amount_array = explode(' ', $invoice_total_shipping_line);

                      $ci_details['invoice_shipping'] = $this->convertToNumber($total_shipping_amount_array[0]);    
                      $ci_details['invoice_total'] = $this->convertToNumber($total_shipping_amount_array[1]); 
                      $ci_details['invoice_amount'] = $ci_details['invoice_total'] - $ci_details['invoice_shipping'];                     
                    }
                  }//locahost
                  else
                  {
                    $invoice_total_line = $arraytext[$key + 15]; 
                    if (preg_match('/\d+/', $invoice_total_line)) 
                    {                        
                      $invoice_total_text = str_replace(
                                        array(":", "\r", "\n", "\r\n")
                                        , "", $invoice_total_line);
                                                                  
                      $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);                                       
                    }

                    $invoice_shipping_line = $arraytext[$key + 14];
                    if (preg_match('/\d+/', $invoice_shipping_line)) 
                    {                        
                      $invoice_shipping_text = str_replace(
                                        array(":", "\r", "\n", "\r\n")
                                        , "", $invoice_shipping_line);
                                            
                      $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_shipping_text);
                    }

                    $ci_details['invoice_amount'] = $ci_details['invoice_total'] - $ci_details['invoice_shipping'];                       
                  }//server
                }
              }

              //Format 13 - LYNGSOE RAINWEAR - NOS-003113 SHIPMENT - SF-1300197.pdf
              //if (stripos($text, "Replacement Statement") !== false)  
              if ($text == "Replacement Statement\r" || $text == "Replacement Statement")  
              {    
                $which_condition .= 'T-13 ';  

                $invoice_total_line = $arraytext[$key - 2]; 
                if (preg_match('/\d+/', $invoice_total_line)) 
                {
                  $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_line); 
                  $ci_details['invoice_amount'] = $ci_details['invoice_total']; 
                }               
              }

              //Format 14 - OUR UNITS - 220324.pdf
              if (stripos($text, "Number of Shipment") !== false)  
              {    
                $which_condition .= 'T-14 ';  

                $invoice_subtotal_line = $arraytext[$key - 4]; 
                if (preg_match('/\d+/', $invoice_subtotal_line)) 
                {
                  $invoice_subtotal_array = explode(' ', $invoice_subtotal_line);

                  $ci_details['invoice_amount'] = $this->convertToNumber($invoice_subtotal_array[count($invoice_subtotal_array)-1]);
                } 

                $invoice_total_shipping_line = $arraytext[$key - 3]; 
                if (preg_match('/\d+/', $invoice_total_shipping_line)) 
                {
                  $invoice_total_shipping_array = explode(' ', $invoice_total_shipping_line);

                  $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_total_shipping_array[0]);
                  $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_shipping_array[1]);
                }               
              }

              //Format 15 - REX HOLM (ID) - Proformafaktura PROF02421.pdf
              if (stripos($text, "I alt ") !== false)  
              {    
                $which_condition .= 'T-15 ';  

                if (stripos($text, "I alt NOK-SALG ekskl. moms") !== false)  
                {
                  $invoice_total_line = $arraytext[$key + 2]; 
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {
                    $invoice_total_array = explode(' ', $invoice_total_line);

                    $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_array[count($invoice_total_array)-1]);
                  }  

                  $invoice_shipping_line = $arraytext[$key - 4]; 
                  $invoice_shipping_array = explode(' ', $invoice_shipping_line);
                  $invoice_shipping_text = $invoice_shipping_array[count($invoice_shipping_array)-1]; 
                  if (preg_match('/\d+/', $invoice_shipping_text)) 
                  {
                    $ci_details['invoice_shipping'] = $this->convertToNumber($invoice_shipping_text);
                    $ci_details['invoice_amount'] = $ci_details['invoice_total'] - $ci_details['invoice_shipping'];
                  }
                }
                else
                {
                  $invoice_total_line = $arraytext[$key + 2]; 
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {
                    $invoice_total_array = explode(' ', $invoice_total_line);

                    $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_array[count($invoice_total_array)-1]);
                    $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                  }   
                }                          
              }

              //Format 16 - RIEKER - 980827682MVA_CI_24802686-24803079_END.pdf
              if (stripos($text, "total value") !== false)  
              {    
                $which_condition .= 'T-16 ';  

                if(isset($exepath))
                {
                  $invoice_total_array = explode(' ', $text);
                  foreach($invoice_total_array as $invoice_total_text)
                  {                
                    if (preg_match('/\d+/', $invoice_total_text)) 
                    {                    
                      $ci_details['invoice_total'] = $this->convertToNumber($invoice_total_text);
                      $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                    }     
                  } 
                } //localhost
                else
                {
                  $invoice_total_line = $arraytext[$key + 4];   
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {                    
                    $total_text = str_replace(
                                        array("NOK ", "\r", "\n", "\r\n")
                                        , "", $invoice_total_line);
                    $ci_details['invoice_total'] = $this->convertToNumber($total_text);
                    $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                  }                  
                }//server
              }

              //Format 17 - AUBO - ESCANI1680187f26c10c4-8d15-4da6-a59e-7a267e6d42a2.pdf
              if (stripos($text, "NOK") !== false)  
              {       
                $which_condition .= 'T-17 ';  

                $invoice_total_before_line = $arraytext[$key - 1];
                if (stripos($invoice_total_before_line, "Delivered at place") !== false)  
                  $invoice_total_line = $arraytext[$key + 1]; 

                if(isset($invoice_total_line))
                {
                  if (preg_match('/\d+/', $invoice_total_line)) 
                  {                    
                    $total_text = str_replace(
                                        array("NOK ", "\r", "\n", "\r\n")
                                        , "", $invoice_total_line);
                    $ci_details['invoice_total'] = $this->convertToNumber($total_text);
                    $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                  }   
                }                           
              }

              //Format 18 - STOF - Samlefaktura 638 Norge.pdf         
              if (stripos($text, "Eksportoren af varer der en omfattet af") !== false)
              { 
                $which_condition .= 'T-18 ';

                $invoice_total_line = $arraytext[$key - 1];    
                $invoice_total_text = str_replace(array("\r", "\n", "\r\n"), "", $invoice_total_line);
                          
                if (preg_match('/\d+/', $invoice_total_text)) 
                {                    
                  $total_text = str_replace(
                                      array("NOK ", "\r", "\n", "\r\n")
                                      , "", $invoice_total_text);
                  $ci_details['invoice_total'] = $this->convertToNumber($total_text);
                  $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                }                 
              }

              //Format 19 - VERNON SPORT (COMMERCIAL INVOICE ON PAGE 3) - EX0414 -doc56197220240206144006.pdf         
              if (stripos($text, "Declaration of origin :") !== false)
              { 
                $which_condition .= 'T-19 ';

                $invoice_total_line = $arraytext[$key - 1];    
                $invoice_total_text = str_replace(array("\r", "\n", "\r\n"), "", $invoice_total_line);
                          
                if (preg_match('/\d+/', $invoice_total_text)) 
                {                    
                  $total_text = str_replace(
                                      array("NOK ", "\r", "\n", "\r\n")
                                      , "", $invoice_total_text);
                  $ci_details['invoice_total'] = $this->convertToNumber($total_text);
                  $ci_details['invoice_amount'] = $ci_details['invoice_total'];
                }                 
              }
              /* --/ TOTAL AMOUNT -- */
                           
            }//for

            if (count($ci_details) > 0)  
            {
              if (stripos($ci_details['sale_invoice_nos'], "***") !== false)   
              {
                $which_condition .= 'final*replace ';             
                $ci_details['sale_invoice_nos'] = str_replace("***", "-", $ci_details['sale_invoice_nos']);
              }

              if(array_key_exists('invoice_amount', $ci_details))  
              {
                if ($ci_details['invoice_amount'] == 0)   
                  $ci_details['invoice_amount'] = $ci_details['invoice_total'] - $ci_details['invoice_shipping'];
              }
              else
              {
                $ci_details['invoice_amount'] = $ci_details['invoice_total'] - $ci_details['invoice_shipping'];
              }
            }
            $ci_details['which_condition'] = $which_condition;
          }//pdftext

          
          //dd($ci_details);

          return $ci_details;
        }//ci
        else if($file_type == 'cdf')
        {
          if($pdftext)       
          {
            $org_no = "";
            $arraytext = explode("\n", $pdftext);

            foreach($arraytext as $text)
            {                  
              if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $text)) 
              {                
                $org_no = preg_replace('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', '', $text);
                
                if (stripos("nr.", $org_no) !== false)  
                  break;
              }            
            }

            dd($org_no);
          }
        }//cargo decalaration files
        else
        {
          $number = "";      
          if($pdftext)       
          {
            $arraytext = explode("\n", $pdftext);
                      
            foreach($arraytext as $text)
            {                  
              if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $text)) 
              {                
                $number = preg_replace('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', '', $text);
                
                if (stripos("Month total:", $number) !== false)  
                  break;
              }            
            }
          } //pdf has text
          else
          {            
            $_o_file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $_o_file_extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
          
            $storage_path = storage_path("app/public/pdfs/");
            
            /*Convert PDF to Image - Only First Page*/

            $pdfPath = $file->storeAs('public/pdfs', $file->getClientOriginalName());

            // // Get the full path to the uploaded PDF
            $fullPdfPath = storage_path('app/' . $pdfPath);

            // // Set the output folder to save the image            
            // $outputFolder = storage_path('app/images/');
            // if (!file_exists($outputFolder)) {
            //     mkdir($outputFolder, 0777, true); // Create folder if it doesn't exist
            // }
                  
            $gs_path = "/usr/bin/gs";
            if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
              $gs_path = "C:/Program Files/gs/gs10.03.1/bin/gswin64c.exe";
            
            \Org_Heigl\Ghostscript\Ghostscript::setGsPath($gs_path);
            $gs = new \Org_Heigl\Ghostscript\Ghostscript();
            $gs->setDevice('png')
               ->setInputFile($fullPdfPath) 
               ->setOutputFile($storage_path . $_o_file_name . '.png')
               ->setResolution(300)
               ->setTextAntiAliasing(\Org_Heigl\Ghostscript\Ghostscript::ANTIALIASING_HIGH);

            try
            {                
              if (true === $gs->render())             
                Storage::disk('public')->delete('pdfs/' .$file->getClientOriginalName());              
            } //try
            catch (\Exception $e) 
            {   
              dd($e);
              return "error";
            }
            /*end Convert PDF to Image - Only First Page*/

            /*Extract text from image*/
            $textractClient = new TextractClient([
                'version' => 'latest',
                'region' => 'us-east-1',//'eu-north-1', // pass your region
                'credentials' => [
                    'key'    => config('services.ses.key'),
                    'secret' => config('services.ses.secret')
                ]
            ]);
           
            $filename = $storage_path . $_o_file_name .".png";

            $file = fopen($filename, "rb");
            $contents = fread($file, filesize($filename));
            fclose($file);
            $options = [
                'Document' => [
                'Bytes' => $contents
                ],
                'FeatureTypes' => ['FORMS'], // REQUIRED
            ];
            $result = $textractClient->analyzeDocument($options);
                      
            $blocks = $result['Blocks'];
            // Loop through all the blocks:
            foreach ($blocks as $key => $value) {
              if (isset($value['BlockType']) && $value['BlockType']) {
                $blockType = $value['BlockType'];
                if (isset($value['Text']) && $value['Text']) {
                  $text = $value['Text'];

                  if ($blockType == 'LINE')
                  {
                    if($pdftext == '')
                      $pdftext .= $text;
                    else
                      $pdftext .= "\n" . $text;
                  }                                
                }
              }
            }
            Storage::disk('public')->delete('pdfs/' . $_o_file_name .".png");
            /*end Extract text from image*/

            if($pdftext)       
            {
              $arraytext = explode("\n", $pdftext);
                        
              foreach($arraytext as $text)
              {                  
                if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $text)) 
                {                
                  $number = preg_replace('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', '', $text);
                  
                  if (stripos("Month total:", $number) !== false)  
                    break;
                }            
              }
            } //pdf has text

          } //pdf null
          
          $month_total = trim($number);
          if(is_numeric($month_total))
            return $month_total;
          else
          {
            if(is_float($month_total))
              return $month_total;
            else
              return 0;
          }
          //return (is_numeric($number)) ? $number : 0;        
        }//PIVS, C79
    } 

    public function convertToNumber($value)
    {   
      $removedot = str_replace('.','',$value);
      $number = str_replace(',','.',$removedot);

      return (float)$number;
    }    
    /* --/ EXTRACT From PDF Text -- */
}
