<!-- Sales/Purchase -->  	   		
@if($tab_name == "overview") 

	@if($vatreg->status == 6) 
		@php
			$table_show = 0;  
		@endphp
		<h4 class="breadcrumb-wrapper mt-4 d-block p-0">{{ \Carbon\Carbon::parse($vatreg->service_start)->format('F Y') . ' ' . $vatreg->country . ' ' . ucfirst($vatreg->general_periods) }}        			
			<button type="button" id="btn-open-comment-{{ $vat_reg_id }}" class="btn btn-dark float-end mx-2 btn-open-comment my-n1" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#onboardingSlideCommentModal-{{ $vat_reg_id }}">Comment</button>
			<span class="bg-label-danger float-end p-2 m-0 h6">Locked</span>
		</h4>
		<p>The Folder locked and moved to archive.</p>	
	@else
		@php
			$table_show = 1;  
		@endphp

		@if($page_type != 'my-tasks')				  
			<h4 class="breadcrumb-wrapper d-block p-0">{{ \Carbon\Carbon::parse($vatreg->service_start)->format('F Y') . ' ' . $vatreg->country . ' ' . ucfirst($vatreg->general_periods) }}</h4>		    		
		@endif

		@if(!$show_vatreturn)
			@php
				$table_show = 0;  
			@endphp
  			
  			{!! isset($vatreg->error) ? ('<p><em class="text-danger"> - Error in fetching datas from api/excel/xml file</em></p>') : '' !!}

  			{!! isset($job_running) ? (($job_running) ? ('<p><em class="bg-label-warning"> - Fetching data from API, Excel, or XML file. Please be patient</em></p>') : '') : '' !!}

  			@include('_partials/_content/_vatreturn/vatreturn-no-invoices-lazy')

  			@include('_partials/_content/_vatreturn/vatreturn-overview-buttons')   			
  		@endif  			
	@endif

@elseif($tab_name == "archive") 

	@if($vatreg->status == 6)
		@php
			$table_show = 1;  
		@endphp

		@if($page_type != 'my-tasks')	
			<h4 class="breadcrumb-wrapper mt-4 d-block p-0">{{ \Carbon\Carbon::parse($vatreg->service_start)->format('F Y') . ' ' . $vatreg->country . ' ' . ucfirst($vatreg->general_periods) }}
				<span class="bg-label-danger float-end p-2 m-0 h6">Locked</span>
			</h4>
		@endif	
	@endif

@elseif($tab_name == "confirm" || $tab_name == "previewreport") 
	@php
		$table_show = 1;  
	@endphp		
@endif  		
		
