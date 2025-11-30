<!-- <div class="table-responsive mb-5"> 
    <table class="table table-bordered table-striped m-0"> -->
<tr>
	<td colspan="6" valign="top">    	
	    <table width="100%" cellspacing="0" cellpadding="0" align="center" class="border-1 p-0 my-2">      
			<thead>						
				<tr class="th reduce-fs">
					<th valign="top" align="left" class="p-2 border-bottom border-end">Declarations</th>
					<th valign="top" align="right" class="p-2 border-bottom border-end">Statistical value</th>
					<th valign="top" align="right" class="p-2 border-bottom border-end">Net Amount</th>
					<th valign="top" align="right" class="p-2 border-bottom border-end">Import VAT</th>		          
					<th valign="top" align="right" class="p-2 border-bottom border-end">Duties</th>
					<th valign="top" align="right" class="p-2 border-bottom border-end">VAT on duties</th>
					<th valign="top" align="right" class="p-2 border-bottom border-end">Adjustment</th>
					<th valign="top" align="right" class="p-2 border-bottom">VAT on adjustment</th>
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
	      		<tr class="odd-row"><td align="center" class="p-2" colspan="8">No data found !!!</td></tr>
	      	@else
	      		@foreach ($importvatfiles as $key => $importvatfile)
	      			@php
	      				$odd_row = (($key%2) == 0) ? false : true;

	      				$net_amount = $importvatfile->invoice_total;
	      				$statistical_number = $importvatfile->statistical_number;
	      				$fee_number = $importvatfile->fee_number;
	      				$adjustment_no = $importvatfile->adjustment_no;

	      				$import_vat = ($fee_number + $statistical_number) * 0.25;
	      				$vat_on_duties = ($fee_number * 0.25);
	      				$vat_on_adjustment = ($adjustment_no * 0.25);
		          	@endphp   		
		          	<tr class="{{ ($odd_row) ? 'odd-row' : '' }}">
						<td align="left" class="p-2 border-bottom border-end">{{ $declaration_no }}</td>
						<td align="right" class="p-2 border-bottom border-end">{{ $currencyFormatter->format($statistical_number) }}</td>
						<td align="right" class="p-2 border-bottom border-end">{{ $currencyFormatter->format($net_amount) }}</td>
						<td align="right" class="p-2 border-bottom border-end">{{ $currencyFormatter->format($import_vat) }}</td>	
						<td align="right" class="p-2 border-bottom border-end">{{ $currencyFormatter->format($fee_number) }}</td>
						<td align="right" class="p-2 border-bottom border-end">{{ $currencyFormatter->format($vat_on_duties) }}</td>
						<td align="right" class="p-2 border-bottom border-end">{{ $currencyFormatter->format($adjustment_no) }}</td>	
						<td align="right" class="p-2 border-bottom">{{ $currencyFormatter->format($vat_on_adjustment) }}</td>	
		            </tr>		            
	      		@endforeach        
			@endif        
	      </tbody>
	    </table>
	</td>
</tr>	
<!-- </div>   -->		