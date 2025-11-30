@if(count($importreconciliationfiles) > 0)	
	@php  	
		$groupedByCurrency = $importreconciliationfiles
			->filter(function ($importreconciliationfile) {
		        return $importreconciliationfile->salesinvoicesdata && !is_null($importreconciliationfile->salesinvoicesdata->currency_code);
		    })
			->groupBy(function ($importreconciliationfile) {
			    return $importreconciliationfile->salesinvoicesdata->currency_code;
			});
		
		$sumByCurrencyAndPercentage = $groupedByCurrency->map(function ($currencyGroup) {
		    return $currencyGroup->groupBy(function ($importreconciliationfile) {
		        return $importreconciliationfile->salesinvoicesdata->tax_total_percent;
		    })->map(function ($percentageGroup) {
		        // Sum the vat_amount and net_amount from the Category model for each group
		        $vatAmountSum = $percentageGroup->sum(function ($importreconciliationfile) {
		            return $importreconciliationfile->salesinvoicesdata->tax_total_amount;
		        });

		        $netAmountSum = $percentageGroup->sum(function ($importreconciliationfile) {
		            return $importreconciliationfile->salesinvoicesdata->tax_total_net_amount;
		        });
		       
		        $invoiceCount = $percentageGroup->count();

		        return [
		            'vat_amount' => $vatAmountSum,
		            'net_amount' => $netAmountSum,
		            'invoice_count' => $invoiceCount,
		        ];
		    });
		});	

		$salestotalnet = 0;
      	$salestotalvat = 0;
	@endphp  	

	@foreach ($sumByCurrencyAndPercentage as $currency => $sumByPercentage)
		@php
			$currencylocale = 'en_US';		          		
			if($currency == 'DKK' || $currency == 'NOK')
      			$currencylocale = 'da_DK';      		
      		else if($currency == 'SEK')
      			$currencylocale = 'sv_SE';
      		else if($currency == 'GBP')
      			$currencylocale = 'en_GB';		          		
      		else if($currency == 'INR')
      			$currencylocale = 'en_IN';
      		else if($currency == 'EUR')
      			$currencylocale = 'fr_FR';

      		$currencyFormatter = new NumberFormatter($currencylocale, NumberFormatter::DECIMAL);	
      		$currencyFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);      		
      		$currencySymbol = $currency;
		@endphp

		@foreach ($sumByPercentage as $percent => $importreconciliationfile)
			@php
				$vat_percentage = str_replace('.00', '', $percent) . '%';

				$row_text_danger = '';
	          	if($vatreg->country == 'FR')
	          	{
	          		if($vat_percentage == "20%" || $vat_percentage == "10%" || $vat_percentage == "8.5%" || $vat_percentage == "5.5%" || $vat_percentage == "2.1%")
	          			$row_text_danger = '';
		          	else
		          		$row_text_danger = 'text-danger';
	          	}
	          	else
	          	{
		          	if(str_starts_with($currency, $vatreg->country))
		          		$row_text_danger = '';
		          	else
		          		$row_text_danger = 'text-danger';	
		        }

		        $salestotalnet += $importreconciliationfile['net_amount'];
      			$salestotalvat += $importreconciliationfile['vat_amount'];
			@endphp

			<tr class="{{ $row_text_danger }}">
				<td class="text-nowrap">{{ 'Sales' }}</td>
				<td class="text-nowrap">{{ 'IR Sale' }} Invoice</td>
				<td class="text-center">				              	
					<a class="cursor-pointer text-decoration-underline" href="#" target="_blank">{{ $importreconciliationfile['invoice_count'] }}</a>
				</td>
				<td class="text-end">{{ $vat_percentage }}</td>	
				<td class="text-end">{{ $currencyFormatter->format($importreconciliationfile['net_amount']) . ' ' . $currencySymbol }}</td>
				<td class="text-end">{{ $currencyFormatter->format($importreconciliationfile['vat_amount']) . ' ' . $currencySymbol }}</td>
	        </tr>	
		@endforeach 
	@endforeach 

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
	    $footer_currencySymbol = $currencycode;	   
	@endphp 
	<tr>
		<td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
		<td class="text-end px-4 py-5 border-bottom-0">
			<p class="mb-2">Subtotal:</p>			                
			<p class="mb-2">VAT:</p>
			<p class="mb-0">Total:</p>
		</td>
		<td class="px-4 py-5 border-bottom-0">
			<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($salestotalnet) . ' ' . $footer_currencySymbol }}</p>
			<p class="fw-semibold mb-2 text-end" id="total-tax-{{ $vat_reg_id }}">{{ $currencyFormatter->format($salestotalvat) . ' ' . $footer_currencySymbol }}</p>
			<p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format(($salestotalnet + $salestotalvat)) . ' ' . $footer_currencySymbol }}</p>
		</td>		                
    </tr>	
@endif	