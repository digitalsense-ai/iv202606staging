<?php

namespace App\Classes;

use Spatie\PdfToText\Pdf as PdfExtract;

use Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

use App\Classes\CommonClass;

class SwissImportReconciliationClass
{ 
  public function readSwissFile($file = NULL, $view = false)
  {               
    try 
    {          
      $commonClass = new CommonClass();
      
      if(!$file)
      {
        $flepath = 'SWISS/';
        
        $filename = 'taxationDecisionVAT_25CHEI003677866440_1_CHE332375380.pdf';

        $file = public_path($flepath . $filename);   
      }

      if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
      {
        $exepath = 'c:/Program Files/Git/mingw64/bin/pdftotext';               
        $pdftext = PdfExtract::getText($file, $exepath); 
      }
      else
        $pdftext = PdfExtract::getText($file); 
      
      if($pdftext)       
      {                   
        $arraytext = explode("\n", $pdftext);
        //if($view)
          //dd($arraytext);

        $start_pos_category_no = 0;   

        $start_pos_com_invoice_no = 0;  
        $start_pos_date = 0;  
        $start_pos_vat_amount = 0; 
        $arr_start_pos_net_amount = [];
        $start_pos_net_amount_count = 0;
        foreach($arraytext as $key => $text)
        {         
          if($start_pos_category_no == 0 && $start_pos_date == 0)
          {
            if (stripos(trim($text), "Ausstellungsdatum") !== false) 
            {
              if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
              {
                if (stripos(trim($arraytext[$key + 2]), ".") !== false) 
                  $start_pos_date = $key + 2;

                if (stripos(trim($arraytext[$key + 10]), ":") !== false)
                  $start_pos_category_no = $key + 26;
                else
                  $start_pos_category_no = $key + 10;
              } //LOCALHOST
              else
              {
                if(trim($arraytext[$key + 3]) == '')
                {
                  if (stripos(trim($arraytext[$key + 5]), ".") !== false) 
                    $start_pos_date = $key + 5;
                }
                else
                {
                  if (stripos(trim($arraytext[$key + 3]), ".") !== false) 
                    $start_pos_date = $key + 3;
                }

                if (stripos(trim($arraytext[$key - 4]), ".") !== false)
                  $start_pos_category_no = $key - 4;
                else
                  $start_pos_category_no = $key + 2;
              } //SERVER
            }
          }// if start_pos_category_no & start_pos_date              

          if($start_pos_com_invoice_no == 0)
          {
            if (stripos(trim($text), "Unterlagen (Art, Nummer, Datum") !== false)  
              $start_pos_com_invoice_no = $key;  
          }// if start_pos_com_invoice_no
          
          if (stripos(trim($text), "Betrag [CHF]") !== false)  
          {
            $arr_start_pos_net_amount[$start_pos_net_amount_count] = $key;  
            $start_pos_net_amount_count++;
          }
          
          if($start_pos_vat_amount == 0)
          {
            if (stripos(trim($text), "Gesamtbetrag") !== false)  
              $start_pos_vat_amount = $key;  
          }// if start_pos_vat_amount

          
        }// for arraytext 
      } // if pdf has text
   
      $category_type = '';
      $decalration_no = ''; //instead of lope_no

      $currency_code = 'CHF';
      $com_invoice_no = ''; 
      $com_invoice_date = '';
      $com_invoice_net_amount = 0;
      $com_invoice_vat_amount = 0;

      /* CATEGORY n DECLARATION NO. */ 
      if($start_pos_category_no > 0)
      {
        $category_type_declaration_no = trim($arraytext[$start_pos_category_no]);

        if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
        {          
          if (stripos($category_type_declaration_no, " ") !== false)
          {
            $arr_category_type_declaration_no = explode(' ', $category_type_declaration_no);
            $category_type_declaration_no = $arr_category_type_declaration_no[0];
          }
        } //LOCALHOST       

        if (stripos($category_type_declaration_no, ".") !== false)
        {
          $arr_category_type_declaration_no = explode('.', trim($category_type_declaration_no));
            
          $category_type = trim($arr_category_type_declaration_no[1]);
          $decalration_no = trim($arr_category_type_declaration_no[0]); 
        }
      }
      /*end CATEGORY n DECLARATION NO. */ 

      /* DATE */ 
      if($start_pos_date > 0)
      {
        $date = trim($arraytext[$start_pos_date]);

        if($date != '')
        {              
          $arr_date = explode(' ', trim($date));

          $format_date = (stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false) ? $arr_date[2] : $arr_date[0];

          $arr_com_invoice_date = explode('.', trim($format_date));

          $com_invoice_date_year = trim(str_replace(',', '', $arr_com_invoice_date[2]));
          $com_invoice_date_month = trim(str_replace(',', '', $arr_com_invoice_date[1]));
          $com_invoice_date_date = trim(str_replace(',', '', $arr_com_invoice_date[0]));
          
          $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT); 
        }
      }
      /*end DATE */ 

      /* COM. INVOICE NO. */ 
      if($start_pos_com_invoice_no > 0)
      {
        if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
        {
          if(trim($arraytext[$start_pos_com_invoice_no]) != '')
          {
            $com_invoice_no_line = $arraytext[$start_pos_com_invoice_no];
                   
            preg_match('/Nummer.*?(\d+),\s*(\d+)/', htmlspecialchars($com_invoice_no_line), $matches);

            if($matches)
            {
              if (isset($matches[count($matches) - 1]))
                $com_invoice_no = trim($matches[count($matches) - 1]);
            }
            else
            {
              $com_invoice_no_line = $arraytext[$start_pos_com_invoice_no + 1];
                                 
              $com_invoice_no_arr = explode(',', $com_invoice_no_line);

              if (count($com_invoice_no_arr) > 2)
                $com_invoice_no = trim($com_invoice_no_arr[1]);   
            }
          }
        } //localhost
        else
        {
          $com_invoice_no_line = $arraytext[$start_pos_com_invoice_no + 4];

          preg_match('/Nummer.*?(\d+),\s*(\d+)/', htmlspecialchars($com_invoice_no_line), $matches);

          if($matches)
          {
            if (isset($matches[count($matches) - 1]))
              $com_invoice_no = trim($matches[count($matches) - 1]);
          }
          else
          {                              
            $com_invoice_no_arr = explode(',', $com_invoice_no_line);

            if (count($com_invoice_no_arr) > 2)
              $com_invoice_no = trim($com_invoice_no_arr[1]);   
          }
        } //server  
      }
      /*end COM. INVOICE NO. */ 

      /* NET AMOUNT */ 
      if($arr_start_pos_net_amount)
      {
        if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
        {
          foreach($arr_start_pos_net_amount as $key => $start_pos_net_amount)
          {
            if(trim($arraytext[$start_pos_net_amount]) != '')
            {
              $net_amount_line = (preg_match('/\d/', $arraytext[$start_pos_net_amount])) ? $arraytext[$start_pos_net_amount] : $arraytext[$start_pos_net_amount + 1];

                $arr_net_amount = explode(' ', trim($net_amount_line));
            
                $net_amount = (preg_match('/\d/', $arraytext[$start_pos_net_amount])) ? $arr_net_amount[count($arr_net_amount) -1] : 
                                $arr_net_amount[0];

                $com_invoice_net_amount += (float) trim(str_replace('\'', '', $net_amount));
            }
          }//loop
        } //LOCALHOST   
        else
        {
          foreach($arr_start_pos_net_amount as $key => $start_pos_net_amount)
          {
            if (stripos(trim($arraytext[$start_pos_net_amount + 6]), "Veranlagungstyp") !== false) 
            {
              if(trim($arraytext[$start_pos_net_amount + 11]) != '')
              {
                $net_amount_text = trim($arraytext[$start_pos_net_amount + 11]);
                            
                $com_invoice_net_amount += (float) str_replace('\'', '', $net_amount_text);                      
              }
            }
            else
            {
              if (stripos(trim($arraytext[$start_pos_net_amount + 6]), "MWST Wert") !== false) 
              {
                if (stripos(trim($arraytext[$start_pos_net_amount + 4]), "Ansatz [%]") !== false) 
                {
                  $net_amount_text = trim($arraytext[$start_pos_net_amount + 8]);
                  if($net_amount_text != '')
                    $com_invoice_net_amount += (float) str_replace('\'', '', $net_amount_text);  
                }
                else
                {
                  $net_amount_text = trim($arraytext[$start_pos_net_amount + 4]);
                  if($net_amount_text != '')
                    $com_invoice_net_amount += (float) str_replace('\'', '', $net_amount_text); 
                } 
              }
              else if (stripos(trim($arraytext[$start_pos_net_amount + 6]), "MWST [CHF]") !== false) 
              {
                $net_amount_text = trim($arraytext[$start_pos_net_amount + 8]);
                if($net_amount_text != '')
                  $com_invoice_net_amount += (float) str_replace('\'', '', $net_amount_text);  
              }
              else
              {
                if (stripos(trim($arraytext[$start_pos_net_amount + 11]), "MWST [CHF]") !== false) 
                {
                  $net_amount_text = trim($arraytext[$start_pos_net_amount + 13]);
                  if($net_amount_text != '')
                    $com_invoice_net_amount += (float) str_replace('\'', '', $net_amount_text);  
                }
                else
                {
                  $net_amount_text = trim($arraytext[$start_pos_net_amount + 6]);
                  if($net_amount_text != '')
                    $com_invoice_net_amount += (float) str_replace('\'', '', $net_amount_text); 
                }   
              } 
            }  
          }//loop    
        } //SERVER  
      }
      /*end NET AMOUNT */ 

      /* VAT AMOUNT */ 
      if($start_pos_vat_amount > 0)
      {
        if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
        {
          $vat_amount = str_replace('\'', '', trim($arraytext[$start_pos_vat_amount + 2]));
          if($vat_amount != '')          
            $com_invoice_vat_amount = str_replace('\'', '', $vat_amount);                
        } //localhost
        else
        {
          $vat_amount_text = str_replace('\'', '', trim($arraytext[$start_pos_vat_amount + 2]));
          if($vat_amount_text != '')
          {
            if(preg_match('/^\d+(\.\d+)?$/', $vat_amount_text))
              $vat_amount = $vat_amount_text;
            else
            {
              $vat_amount_text = str_replace('\'', '', trim($arraytext[$start_pos_vat_amount + 4]));
              if(preg_match('/^\d+(\.\d+)?$/', $vat_amount_text))
                $vat_amount = $vat_amount_text;
              else
              {
                $vat_amount_text = str_replace('\'', '', trim($arraytext[$start_pos_vat_amount + 7]));
                if(preg_match('/^\d+(\.\d+)?$/', $vat_amount_text))
                  $vat_amount = $vat_amount_text;
                else
                {
                  $vat_amount_text = str_replace('\'', '', trim($arraytext[$start_pos_vat_amount + 9]));
                  if(preg_match('/^\d+(\.\d+)?$/', $vat_amount_text))
                    $vat_amount = $vat_amount_text;
                }
              }
            }
            
            if($vat_amount)
              $com_invoice_vat_amount = str_replace('\'', '', $vat_amount);      
            else            
              $com_invoice_vat_amount = $com_invoice_net_amount * 0.081;
          }
        } //server  
      }
      /*end VAT AMOUNT */ 

      return [          
          'category_type' => $category_type,              
          'decalration_no' => $decalration_no,
          'currency_code' => $currency_code,
          'com_invoice_no' => $com_invoice_no,
          'com_invoice_date' => $com_invoice_date,
          'com_invoice_net_amount' => $com_invoice_net_amount,
          'com_invoice_vat_amount' => $com_invoice_vat_amount              
        ];
    } 
    catch (\Exception $e) {
        //dd($e);           
      return "Error: " . $e->getMessage();
    }       
  }
}
