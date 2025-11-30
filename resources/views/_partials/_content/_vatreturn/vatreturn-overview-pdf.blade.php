<tr>
	<td colspan="6" valign="top">
		<!-- Sales/Purchase -->  	   		
		<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none inner-tbl p-0 mt-2-only">			
		  	<tbody>		  		
			    @php
					$tab_name = "previewreport";

					$vat_reg_id = $vatreg->vat_reg_id; 

					$client = $vatreg->client;
					$client_id = $client->client_id;
					$client_users = $client->userclient;  

					$vat_reg_main = $vatreg->vatregmain;
					$client_api = $vat_reg_main->clientapi;

					$vatreturns = $vatreg->vatreturns;
					$vatreturnfiles = ($vatreg->vatreturnfiles) ? $vatreg->vatreturnfiles : []; 

					$pivs_files = ($vatreg->pivs) ? $vatreg->pivs : [];  
					$c79_documents = ($vatreg->c79) ? $vatreg->c79: [];  

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
			    @endif
		    
			    <!--PIVS-->
			    @php
					$pivsmonthtotal = 0;              
			    @endphp
			    @if(count($pivs_files) > 0)   					
			      	@foreach ($pivs_files as $key => $pivs)
				        @php
							$month_total = is_numeric($pivs->month_total) ? $pivs->month_total : 0;
				      		$pivsmonthtotal += $month_total;
				        @endphp						
			      	@endforeach			      					          
			    @endif
			    <!--/ PIVS-->

			    <!--C79-->
			    @php
			      $c79numbers = 0;              
			    @endphp
			    @if(count($c79_documents) > 0)  					                      
					@foreach ($c79_documents as $key => $c79_document)
						@php
							$doc_numbers = is_numeric($c79_document->doc_numbers) ? $c79_document->doc_numbers : 0;		      		
				      		$c79numbers += $doc_numbers;            
						@endphp						
					@endforeach					        
			    @endif
			    <!--/ C79-->            

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
			    <tr>
					<td colspan="2" class="border-none"></td>  
					<td colspan="4" valign="top" class="border-none">
						<div class="border-primary">
							<div class="card-body">
								<div class="">
									<h5 class="text-start text-uppercase">Net VAT to pay to {{ ($vatreg->country == "NO") ? 'SKATTEETATEN' : 'HMRC' }} or reclaim:<h5 class="bg-primary rounded-pill text-uppercase text-white text-end py-1 px-2" style=" float: right;">{{ $currencyFormatter->format($_box5) . ' ' . $footer_currencySymbol }}</h5></h5>
								</div>

								<p>Intravat will report this VAT amount to the authorities, and you will subsequently receive documentation and payment details.</p>
							</div>
						</div>                           
					</td>                    
			    </tr>
				<!--/ BOX 5-->                
			</tbody>
		</table>
		<!-- / Sales/Purchase -->
	</td>
