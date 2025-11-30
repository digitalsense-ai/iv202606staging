<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>{{ ($xmlContent['credit_note']) ? __('Credit Note') : __('Sales Invoice') }}</title> 
       
    <style type="text/css">   
      /*
      @page { margin: 0in; }  
      html, body { margin: 0; padding: 0; }  
      */

      @font-face {
          font-family: 'Rubik';
          font-weight: normal;
          font-style: normal;
          font-variant: normal;
          src: url({{ storage_path('fonts/Rubik/Rubik-VariableFont_wght.ttf') }}) format('truetype');
      }

      body { font-family: 'Rubik', sans-serif; color: #000000; line-height: 1.1; font-size: 0.9375rem; font-weight: normal; }  
      table { border-collapse: collapse; margin: 0; padding: 0; }

      .cover-page { background-color: #003056; color: #fff; min-height: 100%; }
      .rest-page { background-color: #ffffff; color: #677788; min-height: 100%; }
      .full-width { width: 100%; }
    
      .gap-1 { height: 300px; }
      .fw-normal { font-weight: normal; }

      table { padding: 1%; }      
      h1 { font-size: 2rem; padding: 0; margin: 0 0 0.5rem 0; }
      h2 { font-size: 0.8375rem; padding: 0; margin: 0.25rem 0; }
      h5, h6, p { padding: 0; margin: 0; }
      p { font-size: 0.8375rem; font-weight: normal; white-space: pre-wrap; /* CSS3 */ white-space: -moz-pre-wrap; /* Firefox */ 
      white-space: -pre-wrap; /* Opera <7 */ white-space: -o-pre-wrap; /* Opera 7 */ word-wrap: break-word; /* IE */ }
      
      .border-none {  border: none !important; }
      .position-absolute { position: absolute !important; left: 0 !important; } 
      .w-100 { width: 100% !important; }
      .m-0 { margin: 0 !important; }
      .p-0 { padding: 0 !important; }      

      .rest-page h6 { color: #516377;  font-size: 0.9375rem;  font-weight: normal; }
      .rest-page table.fixed { /*padding: 2.28rem 1.1rem !important;*/ padding: 2.375rem !important; }

      .logo { margin: 1rem 0; width: 160px; }

      .inner-tbl { padding: 1rem 0 !important; }
      tr.th,
      .inner-tbl th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; font-weight: bold; }
      .inner-tbl th,
      .inner-tbl td  { padding: 0.625rem 1.5rem; border-bottom: 0.2px solid #d4d8dd; }
      .inner-tbl td h6 { text-transform: none; font-weight: bold; }

      tr.th.reduce-fs {  font-size: 0.60rem; letter-spacing: normal; }

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
      .border-bottom { border-bottom: 1px solid #000000 !important; }    
      .border-end { border-right: 0.2px solid #d4d8dd !important; }  

      tr.odd-row td { background-color: #f8f9fa; color: #677788; }  

    </style>     
</head>
<body>
  <!-- Page -->
  <div class="full-width p-1">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none">          
      <tbody> 
        <!-- Heading --> 
        <tr>
          <th align="left" valign="top" colspan="9" class="border-bottom">  
            <h1 class="text-uppercase">{{ ($xmlContent['credit_note']) ? __('Credit Note') : __('Invoice') }}</h1>
          </th>
        </tr>
        <!--/ Heading --> 

        <!-- Invoice --> 
        <tr>
          <th align="left" valign="top" colspan="2" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Invoice sender') }}</h2>
            <p>{{ $xmlContent['sender']['name'] }}</p>
            <p>{{ $xmlContent['sender']['street'] }}</p>
            <p>{{ $xmlContent['sender']['postcode'] . ' ' . $xmlContent['sender']['city'] }}</p>
            <p>{{ __('Country') . ': ' . $xmlContent['sender']['countrycode'] }}</p>
            <p>{{ $xmlContent['sender']['endpoint'] }}</p>
            <p>{{ $xmlContent['sender']['vat_no'] }}</p>
          </th>

          <th align="left" valign="top" colspan="3" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Contact information') }}</h2>
            <p>{{ __('ID') . ': ' . $xmlContent['sender']['contact']['id'] }}</p>
            <p>{{ $xmlContent['sender']['contact']['name'] }}</p>            
          </th>

          <th align="left" valign="top" colspan="2" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Invoice recipient') }}</h2>
            <p>{{ $xmlContent['buyer']['name'] }}</p>
            <p>{{ $xmlContent['buyer']['street'] }}</p>
            @if($xmlContent['buyer']['houseno'] != '')
              <p>{{ $xmlContent['buyer']['houseno'] }}</p>
            @endif
            <p>{{ $xmlContent['buyer']['postcode'] . ' ' . $xmlContent['buyer']['city'] }}</p>
            <p>{{ __('Country') . ': ' . $xmlContent['buyer']['countrycode'] }}</p>
            <p>{{ $xmlContent['buyer']['endpoint'] }}</p>            
          </th>

          <th align="left" valign="top" colspan="2" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Contact information') }}</h2>
            <p>{{ __('ID') . ': ' . $xmlContent['buyer']['contact']['id'] }}</p>
            <p>{{ $xmlContent['buyer']['contact']['name'] }}</p>            
          </th>
        </tr>
        <!--/ Invoice --> 

        <!-- Delivery --> 
        <tr>
          <th align="left" valign="top" colspan="9">  
            <h2 class="fw-bold">{{ __('Delivery information') }}.</h2>                  
          </th>
        </tr> 
        <!-- Delivery -->  

        <!-- Delivery --> 
        <tr>
          <th align="left" valign="top" colspan="2" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Delivery') }}</h2>
            <p>{{ __('Delivery day') . ': ' . $xmlContent['delivery']['date'] }}</p>            
          </th>

          <th align="left" valign="top" colspan="7" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Delivery location') }}</h2>
            <p>{{ __('Address') . ': ' }}</p>
            <p>{{ $xmlContent['delivery']['street'] }}</p>             
            @if($xmlContent['delivery']['houseno'] != '')
              <p>{{ $xmlContent['delivery']['houseno'] }}</p>
            @endif
            <p>{{ $xmlContent['delivery']['postcode'] . ' ' . $xmlContent['delivery']['city'] }}</p>
            <p>{{ __('Country') . ': ' . $xmlContent['delivery']['countrycode'] }}</p>           
          </th>          
        </tr> 
        <!-- Delivery -->

        <!-- Payment --> 
        <tr>
          <th align="left" valign="top" colspan="2" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Method of payment') }}</h2>
            <p>{{ __('Last payment date') . ': ' }}</p>
            <p>{{ $xmlContent['payment_means']['due_date'] }}</p>    
            <p>{{ __('Domestic') }}</p>
            <p>{{ __('account transfer') . ': ' }}</p>
            <p>{{ '('. $xmlContent['payment_means']['channel_code'] . '): ' . $xmlContent['payment_means']['institute_name'] }}</p>             
            <p>{{ $xmlContent['payment_means']['branch_id'] . ' ' . $xmlContent['payment_means']['id'] }}</p>  
          </th>

          <th align="left" valign="top" colspan="7" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Terms of payment') }}</h2>
            <p>{{ __('Type') . ': ' . $xmlContent['payment_means']['type_id'] }}</p>
            <p>&nbsp;</p>  

            <p>{{ __('Additional information') . ': ' . $xmlContent['payment_means']['note'] }}</p>
            <p>{{ __('Cash discount date') . ': ' . $xmlContent['payment_means']['settlement_date'] }}</p>
            <p>{{ __('Cash discount rate') . __('Percent') . ': ' . $xmlContent['payment_means']['discount_percent'] }}</p>
            <p>{{ __('Penalty date') . ': ' . $xmlContent['payment_means']['penalty_date'] }}</p>
            <p>&nbsp;</p>  

            <h2 class="fw-bold">{{ __('Additional information about totals') }}</h2> 
            <p>{{ __('Total tax') . ': ' . $xmlContent['tax_total']['amount'] . ' ' . $xmlContent['tax_total']['tax_currencycode'] }}</p>
            <p>{{ __('Discount Total') . ': ' . $xmlContent['allowance_charge'] . ' ' . $xmlContent['allowance_charge_currencycode'] }}</p>

            <h2 class="fw-bold">{{ __('Supplementary information for Fee/Discount') }}</h2> 
            <p>{{ __('Discount') . ' (' . __('Discount') . ')' }}</p>    
            <p>{{ __('ID') . ': ' }}</p> 
            <p>{{ __('Conversion factor') . ': ' }}</p> 
            <p>{{ __('Basic amount') . ': ' }}</p> 
            <p>{{ __('Amount') . ': ' }}</p> 
            <p>{{ __('Calculation sequence') . ': ' }}</p> 
          </th>          
        </tr> 
        <!-- Payment -->

        <!-- Invoice --> 
        <tr>
          <th align="left" valign="top" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Invoice no') }}:</h2>                     
          </th>

          <th align="left" valign="top" class="border-bottom">  
            <p>{{ $xmlContent['invoice_no'] }}</p>          
          </th>

          <th align="left" valign="top" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Buyer\'s order no') }}:</h2>                     
          </th>

          <th align="left" valign="top" class="border-bottom">  
            <p>-</p>          
          </th>

          <th align="left" valign="top" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Seller\'s order no') }}:</h2>                     
          </th>

          <th align="left" valign="bottom" class="border-bottom">  
            <p>{{ $xmlContent['order_no'] }}</p>          
          </th>

          <th align="left" valign="top" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Date') }}:</h2>                     
          </th>

          <th align="left" valign="top" colspan="2" class="border-bottom">  
            <p>{{ $xmlContent['invoice_date'] }}</p>          
          </th>
        </tr>
        <!--/ Invoice -->  

        <!-- Invoice Line Heading --> 
        <tr>
          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Line') }}</h2>                     
          </th>

          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Item no') }}</h2>           
          </th>

          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Description') }}</h2>                        
          </th>

          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Qty') }}</h2>            
          </th>

          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Unit') }}</h2>                  
          </th>

          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Unit price') }}</h2>       
          </th>

          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Tax') }}</h2>                   
          </th>

          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Price incl') }}</h2>            
          </th>

          <th align="left" valign="top">  
            <h2 class="fw-bold">{{ __('Price') }}</h2>            
          </th>
        </tr>
        <!--/ Invoice Line Heading --> 

        @foreach($xmlContent['invoices'] as $invoice)
          <tr>
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p>{{ $invoice['no']}}</p>                     
            </td>
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p>{{ $invoice['order_no']}}</p>                     
            </td>
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p>{{ $invoice['item_name']}}</p>
              <p>{{ $invoice['item_desc']}}</p>  
              <h2 class="fw-bold">{{ __('Standard item number') }} :<p>{{ $invoice['std_item_id']}}</p></h2>
            </td>              
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p>{{ $invoice['qty']}}</p>                     
            </td>
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p>{{ ($invoice['unit_code'] == '') ? 'EA' : $invoice['unit_code'] }}</p>                     
            </td>
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p>{{ $invoice['price'] . ' ' . ($invoice['base_qty'] == '1') ? 'pr. 1 EA' : '-' }}</p>
            </td>
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p>{{ ($invoice['tax_percent'] != '') ? number_format($invoice['tax_percent'], 0) . '%' : '-' }}</p>                     
            </td>
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p></p>                     
            </td>
            <td align="left" valign="top" class="{{ ($loop->last) ? 'border-bottom' : '' }}">  
              <p>{{ $invoice['line_amount'] . ' ' . $xmlContent['currency_code'] }}</p>                     
            </td>
          </tr>
        @endforeach

        <!-- Total --> 
        <tr>
          <td align="left" valign="top" colspan="7" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Line sum in total excl. VAT') }}</h2>                     
            <h2 class="fw-bold">{{ __('Discount') . ' ' . __('(Discount)') }}</p></h2>
            <h2 class="fw-bold">{{ __('VAT base') }}</h2>
            <h2 class="fw-bold">{{ __('Total VAT amount') . ' (' . number_format($xmlContent['tax_total']['percent'], 0) . '%)' }}</h2>
            <h2 class="fw-bold">{{ __('Invoice total including VAT') }}</h2>
          </td>

          <td align="right" valign="top" colspan="2" class="border-bottom">  
            <p>{{ number_format($xmlContent['monetary_total']['line_amount'], 2) . ' ' . $xmlContent['monetary_total']['line_currencycode'] }}</p>

            <p>{{ ($xmlContent['allowance_charge']) ? (number_format($xmlContent['allowance_charge'], 2) . ' ' . $xmlContent['allowance_charge_currencycode']) : ('0.00' . ' ' . $xmlContent['monetary_total']['line_currencycode']) }}</p>          
            <p>{{ (($xmlContent['allowance_charge']) ? number_format(($xmlContent['monetary_total']['line_amount'] - $xmlContent['allowance_charge']), 2) : $xmlContent['monetary_total']['line_amount']) . ' ' . $xmlContent['monetary_total']['line_currencycode'] }}</p>          

            {{--<p>{{ $xmlContent['monetary_total']['tax_excl_amount'] . ' ' . $xmlContent['monetary_total']['tax_excl_currencycode'] }}</p>--}}
            <p>{{ number_format($xmlContent['tax_total']['amount'], 2) . ' ' . $xmlContent['monetary_total']['tax_excl_currencycode'] }}</p>
            <p>{{ number_format($xmlContent['monetary_total']['payable_amount'], 2) . ' ' . $xmlContent['monetary_total']['payable_currencycode'] }}</p>          
          </td>         
        </tr>
        <!--/ Total --> 

        <!-- Footer Note --> 
        <tr>
          <td align="left" valign="top" colspan="9" class="border-bottom">  
            <h2 class="fw-bold">{{ __('Additional information') }}: </h2>
            <p>{{ $xmlContent['footer_note'] }}</p> 
          </td>                
        </tr>
        <!--/ Footer Note --> 
      </tbody>
    </table>
  </div>
  <!--/ Page --> 
</body>
</html>