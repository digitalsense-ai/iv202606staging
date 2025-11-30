<div class="table-responsive">
	
    <table class="table mb-5 {{ ($vatreg->is_disregard_import_re) ? 'disabled' : '' }}">    
		<thead>			
			<tr>
				<th class="text-start">Period</th>				
				<th class="text-end">Import VAT</th>
				<th class="text-end">VAT on duties</th>
				<th class="text-end">VAT on adjustment</th>	
				<th class="text-end">Sales VAT Amount</th>	          
				<th class="text-end">VAT Amount Sales Invoices</th>
				<th class="text-end">Sales VAT vs Import VAT</th>
			</tr>
		</thead>
      	<tbody>	
      		@php
      			$vat_on_duties_total = 0;
      			$vat_on_adjustment_total = 0;
      			$import_vat_total = 0;
      			$sales_vat_amount_total = 0;
      			$sales_invoice_vat_amount_total = 0;
      			$sales_vat_vs_import_vat_total = 0;

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
		      		
      		@if(isset($import_vat_files))
		      	@if(count($import_vat_files) > 0)		      	
		      		@foreach ($import_vat_files as $key => $import_vat_file)
		      		
		      			@php
		      				$month_year = $import_vat_file->month_year;

		      				$statistical_number = $import_vat_file->statistical_number;

		      				$fee_number = $import_vat_file->fee_number;
		      				$vat_on_duties = $fee_number * 0.25;
		      				$vat_on_duties_total += $vat_on_duties;

		      				$fee_statistical_amount = $fee_number + $statistical_number;

							$adjustment_no = $import_vat_file->adjustment_no;
							$vat_on_adjustment = $adjustment_no * 0.25;
							$vat_on_adjustment_total += $vat_on_adjustment;

							$import_vat = $fee_statistical_amount * 0.25;
							$import_vat_total += $import_vat;
							
							$sales_vat_amount = ($import_vat - $vat_on_duties - $vat_on_adjustment);
							$sales_vat_amount_total += $sales_vat_amount;

							$sales_invoice_vat_amount = 0;
							if(count($importreconciliationsalesinvoices) > 0)
							{								
								$filtered_importreconciliationsalesinvoices = $importreconciliationsalesinvoices->filter(function ($importreconciliationsalesinvoice, $key) use ($month_year) {         
						            return \Carbon\Carbon::parse($importreconciliationsalesinvoice->invoice_date)->format('m-Y') == $month_year; 
						        });

								$sales_invoice_vat_amount = $filtered_importreconciliationsalesinvoices->sum('vat_amount');
																
								$sales_invoice_vat_amount_total += $sales_invoice_vat_amount;
							}

							$sales_vat_vs_import_vat = $sales_vat_amount - $sales_invoice_vat_amount;
							$sales_vat_vs_import_vat_total += $sales_vat_vs_import_vat;

							$row_text_danger = ($sales_vat_vs_import_vat == 0) ? '' : 'text-danger';
							
			          	@endphp   		
			          	<tr>
			          		<td class="text-start">{{ \Carbon\Carbon::parse('01-'.$import_vat_file->month_year)->format('F Y') }}</td>
			          		<td class="text-end">{{ $currencyFormatter->format($import_vat) }}</td>
							<td class="text-end">{{ $currencyFormatter->format($vat_on_duties) }}</td>
							<td class="text-end">{{ $currencyFormatter->format($vat_on_adjustment) }}</td>
							<td class="text-end">{{ $currencyFormatter->format($sales_vat_amount) }}</td>
							<td class="text-end sales-invoices-vat-amount-{{ $vat_reg_id }}-{{ \Carbon\Carbon::parse('01-'.$import_vat_file->month_year)->format('m-Y') }}" data-sales_vat_amount="{{ $sales_vat_amount }}">{{ $currencyFormatter->format($sales_invoice_vat_amount) }}</td>
							<td class="text-end {{ $row_text_danger }} sales-vat-vs-import-vat-{{ $vat_reg_id }}-{{ \Carbon\Carbon::parse('01-'.$import_vat_file->month_year)->format('m-Y') }}">{{ $currencyFormatter->format($sales_vat_vs_import_vat) }}</td>
			            </tr>
			           		            
		      		@endforeach	      		
		      		
		      		@if($vatreg->frequency == count($import_vat_files))

		      		@else		      		
		      			@php
		      				$sales_invoice_vat_amount = 0;
							if(count($importreconciliationsalesinvoices) == 0)
							{
								for ($i = 0; $i < $vatreg->frequency; $i++) 
				        		{
			      					$new_month_year = \Carbon\Carbon::parse('01-' . $month_year)->addMonth($i)->format('m-Y');

			      					if($new_month_year != $month_year)
			      						$month_year = $new_month_year;
								}
							}
							else
							{								
								$filtered_importreconciliationsalesinvoices = $importreconciliationsalesinvoices->filter(function ($importreconciliationsalesinvoice, $key) use ($month_year) {         
						            return \Carbon\Carbon::parse($importreconciliationsalesinvoice->invoice_date)->format('m-Y') != $month_year; 
						        });

						        if(count($filtered_importreconciliationsalesinvoices) > 0)
						        {
							        $month_year = \Carbon\Carbon::parse($filtered_importreconciliationsalesinvoices->first()->invoice_date)->format('m-Y');
									$sales_invoice_vat_amount = $filtered_importreconciliationsalesinvoices->sum('vat_amount');
								}
															
								$sales_invoice_vat_amount_total += $sales_invoice_vat_amount;

								$sales_vat_vs_import_vat = 0 - $sales_invoice_vat_amount;
								$sales_vat_vs_import_vat_total += $sales_vat_vs_import_vat;
							}							
		      			@endphp
		      			<tr>
			          		<td class="text-start">{{ \Carbon\Carbon::parse('01-'.$month_year)->format('F Y') }}</td>
			          		<td class="text-end">{{ $currencyFormatter->format(0) }}</td>
							<td class="text-end">{{ $currencyFormatter->format(0) }}</td>
							<td class="text-end">{{ $currencyFormatter->format(0) }}</td>
							<td class="text-end">{{ $currencyFormatter->format(0) }}</td>
							<td class="text-end sales-invoices-vat-amount-{{ $vat_reg_id }}-{{ \Carbon\Carbon::parse('01-'.$month_year)->format('m-Y') }}" data-sales_vat_amount="0">{{ $currencyFormatter->format($sales_invoice_vat_amount) }}</td>
							<td class="text-end {{ $row_text_danger }} sales-vat-vs-import-vat-{{ $vat_reg_id }}-{{ \Carbon\Carbon::parse('01-'.$month_year)->format('m-Y') }}">{{ $currencyFormatter->format($sales_vat_vs_import_vat) }}</td>
			            </tr>
		      		@endif

		      		<tr>
						<td class="text-end align-top px-4 py-5 border-bottom-0"><p class="mb-0">Total:</p></td>
						<td class="text-end px-4 py-5 border-bottom-0">
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($import_vat_total) }}</p>
						</td>
						<td class="text-end px-4 py-5 border-bottom-0">
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($vat_on_duties_total) }}</p>
						</td>
						<td class="text-end px-4 py-5 border-bottom-0">
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($vat_on_adjustment_total) }}</p>
						</td>
						<td class="text-end px-4 py-5 border-bottom-0">
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($sales_vat_amount_total) }}</p>
						</td>
						<td class="text-end px-4 py-5 border-bottom-0 total-sales-invoices-vat-amount-{{ $vat_reg_id }}">
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($sales_invoice_vat_amount_total) }}</p>
						</td>
						<td class="text-end px-4 py-5 border-bottom-0 total-sales-vat-vs-import-vat-{{ $vat_reg_id }}">
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($sales_vat_vs_import_vat_total) }}</p>
						</td>	         
					</tr>
		        @else  
		        	@if(count($importreconciliationsalesinvoices) > 0)
		        		@for ($i = 0; $i < $vatreg->frequency; $i++) 
				        	@php
			      				$month_year = \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('m-Y');

			      				$statistical_number = 0;

			      				$fee_number = 0;
			      				$vat_on_duties = $fee_number * 0.25;
			      				$vat_on_duties_total += $vat_on_duties;

			      				$fee_statistical_amount = $fee_number + $statistical_number;

								$adjustment_no = 0;
								$vat_on_adjustment = $adjustment_no * 0.25;
								$vat_on_adjustment_total += $vat_on_adjustment;

								$import_vat = $fee_statistical_amount * 0.25;
								$import_vat_total += $import_vat;
								
								$sales_vat_amount = ($import_vat - $vat_on_duties - $vat_on_adjustment);
								$sales_vat_amount_total += $sales_vat_amount;

								$sales_invoice_vat_amount = 0;
																
								$filtered_importreconciliationsalesinvoices = $importreconciliationsalesinvoices->filter(function ($importreconciliationsalesinvoice, $key) use ($month_year) {         
						            return \Carbon\Carbon::parse($importreconciliationsalesinvoice->invoice_date)->format('m-Y') == $month_year; 
						        });

								$sales_invoice_vat_amount = $filtered_importreconciliationsalesinvoices->sum('vat_amount');
																	
								$sales_invoice_vat_amount_total += $sales_invoice_vat_amount;
								

								$sales_vat_vs_import_vat = $sales_vat_amount - $sales_invoice_vat_amount;
								$sales_vat_vs_import_vat_total += $sales_vat_vs_import_vat;

								$row_text_danger = ($sales_vat_vs_import_vat == 0) ? '' : 'text-danger';								
				          	@endphp
				          	<tr>
				          		<td class="text-start">{{ \Carbon\Carbon::parse('01-'.$month_year)->format('F Y') }}</td>
				          		<td class="text-end">{{ $currencyFormatter->format($import_vat) }}</td>
								<td class="text-end">{{ $currencyFormatter->format($vat_on_duties) }}</td>
								<td class="text-end">{{ $currencyFormatter->format($vat_on_adjustment) }}</td>
								<td class="text-end">{{ $currencyFormatter->format($sales_vat_amount) }}</td>
								<td class="text-end sales-invoices-vat-amount-{{ $vat_reg_id }}-{{ \Carbon\Carbon::parse('01-'.$month_year)->format('m-Y') }}" data-sales_vat_amount="{{ $sales_vat_amount }}">{{ $currencyFormatter->format($sales_invoice_vat_amount) }}</td>
								<td class="text-end {{ $row_text_danger }} sales-vat-vs-import-vat-{{ $vat_reg_id }}-{{ \Carbon\Carbon::parse('01-'.$month_year)->format('m-Y') }}">{{ $currencyFormatter->format($sales_vat_vs_import_vat) }}</td>
				            </tr>
				        @endfor

				        <tr>
							<td class="text-end align-top px-4 py-5 border-bottom-0"><p class="mb-0">Total:</p></td>
							<td class="text-end px-4 py-5 border-bottom-0">
								<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($import_vat_total) }}</p>
							</td>
							<td class="text-end px-4 py-5 border-bottom-0">
								<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($vat_on_duties_total) }}</p>
							</td>
							<td class="text-end px-4 py-5 border-bottom-0">
								<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($vat_on_adjustment_total) }}</p>
							</td>
							<td class="text-end px-4 py-5 border-bottom-0">
								<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($sales_vat_amount_total) }}</p>
							</td>
							<td class="text-end px-4 py-5 border-bottom-0 total-sales-invoices-vat-amount-{{ $vat_reg_id }}">
								<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($sales_invoice_vat_amount_total) }}</p>
							</td>
							<td class="text-end px-4 py-5 border-bottom-0 total-sales-vat-vs-import-vat-{{ $vat_reg_id }}">
								<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($sales_vat_vs_import_vat_total) }}</p>
							</td>	         
						</tr>
			        @else
			        	@include('_partials/_content/_importreconciliation/importreconciliation-no-controls')	
			        @endif    

		      		{{--@include('_partials/_content/_importreconciliation/importreconciliation-only-control')--}}
				@endif
			@else
				@include('_partials/_content/_importreconciliation/importreconciliation-no-controls')	
			@endif	
        </tbody>
    </table>

   @include('_partials/_content/_importreconciliation/importreconciliation-overview-buttons')
</div>  