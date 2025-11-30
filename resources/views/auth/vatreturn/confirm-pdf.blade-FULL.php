<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>VAT Returns - {{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                  \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</title> 
   
    <style type="text/css">       
      body { font-family: Courier; color: #516377; line-height: 1.1; font-size: 0.9375rem; font-weight: 500; }  
      table { border-collapse: collapse; }
      tr.header th,
      td { padding: 0.625rem 0; border-bottom: 1px solid #d4d8dd; }
      /*tr.header th {  border-top: 1px solid #d4d8dd; }*/
      h4 { font-size: 1.375rem; font-weight: 500; line-height: 1.1; color: #516377; padding: 0; margin: 0; }
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

      /*.position-absolute { position: absolute; left: -1.375px; right: -1.375px; border-color: #d4d8dd; }*/
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
      /*.width-auto { width: auto; }*/
      .d-flex { display: -ms-flexbox !important; display: flex !important; }
      
    </style> 
</head>

<body>

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
        {{--
        <tr>
          <th class="padding-top border-bottom" align="left" valign="middle" colspan="6">                
            <h4>VAT Return</h4>
            <p class="padding-bottom fw-normal">{{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                  \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</p>        
          </th>
        </tr> 
        <tr>
          <th class="padding-top fw-normal" align="left" valign="middle" colspan="2">Company Name:</th>
          <th class="padding-top fw-normal" align="left" valign="middle" colspan="4">{{ $client->client_name }}</th>
        </tr>
        <tr>
          <th class="padding-bottom fw-normal" align="left" valign="middle" colspan="2">VAT No.:</th>
          <th class="padding-bottom fw-normal" align="left" valign="middle" colspan="4">{{ $client->vatno }}</th>
        </tr>
        --}}    
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
            $totalnet = $salestotalnet - $purchasetotalnet;
            $totalvat = $salestotalvat - $purchasetotalvat;                   
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
          {{--
          <tr>
            <td colspan="6" class="align-top px-4 py-5 border-bottom-0">
              <div class="alert alert-primary">
              <h6 class="alert-heading mb-1">Net VAT to pay to HMRC or reclaim: {{ $currencyFormatter->format($_box5) . ' ' . $footer_currencySymbol }}</h6>
              <span>Intravat will report this VAT amount to the authorities, and you will subsequently receive documentation and payment details.</span>
            </div>                           
            </td>                    
          </tr>
          --}}
          <tr>
            <td colspan="1" class="border-0"></td>  
            <td colspan="5" class="align-top px-4 py-5 border-bottom-0">
              <div class="card border border-2 border-primary">
                <div class="card-body p-0">
                  <div class="d-flex justify-content-between flex-wrap mb-3">
                    <h5 class="text-start text-uppercase mb-0">Net VAT to pay to HMRC or reclaim:</h5>
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
  </div> 

{{--
  <!-- <header>
      <img src="<?php //echo $logo ?>" width="50%"> 
  </header> -->

  <table width="100%" cellspacing="0" cellpadding="10" border="0" align="center" class="main-table border-none">
    <!-- <thead> --> <!-- uncomment this if need header for all pages --> 
    <thead>  
        <tr>
          <td align="center" valign="middle">  
            <img src="<?php echo $logo ?>" width="50%"><br>          
          </td>
        </tr>

        <tr><td class="separator"></td></tr>
    </thead>

    <tbody>    
        <tr>
          <td align="center" valign="middle" class="subject bold">  
            {{ $data['subject'] }}
          </td>
        </tr>

        <tr>
          <td>          
            <table width="100%" border="0" cellspacing="0" cellpadding="10" class="border-none">  
              <thead>
                <tr>
                  <th align="left" class="border-top border-bottom">Item</th>
                  <th align="left" class="border-top border-bottom">Description</th>
                  <th align="left" class="border-top border-bottom">% VAT</th>
                  <th align="left" class="border-top border-bottom">VAT</th>
                  <th align="left" class="border-top border-bottom">NET</th>
                </tr>
              </thead>
              <tbody>              
                @php         
                  $netamount = 0; 
                  $totalvat = 0;  
                  $currencycode = '';  

                  $currencylocale = 'en_US';
                  if($data['client']['currency_code'] == 'DKK')
                    $currencylocale = 'da_DK';
                  else if($data['client']['currency_code'] == 'INR')
                    $currencylocale = 'en_IN';
                  else if($data['client']['currency_code'] == 'EUR')
                    $currencylocale = 'fr_FR';  
                                
                  $currencyFormatter = new NumberFormatter($currencylocale, NumberFormatter::DECIMAL);
                  $currencyFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);  
                @endphp

                @foreach ($data['client'] as $key => $salespurchases)
                  @if($key == 'sale' || $key == 'purchase')
                    @foreach ($salespurchases as $salepurchase_currency) 
                      @foreach ($salepurchase_currency as $salepurchase)
                        @php                                          
                          $netamount += $salepurchase['net_amount'];
                          $totalvat += $salepurchase['vat_amount'];

                          $currencycode = $salepurchase['currency_code'];                                    
                        @endphp                    
                        <tr>
                          <td class="border-bottom">{{ ($key == 'sale') ? 'Sales' : 'Purchases' }}</td>
                          <td class="border-bottom">{{ ($key == 'sale') ? 'Sale' : 'Purchase' }} Invoice</td>
                          <td class="border-bottom">{{ $salepurchase['vat_percentage'] }}</td>
                          <td class="border-bottom">{{ $currencyFormatter->format($salepurchase['vat_amount']) . ' ' . $currencycode }}</td>
                          <td class="border-bottom">{{ $currencyFormatter->format($salepurchase['net_amount']) . ' ' . $currencycode }}</td>
                        </tr>  
                      @endforeach                      
                    @endforeach  
                  @endif
                @endforeach
                
                <tr>
                  <td colspan="3" class="align-top px-4 py-5 border-bottom-0">
                    
                  </td>
                  <td class="text-end px-4 py-5 border-bottom-0">
                    <p class="mb-2" align="right">Subtotal:</p>                     
                    <p class="mb-2" align="right">Tax:</p>
                    <p class="mb-0" align="right">Total:</p>
                  </td>
                  <td class="px-4 py-5 border-bottom-0">
                    <p class="fw-semibold mb-2">{{ $currencyFormatter->format($netamount) . ' ' . $currencycode }}</p>
                    <p class="fw-semibold mb-2">{{ $currencyFormatter->format($totalvat) . ' ' . $currencycode }}</p>
                    <p class="fw-semibold mb-0">{{ $currencyFormatter->format(($netamount + $totalvat)) . ' ' . $currencycode }}</p>
                  </td>
                </tr>
                
                @php 
                /*
                <tr>
                  <td class="border-bottom">Sales</td>
                  <td class="border-bottom">Sale Invoice</td>
                  <td class="border-bottom">{{ $data['client']['sale']['vat_percentage'] }}</td>
                  <td class="border-bottom">{{ $currencyFormatter->format($data['client']['sale']['vat_amount']) . ' ' . $data['client']['currency_code'] }}</td>
                  <td class="border-bottom">{{ $currencyFormatter->format($data['client']['sale']['net_amount']) . ' ' . $data['client']['currency_code'] }}</td>
                </tr>
                <tr>
                  <td class="border-bottom">Purchases</td>
                  <td class="border-bottom">Purchase Invoice</td>
                  <td class="border-bottom">{{ $data['client']['purchase']['vat_percentage'] }}</td>
                  <td class="border-bottom">{{ $currencyFormatter->format($data['client']['purchase']['vat_amount']) . ' ' . $data['client']['currency_code'] }}</td>
                  <td class="border-bottom">{{ $currencyFormatter->format($data['client']['purchase']['net_amount']) . ' ' . $data['client']['currency_code'] }}</td>
                </tr>
                
                
                <tr>
                  <td colspan="3" class="align-top px-4 py-5 border-bottom-0">
                    
                  </td>
                  <td class="text-end px-4 py-5 border-bottom-0">
                    <p class="mb-2" align="right">Subtotal:</p>                     
                    <p class="mb-2" align="right">Tax:</p>
                    <p class="mb-0" align="right">Total:</p>
                  </td>
                  <td class="px-4 py-5 border-bottom-0">
                    <p class="fw-semibold mb-2">{{ $currencyFormatter->format(($data['client']['sale']['net_amount'] + $data['client']['purchase']['net_amount'])) . ' ' . $data['client']['currency_code'] }}</p>
                    <p class="fw-semibold mb-2">{{ $currencyFormatter->format(($data['client']['sale']['vat_amount'] + $data['client']['purchase']['vat_amount'])) . ' ' . $data['client']['currency_code'] }}</p>
                    <p class="fw-semibold mb-0">{{ $currencyFormatter->format(($data['client']['sale']['net_amount'] + $data['client']['purchase']['net_amount']) + ($data['client']['sale']['vat_amount'] + $data['client']['purchase']['vat_amount'])) . ' ' . $data['client']['currency_code'] }}</p>
                  </td>
                </tr>
                */ 
                @endphp

                
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>      
  </table>  

  <footer class="footer-bg">
      {{config('variables.templateName')}} ©
  </footer>
  --}}
</body>
</html>