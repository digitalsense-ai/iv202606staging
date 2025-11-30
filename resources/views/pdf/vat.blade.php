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

      /*.logo { width: 80px; height: 80px; background-color: rgb(18, 18, 28); border-radius: 50%; }*/
      .full-width { width: 100%; }
      .text-red { color: #cf2b20; }
      .text-blue { color: #003d78; text-decoration: underline; }
      .bold { font-weight: bold; }
      .uppercase { text-transform: uppercase; }

      .wrap-txt { word-wrap:break-word; }

      .subject { font-size: 1.5rem; color: #5a8dee; }
      tbody tr td { font-weight: normal; }
      /*.footer-logo { width: 100%; height: 13px; background-color: none; }
      footer { position: fixed; bottom: 0; left: 0; right: 0; }*/
    </style> 
</head>

<body>

  <table width="100%" cellspacing="0" cellpadding="10" border="0" align="center" class="main-table border-none">
    <!-- <thead> --> <!-- uncomment this if need header for all pages --> 
      <tr>
        <td align="center" valign="middle">  
        	<img src="<?php echo $logo ?>" width="50%"><br>          
        </td>
      </tr>

      <tr><td class="separator"></td></tr>
      
      <tr>
        <td align="center" valign="middle" class="subject bold">  
        	{{ $data['subject'] }}
        </td>
      </tr>

      <tr>
        <td align="center" valign="middle">  
        	<table width="100%" border="0" cellspacing="0" cellpadding="10" class="border">
        		<tr>
        			<td align="center" class="bold">
        				<img src="<?php echo $icn_purchase ?>"><br>Purchases
        			</td>
        		</tr>
        		<tr>
        			<td class="padding">
        				<table width="100%" border="0" cellspacing="0" cellpadding="10" class="border-none">
	                        <thead>
	                          <tr>
	                            <th></th>
	                            <th>% VAT</th>                                    
	                            <th>VAT</th>
	                            <th>NET</th>
	                          </tr>
	                        </thead>
	                        <tbody>                                 
	                          <tr>
		                        <td class="border-top border-bottom" align="center">Purchase Invoice</td>
		                        <td class="border-top border-bottom" align="center">{{ $data['client']['purchase']['vat_percentage'] }}</td>                    
		                        <td class="border-top border-bottom" align="center">{{ $data['client']['purchase']['vat_amount'] }}</td>
		                        <td class="border-top border-bottom" align="center">{{ $data['client']['purchase']['net_amount'] . ' ' . $data['client']['currency_code'] }}</td>
		                      </tr>
	                        </tbody>
                      	</table>
        			</td>        			
        		</tr>
        	</table>
        </td>
      </tr>
      <tr>
      	<td align="left" valign="middle" class="bold">  
      		Total input VAT (excl. RC): {{ $data['client']['purchase']['net_amount'] . ' ' . $data['client']['currency_code'] }}
      	</td>	
      </tr>

      <tr><td class="separator"></td></tr>

      <tr>
        <td align="center" valign="middle">  
        	<table width="100%" border="0" cellspacing="0" cellpadding="10" class="border">
        		<tr>
        			<td align="center" class="bold">
        				<img src="<?php echo $icn_sale ?>"><br>Sales
        			</td>
        		</tr>
        		<tr>
        			<td class="padding">
        				<table width="100%" border="0" cellspacing="0" cellpadding="10" class="border-none">
	                        <thead>
	                          <tr>
	                            <th></th>
	                            <th>% VAT</th>                                    
	                            <th>VAT</th>
	                            <th>NET</th>
	                          </tr>
	                        </thead>
	                        <tbody>                                 
	                          <tr>
		                        <td class="border-top border-bottom" align="center">Sale Invoice</td>
		                        <td class="border-top border-bottom" align="center">{{ $data['client']['sale']['vat_percentage'] }}</td>                    
		                        <td class="border-top border-bottom" align="center">{{ $data['client']['sale']['vat_amount'] }}</td>
		                        <td class="border-top border-bottom" align="center">{{ $data['client']['sale']['net_amount'] . ' ' . $data['client']['currency_code'] }}</td>
		                      </tr>
	                        </tbody>
                      	</table>
        			</td>        			
        		</tr>
        	</table>
        </td>
      </tr>
      <tr>
      	<td align="left" valign="middle" class="bold">  
      		Total output VAT (excl. RC): {{ $data['client']['sale']['net_amount'] . ' ' . $data['client']['currency_code'] }}
      	</td>	
      </tr>
      
  </table>  
</body>
</html>