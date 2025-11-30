<tr>
	<td colspan="6" valign="top">    	
	    <table width="100%" cellspacing="0" cellpadding="0" align="center" class="border-1 p-0 my-2">
			<thead>															      
				<tr class="th">
					<th valign="top" align="left" class="p-2 border-bottom border-end">Declarations</th>
					<th valign="top" align="left" class="p-2 border-bottom border-end">Date</th>
					<th valign="top" align="left" class="p-2 border-bottom border-end">Commercial invoice</th>
					<th valign="top" align="right" class="p-2 border-bottom border-end" colspan="2">Net Amount</th>
					<th valign="top" align="right" class="p-2 border-bottom border-end" colspan="2">Import VAT</th>
					<th valign="top" align="right" class="p-2 border-bottom">Currency</th>
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
	      		<tr class="odd-row"><td align="center" class="p-2" colspan="8">No data found !!!</td></tr>
	      	@else			      	
  				@php
					$chunk = $importreconciliationcominvoices->take($row_per_page);    
				@endphp

      			@foreach ($chunk as $key => $importreconciliationcominvoice)
	      			@php	
	      				$odd_row = (($key%2) == 0) ? false : true;
	      				      				      			
	      				$net_amount = ($importreconciliationcominvoice->net_amount) ? $importreconciliationcominvoice->net_amount : 0;
	      				$import_vat = $net_amount * 0.25;

	      				$currency_code = $importreconciliationcominvoice->currency_code;
		          	@endphp  
    	
			        <tr class="{{ ($odd_row) ? 'odd-row' : '' }}">
		            	<td align="left" class="p-2 border-bottom border-end">{{ $declaration_no }}</td>
		            	<td align="left" class="p-2 border-bottom border-end">{{ $importreconciliationcominvoice->invoice_date }}</td>
						<td align="left" class="p-2 border-bottom border-end">{{ $importreconciliationcominvoice->invoice_no }}</td>								
						<td align="right" class="p-2 border-bottom border-end" colspan="2">{{ $currencyFormatter->format($net_amount) }}</td>
						<td align="right" class="p-2 border-bottom border-end" colspan="2">{{ $currencyFormatter->format($import_vat) }}</td>			
						<td align="right" class="p-2 border-bottom">{{ $currency_code }}</td>
			        </tr> 					        
	          	@endforeach

	          	@php
					$importreconciliationcominvoices = $importreconciliationcominvoices->slice($row_per_page)->values();    
				@endphp

				@if(count($importreconciliationcominvoices) > 0)
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>

						@php							
							$page_no = isset($page_no) ? ($page_no + 1) : 2;									
						@endphp
						@include('_partials/_content/_previewreport/footer-pdf') 
					</div>
					<!--/ Content Page -->
				@endif

				@if(count($importreconciliationcominvoices) > 0)
					<!-- Content Page -->
					<div class="full-width p-1 rest-page page-break" style="position: relative;">    
						<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">
							@include('_partials/_content/_previewreport/header-pdf')       
							<tbody>
								@include('_partials/_content/_previewreport/cominvoice-pdf') 
				@endif
			@endif 
	      	</tbody>
    	</table>
	</td>
</tr>