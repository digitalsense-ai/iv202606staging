<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>VAT Returns - {{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                  \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</title> 
   
    <!-- <style type="text/css">    
      @font-face {
          font-family: 'Rubik';
          font-weight: normal;
          font-style: normal;
          font-variant: normal;
          src: url({{ storage_path('fonts/Rubik/Rubik-VariableFont_wght.ttf') }}) format('truetype');
      }

      body { font-family: 'Rubik', sans-serif; color: #516377; line-height: 1.1; font-size: 0.9375rem; }  
      table { border-collapse: collapse; }
      tr.header th,
      td { padding: 0.625rem 0; border-bottom: 1px solid #d4d8dd; }     
      h4 { font-size: 1.375rem; line-height: 1.1; color: #516377; padding: 0; margin: 0; }
      p { padding: 0; margin: 0; }
      .table-layout { table-layout: fixed; }
      .main-table { border: 1px solid #d4d8dd; }
      .border { border: 1px solid #d4d8dd; }
      .border-left { border-left: 1px solid #d4d8dd; }
      .border-right { border-right: 1px solid #d4d8dd; }
      .border-top { border-top: 1px solid #d4d8dd; }
      .border-bottom { border-bottom: 1px solid #d4d8dd; }
      .border-none { border: none; }
      .border-radius { border-radius: 5px; }

      .padding { padding: 10px; }
      .padding-left { padding-left: 10px; }
      .padding-right { padding-right: 10px; }
      .padding-top { padding-top: 10px; }
      .padding-bottom { padding-bottom: 10px; }

      .mb-2 { margin-bottom: 0.5rem !important; }
      .separator { height: 5px; border-bottom: 1px solid #cccccc; }
      
      .full-width { width: 100%; }
      .text-red { color: #cf2b20; }
      .text-blue { color: #003d78; text-decoration: underline; }
      .bold { font-weight: bold; }
      .uppercase { text-transform: uppercase; }

      .fw-normal { font-weight: normal; }

      .wrap-txt { word-wrap:break-word; }

      .text-start { text-align: left; }
      .text-center { text-align: center; }
      .text-end { text-align: right; }
      
      .w-100 { width: 100%; }
      .m-0 { margin: 0; }
      .p-0 { padding: 0; }
      .border-0 { border: 0; }
      h6 { font-size: 0.9375rem; }

      .alert { padding: 0.75rem 1.25rem; }
      .alert-primary { background-color: #e5edfc; border-color: #ceddfa; color: #5a8dee; }
      .alert h6 { font-size: 0.9375rem; margin: 0; padding: 0; }

      .subject { font-size: 1.5rem; color: #5a8dee; }
      tbody tr td { font-weight: normal; }
     
      header, footer { position: fixed; left: 0; right: 0; }
      header {  top: 0; text-align: center; }
      footer { background-color: rgba(229, 233, 237, 1); padding: 1rem; bottom: 0;  }


      .card-body { padding: 1.375rem; flex: 1 1 auto; }
      .border-primary { border-color: #5a8dee !important; }
      .border-2 { border-width: 2px !important; }
      h5, .h5 { font-size: 1.125rem; }
      .bg-primary { background-color: #5a8dee !important; }
      .rounded-pill { border-radius: 50rem !important; }
      .bg-primary { --bs-bg-opacity: 1; background-color: rgba(#5a8dee, 1) !important; }
      .text-white { color: #fff !important; }
      .text-uppercase { text-transform: uppercase !important; }
      .py-1 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
      .px-2 { padding-right: 0.5rem !important; padding-left: 0.5rem !important; }
      .mt-n1 { margin-top: -0.25rem !important; }
      .mb-0 { margin-bottom: 0 !important; }    
      .d-flex { display: -ms-flexbox !important; display: flex !important; }
      
    </style> --> 
    <style type="text/css">   
      @page { margin: 0in; }  
      html, body { margin: 0; padding: 0; }  

      @font-face {
          font-family: 'Rubik';
          font-weight: normal;
          font-style: normal;
          font-variant: normal;
          src: url({{ storage_path('fonts/Rubik/Rubik-VariableFont_wght.ttf') }}) format('truetype');
      }

      body { font-family: 'Rubik', sans-serif; color: #516377; line-height: 1.1; font-size: 0.9375rem; font-weight: normal; }  
      table { border-collapse: collapse; margin: 0; padding: 0; }

      .cover-page { background-color: #003056; color: #fff; min-height: 100%; }
      .rest-page { background-color: #ffffff; color: #677788; min-height: 100%; }
      .full-width { width: 100%; }
    
      .gap-1 { height: 300px; }
      .fw-normal { font-weight: normal; }

      table { padding: 1%; }      
      h1 { font-size: 4rem; }
      h2 { font-size: 2rem; padding: 0; margin: 0 0 1rem; }
      h5, h6 { padding: 0; margin: 0; }
      h5 { font-size: 0.9375rem; font-weight: normal; }
      
      .border-none {  border: none !important; }
      .position-absolute { position: absolute !important; left: 0 !important; } 
      .w-100 { width: 100% !important; }
      .m-0 { margin: 0 !important; }
      .p-0 { padding: 0 !important; }      

      .rest-page h6 { color: #516377;  font-size: 0.9375rem;  font-weight: normal; }
      .rest-page table.fixed { padding: 2.375rem !important; }

      .logo { margin: 1rem 0; width: 160px; }

      .inner-tbl { padding: 1rem 0 !important; }
      .inner-tbl th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; font-weight: bold; }
      .inner-tbl th,
      .inner-tbl td  { padding: 0.625rem 1.5rem; border-bottom: 0.2px solid #d4d8dd; }
      .inner-tbl td h6 { text-transform: none; font-weight: bold; }

      .page-break { page-break-before: always; }  

      .border-primary { border: 2px solid #5a8dee !important; box-shadow: 0 2px 14px rgba(38, 60, 85, 0.16); 
        border-radius: 0.3125rem;  }
      .card-body { padding: 1.375rem; }
      .text-uppercase { text-transform: uppercase !important; }
      .bg-primary { background-color: #5a8dee !important; }
      .rounded-pill { border-radius: 50rem !important; }
      .text-white { color: #fff; }
      .text-start { text-align: left; }
      .text-end { text-align: right; }
      .py-1 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
      .px-2 { padding-right: 0.5rem !important; padding-left: 0.5rem !important; }
      .p-2 { padding: 0.5rem !important; }
      .pt-4 { padding-top: 1.5rem !important; }
      .pb-4 { padding-bottom: 1.5rem !important; }
      .pe-4 { padding-right: 1.5rem !important; }
      .mb-2 { margin-bottom: 0.5rem !important; }
      .fw-bold { font-weight: bold !important; }
      .v-middle { vertical-align: middle !important; }
      .my-1 { margin-top: 1rem !important; margin-bottom: 1rem !important; }
      .my-2 { margin-top: 2rem !important; margin-bottom: 2rem !important; }
      .mt-2-only { margin-top: 2rem !important; }
      .mb-2-only { margin-bottom: 2rem !important; }

      .pb-0 { padding-bottom: 0 !important; }
      
      .d-none { display: none; } 

      .border-1 { border: 0.2px solid #d4d8dd !important; }    
      .border-start { border-left: 0.2px solid #d4d8dd !important; }    
      .border-top { border-top: 0.2px solid #d4d8dd !important; }    
      .border-bottom { border-bottom: 0.2px solid #d4d8dd !important; }    
      .border-end { border-right: 0.2px solid #d4d8dd !important; }  

      tr.odd-row td { background-color: #f8f9fa; color: #677788; }  

      /*extra*/
      .h4 { font-size: 1.375rem; color: #516377; padding: 0; margin: 0; }
    </style>
</head>

<body>  
  <!-- Content Page -->
  <div class="full-width p-1 rest-page" style="position: relative;">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">      
      <thead>    
        <tr>
          <th align="right" valign="middle" colspan="6">  
            <img src="<?php echo $logo ?>" width="25%" class="mb-2-only">
          </th>
        </tr>
       
        <tr>
          <th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="2">Company Name:</th>
          <th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="3">{{ $client->client_name }}</th>
          <th class="h4 p-0 m-0" align="left" valign="middle" colspan="1">VAT Return</th>
        </tr>

        <tr>
          <th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="2">VAT No.:</th>
          <th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="3">{{ $client->vatno }}</th>
          <th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="1">
            <p class="fw-normal p-0 m-0">{{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                  \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</p>
          </th>
        </tr>                    
      </thead>

      <tbody>
        @php
          $page = 'confirm';
        @endphp
        @include('_partials/_content/_vatreturn/vatreturn-overview-pdf')
      </tbody>
    </table>    
   
    @php
      $vatreturns = $vatreg->vatreturns;      
      $page_no = (count($vatreturns) > 2) ? 2 : 1;
    @endphp
    @include('_partials/_content/_previewreport/footer-pdf') 
  </div>
  <!--/ Content Page -->

  {{--
  <div class="full-width">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="main-table border-none">         
      <thead>    
        <tr>
          <th align="left" valign="middle" colspan="6">  
            <img src="<?php echo $logo ?>" width="25%">
          </th>
        </tr>

        <tr>
          <th class="padding-top fw-normal" align="left" valign="middle" colspan="2">Company Name:</th>
          <th class="padding-top fw-normal" align="left" valign="middle" colspan="3">{{ $client->client_name }}</th>
          <th class="padding-top fw-normal" align="left" valign="middle" colspan="1"><h4>VAT Return</h4></th>
        </tr>

        <tr>
          <th class="padding-bottom fw-normal" align="left" valign="middle" colspan="2">VAT No.:</th>
          <th class="padding-bottom fw-normal" align="left" valign="middle" colspan="3">{{ $client->vatno }}</th>
          <th class="padding-top fw-normal" align="left" valign="middle" colspan="1">
            <p class="padding-bottom fw-normal">{{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                  \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</p>
          </th>
        </tr>
        
        <tr>
          <td colspan="6" class="border-0 p-0">
            <h6>Sales and Purchases</h6>   
            <!-- <div class="alert alert-secondary m-0">Text</div> -->                    
          </td>
        </tr> 
        <tr class="header">
          <th class="text-start" width="5%">Item</th>
          <th class="text-start" width="20%">Description</th>
          <th class="text-center" width="5%">Invoices</th>
          <th class="text-end" width="10%">% VAT</th>             
          <th class="text-end" width="25%">NET</th>
          <th class="text-end">VAT</th>
        </tr>
       
      </thead>
      <tbody> 
        @include('_partials/_content/_vatreturn/vatreturn-overview-pdf')   

       
        @php
          $footer_currencySymbol = '';

          $salestotalvat = 0;
          $purchasetotalvat = 0;

          $salestotalnet = 0;
          $purchasetotalnet = 0;
        @endphp  

        @if(count($vatreturns) > 0)
          @php
            /*
            $salestotalvat = 0;
            $purchasetotalvat = 0;

            $salestotalnet = 0;
            $purchasetotalnet = 0;
            */
          @endphp
          @foreach ($vatreturns as $key => $vatreturn)
            @php      
              $vat_percentage = str_replace('.00', '', $vatreturn->vat_percentage) . '%';

                $currencylocale = 'en_US';                  
                if($vatreturn->currency_code == 'DKK' || $vatreturn->currency_code == 'NOK')
                  $currencylocale = 'da_DK';
                //else if($vatreturn->currency_code == 'NOK')
                  //$currencylocale = 'no_NO';
                else if($vatreturn->currency_code == 'SEK')
                  $currencylocale = 'sv_SE';
                else if($vatreturn->currency_code == 'GBP')
                  $currencylocale = 'en_GB';                
                else if($vatreturn->currency_code == 'INR')
                  $currencylocale = 'en_IN';
                else if($vatreturn->currency_code == 'EUR')
                  $currencylocale = 'fr_FR';
                              
                $currencyFormatter = new NumberFormatter($currencylocale, NumberFormatter::DECIMAL);  
                $currencyFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);                
                $currencySymbol = $vatreturn->currency_code;

                if(str_starts_with($vatreturn->currency_code, $vatreg->country) || ($vatreg->country == "FR"))
                {                 
                  if($vatreturn->invoice_type == 'sale')
                  {                   
                    $salestotalnet += $vatreturn->net_amount;
                    $salestotalvat += $vatreturn->vat_amount;

                    if($vatreg->country == 'NO')
                    {
                      if($vat_percentage == "25%")
                      {
                        $sales_standard_totalnet += $vatreturn->net_amount;
                        $sales_standard_totalvat += $vatreturn->vat_amount;
                      }
                      else if($vat_percentage == "15%")
                      {
                        $sales_medium_totalnet += $vatreturn->net_amount;
                        $sales_medium_totalvat += $vatreturn->vat_amount;
                      }
                      else if($vat_percentage == "12%")
                      {
                        $sales_low_totalnet += $vatreturn->net_amount;
                        $sales_low_totalvat += $vatreturn->vat_amount;
                      }
                      else if($vat_percentage == "0%")
                      {
                        $sales_zero_totalnet += $vatreturn->net_amount;
                        $sales_zero_totalvat += $vatreturn->vat_amount;
                      }
                      else if($vat_percentage == "11.11%")
                      {
                        $sales_fish_totalnet += $vatreturn->net_amount;
                        $sales_fish_totalvat += $vatreturn->vat_amount;
                      }
                    }
                  }

                  if($vatreturn->invoice_type == 'purchase')
                  {
                    $purchasetotalnet += $vatreturn->net_amount;
                    $purchasetotalvat += $vatreturn->vat_amount;

                    if($vatreg->country == 'NO')
                    {
                      if($vat_percentage == "25%")
                      {
                        $purchases_standard_totalnet += $vatreturn->net_amount;
                        $purchases_standard_totalvat += $vatreturn->vat_amount;
                      }
                      else if($vat_percentage == "15%")
                      {
                        $purchases_medium_totalnet += $vatreturn->net_amount;
                        $purchases_medium_totalvat += $vatreturn->vat_amount;
                      }
                      else if($vat_percentage == "12%")
                      {
                        $purchases_low_totalnet += $vatreturn->net_amount;
                        $purchases_low_totalvat += $vatreturn->vat_amount;
                      }
                      else if($vat_percentage == "0%")
                      {
                        $purchases_zero_totalnet += $vatreturn->net_amount;
                        $purchases_zero_totalvat += $vatreturn->vat_amount;
                      }
                      else if($vat_percentage == "11.11%")
                      {
                        $purchases_fish_totalnet += $vatreturn->net_amount;
                        $purchases_fish_totalvat += $vatreturn->vat_amount;
                      }
                    }                               
                  }

                  $currencycode = $vatreturn->currency_code;                
                  $footer_currencySymbol = $currencycode;
                }                                   
              @endphp       
              <tr class="{{ (str_starts_with($vatreturn->currency_code, $vatreg->country)) ? '' : 'text-danger'}}">
                <td class="text-nowrap">{{ ($vatreturn->invoice_type == 'sale') ? 'Sales' : 'Purchases' }}</td>
                <td class="text-nowrap">{{ ($vatreturn->invoice_type == 'sale') ? 'Sale' : 'Purchase' }} Invoice</td>
                <td class="text-center">{{ $vatreturn->invoice_count }}</td>
                <td class="text-end">{{ $vat_percentage }}</td> 
                <td class="text-end">{{ ($vatreturn->invoice_type == 'purchase' && $vatreturn->net_amount > 0) ? '- ' : '' }}{{ $currencyFormatter->format($vatreturn->net_amount) . ' ' . (($currencySymbol) ? $currencySymbol : $vatreturn->currency_code) }}</td>
                <td class="text-end">{{ ($vatreturn->invoice_type == 'purchase' && $vatreturn->vat_amount > 0) ? '- ' : '' }}{{ $currencyFormatter->format($vatreturn->vat_amount) . ' ' . (($currencySymbol) ? $currencySymbol : $vatreturn->currency_code) }}</td>
              </tr>               
          @endforeach           
          @php
            if($client_api)
            {
              if($client_api->api_name == 'E-conomic')   
              {
                $totalnet = $salestotalnet + $purchasetotalnet;
                $totalvat = $salestotalvat + $purchasetotalvat;
              }   
              else
              {       
                $totalnet = $salestotalnet - $purchasetotalnet;
                $totalvat = $salestotalvat - $purchasetotalvat;    
              }
            }
            else
            {
              $totalnet = $salestotalnet - $purchasetotalnet;
              $totalvat = $salestotalvat - $purchasetotalvat;    
            }               
          @endphp                                            

          <tr>
            <td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
            <td class="text-end px-4 py-5 border-bottom-0">
              <p class="mb-2">Subtotal:</p>                     
              <p class="mb-2">Tax:</p>
              <p class="mb-0">Total:</p>
            </td>
            <td class="px-4 py-5 border-bottom-0">
              <p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($totalnet) . ' ' . $footer_currencySymbol }}</p>
              <p class="fw-semibold mb-2 text-end" id="total-tax-{{ $vat_reg_id }}">{{ $currencyFormatter->format($totalvat) . ' ' . $footer_currencySymbol }}</p>
              <p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format(($totalnet + $totalvat)) . ' ' . $footer_currencySymbol }}</p>
            </td>                  
          </tr>
        @else               
          @php
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
            $currencySymbol = $currencycode;
            $footer_currencySymbol = $currencycode;
          @endphp 
          @include('_partials/_content/_vatreturn/vatreturn-no-invoices-lazy')     
        @endif

        @if($tab_name == 'confirm')
          <!--PIVS-->
          @php
            $pivsmonthtotal = 0;              
          @endphp
          @if(count($pivs_files) > 0)             
            <tr>
              <td colspan="6" class="border-0 p-0">
                <h6>Postponed import VAT statement</h6>   
                <!-- <div class="alert alert-secondary m-0">Text</div> -->                    
              </td>
            </tr>               
            @foreach ($pivs_files as $key => $pivs)
              @php
                $pivsmonthtotal += $pivs->month_total;              
              @endphp
              <tr>
                  <td class="text-nowrap">{{ \Carbon\Carbon::parse('01-' .$pivs->month_year)->format('F') }}</td>
                  <td class="text-nowrap">PIVS</td>
                  <td class="text-center">-</td>
                  <td class="text-end">-</td> 
                  <td class="text-end">{{ $currencyFormatter->format(($pivs->month_total * 100)/20) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
                  <td class="text-end">{{ $currencyFormatter->format($pivs->month_total) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
                </tr>
            @endforeach
            <tr>
              <td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
              <td class="text-end px-4 py-5 border-bottom-0">
                <p class="mb-2">Subtotal:</p>                     
                <p class="mb-2">Tax:</p>
                <p class="mb-0">Total:</p>
              </td>
              <td class="px-4 py-5 border-bottom-0">
                <p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format(($pivsmonthtotal * 100)/20) . ' ' . $footer_currencySymbol }}</p>
                <p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($pivsmonthtotal) . ' ' . $footer_currencySymbol }}</p>
                <p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format((($pivsmonthtotal * 100)/20) + $pivsmonthtotal) . ' ' . $footer_currencySymbol }}</p>
              </td>                    
            </tr>
          @endif
          <!--/ PIVS-->

          <!--C79-->
          @php
            $c79numbers = 0;              
          @endphp
          @if(count($c79_documents) > 0)              
            <tr>
              <td colspan="6" class="border-0 p-0">
                <h6>C79 Import VAT Certificate</h6> 
                <!-- <div class="alert alert-secondary m-0">Text</div> -->      
              </td>
            </tr>            
            @foreach ($c79_documents as $key => $c79_document)
              @php
                $c79numbers += $c79_document->doc_numbers;              
              @endphp
              <tr>
                  <td class="text-nowrap">{{ \Carbon\Carbon::parse('01-' .$c79_document->month_year)->format('F') }}</td>
                  <td class="text-nowrap">C79</td>
                  <td class="text-center">-</td>
                  <td class="text-end">-</td> 
                  <td class="text-end">{{ $currencyFormatter->format(($c79_document->doc_numbers * 100)/20) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
                  <td class="text-end">{{ $currencyFormatter->format($c79_document->doc_numbers) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
                </tr>
            @endforeach
            <tr>
              <td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
              <td class="text-end px-4 py-5 border-bottom-0">
                <p class="mb-2">Subtotal:</p>                     
                <p class="mb-2">Tax:</p>
                <p class="mb-0">Total:</p>
              </td>
              <td class="px-4 py-5 border-bottom-0">
                <p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format(($c79numbers * 100)/20) . ' ' . $footer_currencySymbol }}</p>
                <p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($c79numbers) . ' ' . $footer_currencySymbol }}</p>
                <p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format((($c79numbers * 100)/20) + $c79numbers) . ' ' . $footer_currencySymbol }}</p>
              </td>                    
            </tr>
          @endif
          <!--/ C79-->

          <!--BOX 5-->
          @php
            if($vatreg->submittingfields)
              $_box5 = $vatreg->submittingfields->box_5;
            else
              $_box5 = (($pivsmonthtotal + $salestotalvat) - ($c79numbers + $pivsmonthtotal + $purchasetotalvat));
          @endphp          
          <tr>
            <td colspan="1" class="border-0"></td>  
            <td colspan="5" class="align-top px-4 py-5 border-bottom-0">
              <div class="card border border-2 border-primary">
                <div class="card-body p-0">
                  <div class="d-flex justify-content-between flex-wrap mb-3">
                    <h5 class="text-start text-uppercase mb-0">Net VAT to pay to {{ ($vatreg->country == "NO") ? 'SKATTEETATEN' : 'HMRC' }} or reclaim:</h5>
                    <h5 class="width-auto bg-primary rounded-pill text-uppercase mb-0 py-1 px-2 mt-n1 text-white">{{ $currencyFormatter->format($_box5) . ' ' . $footer_currencySymbol }}</h5>
                  </div>
                 
                  <p>Intravat will report this VAT amount to the authorities, and you will subsequently receive documentation and payment details.</p>
                </div>
              </div>                           
            </td>                    
          </tr>
          <!--/ BOX 5-->
        @endif    
   
      </tbody>
    </table>
  </div>  --}}
</body>
</html>