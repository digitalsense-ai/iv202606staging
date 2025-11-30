<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>VAT Returns - {{ $data['subject'] }}</title> 
   
    <style type="text/css">	
      body { font-family: Courier; color: #516377; line-height: 1.1; font-size: 0.9375rem; font-weight: 500; }	
      table { border-collapse: collapse; }
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

      .separator { height: 20px; }
      
      .full-width { width: 100%; }
      .text-red { color: #cf2b20; }
      .text-blue { color: #003d78; text-decoration: underline; }
      .bold { font-weight: bold; }
      .uppercase { text-transform: uppercase; }

      .wrap-txt { word-wrap:break-word; }

      .subject { font-size: 1.5rem; color: #5a8dee; }
      tbody tr td { font-weight: normal; }
     
      header, footer { position: fixed; left: 0; right: 0; }
      header {  top: 0; text-align: center; }
      footer { background-color: rgba(229, 233, 237, 1); padding: 1rem; bottom: 0;  }
      
    </style> 
</head>

<body>
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
</body>
</html>