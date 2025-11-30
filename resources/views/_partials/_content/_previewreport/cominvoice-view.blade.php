<div class="table-responsive mb-5">
    <table class="table table-bordered table-striped m-0">
		<thead>															      
			<tr>
				<th valign="top" class="text-start p-2">Declarations</th>
				<th valign="top" class="text-start p-2 border-top-0 border-end">Date</th>
				<th valign="top" class="text-start p-2 border-top-0 border-end">Commercial invoice</th>
				<th valign="top" class="text-end p-2 border-top-0 border-end" colspan="2">Net Amount</th>
				<th valign="top" class="text-end p-2 border-top-0 border-end" colspan="2">Import VAT</th>
				<th valign="top" class="text-end p-2 border-top-0">Currency</th>
			</tr>
		</thead>
      	<tbody>	    
      	@php
      		$vatregmain = $vatreg->vatregmain;
      		$declaration_no = ($vatregmain->org_no) ? $vatregmain->org_no : '-';

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
      	@endphp  	   	
      	@if(count($importreconciliationcominvoices) == 0)      
      		<tr><td class="text-center p-2" colspan="8">No data found !!!</td></tr>
      	@else		
      		{{--@foreach ($importvatfiles as $key => $importvatfile)--}}
      			@foreach ($importreconciliationcominvoices as $key => $importreconciliationcominvoice)
	      			@php	      				      			
	      				$net_amount = ($importreconciliationcominvoice->net_amount) ? $importreconciliationcominvoice->net_amount : 0;
	      				$import_vat = $net_amount * 0.25;

	      				$currency_code = $importreconciliationcominvoice->currency_code;
		          	@endphp  

		          	{{--@if(\Carbon\Carbon::parse('01-' . $importvatfile->month_year)->format('Ym') == \Carbon\Carbon::parse($importreconciliationcominvoice->invoice_date)->format('Ym'))--}}
			          	<tr class="{{ (($key%2) == 0) ? 'odd' : '' }} {{ ($importreconciliationcominvoice->comment_reason) ? 'has-comment' : '' }}">
			            	<td class="text-start p-2">{{ $declaration_no }}</td>
			            	<td class="text-start p-2">{{ $importreconciliationcominvoice->invoice_date }}</td>
							<td class="text-start p-2">{{ $importreconciliationcominvoice->invoice_no }}</td>								
							<td class="text-end p-2" colspan="2">{{ $currencyFormatter->format($net_amount) }}</td>
							<td class="text-end p-2" colspan="2">{{ $currencyFormatter->format($import_vat) }}</td>			
							<td class="text-end p-2">{{ $currency_code }}</td>
				        </tr>

				        @if($importreconciliationcominvoice->comment_reason)
				        	<tr class="{{ (($key%2) == 0) ? 'odd' : '' }}">
			            		<td class="text-start p-2 comment" colspan="8">
			            			<span>{{ strtoupper($importreconciliationcominvoice->comment_reason) }}</span>
			            			{!! $importreconciliationcominvoice->comment !!}
			            		</td>
			            	</tr>
				        @endif
			        {{--@endif --}}
	          	@endforeach

	          	{{--
	            <tr>
	            	<td class="text-start p-2">{{ $declaration_no }}</td>
	            	<td class="text-start p-2">01-01-2024</td>
					<td class="text-start p-2">PROF02449</td>								
					<td class="text-end p-2" colspan="2">5,573.29</td>
					<td class="text-end p-2" colspan="2">1,393.35</td>			
					<td class="text-end p-2">NOK</td>
		        </tr> 
		        <tr>
		        	<td class="text-start p-2">{{ $declaration_no }}</td>
		        	<td class="text-start p-2">01-02-2024</td>
					<td class="text-start p-2">PROF02448</td>
					<td class="text-end p-2" colspan="2">5,573.29</td>
					<td class="text-end p-2" colspan="2">1,393.32</td>			
					<td class="text-end p-2">NOK</td>
		        </tr>
		        
      		@endforeach   --}}     
		@endif        
      </tbody>
    </table>
</div>  		