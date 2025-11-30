<?php

namespace App\Classes;

use Spatie\PdfToText\Pdf as PdfExtract;

use Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

use App\Classes\CommonClass;
use App\Models\ImportReconciliationComInvoices;

class CargoDeclarationClass
{ 
  public function readCargoDeclarationFile($filename = NULL, $subfolder = NULL, $view = false)
    {               
        try 
        {
            $commercialInvoicesClass = new CommercialInvoicesClass();
            $commonClass = new CommonClass();

            if($filename)
            {
                $flepath = storage_path('app/public/mailbox/cargodeclarationfiles/');  
                $sub_folder = ($subfolder) ? $subfolder : '';  
                $file = $flepath . $sub_folder . $filename; 
            }
            else
            {
                $flepath = 'CARGO-DECLARATION-FILES/';

                $filename = 'N00218144-N00218163_4410022500150156.pdf';//            

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
              //get VAT reg. main with country NO for organization no. 
              $_where = [
                'country' => ['operator' => '=', 'value' => 'NO'],              
              ]; 
              $vatregmains = $commonClass->getVatRegMainLazy(null, $_where);            

              $arraytext = explode("\n", $pdftext);
              if($view)
                dd($arraytext, $vatregmains)  ;  

              $line_is_arr = false; 
              $single_line_lope_expo_no = false;
              $start_pos = 0; 
              $start_pos_com_invoice = 0;               
              $is_a8 = false; 
              $_search_for_fatura_list = false;
              $vat_reg_main_id = [];
              foreach($arraytext as $key => $text)
              {                  
                if($vatregmains)
                {
                  if(trim($text) != '')
                  {
                    $vatregmain_filter = $vatregmains->filter(function($vatregmain, $key) use ($text) {                      
                      return (stripos(trim($text), $vatregmain->org_no) || trim($text) == $vatregmain->org_no);
                    });
                    
                    if(count($vatregmain_filter) > 0)                     
                      $vat_reg_main_id = $vatregmain_filter->first()->id;                                     
                  }
                }

                if($start_pos == 0)
                {
                  if (stripos(trim($text), "LINJEDEKLARERT") !== false)  
                  {      
                    $start_pos = $key;  

                    $arr_line = explode(' ', trim($text));                    
                    if(count($arr_line) > 1)
                      $line_is_arr = true;           
                  }
                  else if (stripos(trim($text), "preferanse/opprinnelse") !== false)  
                  {
                    $start_pos = $key;
                    $single_line_lope_expo_no = true;
                  }   
                  else if (stripos(trim($text), "OMBEREGNING") !== false)                    
                    $start_pos = $key;
                  else if (stripos(trim($text), "Sats dato:") !== false)                    
                    $start_pos = $key;
                }

                if (stripos(trim($text), "BANKDATA") !== false)  
                {      
                  $start_pos_com_invoice = $key;

                  if(trim($arraytext[$start_pos_com_invoice + 1]) == '')
                  {                    
                    if (stripos(trim($arraytext[$start_pos_com_invoice + 2]), "fakturaliste") !== false || 
                      stripos(trim($arraytext[$start_pos_com_invoice + 2]), "se faktura") !== false
                	)                      
                      $_search_for_fatura_list = true;                    
                  }                  
                } 
                else if (stripos(trim($text), "Invoices listed") !== false)
                {
                  $start_pos_com_invoice = $key;
                }
                else if (stripos(trim($text), "A8") !== false)  
                {   
                    if($start_pos_com_invoice == '') 
                    {   
	                  $start_pos_com_invoice = $key; 

	                  if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
	                  {
	                    $date_com_invoice_no = trim($arraytext[$start_pos_com_invoice + 6]);                  
	                    $arr_com_invoice_date = explode('.', trim($date_com_invoice_no));

	                    if(count($arr_com_invoice_date) == 3)
	                    {
	                      $com_invoice_date_year = $arr_com_invoice_date[0];
	                      $com_invoice_date_month = $arr_com_invoice_date[1];
	                      $com_invoice_date_date = $arr_com_invoice_date[2];

	                      $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  

	                      $date_data = [
	                          'date_field' => $com_invoice_date,
	                      ];
	                      $datevalidator = Validator::make($date_data, [
	                        'date_field' => 'required|date_format:Y-m-d',
	                      ]);

	                      if ($datevalidator->fails())
	                        $start_pos_com_invoice = 0; 
	                      else
	                        $is_a8 = true;   
	                    }
	                    else                 
	                      $start_pos_com_invoice = 0;
	                  }//localhost
	                  else
	                  {   
	                    $start_pos_com_invoice = 0;                 
	                    if(trim($text) == "A8")
	                    {
	                      $is_a8 = true;
	                      $start_pos_com_invoice = $key; 
	                    }                    
	                  }//SERVER
	                }//not null  
                } 

                if($_search_for_fatura_list)
                {
                  if (stripos(trim($text), "Fakturanummer") !== false || stripos(trim($text), "Finansielle opplysninger og bankdata") !== false)
                    $start_pos_com_invoice = $key;                    
                }
                else
                {
                  if($start_pos > 0 && $start_pos_com_invoice > 0 && $vat_reg_main_id != '')  
                    break;
                }
              } //for 
//dd($start_pos, $line_is_arr, $start_pos_com_invoice);
              /*EXPO/LOPE NO.*/
              $service_date = '';
              $cargo_date = '';
              $expo_no = '';
              $lope_no = '';                
              if($start_pos > 0 && $line_is_arr)
              {
                $line = $arraytext[$start_pos];

                if (stripos(trim($line), "-") !== false) 
                {
                  $arr_line = explode(' ', trim($line));
                  
                  foreach($arr_line as $arr_line_key => $arr_line_text)
                  {
                    if (stripos(trim($arr_line_text), "-") !== false) 
                    {
                      $arr_expo_lope_no = explode('-', trim($arr_line_text));

                      $lope_no = trim($arr_expo_lope_no[1]);
                      $expo_no = trim($arr_expo_lope_no[0]);
                    } //has -
                  }//for 

                  $_next_line = $arraytext[$start_pos + 1];

                  $arr_line = explode(' ', trim($_next_line));

                  if(count($arr_line) > 5)
                  {
                    $file_date = trim($arr_line[5]);
                    $arr_file_date = explode('.', trim($file_date));

                    $file_date_year = $arr_file_date[2];
                    $file_date_month = $arr_file_date[1];
                    $file_date_date = $arr_file_date[0];

                    $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                    $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                  }
                } // HYPHEN FORMAT          
                else
                {
                  $arr_line = explode(' ', trim($line));    

                  if(count($arr_line) == 4)
                  {
                    $file_date = trim($arr_line[1]);
                    $file_date_year = substr($file_date, 0, 4);
                    $file_date_month = substr($file_date, 4, 2);
                    $file_date_date = substr($file_date, 6, 2);

                    $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                    $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                    $lope_no = trim($arr_line[2]);
                    $expo_no = trim($arr_line[3]);
                  }
                  else if(count($arr_line) == 3)
                  {
                    $arr_line_text = $arraytext[$start_pos + 1];
                    if(stripos(trim($arr_line_text), "-") !== false && strlen($arr_line_text) == 17)
                    {
                      $arr_line_text_date = trim($arraytext[$start_pos - 8]);
                      if (stripos(trim($arr_line_text_date), ".") !== false) 
                      {
                        $arr_line_date = explode(' ', trim($arr_line_text_date));
                        foreach($arr_line_date as $arr_line_key => $arr_date)
                        {
                          if (stripos(trim($arr_date), ".") !== false) 
                          {
                            $arr_file_date = explode('.', trim($arr_date));

                            $file_date_year = $arr_file_date[2];
                            $file_date_month = $arr_file_date[1];
                            $file_date_date = $arr_file_date[0];

                            $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                            $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                          }
                        }
                      } // has .
                      else
                      {
                        $arr_line_text_date = trim($arraytext[$start_pos - 16]);
                        if (stripos(trim($arr_line_text_date), ".") !== false) 
                        {
                          $arr_line_date = explode(' ', trim($arr_line_text_date));
                          foreach($arr_line_date as $arr_line_key => $arr_date)
                          {
                            if (stripos(trim($arr_date), ".") !== false) 
                            {
                              $arr_file_date = explode('.', trim($arr_date));

                              $file_date_year = $arr_file_date[2];
                              $file_date_month = $arr_file_date[1];
                              $file_date_date = $arr_file_date[0];

                              $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                              $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                            }
                          }
                        } // has .
                      } // no .

                      $arr_expo_lope_no = explode('-', trim($arr_line_text));

                      $lope_no = trim($arr_expo_lope_no[1]);
                      $expo_no = trim($arr_expo_lope_no[0]);
                    } //NEXT LINE
                    else
                    {
                      $arr_line_text = $arraytext[$start_pos - 24];
                      if (stripos(trim($arr_line_text), "-") !== false) 
                      {
                        $arr_line_text_date = trim($arraytext[$start_pos - 9]);
                        $arr_line_date = explode(' ', trim($arr_line_text_date));
                        foreach($arr_line_date as $arr_line_key => $arr_date)
                        {
                          if (stripos(trim($arr_date), ".") !== false) 
                          {
                            $arr_file_date = explode('.', trim($arr_date));

                            $file_date_year = $arr_file_date[2];
                            $file_date_month = $arr_file_date[1];
                            $file_date_date = $arr_file_date[0];

                            $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                            $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                          }
                        }

                        $arr_expo_lope_no = explode('-', trim($arr_line_text));

                        $lope_no = trim($arr_expo_lope_no[1]);
                        $expo_no = trim($arr_expo_lope_no[0]);
                      } //has -
                      else if (stripos(trim($arraytext[$start_pos - 27]), "-") !== false)
                      {
                        $arr_line_text = $arraytext[$start_pos - 27];
                        if (stripos(trim($arr_line_text), "-") !== false) 
                        {
                          $arr_line_text_date = trim($arraytext[$start_pos - 8]);
                          $arr_line_date = explode(' ', trim($arr_line_text_date));

                          foreach($arr_line_date as $arr_line_key => $arr_date)
                          {
                            if (stripos(trim($arr_date), ".") !== false) 
                            {
                              $arr_file_date = explode('.', trim($arr_date));

                              $file_date_year = $arr_file_date[2];
                              $file_date_month = $arr_file_date[1];
                              $file_date_date = $arr_file_date[0];

                              $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                              $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                            }
                          }

                          $arr_expo_lope_no = explode('-', trim($arr_line_text));

                          $lope_no = trim($arr_expo_lope_no[1]);
                          $expo_no = trim($arr_expo_lope_no[0]);
                        } //has -
                      } //line -27
                      else if (stripos(trim($arraytext[$start_pos - 30]), "-") !== false)
                      {
                        $arr_line_text = $arraytext[$start_pos - 30];
                        if (stripos(trim($arr_line_text), "-") !== false) 
                        {
                          $arr_line_text_date = trim($arraytext[$start_pos - 9]);
                          $arr_line_date = explode(' ', trim($arr_line_text_date));

                          foreach($arr_line_date as $arr_line_key => $arr_date)
                          {
                            if (stripos(trim($arr_date), ".") !== false) 
                            {
                              $arr_file_date = explode('.', trim($arr_date));

                              $file_date_year = $arr_file_date[2];
                              $file_date_month = $arr_file_date[1];
                              $file_date_date = $arr_file_date[0];

                              $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                              $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                            }
                          }

                          $arr_expo_lope_no = explode('-', trim($arr_line_text));

                          $lope_no = trim($arr_expo_lope_no[1]);
                          $expo_no = trim($arr_expo_lope_no[0]);
                        } //has -
                      } //line -30
                    }
                  } //has LINJEDEKLARERT TVINN (TK)
                  else if(count($arr_line) > 4)
                  {
                    $file_date = trim($arr_line[3]);

                    $file_date_year = substr($file_date, 6, 4);
                    $file_date_month = substr($file_date, 3, 2);
                    $file_date_date = substr($file_date, 0, 2);

                    $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                    $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                    //LOPE/RUN NO.
                    $expo_run_no = trim($arr_line[1]);
                    $lope_no = substr($expo_run_no, 6, 10);
                    $expo_no = substr($expo_run_no, 0, 6);
                  }
                } // SPACE FORMAT                            
              }//line is ARRAY
              else
              {   
                if (stripos(trim($arraytext[$start_pos]), "Sats dato:") !== false) 
                {                  
                  $file_date = trim($arraytext[$start_pos + 1]);
                  $arr_file_date = explode('-', trim($file_date));

                  $file_date_year = $arr_file_date[2];
                  $file_date_month = $arr_file_date[1];
                  $file_date_date = $arr_file_date[0];

                  $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                  $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                  $arr_expo_lope_no_text = trim($arraytext[$start_pos + 9]);
                  if (stripos(trim($arr_expo_lope_no_text), "/") !== false) 
                  {
                    $arr_expo_lope_no = explode('/', trim($arr_expo_lope_no_text));

                    $lope_no = trim($arr_expo_lope_no[1]);
                    $expo_no = trim($arr_expo_lope_no[0]);
                  }
                } //Sats dato:  
                else if (stripos(trim($arraytext[$start_pos]), "A AVGANGS-/") !== false) 
                {                  
                  $file_date = trim($arraytext[$start_pos + 34]);
                 
                  $file_date_year = substr($file_date, 0, 4);
                  $file_date_month = substr($file_date, 4, 2);
                  $file_date_date = substr($file_date, 6, 2);

                  $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                  $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                  $arr_expo_lope_no_text = trim($arraytext[$start_pos + 8]);
                  if (strlen(trim($arr_expo_lope_no_text)) == 16) 
                  {                    
                    $lope_no = substr($arr_expo_lope_no_text, 6, 16);
                    $expo_no = substr($arr_expo_lope_no_text, 0, 6); 
                  }
                } //A AVGANGS-/
                else if($single_line_lope_expo_no) 
                {
                  $single_line_lope_expo_no_value = trim($arraytext[$start_pos + 1]);
                  if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
                  {                    
                    if($single_line_lope_expo_no_value != '' && strlen($single_line_lope_expo_no_value) === 16)
                    {
                      $lope_no = substr($single_line_lope_expo_no_value, 6, 16);
                      $expo_no = substr($single_line_lope_expo_no_value, 0, 6);                    
                    }
                  } // LOCALHOST
                  else
                  {
                    if($single_line_lope_expo_no_value == '')
                    {
                      $single_line_lope_expo_no_value = trim($arraytext[$start_pos - 12]);
                      if($single_line_lope_expo_no_value != '' && strlen($single_line_lope_expo_no_value) === 16)
                      {
                        $lope_no = substr($single_line_lope_expo_no_value, 6, 16);
                        $expo_no = substr($single_line_lope_expo_no_value, 0, 6);                    
                      }
                    }
                  } //SERVER 
                } //single line EXPO/LOPE NO.
                else
                {
                  if(stripos($arraytext[$start_pos], "Vedlegg til omberegningsdeklarasjon") !== false)
                  {
                    $arr_expo_run_no = explode('Vedlegg til omberegningsdeklarasjon:', $arraytext[$start_pos]);
                    $line_expo_run_no = trim($arr_expo_run_no[1]);

                    $lope_no = substr($line_expo_run_no, 0, 6);
                    $expo_no = substr($line_expo_run_no, 6, (strlen($line_expo_run_no) - 1));
                  }
                  else if(trim($arraytext[$start_pos + 1]) == '')
                  {
                    $end_pos = $start_pos + 2;

                    $date_expo_run_no = $arraytext[$end_pos];
                    $arr_date_expo_run_no = explode(' ', $date_expo_run_no);
                                  
                    if(count($arr_date_expo_run_no) == 3)
                    {
                      $file_date = trim($arr_date_expo_run_no[0]);
                      $file_date_year = substr($file_date, 0, 4);
                      $file_date_month = substr($file_date, 4, 2);
                      $file_date_date = substr($file_date, 6, 2);

                      $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                      $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                      $lope_no = trim($arr_date_expo_run_no[1]);
                      $expo_no = trim($arr_date_expo_run_no[2]);
                    } //if found
                    else
                    {
                      if(strlen(trim($arraytext[$start_pos + 7])) == 8)
                      {
                        $file_date = trim($arraytext[$start_pos + 7]);
                        $file_date_year = substr($file_date, 0, 4);
                        $file_date_month = substr($file_date, 4, 2);
                        $file_date_date = substr($file_date, 6, 2);

                        $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                        $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                        if(strlen(trim($arraytext[$start_pos + 8])) == 10)                      
                          $lope_no = trim($arraytext[$start_pos + 8]);

                        if(strlen(trim($arraytext[$start_pos + 9])) == 6)                      
                          $expo_no = trim($arraytext[$start_pos + 9]);              
                      }
                      else  if(strlen(trim($arraytext[$start_pos - 21])) == 8)
                      {
                        $file_date = trim($arraytext[$start_pos - 21]);
                        $file_date_year = substr($file_date, 0, 4);
                        $file_date_month = substr($file_date, 4, 2);
                        $file_date_date = substr($file_date, 6, 2);

                        $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                        $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                        if(strlen(trim($arraytext[$start_pos - 20])) == 10)                      
                          $lope_no = trim($arraytext[$start_pos - 20]);

                        if(strlen(trim($arraytext[$start_pos - 19])) == 6)                      
                          $expo_no = trim($arraytext[$start_pos - 19]); 
                      }
                      else if(strlen(trim($arraytext[$start_pos - 21])) == 6)
                      {
                        $expo_no = trim($arraytext[$start_pos - 21]); 
                        $lope_no = trim($arraytext[$start_pos - 22]); 

                        $file_date = trim($arraytext[$start_pos - 23]);
                        $file_date_year = substr($file_date, 0, 4);
                        $file_date_month = substr($file_date, 4, 2);
                        $file_date_date = substr($file_date, 6, 2);

                        $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                        $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                      }
                      else if(strlen(trim($arraytext[$start_pos - 23])) == 6)
                      {
                        $expo_no = trim($arraytext[$start_pos - 23]); 
                        $lope_no = trim($arraytext[$start_pos - 24]); 

                        $file_date = trim($arraytext[$start_pos - 25]);
                        $file_date_year = substr($file_date, 0, 4);
                        $file_date_month = substr($file_date, 4, 2);
                        $file_date_date = substr($file_date, 6, 2);

                        $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                        $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                      } 
                      else if(strlen(trim($arraytext[$start_pos - 13])) == 6)
                      {
                        $expo_no = trim($arraytext[$start_pos - 13]); 
                        $lope_no = trim($arraytext[$start_pos - 14]); 

                        $file_date = trim($arraytext[$start_pos - 15]);
                        if (stripos(trim($file_date), "ORI.: ") !== false) 
                          $file_date = str_replace("ORI.: ", '', $file_date);
                        $file_date_year = substr($file_date, 0, 4);
                        $file_date_month = substr($file_date, 4, 2);
                        $file_date_date = substr($file_date, 6, 2);

                        $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                        $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';
                      }                
                    } //SERVER
                  } // LOCALHOST
                  else
                  {
                    $file_date = trim($arraytext[$start_pos + 1]);

                    if (stripos(trim($file_date), "Tolldato") !== false) 
                    {     
                      dd("Tolldato Tolldato Tolldato Tolldato");
                    } // Tolldato FORMAT    
                    else
                    {
                      $file_date = trim($arraytext[$start_pos + 2]);                    
                      if (stripos(trim($file_date), "Tolldato") !== false) 
                      {  
                        $file_date = str_replace('Tolldato: ', '', $file_date);

                        $arr_file_date = explode('.', trim($file_date));

                        $file_date_year = $arr_file_date[2];
                        $file_date_month = $arr_file_date[1];
                        $file_date_date = $arr_file_date[0];

                        $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                        $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                        $expo_lope_no = trim($arraytext[$start_pos + 1]);  
                        $lope_no = substr($expo_lope_no, 0, 6);
                        $expo_no = substr($expo_lope_no, 6, 10);           
                      } // Tolldato FORMAT    
                      else
                      { 
                        $file_date = trim($arraytext[$start_pos + 1]);

                        $file_date_year = substr($file_date, 0, 4);
                        $file_date_month = substr($file_date, 4, 2);
                        $file_date_date = substr($file_date, 6, 2);

                        $cargo_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_date, 2, "0", STR_PAD_LEFT);
                        $service_date = str_pad($file_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($file_date_month, 2, "0", STR_PAD_LEFT) . '-01';

                        if (stripos(trim($arraytext[$start_pos + 2]), "ORI.:") !== false) 
                        {
                          $lope_no = trim($arraytext[$start_pos + 3]);
                          $expo_no = trim($arraytext[$start_pos + 5]);
                        } //ORI
                        else
                        {
	                        $lope_no = trim($arraytext[$start_pos + 2]);
	                        $expo_no = trim($arraytext[$start_pos + 3]);
	                    } //not ORI
                      }
                    }                  
                  } //SERVER 
                } //single line NOT EXPO/LOPE NO.
              }//line is NOT ARRAY
              /*end EXPO/LOPE NO.*/
//dd('lope no: ' . $lope_no, 'expo no: ' . $expo_no, 'cargo date: ' . $cargo_date, 'service date: ' . $service_date, $is_a8, $_search_for_fatura_list, 'com. invoice pos: ' . $start_pos_com_invoice);                  
              /*COM. INVOICE*/
              $com_invoice_no = ''; 
              $com_invoice_date = '';
              if($is_a8)
              {
                if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
                {
                  if(trim($arraytext[$start_pos_com_invoice - 1]) == '')
                  {                  
                    $com_invoice_no = trim($arraytext[$start_pos_com_invoice - 2]);

                    $date_com_invoice_no = trim($arraytext[$start_pos_com_invoice + 6]);                  
                    $arr_com_invoice_date = explode('.', trim($date_com_invoice_no));

                    $com_invoice_date_year = $arr_com_invoice_date[0];
                    $com_invoice_date_month = $arr_com_invoice_date[1];
                    $com_invoice_date_date = $arr_com_invoice_date[2];

                    $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                  }
                } //localhost
                else
                {
                  if(trim($arraytext[$start_pos_com_invoice - 1]) == '')
                  {  
                    if (stripos(trim($arraytext[$start_pos_com_invoice - 4]), "10 DAGERS LAGER") !== false)                 
                      $com_invoice_no = trim($arraytext[$start_pos_com_invoice - 5]);
                    else if (stripos(trim($arraytext[$start_pos_com_invoice - 6]), "10 DAGERS LAGER") !== false)
                      $com_invoice_no = trim($arraytext[$start_pos_com_invoice - 7]);
                    else if (stripos(trim($arraytext[$start_pos_com_invoice - 8]), "10 DAGERS LAGER") !== false)
                      $com_invoice_no = trim($arraytext[$start_pos_com_invoice - 9]);
                    else if (stripos(trim($arraytext[$start_pos_com_invoice - 8]), "TOLLAGER A - 10") !== false)
                      $com_invoice_no = trim($arraytext[$start_pos_com_invoice - 11]);

                    if (stripos(trim($com_invoice_no), " - ") !== false) 
                    {
                      $arr_com_invoice_no_date = explode(' - ', trim($com_invoice_no));

                      $com_invoice_no = trim($arr_com_invoice_no_date[0]);

                      $date_com_invoice_no = trim($arr_com_invoice_no_date[1]);
                      $arr_com_invoice_date = explode('/', trim($date_com_invoice_no));

                      $com_invoice_date_year = $arr_com_invoice_date[2];
                      $com_invoice_date_month = $arr_com_invoice_date[1];
                      $com_invoice_date_date = $arr_com_invoice_date[0];

                      $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                    }   
                    else
                    { 
                      if (stripos(trim($arraytext[$start_pos_com_invoice - 2]), "bankdata") !== false) 
                      {
                        $date_com_invoice_no = trim($arraytext[$start_pos_com_invoice + 84]);
                        if (stripos(trim($date_com_invoice_no), " - ") !== false) 
                        {
                          $arr_com_invoice_no_date = explode(' - ', trim($date_com_invoice_no));

                          $com_invoice_no = trim($arr_com_invoice_no_date[0]);

                          $date_com_invoice_no = trim($arr_com_invoice_no_date[1]);
                          $arr_com_invoice_date = explode('/', trim($date_com_invoice_no));

                          $com_invoice_date_year = $arr_com_invoice_date[2];
                          $com_invoice_date_month = $arr_com_invoice_date[0];
                          $com_invoice_date_date = $arr_com_invoice_date[1];

                          $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                        }
                      } //bankdata
                      else if (stripos(trim($arraytext[$start_pos_com_invoice - 3]), "fakturaoversikt") !== false) 
                      {
                        if (stripos(trim($arraytext[$start_pos_com_invoice + 31]), "bankdata") !== false && 
                          stripos(trim($arraytext[$start_pos_com_invoice + 33]), "dato") !== false
                        ) 
                        {
                          //dd("dfsffsdfd");
                        }
                      }
                      else
                      {
                        $date_com_invoice_no = trim($arraytext[$start_pos_com_invoice - 2]);
                        $arr_com_invoice_date = explode('.', trim($date_com_invoice_no));

                        $com_invoice_date_year = $arr_com_invoice_date[0];
                        $com_invoice_date_month = $arr_com_invoice_date[1];
                        $com_invoice_date_date = $arr_com_invoice_date[2];

                        $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                      }                    
                    } 
                  }
                  else
                  {
                    if($_search_for_fatura_list)
                    {
                      if (stripos(trim($arraytext[$start_pos_com_invoice]), "Finansielle opplysninger og bankdata") !== false) 
                      {
                        $fatura_start_pos = $start_pos_com_invoice + 2;

                        foreach (array_slice($arraytext, $fatura_start_pos) as $item) 
                        {
                          if ($item === null || $item === '')
                            break;

                          if(stripos(trim($item), ".") !== false && strlen($item) == 10)
                          {
                            $arr_com_invoice_date = explode('.', trim($item));

                            $com_invoice_date_year = trim($arr_com_invoice_date[0]);
                            $com_invoice_date_month = trim($arr_com_invoice_date[1]);
                            $com_invoice_date_date = trim($arr_com_invoice_date[2]);
                            
                            if($com_invoice_date == '')
                              $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);
                            else
                              $com_invoice_date .= ',' . str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);
                          } //date
                          else if (stripos(trim($item), ",") !== false || stripos(trim($item), " NOK") !== false)
                          {

                          } //amount
                          else
                          {                            
                            if($com_invoice_no == '') 
                              $com_invoice_no = trim(str_replace('--', '', $item));
                            else
                              $com_invoice_no .= ',' . trim(str_replace('--', '', $item));
                          } //invoice no.
                        } //loop
                      } // has Finansielle opplysninger og bankdata
                    } // has FAKTURA LIST
                  } // FAKTURA LIST
                }
              }// A8
              else
              {
                if($_search_for_fatura_list)
                {
                  $arr_com_invoice_nos = explode(' ', trim($arraytext[$start_pos_com_invoice]));                
                  foreach($arr_com_invoice_nos as $key => $arr_com_invoice_no)
                  {
                    if (stripos(trim($arr_com_invoice_no), "Fakturanummer") !== false) 
                    {

                    }
                    else
                    {
                      if($com_invoice_no == '')
                        $com_invoice_no = trim($arr_com_invoice_no);
                      else
                        $com_invoice_no .= ',' . trim($arr_com_invoice_no);
                    }
                  }//for FAKTURA list

                  $arr_com_invoice_dates = explode(' ', trim($arraytext[$start_pos_com_invoice + 2]));                  
                  foreach($arr_com_invoice_dates as $key => $arr_com_invoice_date_row)
                  {
                    if (stripos(trim($arr_com_invoice_date_row), "Dato") !== false) 
                    {

                    }
                    else
                    {
                      $arr_com_invoice_date = explode('.', trim($arr_com_invoice_date_row));

                      $com_invoice_date_year = trim($arr_com_invoice_date[2]);
                      $com_invoice_date_month = trim($arr_com_invoice_date[1]);
                      $com_invoice_date_date = trim($arr_com_invoice_date[0]);
                      
                      if($com_invoice_date == '')
                        $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);
                      else
                        $com_invoice_date .= ',' . str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);
                    }
                  }//for FAKTURA list                  
                } //FAKTURA list
                else
                {
                  if(stripos($arraytext[$start_pos], "Vedlegg til omberegningsdeklarasjon") !== false)
                  {
                    if (stripos($_SERVER['HTTP_HOST'], "localhost:8000") !== false) 
                    {

                    } //LOCALHOST
                    else
                    {
                      $end_pos_com_invoice = $start_pos_com_invoice + 86;

                      $date_com_invoice_no = trim($arraytext[$end_pos_com_invoice]);

                      if (stripos(trim($date_com_invoice_no), " - ") !== false) 
                      {
                        $arr_date_com_invoice_no = explode(' - ', $date_com_invoice_no);

                        if(count($arr_date_com_invoice_no) == 2)
                        {           
                          if(stripos(trim($arr_date_com_invoice_no[1]), ".") !== false)       
                            $arr_com_invoice_date = explode('.', trim($arr_date_com_invoice_no[1]));
                          else if(stripos(trim($arr_date_com_invoice_no[1]), "/") !== false)       
                            $arr_com_invoice_date = explode('/', trim($arr_date_com_invoice_no[1]));

                          $com_invoice_date_year = trim($arr_com_invoice_date[2]);
                          $com_invoice_date_month = trim($arr_com_invoice_date[0]);
                          $com_invoice_date_date = trim($arr_com_invoice_date[1]);

                          $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                        
                          $com_invoice_no = trim($arr_date_com_invoice_no[0]);   
                                              
                          if (stripos(trim($arraytext[$end_pos_com_invoice + 1]), "-") !== false && 
                            stripos(trim($arraytext[$end_pos_com_invoice + 1]), ".") !== false
                          )
                          {
                            $date_com_invoice_no = trim($arraytext[$end_pos_com_invoice + 1]);
                            if (stripos(trim($date_com_invoice_no), "A8") !== false) 
                              $date_com_invoice_no = trim(str_replace('A8', '', $date_com_invoice_no));
                            
                            $arr_date_com_invoice_no = explode(' - ', $date_com_invoice_no);
                            if(count($arr_date_com_invoice_no) == 2)
                            {                    
                              $arr_com_invoice_date = explode('.', trim($arr_date_com_invoice_no[1]));

                              $com_invoice_date_year = trim($arr_com_invoice_date[2]);
                              $com_invoice_date_month = trim($arr_com_invoice_date[1]);
                              $com_invoice_date_date = trim($arr_com_invoice_date[0]);

                              $com_invoice_date .= ',' . str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                            
                              $com_invoice_no .= ',' . trim($arr_date_com_invoice_no[0]);   
                            }
                          } //HAS extra com. invoices                  
                        } //if found
                      } // hypen found
                    } //SERVER
                  } //Vedlegg til omberegningsdeklarasjon
                  else if(trim($arraytext[$start_pos_com_invoice + 1]) == '')
                  {
                    $end_pos_com_invoice = $start_pos_com_invoice + 2;

                    $date_com_invoice_no = trim($arraytext[$end_pos_com_invoice]);

                    if (stripos(trim($date_com_invoice_no), " - ") !== false) 
                    {
                      $arr_date_com_invoice_no = explode(' - ', $date_com_invoice_no);

                      if(count($arr_date_com_invoice_no) == 2)
                      {           
                        if(stripos(trim($arr_date_com_invoice_no[1]), ".") !== false)       
                          $arr_com_invoice_date = explode('.', trim($arr_date_com_invoice_no[1]));
                        else if(stripos(trim($arr_date_com_invoice_no[1]), "/") !== false)       
                          $arr_com_invoice_date = explode('/', trim($arr_date_com_invoice_no[1]));

                        $com_invoice_date_year = trim($arr_com_invoice_date[2]);
                        $com_invoice_date_month = trim($arr_com_invoice_date[1]);
                        $com_invoice_date_date = trim($arr_com_invoice_date[0]);

                        $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                      
                        $com_invoice_no = trim($arr_date_com_invoice_no[0]);   
                                            
                        if (stripos(trim($arraytext[$end_pos_com_invoice + 1]), "-") !== false && 
                          stripos(trim($arraytext[$end_pos_com_invoice + 1]), ".") !== false
                        )
                        {
                          $date_com_invoice_no = trim($arraytext[$end_pos_com_invoice + 1]);
                          if (stripos(trim($date_com_invoice_no), "A8") !== false) 
                            $date_com_invoice_no = trim(str_replace('A8', '', $date_com_invoice_no));
                          
                          $arr_date_com_invoice_no = explode(' - ', $date_com_invoice_no);
                          if(count($arr_date_com_invoice_no) == 2)
                          {                    
                            $arr_com_invoice_date = explode('.', trim($arr_date_com_invoice_no[1]));

                            $com_invoice_date_year = trim($arr_com_invoice_date[2]);
                            $com_invoice_date_month = trim($arr_com_invoice_date[1]);
                            $com_invoice_date_date = trim($arr_com_invoice_date[0]);

                            $com_invoice_date .= ',' . str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                          
                            $com_invoice_no .= ',' . trim($arr_date_com_invoice_no[0]);   
                          }
                        } //HAS extra com. invoices                  
                      } //if found
                    } // hypen found
                    else
                    {
                    	if(stripos(trim($arraytext[$start_pos_com_invoice]), "DAGERS LAGER") !== false) 
                      	{
                        	$com_invoice_no = trim($arraytext[$start_pos_com_invoice - 1]);

                        	if (stripos(trim($arraytext[$start_pos_com_invoice + 6]), ".") !== false) 
                        	{
                          	$arr_com_invoice_date = explode('.', trim($arraytext[$start_pos_com_invoice + 6]));

                          	$com_invoice_date_year = (strlen(trim($arr_com_invoice_date[0])) == 4) ? trim($arr_com_invoice_date[0]) : trim($arr_com_invoice_date[2]);
                          	$com_invoice_date_month = trim($arr_com_invoice_date[1]);
                          	$com_invoice_date_date = (strlen(trim($arr_com_invoice_date[2])) == 4) ? trim($arr_com_invoice_date[0]) : trim($arr_com_invoice_date[2]);

                          	$com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);
                        	}
                      	} // DAGERS LAGER
                      	else
                      	{
  	                      $com_invoice_no = trim($arraytext[$end_pos_com_invoice]);

                          if(strlen($com_invoice_no) == 1)
                          {
                            $com_invoice_no_date = trim($arraytext[$start_pos_com_invoice + 45]);
                            if (stripos(trim($com_invoice_no_date), " - ") !== false) 
                            {
                              $com_invoice_no_date_text = explode(' - ', trim($com_invoice_no_date));

                              $com_invoice_no = trim($com_invoice_no_date_text[0]);

                              $arr_com_invoice_date = explode('-', trim($com_invoice_no_date_text[1]));
                              $com_invoice_date_year = (strlen(trim($arr_com_invoice_date[0])) == 4) ? trim($arr_com_invoice_date[0]) : trim($arr_com_invoice_date[2]);
                              $com_invoice_date_month = trim($arr_com_invoice_date[1]);
                              $com_invoice_date_date = (strlen(trim($arr_com_invoice_date[2])) == 4) ? trim($arr_com_invoice_date[0]) : trim($arr_com_invoice_date[2]);

                              $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);
                            } //has -
                          }
                          else
                          {
    	                      if (stripos(trim($arraytext[$end_pos_com_invoice + 2]), ".") !== false) 
    	                      {
    	                        $arr_com_invoice_date = explode('.', trim($arraytext[$end_pos_com_invoice + 2]));

    	                        $com_invoice_date_year = (strlen(trim($arr_com_invoice_date[0])) == 4) ? trim($arr_com_invoice_date[0]) : trim($arr_com_invoice_date[2]);
    	                        $com_invoice_date_month = trim($arr_com_invoice_date[1]);
    	                        $com_invoice_date_date = (strlen(trim($arr_com_invoice_date[2])) == 4) ? trim($arr_com_invoice_date[0]) : trim($arr_com_invoice_date[2]);

    	                        $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);
    	                      } //dot
                            else if (stripos($com_invoice_no, " ") !== false) 
                            {
                              $arr_com_invoice_date = explode(' ', $com_invoice_no);

                              $com_invoice_no = trim($arr_com_invoice_date[0]);

                              $line_com_invoice_date = trim($arr_com_invoice_date[1]);

                              $com_invoice_date_year = substr($line_com_invoice_date, 0, 4);
                              $com_invoice_date_month = substr($line_com_invoice_date, 4, 2);
                              $com_invoice_date_date = substr($line_com_invoice_date, 6, 2);

                              $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);
                            } // single line with space
                          }
	                      } // not DAGERS LAGER  
                    } // hypen NOT found
                  }
                  else
                  {
                    if (stripos(trim($arraytext[$start_pos_com_invoice]), "SVINESUND SE") !== false)
                    {
                      $com_invoice_no = trim($arraytext[$start_pos_com_invoice - 6]);

                      $date_com_invoice_no = trim($arraytext[$start_pos_com_invoice - 2]);
                      $arr_com_invoice_date = explode('.', $date_com_invoice_no);

                      $com_invoice_date_year = trim($arr_com_invoice_date[0]);
                      $com_invoice_date_month = trim($arr_com_invoice_date[1]);
                      $com_invoice_date_date = trim($arr_com_invoice_date[2]);

                      $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                    } //SVINESUND SE
                    else
                    {
                      $end_pos_com_invoice = $start_pos_com_invoice + 1;

                      $date_com_invoice_no = trim($arraytext[$end_pos_com_invoice]);
                      $arr_date_com_invoice_no = explode(' - ', $date_com_invoice_no);

                      if(count($arr_date_com_invoice_no) == 2)
                      { 
                        $separator = '';  
                        if (stripos(trim($arr_date_com_invoice_no[1]), "/") !== false)                  
                          $separator = '/';
                        else if (stripos(trim($arr_date_com_invoice_no[1]), ".") !== false) 
                          $separator = '.';
                         
                        if($separator != '')
                        {
                          $arr_com_invoice_date = explode($separator, trim($arr_date_com_invoice_no[1]));

                          $com_invoice_date_year = trim($arr_com_invoice_date[2]);
                          $com_invoice_date_month = ($separator == '/') ? trim($arr_com_invoice_date[0]) : trim($arr_com_invoice_date[1]);
                          $com_invoice_date_date = ($separator == '/') ? trim($arr_com_invoice_date[1]) : trim($arr_com_invoice_date[0]);

                          $com_invoice_date = str_pad($com_invoice_date_year, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($com_invoice_date_date, 2, "0", STR_PAD_LEFT);  
                        
                          $com_invoice_no = trim($arr_date_com_invoice_no[0]);
                        }
                      } //if found
                    } //not SVINESUND SE
                  }//NOT NULL
                } //NOT FAKTURA list
              }//BANK DATA
              /*end COM. INVOICE*/                  
