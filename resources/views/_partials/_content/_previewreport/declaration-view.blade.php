<div class="table-responsive mb-5">
    <table class="table table-bordered table-striped m-0">       
		<thead>			
			{{--
			<tr>
	        	<td colspan="6" class="border-0 p-0">
	        		<h6>Declaration</h6>		
	        		<!-- <div class="alert alert-secondary m-0">Text</div> -->   			        		
	        	</td>
	        </tr>
	        --}}
			<tr>
				<th valign="top" class="text-start p-2">Declarations</th> <!-- border-start border-top border-end -->
				<th valign="top" class="text-end p-2">Statistical value</th> <!-- border-top border-end -->
				<th valign="top" class="text-end p-2">Net Amount</th>
				<th valign="top" class="text-end p-2">Import VAT</th>		          
				<th valign="top" class="text-end p-2">Duties</th>
				<th valign="top" class="text-end p-2">VAT on duties</th>
				<th valign="top" class="text-end p-2">Adjustment</th>
				<th valign="top" class="text-end p-2">VAT on adjustment</th>
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
      	@if(count($importvatfiles) == 0)      		
      		<tr><td class="text-center p-2" colspan="8">No data found !!!</td></tr>
      	@else
      		@foreach ($importvatfiles as $key => $importvatfile)
      			@php			
      				$net_amount = $importvatfile->invoice_total;
      				$statistical_number = $importvatfile->statistical_number;
      				$fee_number = $importvatfile->fee_number;
      				$adjustment_no = $importvatfile->adjustment_no;

      				$import_vat = ($fee_number + $statistical_number) * 0.25;
      				$vat_on_duties = ($fee_number * 0.25);
      				$vat_on_adjustment = ($adjustment_no * 0.25);
	          	@endphp   		
	          	<tr class="{{ (($key%2) == 0) ? 'odd' : '' }} {{ ($importvatfile->comment_reason) ? 'has-comment' : '' }}">
					<td class="text-start p-2">{{ $declaration_no }}</td> <!-- border-start border-end -->
					<td class="text-end p-2">{{ $currencyFormatter->format($statistical_number) }}</td>  <!-- border-end -->
					<td class="text-end p-2">{{ $currencyFormatter->format($net_amount) }}</td>
					<td class="text-end p-2">{{ $currencyFormatter->format($import_vat) }}</td>	
					<td class="text-end p-2">{{ $currencyFormatter->format($fee_number) }}</td>
					<td class="text-end p-2">{{ $currencyFormatter->format($vat_on_duties) }}</td>		             
					<td class="text-end p-2">{{ $currencyFormatter->format($adjustment_no) }}</td>	
					<td class="text-end p-2">{{ $currencyFormatter->format($vat_on_adjustment) }}</td>	
	            </tr>

	            @if($importvatfile->comment_reason)
		        	<tr class="{{ (($key%2) == 0) ? 'odd' : '' }}">
	            		<td class="text-start p-2 comment" colspan="8">
	            			<span>{{ strtoupper($importvatfile->comment_reason) }}</span>
	            			{!! $importvatfile->comment !!}
	            		</td>
	            	</tr>
		        @endif		            
      		@endforeach        
		@endif        
      </tbody>
    </table>
</div>  		