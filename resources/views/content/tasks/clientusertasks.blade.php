@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')

@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-profile.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
@endsection

@section('page-script')

@endsection


@section('content')		
	
	<!-- VAT Returns -->               				
	<!-- Accordion Header Color -->
	<div class="col-md">					
		<h4 class="py-3 breadcrumb-wrapper mb-4">
		  <span class="text-muted fw-light">Tasks</span>
					
		</h4>

		<table class="table border-0 m-0 tbl-header" id="tbl-header" style="display: none;">
			<colgroup>			    					
				<col width="11%"/>
				<col width="43.5%"/>
				<col width="9.5%"/>
				<col width="9.5%"/>
				<col width="9.5%"/>
				<col width="17%"/>
			</colgroup>
			<thead>
				<tr>              
					<th class="border-bottom-0 p-0">Country</th>
					<th class="border-bottom-0 p-0">Company</th>
					<th class="border-bottom-0 p-0">Frequency</th>              
					<th class="border-bottom-0 p-0">Period</th>
					<th class="border-bottom-0 p-0">Amount Due</th>
					<th class="border-bottom-0 p-0">Status</th>					                 
				</tr>
			</thead>              
      	</table>       

		<div class="accordion mt-0 accordion-header-primary" id="accordionStyleAllTasks">
		@if(count($result) == 0)
			@include('_partials/_content/_tasks/no-tasks-lazy')
		@else	
			@foreach ($result as $key => $vatreg)	
				@php				
					$client = $vatreg->client;
					$vat_reg_id = $vatreg->vat_reg_id; 
					$vatreturns = $vatreg->vatreturns;
					
					$vat_reg_main = $vatreg->vatregmain;
					$client_api = $vat_reg_main->clientapi;
      				
					$product_type = $vat_reg_main->product_type; 
					$product_type_name = '';
					if($product_type == 1)
						$product_type_name = 'NUF VAT Return';
					else if($product_type == 2)
						$product_type_name = 'Import Reconciliation';
					else if($product_type == 4)
        				$product_type_name = 'VOEC VAT Return'; 	
					else if($product_type == 3)
						$product_type_name = 'NUF VAT Return & Import Reconciliation';	
					else if($product_type == 5)
						$product_type_name = 'VOEC VAT Return & Import Reconciliation';

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
				@endphp

				@include('_partials/_content/_vatreturn/vatreturns-clientuser-tasks')	
			@endforeach
		@endif									
		</div>

	</div>
	<!--/ Accordion Header Color -->
	<!--/ VAT Returns -->
	
@endsection