@if($table_show == 1)
	<div class="table-responsive {{ ($tab_name == 'confirm' || $tab_name == 'previewreport') ? '' : 'text-nowrap' }}">
		
	    <table class="table m-0 {{ ($vatreg->is_disregard) ? 'disabled' : '' }}">
	    @if($tab_name != 'confirm' && $tab_name != 'previewreport')
	    	<colgroup>          
				<col width="10%"/>
			</colgroup>
			<colgroup>
				<col width="40%"/>
			</colgroup>
			<colgroup>
				<col width="10%"/>
			</colgroup>
			<colgroup>
				<col width="10%"/>
			</colgroup>
			<colgroup>
				<col width="15%"/>
			</colgroup>
			<colgroup>
				<col width="15%"/>        
			</colgroup>	
		@endif    	
			<thead>
				@if($tab_name == 'confirm' || $tab_name == 'previewreport')
					<tr>
			        	<td colspan="6" class="border-0 p-0">
			        		<h6>Sales and Purchases</h6>		
			        		<!-- <div class="alert alert-secondary m-0">Text</div> -->   			        		
			        	</td>
			        </tr>
	    		@endif
				<tr>
					<th>Item</th>
					<th>Description</th>
					<th class="text-center">Invoices</th>
					<th class="text-end">% VAT</th>		          
					<th class="text-end">NET</th>
					<th class="text-end">VAT</th>
				</tr>
			</thead>
	      	<tbody>	
	      	@php
	      		$footer_currencySymbol = '';	      		
			@endphp	     

			@php
	      		$salestotalvat = 0;
	      		$purchasetotalvat = 0;

	      		$salestotalnet = 0;
	      		$purchasetotalnet = 0;

	      		$sales_standard_totalnet = 0;
                $sales_standard_totalvat = 0;

                $sales_medium_totalnet = 0;
                $sales_medium_totalvat = 0;

                $sales_low_totalnet = 0;
                $sales_low_totalvat = 0;

                $sales_zero_totalnet = 0;
                $sales_zero_totalvat = 0;

                $sales_fish_totalnet = 0;
                $sales_fish_totalvat = 0;

                $purchases_standard_totalnet = 0;
                $purchases_standard_totalvat = 0;

                $purchases_medium_totalnet = 0;
                $purchases_medium_totalvat = 0;

                $purchases_low_totalnet = 0;
                $purchases_low_totalvat = 0;

                $purchases_zero_totalnet = 0;
                $purchases_zero_totalvat = 0;

                $purchases_fish_totalnet = 0;
                $purchases_fish_totalvat = 0;
	      	@endphp 		
	      	@if(count($vatreturns) > 0)	      		
	      		@foreach ($vatreturns as $key => $vatreturn)
	      			@php			
	      				$vat_percentage = str_replace('.00', '', $vatreturn->vat_percentage) . '%';

		          		$currencylocale = 'en_US';		          		
						if($vatreturn->currency_code == 'DKK' || $vatreturn->currency_code == 'NOK')
		          			$currencylocale = 'da_DK';
		          		//else if($vatreturn->currency_code == 'NOK')
		          			//$currencylocale = 'no_NO';
		          		else if($vatreturn->currency_code == 'SEK')
		          			$currencylocale = 'sv_SE';
		          		else if($vatreturn->currency_code == 'GBP')
		          			$currencylocale = 'en_GB';		          		
		          		else if($vatreturn->currency_code == 'INR')
		          			$currencylocale = 'en_IN';
		          		else if($vatreturn->currency_code == 'EUR')
		          			$currencylocale = 'fr_FR';
									          		
		          		$currencyFormatter = new NumberFormatter($currencylocale, NumberFormatter::DECIMAL);	
		          		$currencyFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
		          		//$currencySymbol = $currencyFormatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
		          		$currencySymbol = $vatreturn->currency_code;

      					if(str_starts_with($vatreturn->currency_code, $vatreg->country) || ($vatreg->country == "FR"))
      					{      						
          					if($vatreturn->invoice_type == 'sale')
      						{      							
      							$salestotalnet += $vatreturn->net_amount;
      							$salestotalvat += $vatreturn->vat_amount;

      							if($vatreg->country == 'NO')
								{
									if($vat_percentage == "25%")
									{
										$sales_standard_totalnet += $vatreturn->net_amount;
          								$sales_standard_totalvat += $vatreturn->vat_amount;
          							}
          							else if($vat_percentage == "15%")
									{
										$sales_medium_totalnet += $vatreturn->net_amount;
          								$sales_medium_totalvat += $vatreturn->vat_amount;
          							}
          							else if($vat_percentage == "12%")
									{
										$sales_low_totalnet += $vatreturn->net_amount;
          								$sales_low_totalvat += $vatreturn->vat_amount;
          							}
          							else if($vat_percentage == "0%")
									{
										$sales_zero_totalnet += $vatreturn->net_amount;
          								$sales_zero_totalvat += $vatreturn->vat_amount;
          							}
          							else if($vat_percentage == "11.11%")
									{
										$sales_fish_totalnet += $vatreturn->net_amount;
          								$sales_fish_totalvat += $vatreturn->vat_amount;
          							}
								}
      						}

      						if($vatreturn->invoice_type == 'purchase')
      						{
      							$purchasetotalnet += $vatreturn->net_amount;
      							$purchasetotalvat += $vatreturn->vat_amount;

      							if($vatreg->country == 'NO')
								{
									if($vat_percentage == "25%")
									{
										$purchases_standard_totalnet += $vatreturn->net_amount;
          								$purchases_standard_totalvat += $vatreturn->vat_amount;
          							}
          							else if($vat_percentage == "15%")
									{
										$purchases_medium_totalnet += $vatreturn->net_amount;
          								$purchases_medium_totalvat += $vatreturn->vat_amount;
          							}
          							else if($vat_percentage == "12%")
									{
										$purchases_low_totalnet += $vatreturn->net_amount;
          								$purchases_low_totalvat += $vatreturn->vat_amount;
          							}
          							else if($vat_percentage == "0%")
									{
										$purchases_zero_totalnet += $vatreturn->net_amount;
          								$purchases_zero_totalvat += $vatreturn->vat_amount;
          							}
          							else if($vat_percentage == "11.11%")
									{
										$purchases_fish_totalnet += $vatreturn->net_amount;
          								$purchases_fish_totalvat += $vatreturn->vat_amount;
          							}
								}				          							
      						}

			          		$currencycode = $vatreturn->currency_code;
			          		//$footer_currencySymbol = $currencyFormatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
			          		$footer_currencySymbol = $currencycode;
			          	}	

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
				          	if(str_starts_with($vatreturn->currency_code, $vatreg->country))
				          		$row_text_danger = '';
				          	else
				          		$row_text_danger = 'text-danger';	
				        }				        				        
		          	@endphp   		
		          	<tr class="{{ $row_text_danger }}">
						<td class="text-nowrap">{{ ($vatreturn->invoice_type == 'sale') ? 'Sales' : 'Purchases' }}</td>
						<td class="text-nowrap">{{ ($vatreturn->invoice_type == 'sale') ? 'Sale' : 'Purchase' }} Invoice</td>
						<td class="text-center">				              	
							<a class="cursor-pointer text-decoration-underline" href="{{ url('invoices/' . $vat_reg_id . '?type=' . $vatreturn->invoice_type . '&percentage=' . (str_replace('%', '', $vat_percentage)) . '&currency=' . $vatreturn->currency_code ) }}" target="_blank">{{ $vatreturn->invoice_count }}</a>
						</td>
						<td class="text-end">{{ $vat_percentage }}</td>	
						<td class="text-end">{{ ($vatreturn->invoice_type == 'purchase' && $vatreturn->net_amount > 0) ? '-' : '' }}{{ $currencyFormatter->format($vatreturn->net_amount) . ' ' . (($currencySymbol) ? $currencySymbol : $vatreturn->currency_code) }}</td>
						<td class="text-end">{{ ($vatreturn->invoice_type == 'purchase' && $vatreturn->vat_amount > 0) ? '-' : '' }}{{ $currencyFormatter->format($vatreturn->vat_amount) . ' ' . (($currencySymbol) ? $currencySymbol : $vatreturn->currency_code) }}</td>		             
		            </tr>		            
	      		@endforeach	      		
	      		@php
	      			if($client_api)
	      			{
		      			if($client_api->api_name == 'E-conomic')	 
		      			{
		      				$totalnet = $salestotalnet + $purchasetotalnet;
	          				$totalvat = $salestotalvat + $purchasetotalvat;
		      			}   
		      			else
		      			{  			
					        $totalnet = $salestotalnet - $purchasetotalnet;
		          			$totalvat = $salestotalvat - $purchasetotalvat;    
		          		}
		          	}
					else
	      			{  			
				        $totalnet = $salestotalnet - $purchasetotalnet;
	          			$totalvat = $salestotalvat - $purchasetotalvat;    
	          		}
          		@endphp          	

		        <tr>
					<td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
					<td class="text-end px-4 py-5 border-bottom-0">
						<p class="mb-2">Subtotal:</p>			                
						<p class="mb-2">VAT:</p>
						<p class="mb-0">Total:</p>
					</td>
					<td class="px-4 py-5 border-bottom-0">
						<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($totalnet) . ' ' . $footer_currencySymbol }}</p>
						<p class="fw-semibold mb-2 text-end" id="total-tax-{{ $vat_reg_id }}">{{ $currencyFormatter->format($totalvat) . ' ' . $footer_currencySymbol }}</p>
						<p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format(($totalnet + $totalvat)) . ' ' . $footer_currencySymbol }}</p>
					</td>		                
		        </tr>

	        @else	          		
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
			        $currencySymbol = $currencycode;
			        $footer_currencySymbol = $currencycode;

			        $overview_partial = 1;
          		@endphp 

          		@if(stripos(strtolower($client->client_name), "aubo") !== false || stripos(strtolower($client->client_name), "beck") !== false ||
	            	stripos(strtolower($client->client_name), "geisler") !== false || stripos(strtolower($client->client_name), "noscomed") !== false ||
	            	stripos(strtolower($client->client_name), "rexholm") !== false || stripos(strtolower($client->client_name), "villy") !== false
				)	
					@php
						$importreconciliationfiles = ($vatreg->importreconciliationfiles) ? $vatreg->importreconciliationfiles : [];	
					@endphp
							
					@if(count($importreconciliationfiles) > 0)				
						@include('_partials/_content/_vatreturn/vatreturn-ftp-salesinvoices')
					@else	
						@include('_partials/_content/_vatreturn/vatreturn-no-invoices-lazy')
					@endif
				@else	
					@include('_partials/_content/_vatreturn/vatreturn-no-invoices-lazy')		
				@endif
				
			@endif
			
	        @if($tab_name == 'confirm' || $tab_name == 'previewreport')
	        	<!--NO - IMPORT/EXPORT-->
	        	@if($vatreg->country == "NO")
	        		@php
			      		$import_statistical_number = 0;
			      		$import_fee_number = 0;
			      		$export_statistical_number = 0;
			      	@endphp
			      	
	        		@if(count($import_vat_files) > 0)
		        		<tr>
				        	<td colspan="6" class="border-0"><hr class="position-absolute w-100 m-0" style="left: 0;" /></td>
				        </tr>
				        <tr>
				        	<td colspan="6" class="border-0 p-0">
				        		<h6>Import and Export</h6>					        		
				        	</td>
				        </tr>

				        @foreach ($import_vat_files as $key => $import_vat_file)
				        	@php				      							      		
					      		$import_statistical_number += $import_vat_file->statistical_number;
					      		$import_fee_number += $import_vat_file->fee_number;
					      		$export_statistical_number += $import_vat_file->e_statistical_number;
					      	@endphp
				        @endforeach
				        <tr>
							<td class="text-nowrap"><strong>Import</strong></td>
							<td class="text-nowrap">Statistic value</td>
							<td class="text-center">-</td>
							<td class="text-end">-</td>	
							<td class="text-end">-</td>
							<td class="text-end">{{ $currencyFormatter->format($import_statistical_number) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
			            </tr>
			            <tr>
							<td class="text-nowrap"><strong>Import</strong></td>
							<td class="text-nowrap">Duties & taxes</td>
							<td class="text-center">-</td>
							<td class="text-end">-</td>	
							<td class="text-end">-</td>
							<td class="text-end">{{ $currencyFormatter->format($import_fee_number) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
			            </tr>
			            <tr>
							<td class="text-nowrap"><strong>Export & Re-export</strong></td>
							<td class="text-nowrap">Statistic value</td>
							<td class="text-center">-</td>
							<td class="text-end">-</td>	
							<td class="text-end">-</td>
							<td class="text-end">{{ $currencyFormatter->format($export_statistical_number) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
			            </tr>
			        @endif				   
	        	@endif
	        	<!--/ NO - IMPORT/EXPORT-->

		        <!--PIVS-->
		        @php
		      		$pivsmonthtotal = 0;
		      	@endphp
		        @if(count($pivs_files) > 0)		
		        	<tr>
			        	<td colspan="6" class="border-0"><hr class="position-absolute w-100 m-0" style="left: 0;" /></td>
			        </tr>
			        <tr>
			        	<td colspan="6" class="border-0 p-0">
			        		<h6>Postponed import VAT statement</h6>		
			        		<!-- <div class="alert alert-secondary m-0">Text</div> -->   			        		
			        	</td>
			        </tr>   			            	
		      		@foreach ($pivs_files as $key => $pivs)
		      			@php				      		
				      		$month_total = is_numeric($pivs->month_total) ? $pivs->month_total : 0;
				      		$pivsmonthtotal += $month_total;
				      	@endphp
		      			<tr>
							<td class="text-nowrap">{{ \Carbon\Carbon::parse('01-' .$pivs->month_year)->format('F') }}</td>
							<td class="text-nowrap">PIVS</td>
							<td class="text-center">-</td>
							<td class="text-end">-</td>	
							<td class="text-end">{{ $currencyFormatter->format(($month_total * 100)/20) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
							<td class="text-end">{{ $currencyFormatter->format($month_total) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
			            </tr>
		      		@endforeach
		      		{{--
		      		<tr>
						<td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
						<td class="text-end px-4 py-5 border-bottom-0">
							<p class="mb-2">Subtotal:</p>			                
							<p class="mb-2">VAT:</p>
							<p class="mb-0">Total:</p>
						</td>
						<td class="px-4 py-5 border-bottom-0">
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format(($pivsmonthtotal * 100)/20) . ' ' . $footer_currencySymbol }}</p>
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($pivsmonthtotal) . ' ' . $footer_currencySymbol }}</p>
							<p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format((($pivsmonthtotal * 100)/20) + $pivsmonthtotal) . ' ' . $footer_currencySymbol }}</p>
						</td>		                 
			        </tr>
			        --}}			        
		      	@endif
		      	<!--/ PIVS-->

		      	<!--C79-->
		      	@php
		      		$c79numbers = 0;		      		
		      	@endphp
		      	@if(count($c79_documents) > 0)	
		      		<tr>
			        	<td colspan="6" class="border-0"><hr class="position-absolute w-100 m-0" style="left: 0;" /></td>
			        </tr>
			        <tr>
			        	<td colspan="6" class="border-0 p-0">
			        		<h6>C79 Import VAT Certificate</h6>	
			        		<!-- <div class="alert alert-secondary m-0">Text</div> -->   		
			        	</td>
			        </tr>   			         	      	
		      		@foreach ($c79_documents as $key => $c79_document)
		      			@php				      		      	
				      		$doc_numbers = is_numeric($c79_document->doc_numbers) ? $c79_document->doc_numbers : 0;		      		
				      		$c79numbers += $doc_numbers;	
				      	@endphp
		      			<tr>
							<td class="text-nowrap">{{ \Carbon\Carbon::parse('01-' .$c79_document->month_year)->format('F') }}</td>
							<td class="text-nowrap">C79</td>
							<td class="text-center">-</td>
							<td class="text-end">-</td>	
							<td class="text-end">{{ $currencyFormatter->format(($doc_numbers * 100)/20) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
							<td class="text-end">{{ $currencyFormatter->format($doc_numbers) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
			            </tr>
		      		@endforeach
		      		{{--
		      		<tr>
						<td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
						<td class="text-end px-4 py-5 border-bottom-0">
							<p class="mb-2">Subtotal:</p>			                
							<p class="mb-2">VAT:</p>
							<p class="mb-0">Total:</p>
						</td>
						<td class="px-4 py-5 border-bottom-0">
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format(($c79numbers * 100)/20) . ' ' . $footer_currencySymbol }}</p>
							<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format($c79numbers) . ' ' . $footer_currencySymbol }}</p>
							<p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format((($c79numbers * 100)/20) + $c79numbers) . ' ' . $footer_currencySymbol }}</p>
						</td>		                 
			        </tr>
			        --}}
			        <!-- <tr>
			        	<td colspan="6" class="border-0"><hr class="position-absolute w-100 m-0" /></td>
			        </tr> -->
		      	@endif
		      	<!--/ C79-->

		      	{{--//hide for now
		      	<!-- TOTAL CALC-->
		      	<tr>
		        	<td colspan="6" class="border-0 p-0">
		        		<h6>Summary</h6>	
		        		<!-- <div class="alert alert-secondary m-0">Text</div> -->   		
		        	</td>
		        </tr>
		      	<tr>
					<td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
					<td class="text-end px-4 py-5 border-bottom-0">
						<p class="mb-2">Subtotal:</p>			                
						<p class="mb-2"><b>VAT:</b></p>
						<p class="mb-0">Total:</p>
					</td>
					<td class="px-4 py-5 border-bottom-0">
						<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format(($totalnet) + (($pivsmonthtotal * 100)/20) + (($c79numbers * 100)/20)) . ' ' . $footer_currencySymbol }}</p>
						<p class="fw-semibold mb-2 text-end"><b>{{ $currencyFormatter->format(($totalvat) + ($pivsmonthtotal) + ($c79numbers)) . ' ' . $footer_currencySymbol }}</b></p>
						<p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format((($totalnet + $totalvat)) + ((($pivsmonthtotal * 100)/20) + $pivsmonthtotal) + ((($c79numbers * 100)/20) + $c79numbers)) . ' ' . $footer_currencySymbol }}</p>
					</td>		                 
				</tr>				
		      	<!--/ TOTAL CALC-->
		      	--}}

		      	<!--BOX 5-->
		      	@php
		      		if($vatreg->submittingfields)
		      			$_box5 = $vatreg->submittingfields->box_5;
		      		else
		      		{
		      			if($client_api)
		      			{
			      			if($client_api->api_name == 'E-conomic')
			      			{	 
			      				$purchasetotalvat_positive = str_starts_with($purchasetotalvat, '-') ? (-1 * $purchasetotalvat) : $purchasetotalvat;
			      				$_box5 = (($pivsmonthtotal + $salestotalvat) - ($c79numbers + $pivsmonthtotal + $purchasetotalvat_positive)); 
			      			} 
			      			else
			      				$_box5 = (($pivsmonthtotal + $salestotalvat) - ($c79numbers + $pivsmonthtotal + $purchasetotalvat));
			          	}
						else	      			
		      				$_box5 = (($pivsmonthtotal + $salestotalvat) - ($c79numbers + $pivsmonthtotal + $purchasetotalvat));
		      		}
		      	@endphp		      	
		        <tr id="confirm-vatreturns-footer" class="d-none">
		        	<td colspan="2" class="border-0"></td>	
					<td colspan="4" class="align-top px-4 py-5 border-bottom-0">
						<div class="card border border-2 border-primary">
				            <div class="card-body">
								<div class="d-flex justify-content-between flex-wrap mb-3">
									<h5 class="text-start text-uppercase mb-0">Net VAT to pay to {{ ($vatreg->country == "NO") ? 'SKATTEETATEN' : 'HMRC' }} or reclaim:</h5>
									<h5 class="bg-primary rounded-pill text-uppercase mb-0 py-1 px-2 mt-n1 text-white">{{ $currencyFormatter->format($_box5) . ' ' . $footer_currencySymbol }}</h5>
								</div>
				             
				              	<p>Intravat will report this VAT amount to the authorities, and you will subsequently receive documentation and payment details.</p>
				            </div>
				        </div>		          		         
					</td>		                 
		        </tr>
		      	<!--/ BOX 5-->
		    @endif  	
	      </tbody>
	    </table>

	    @if($tab_name == "overview") 
	    	@if($vatreg->country == 'GB')  
	          	<input type="hidden" id="salestotalvat-{{ $vat_reg_id }}" value="{{ $salestotalvat }}" />   
				<input type="hidden" id="purchasetotalvat-{{ $vat_reg_id }}" value="{{ $purchasetotalvat }}" />   
				<input type="hidden" id="salestotalnet-{{ $vat_reg_id }}" value="{{ $salestotalnet }}" />   
				<input type="hidden" id="purchasetotalnet-{{ $vat_reg_id }}" value="{{ $purchasetotalnet }}" />   
	        @elseif($vatreg->country == 'NO')  
	        	{{--<input type="hidden" id="sales-standard-totalvat-{{ $vat_reg_id }}" value="{{ number_format($sales_standard_totalvat, 0, '', '') }}" />   
	          	<input type="hidden" id="sales-medium-totalvat-{{ $vat_reg_id }}" value="{{ number_format($sales_medium_totalvat, 0, '', '') }}" />
	          	<input type="hidden" id="sales-low-totalvat-{{ $vat_reg_id }}" value="{{ number_format($sales_low_totalvat, 0, '', '') }}" />
	          	<input type="hidden" id="sales-zero-totalvat-{{ $vat_reg_id }}" value="{{ number_format($sales_zero_totalvat, 0, '', '') }}" /> 
	          	<input type="hidden" id="sales-fish-totalvat-{{ $vat_reg_id }}" value="{{ number_format($sales_fish_totalvat, 0, '', '') }}" />--}}

	          	<input type="hidden" id="sales-standard-totalnet-{{ $vat_reg_id }}" value="{{ number_format($sales_standard_totalnet, 0, '', '') }}" />   
	          	<input type="hidden" id="sales-medium-totalnet-{{ $vat_reg_id }}" value="{{ number_format($sales_medium_totalnet, 0, '', '') }}" />
	          	<input type="hidden" id="sales-low-totalnet-{{ $vat_reg_id }}" value="{{ number_format($sales_low_totalnet, 0, '', '') }}" />
	          	<input type="hidden" id="sales-zero-totalnet-{{ $vat_reg_id }}" value="{{ number_format($sales_zero_totalnet, 0, '', '') }}" /> 
	          	<input type="hidden" id="sales-fish-totalnet-{{ $vat_reg_id }}" value="{{ number_format($sales_fish_totalnet, 0, '', '') }}" /> 

	          	<input type="hidden" id="purchases-standard-totalvat-{{ $vat_reg_id }}" value="{{ ((str_starts_with($purchases_standard_totalvat, '-')) ? number_format((-1 * $purchases_standard_totalvat), 0, '', '') : number_format($purchases_standard_totalvat, 0, '', '')) }}" />   
	          	<input type="hidden" id="purchases-medium-totalvat-{{ $vat_reg_id }}" value="{{ ((str_starts_with($purchases_medium_totalvat, '-')) ? number_format((-1 * $purchases_medium_totalvat), 0, '', '') : number_format($purchases_medium_totalvat, 0, '', '')) }}" />
	          	<input type="hidden" id="purchases-low-totalvat-{{ $vat_reg_id }}" value="{{ ((str_starts_with($purchases_low_totalvat, '-')) ? number_format((-1 * $purchases_low_totalvat), 0, '', '') : number_format($purchases_low_totalvat, 0, '', '')) }}" />
	          	<input type="hidden" id="purchases-zero-totalvat-{{ $vat_reg_id }}" value="{{ number_format($purchases_zero_totalvat, 0, '', '') }}" /> 
	          	<input type="hidden" id="purchases-fish-totalvat-{{ $vat_reg_id }}" value="{{ number_format($purchases_fish_totalvat, 0, '', '') }}" />	
	        @endif
	        
	        @include('_partials/_content/_vatreturn/vatreturn-overview-buttons') 	         
		@endif    
	</div>  
@endif	
<!-- / Sales/Purchase -->
		