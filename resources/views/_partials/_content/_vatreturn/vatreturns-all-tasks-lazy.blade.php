{{--
@php      
  $filtered_result = $result->filter(function ($vatreg, $key) {         
      return (\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd')); 
  });         
@endphp
@if(count($filtered_result) == 0)  
  @include('_partials/_content/_tasks/no-tasks-lazy')    
@else
  @foreach ($filtered_result as $key => $vatreg)
--}}  

@php   
  $final_result = $result;
@endphp

@if(isset($title))
  @php      
    $filtered_result = $result->filter(function ($vatreg, $key) {         
        return (\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd')); 
    }); 
    $final_result = $filtered_result;        
  @endphp
@endif

{{--
@if(count($final_result) == 0)  
  @include('_partials/_content/_tasks/no-tasks-lazy')    
@else
--}}
  @foreach ($final_result->lazy() as $key => $vatreg)
    @php
      $client = $vatreg->client;
      $client_id = $client->client_id;

      $vat_reg_main = $vatreg->vatregmain;
      $client_api = $vat_reg_main->clientapi;
      $vatregmain_status = $vat_reg_main->status;
      
      $vat_reg_id = $vatreg->vat_reg_id; 
      $product_type = $vat_reg_main->product_type; 
      $product_type_name = '';
      if($product_type == 1)
      {
        if($vatreg->country == "NO") 
          $product_type_name = 'NUF VAT Return';
        else
          $product_type_name = 'VAT Return';  
      }
      else if($product_type == 2)
        $product_type_name = 'Import Reconciliation';
      else if($product_type == 4)
        $product_type_name = 'VOEC VAT Return';  
      else if($product_type == 3 || $product_type == 5)
      {
        if($check_product_type == 1)
          $product_type_name = 'VAT Return';
        else if($check_product_type == 2)
          $product_type_name = 'Import Reconciliation';
      }
      
      $page_type = 'my-tasks';

      //$vatreturn = $vatreg->api_ledger;
      $vatreturns = $vatreg->vatreturns;
      $vatreturn_notes = ($vatreg->vatreturnnotes) ? $vatreg->vatreturnnotes : [];
      $importreconciliation_notes = ($vatreg->importreconciliationnotes) ? $vatreg->importreconciliationnotes : [];
      //$importreconciliations = $vatreg->importreconciliations;

      $client_users = $client->userclient;  

      $pivs_files = ($vatreg->pivs) ? $vatreg->pivs : [];  
      $vatreturnfiles = ($vatreg->vatreturnfiles) ? $vatreg->vatreturnfiles : [];   
      $vatcontrolfiles = ($vatreg->vatcontrolfiles) ? $vatreg->vatcontrolfiles : []; 
      $ircontrolfiles = ($vatreg->ircontrolfiles) ? $vatreg->ircontrolfiles : []; 
      //$excelcolumntemplate = ($vatreg->excelcolumntemplate) ? $vatreg->excelcolumntemplate : null;   
      $anyexceltemplate = ($vatreg->anyexceltemplate) ? $vatreg->anyexceltemplate : null;   
      //$histories = $vatreg->timelines;  
      $documents = ($vatreg->documents) ? $vatreg->documents : [];  
      $c79_documents = ($vatreg->c79) ? $vatreg->c79: [];  

      $importreconciliationanyexcelfiles = ($vatreg->importreconciliationanyexcelfiles) ? $vatreg->importreconciliationanyexcelfiles : [];   
      $importreconciliationsalesinvoices = ($vatreg->importreconciliationsalesinvoices) ? $vatreg->importreconciliationsalesinvoices : [];   
      
      $import_vat_files = [];
      if($vatreg->country == 'NO')
      {
        $import_vat_files = ($vatreg->importvatfiles) ? $vatreg->importvatfiles : [];

        if($import_vat_files)
        {
          $import_vat_files_all = $import_vat_files;

          //$filtered_import_vat_files = $import_vat_files->filter(function ($import_vat_file, $key) {         
          $filtered_import_vat_files = $import_vat_files_all->filter(function ($import_vat_file, $key) {         
              return $import_vat_file->file_type == 'xml'; 
          });

          $import_vat_files = $filtered_import_vat_files;          
        }

        $submitting_fields = ($vatreg->submittingfieldsNO) ? $vatreg->submittingfieldsNO : [];
        $commercial_invoices_files = ($vatreg->commercialinvoicesfiles) ? $vatreg->commercialinvoicesfiles : [];

        $invoices = ($vatreg->invoices) ? $vatreg->invoices : [];

        $missing_commercial_invoices = '';
        foreach ($commercial_invoices_files as $key => $commercial_invoices_file)
        {
          if($commercial_invoices_file->sale_invoice_nos != '')
          {     
            $sale_invoice_nos = explode(',', $commercial_invoices_file->sale_invoice_nos);

            if(count($invoices) == 0)
            {
              if($missing_commercial_invoices == '')
                $missing_commercial_invoices = $commercial_invoices_file->sale_invoice_nos;
              else
                $missing_commercial_invoices .= ', ' . $commercial_invoices_file->sale_invoice_nos;     
            } /* --end if MISSING COMMERCIAL INVOICES -- */  
            else        
            {
              $filtered_invoices = $invoices->filter(function ($invoice, $key) use($sale_invoice_nos) {       
                    return (in_array($invoice->invoice_no, $sale_invoice_nos)) ? null : $invoice->invoice_no; 
                });               
            } /* --end else MISSING COMMERCIAL INVOICES -- */             
          } /* --end if COMMERCIAL INVOICES -- */
        } /* --end for COMMERCIAL INVOICES -- */
      }

      if($vatreg->country == 'GB')   
        $submitting_fields = ($vatreg->submittingfields) ? $vatreg->submittingfields : [];   
      else if($vatreg->country == 'CH') 
      {
        $importreconciliationcominvoices = ($vatreg->importreconciliationcominvoices) ? $vatreg->importreconciliationcominvoices : [];   
        $submitting_fields = ($vatreg->submittingfieldsCH) ? $vatreg->submittingfieldsCH : [];
      }

      if($vatreg->country == 'GB' && $vat_reg_main->cash_acc_stmt == 1)       
        $cash_account_statement_files = ($vatreg->cas) ? $vatreg->cas : [];     

      if($vatreg->country == 'NO' && $vat_reg_main->duty_defer_acc == 1)      
        $duty_deferment_account_files = ($vatreg->dda) ? $vatreg->dda: [];                

      $currencycode = ''; 
      $currencylocale = 'en_US';        
      if($vatreg->country == "DK")
      {
          $currencycode = "DKK";
          $currencylocale = 'da_DK';
      }
      elseif($vatreg->country == "NO") 
      { 
          $currencycode = "NOK";
          //$currencylocale = 'no_NO';
          $currencylocale = 'da_DK';
      }
      elseif($vatreg->country == "SE") 
      { 
          $currencycode = "SEK";
          $currencylocale = 'sv_SE';
      }
      elseif($vatreg->country == "GB")
      {
          $currencycode = "GBP";
          $currencylocale = 'en_GB';
      }
      elseif($vatreg->country == "IN")  
      {
          $currencycode = "INR";
          $currencylocale = 'en_IN';
      }
      elseif($vatreg->country == "FR")  
      {
          $currencycode = "EUR";
          $currencylocale = 'fr_FR';
      }
      elseif($vatreg->country == "CH")  
      {
          $currencycode = "CHF";
          $currencylocale = 'fr_FR';
      }

      $currencyFormatter = new NumberFormatter($currencylocale, NumberFormatter::DECIMAL);  
      $currencyFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
      //$currencySymbol = $currencyFormatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);    
      $currencySymbol = $currencycode;
      
      $totalnet = 0;
      $purchasetotalnet = 0;
      $salestotalnet = 0;

      $totalvat = 0;
      $purchasetotalvat = 0;
      $salestotalvat = 0;
      
      foreach ($vatreturns as $key => $vatreturn)
      {
        if(str_starts_with($vatreturn->currency_code, $vatreg->country) || ($vatreg->country == "FR"))
        {
          if($vatreturn->invoice_type == 'sale')
            $salestotalvat += $vatreturn->vat_amount;
          elseif($vatreturn->invoice_type == 'purchase')
            $purchasetotalvat += $vatreturn->vat_amount;
        }
      }

      if($client_api)
      {
        if($client_api->api_name == 'E-conomic')           
          $totalvat = $salestotalvat + $purchasetotalvat;       
        else        
          $totalvat = $salestotalvat - $purchasetotalvat;            
      }
      else        
        $totalvat = $salestotalvat - $purchasetotalvat;

      if($vatreg->country == 'NO')
      {
        $sales_standard_totalvat = 0; 
        $sales_medium_totalvat = 0; 
        $sales_low_totalvat = 0;
        $sales_zero_totalvat = 0;
        $sales_fish_totalvat = 0;

        $sales_standard_totalnet = 0;
        $sales_medium_totalnet = 0;
        $sales_low_totalnet = 0;
        $sales_zero_totalnet = 0;
        $sales_fish_totalnet = 0;

        $purchases_standard_totalvat = 0; 
        $purchases_medium_totalvat = 0; 
        $purchases_low_totalvat = 0;
        $purchases_zero_totalvat = 0;
        $purchases_fish_totalvat = 0;

        $purchases_standard_totalnet = 0;
        $purchases_medium_totalnet = 0;
        $purchases_low_totalnet = 0;
        $purchases_zero_totalnet = 0;
        $purchases_fish_totalnet = 0;
      }

      $show_vatreturn = 0;            
      if(($client_api === null)) 
      {
        if((count($vatreturnfiles) > 0) || count($vatreturns) > 0)
            $show_vatreturn = 1;
        else
            $show_vatreturn = 0;   
      } 
      else
      {
        if(count($vatreturns) == 0)
            $show_vatreturn = 0;
        else
            $show_vatreturn = 1;  
      }  

      /*
      $show_importreconciliation = 0;            
      if(count($importreconciliations) == 0 || count($importreconciliationfiles) == 0)
          $show_importreconciliation = 0;            
      else
          $show_importreconciliation = 1; 
          */

      $show_importreconciliation = 0;            
      if(count($import_vat_files) == 0 && count($importreconciliationsalesinvoices) == 0)
          $show_importreconciliation = 0;            
      else
          $show_importreconciliation = 1;    
    @endphp

    @include('_partials/_content/_vatreturn/vatreturns-list-lazy')    
  @endforeach
{{--
@endif  
--}}