</tr>
<tr>
	<td colspan="6" valign="top"> 
		<!-- Sales/Purchase -->  	   		
		<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none inner-tbl p-0">			
		  	<tbody>
		  		<tr>
					<td align="left" colspan="6" class="border-none p-0">
						<h6>Sales and Purchases</h6>                        
					</td>
				</tr>
				<tr>
					<th align="left" class="p-2">Item</th>
					<th align="left" class="p-2">Description</th>
					<th align="center" class="p-2">Invoices</th>
					<th align="center" class="p-2">% VAT</th>
					<th align="right" class="p-2">VAT</th>
					<th align="right" class="p-2">NET</th>
				</tr>
				
			    @php
					$tab_name = "previewreport";

					$vat_reg_id = $vatreg->vat_reg_id; 

					$client = $vatreg->client;
					$client_id = $client->client_id;
					$client_users = $client->userclient;  

					$vat_reg_main = $vatreg->vatregmain;
					$client_api = $vat_reg_main->clientapi;

					$vatreturns = $vatreg->vatreturns;
					$vatreturnfiles = ($vatreg->vatreturnfiles) ? $vatreg->vatreturnfiles : []; 

					$pivs_files = ($vatreg->pivs) ? $vatreg->pivs : [];  
					$c79_documents = ($vatreg->c79) ? $vatreg->c79: [];

					$import_vat_files = ($vatreg->importvatfiles) ? $vatreg->importvatfiles : [];
			        if($import_vat_files)
			        {
			          $import_vat_files_all = $import_vat_files;
			          
			          $filtered_import_vat_files = $import_vat_files_all->filter(function ($import_vat_file, $key) {         
			              return $import_vat_file->file_type == 'xml'; 
			          });

			          $import_vat_files = $filtered_import_vat_files;          
			        }  

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
							<td align="left" class="p-2">{{ ($vatreturn->invoice_type == 'sale') ? 'Sales' : 'Purchases' }}</td>
							<td align="left" class="p-2">{{ ($vatreturn->invoice_type == 'sale') ? 'Sale' : 'Purchase' }} Invoice</td>
							<td align="center" class="p-2">{{ $vatreturn->invoice_count }}</td>
							<td align="center" class="p-2">{{ $vat_percentage }}</td> 
							<td align="right" class="p-2">{{--{{ ($vatreturn->invoice_type == 'purchase' && $vatreturn->net_amount > 0) ? '-' : '' }}--}}{{ $currencyFormatter->format($vatreturn->net_amount) . ' ' . (($currencySymbol) ? $currencySymbol : $vatreturn->currency_code) }}</td>
							<td align="right" class="p-2">{{--{{ ($vatreturn->invoice_type == 'purchase' && $vatreturn->vat_amount > 0) ? '-' : '' }}--}}{{ $currencyFormatter->format($vatreturn->vat_amount) . ' ' . (($currencySymbol) ? $currencySymbol : $vatreturn->currency_code) }}</td>                 
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
					
					<tr><td colspan="6" class="border-none pt-4"></td></tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">Subtotal:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format($totalnet) . ' ' . $footer_currencySymbol }}</td>                   
					</tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">VAT:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format($totalvat) . ' ' . $footer_currencySymbol }}</td>                   
					</tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">Total:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format(($totalnet + $totalvat)) . ' ' . $footer_currencySymbol }}</td>                   
					</tr> 
					<tr><td colspan="6" class="border-none pb-4"></td></tr>
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
			      	@include('_partials/_content/_vatreturn/vatreturn-no-invoices-lazy')
			    @endif
		    
		    	<!--NO - IMPORT/EXPORT-->
		    	@if($vatreg->country == "NO")
				    @php
						$import_statistical_number = 0;
			      		$import_fee_number = 0;
			      		$export_statistical_number = 0;              
				    @endphp
				    @if(count($import_vat_files) > 0)   
						<tr>
							<td colspan="6" class="border-none"><hr class="position-absolute w-100 m-0" style="border: 0.2px solid #d4d8dd;" /></td>
						</tr>

						@if(count($vatreturns) > 2)
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
								
								@php							
									$page_no = (isset($page)) ? (($page == 'confirm') ? 1 :	3) : 3;								
								@endphp	
								@include('_partials/_content/_previewreport/footer-pdf') 
							</div>
							<!--/ Content Page -->

							@if(isset($page))	
								@if($page == 'confirm')												
									<!-- Content Page -->
									<div class="full-width p-1 rest-page" style="position: relative;">    
										<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">
											<thead>    
												<tr>
													<th align="right" valign="middle" colspan="6">  
														<img src="<?php echo $logo ?>" width="25%" class="mb-2-only">
													</th>
												</tr>

												<tr>
													<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="2">Company Name:</th>
													<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="3">{{ $client->client_name }}</th>
													<th class="h4 p-0 m-0" align="left" valign="middle" colspan="1">VAT Return</th>
												</tr>

												<tr>
													<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="2">VAT No.:</th>
													<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="3">{{ $client->vatno }}</th>
													<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="1">
													<p class="fw-normal p-0 m-0">{{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
													\Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</p>
													</th>
												</tr>                    
											</thead>
											<tbody>
												<tr>
													<td colspan="6" valign="top"> 
														<!-- Sales/Purchase -->  	   		
														<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none inner-tbl p-0 my-2">			
														  	<tbody>
								@endif						  		
							@else
								<!-- Content Page -->
								<div class="full-width p-1 rest-page page-break" style="position: relative;">    
									<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">
										@include('_partials/_content/_previewreport/header-pdf')       
										<tbody>							
											<tr>
												<td colspan="6" valign="top"> 
													<!-- Sales/Purchase -->  	   		
													<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none inner-tbl p-0 my-2">			
													  	<tbody>
							@endif					  		
						@endif

						<tr>
							<td colspan="6" class="border-none p-0">
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
							<td align="left" class="p-2"><strong>Import</strong></td>
							<td align="left" class="p-2">Statistic value</td>
							<td align="center" class="p-2">-</td>
							<td align="center" class="p-2">-</td> 
							<td align="right" class="p-2">-</td>
							<td align="right" class="p-2">{{ $currencyFormatter->format($import_statistical_number) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
						</tr>

						<tr>
							<td align="left" class="p-2"><strong>Import</strong></td>
							<td align="left" class="p-2">Duties & taxes</td>
							<td align="center" class="p-2">-</td>
							<td align="center" class="p-2">-</td> 
							<td align="right" class="p-2">-</td>
							<td align="right" class="p-2">{{ $currencyFormatter->format($import_fee_number) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
						</tr>

						<tr>
							<td align="left" class="p-2"><strong>Export & Re-export</strong></td>
							<td align="left" class="p-2">Statistic value</td>
							<td align="center" class="p-2">-</td>
							<td align="center" class="p-2">-</td> 
							<td align="right" class="p-2">-</td>
							<td align="right" class="p-2">{{ $currencyFormatter->format($export_statistical_number) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
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
						<td colspan="6" class="border-none"><hr class="position-absolute w-100 m-0" style="border: 0.2px solid #d4d8dd;" /></td>
					</tr>

					@if(count($vatreturns) > 2)
												</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
							
							@php							
								$page_no = (isset($page)) ? (($page == 'confirm') ? 1 :	3) : 3;								
							@endphp	
							@include('_partials/_content/_previewreport/footer-pdf') 
						</div>
						<!--/ Content Page -->

						@if(isset($page))	
							@if($page == 'confirm')												
								<!-- Content Page -->
								<div class="full-width p-1 rest-page" style="position: relative;">    
									<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">
										<thead>    
											<tr>
												<th align="right" valign="middle" colspan="6">  
													<img src="<?php echo $logo ?>" width="25%" class="mb-2-only">
												</th>
											</tr>

											<tr>
												<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="2">Company Name:</th>
												<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="3">{{ $client->client_name }}</th>
												<th class="h4 p-0 m-0" align="left" valign="middle" colspan="1">VAT Return</th>
											</tr>

											<tr>
												<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="2">VAT No.:</th>
												<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="3">{{ $client->vatno }}</th>
												<th class="fw-normal p-0 m-0" align="left" valign="middle" colspan="1">
												<p class="fw-normal p-0 m-0">{{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
												\Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</p>
												</th>
											</tr>                    
										</thead>
										<tbody>
											<tr>
												<td colspan="6" valign="top"> 
													<!-- Sales/Purchase -->  	   		
													<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none inner-tbl p-0 my-2">			
													  	<tbody>
							@endif						  		
						@else
							<!-- Content Page -->
							<div class="full-width p-1 rest-page page-break" style="position: relative;">    
								<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">
									@include('_partials/_content/_previewreport/header-pdf')       
									<tbody>							
										<tr>
											<td colspan="6" valign="top"> 
												<!-- Sales/Purchase -->  	   		
												<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none inner-tbl p-0 my-2">			
												  	<tbody>
						@endif					  		
					@endif

					<tr>
						<td colspan="6" class="border-none p-0">
							<h6>Postponed import VAT statement</h6>
						</td>
					</tr>   

			      	@foreach ($pivs_files as $key => $pivs)
				        @php
							$month_total = is_numeric($pivs->month_total) ? $pivs->month_total : 0;
				      		$pivsmonthtotal += $month_total;
				        @endphp
						<tr>
							<td align="left" class="p-2">{{ \Carbon\Carbon::parse('01-' .$pivs->month_year)->format('F') }}</td>
							<td align="left" class="p-2">PIVS</td>
							<td align="center" class="p-2">-</td>
							<td align="center" class="p-2">-</td> 
							<td align="right" class="p-2">{{ $currencyFormatter->format(($month_total * 100)/20) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
							<td align="right" class="p-2">{{ $currencyFormatter->format($month_total) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
						</tr>
			      	@endforeach			      

			      	{{--
					<tr><td colspan="6" class="border-none pt-4"></td></tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">Subtotal:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format(($pivsmonthtotal * 100)/20) . ' ' . $footer_currencySymbol }}</td>                   
					</tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">VAT:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format($pivsmonthtotal) . ' ' . $footer_currencySymbol }}</td>                   
					</tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">Total:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format((($pivsmonthtotal * 100)/20) + $pivsmonthtotal) . ' ' . $footer_currencySymbol }}</td>                   
					</tr> 
					<tr><td colspan="6" class="border-none pb-4"></td></tr> 
					--}}            
			    @endif
			    <!--/ PIVS-->

			    <!--C79-->
			    @php
			      $c79numbers = 0;              
			    @endphp
			    @if(count($c79_documents) > 0)  
					<tr>
						<td colspan="6" class="border-none"><hr class="position-absolute w-100 m-0" style="border: 0.2px solid #d4d8dd;" /></td>
					</tr>					

					<tr>
						<td colspan="6" class="border-none p-0">
							<h6>C79 Import VAT Certificate</h6>                  
						</td>
					</tr>                           
					@foreach ($c79_documents as $key => $c79_document)
						@php
							$doc_numbers = is_numeric($c79_document->doc_numbers) ? $c79_document->doc_numbers : 0;		      		
				      		$c79numbers += $doc_numbers;            
						@endphp
						<tr>
							<td align="left" class="p-2">{{ \Carbon\Carbon::parse('01-' .$c79_document->month_year)->format('F') }}</td>
							<td align="left" class="p-2">C79</td>
							<td align="center" class="p-2">-</td>
							<td align="center" class="p-2">-</td> 
							<td align="right" class="p-2">{{ $currencyFormatter->format(($doc_numbers * 100)/20) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
							<td align="right" class="p-2">{{ $currencyFormatter->format($doc_numbers) . ' ' . (($currencySymbol) ? $currencySymbol : '') }}</td>
						</tr>
					@endforeach

					{{--
					<tr><td colspan="6" class="border-none pt-4"></td></tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">Subtotal:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format(($c79numbers * 100)/20) . ' ' . $footer_currencySymbol }}</td>                   
					</tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">VAT:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format($c79numbers) . ' ' . $footer_currencySymbol }}</td>                   
					</tr>
					<tr>                    
						<td align="right" colspan="5" class="border-none p-2 pb-0">Total:</td>
						<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format((($c79numbers * 100)/20) + $c79numbers) . ' ' . $footer_currencySymbol }}</td>                   
					</tr> 
					<tr><td colspan="6" class="border-none pb-4"></td></tr>
					--}}            
			    @endif
			    <!--/ C79-->            

			    {{--
				<!--BOX 5-->
			    @php
					if($vatreg->submittingfields)
						$_box5 = $vatreg->submittingfields->box_5;
					else
						$_box5 = (($pivsmonthtotal + $salestotalvat) - ($c79numbers + $pivsmonthtotal + $purchasetotalvat));
			    @endphp           
			    <tr id="previewreport-vatreturns-footer" class="">
					<td colspan="2" class="border-none"></td>  
					<td colspan="4" valign="top" class="border-none">
						<div class="card border border-2 border-primary">
							<div class="card-body">
								<div class="">
									<h5 class="text-start text-uppercase">Net VAT to pay to HMRC or reclaim:<h5 class="bg-primary rounded-pill text-uppercase text-white text-end py-1 px-2" style=" float: right;">{{ $currencyFormatter->format($_box5) . ' ' . $footer_currencySymbol }}</h5></h5>
								</div>

								<p>Intravat will report this VAT amount to the authorities, and you will subsequently receive documentation and payment details.</p>
							</div>
						</div>                           
					</td>                    
			    </tr>
				<!--/ BOX 5-->   
				--}}             
			</tbody>
		</table>
		<!-- / Sales/Purchase -->	
	</td>
</tr>	