//dd($com_invoice_no, $com_invoice_date);  
              /* CHECK EXPO, LOPE NO. LENGTH*/ 
              $final_expo_no = $expo_no;  
              $final_lope_no = $lope_no;  
              if(strlen($expo_no) > 6)
              {
                if(strlen($lope_no) == 6)
                  $final_expo_no = $lope_no;
              }

              if(strlen($lope_no) < 10)
              {
                if(strlen($expo_no) == 10)
                  $final_lope_no = $expo_no;
              }
              /*end CHECK EXPO, LOPE NO. LENGTH*/   

              if($service_date == '')             
                $service_date = ($com_invoice_date) ? $com_invoice_date : '';
              
              //dd($filename, $vat_reg_main_id, $service_date, $cargo_date, $lope_no, $expo_no, $com_invoice_no, $com_invoice_date, $final_expo_no, $final_lope_no); 

              $match_vatreg = [];
              if($service_date != '' && strlen($service_date) == 10 && $vat_reg_main_id)
              {
                $date_data = [
                    'date_field' => $service_date,  // Sample date to validate
                ];
                $datevalidator = Validator::make($date_data, [
                  'date_field' => 'required|date_format:Y-m-d',  // Validation for a specific format
                ]);

                if ($datevalidator->fails()) 
                {
                  dd("Incorrect date",  $date_data);
                }
                else
                {
                  //GET MATCH VATREG.
                  $_with = [];      
                  $_where = [
                    'vat_reg_main_id' => ['operator' => '=', 'value' => $vat_reg_main_id]
                  ];
                  $_whereHas = [];      
                  $_orderBy = [
                    'id' => 'ASC'
                  ];
                  $_final = 'get';      
                  $match_vatregs = $commonClass->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final);

                  $filtered_vatregs = $match_vatregs->filter(function($vatreg, $key) use ($service_date, $commonClass) {                      
                    $frequency = $commonClass->getFrequency($vatreg->general_periods);    
                  
                    return  (
                      (Carbon::parse($service_date)->format('Ymd') >= Carbon::parse($vatreg->service_start)->format('Ymd')) && 
                      (Carbon::parse($service_date)->format('Ymd') <= Carbon::parse($vatreg->service_start)->addMonth($frequency-1)->endOfMonth()->format('Ymd'))
                      )
                    ;
                  });

                  if(count($filtered_vatregs) > 0)
                    $match_vatreg = $filtered_vatregs->first();
                } 
              } //if SERVICE DATE IS NULL
              else
              {
                $ir_com_invoice = ImportReconciliationComInvoices::where('expo_no', $final_expo_no)
                  ->where('lope_no', $final_lope_no)
                  ->first();

                if($ir_com_invoice)  
                {
                  $vat_reg_id = $ir_com_invoice->vat_reg_id;  

                  //GET MATCH VATREG.                 
                  $match_vatreg = $commonClass->getVatRegLazy($vat_reg_id, [], []);
                }                
              } //else SERVICE DATE IS NOT NULL
 //dd($filename, $vat_reg_main_id, $service_date, $cargo_date, $lope_no, $expo_no, $com_invoice_no, $com_invoice_date, $final_expo_no, $final_lope_no, $match_vatreg); 
              if($match_vatreg)
                return [
                  'match_vatreg' => $match_vatreg,
                  'cargo_date' => ($cargo_date == '') ? $com_invoice_date : $cargo_date,
                  'expo_no' => $final_expo_no, //$expo_no,
                  'lope_no' => $final_lope_no, //$lope_no,
                  'com_invoice_no' => $com_invoice_no,
                  'com_invoice_date' => $com_invoice_date
                ];
              else
                return []; 
            }           
        } 
        catch (\Exception $e) {
            //dd($e);  
          //return ['error' => $e->getMessage()]; 
          return "Error: " . $e->getMessage();
        }       
    }    
}
