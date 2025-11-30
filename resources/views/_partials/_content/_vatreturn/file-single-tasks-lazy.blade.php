@if(!isset($morepage))
<div class="col-md pending-single-tasks">					
	<h4 class="py-3 breadcrumb-wrapper mb-4">
	  	<span class="text-muted fw-light">Pending {{ $file_type_title }} Tasks</span>		
	</h4>

	<div class="accordion mt-0 accordion-header-primary" id="{{ $file_type }}_tasks">
@endif
		@foreach ($filetasks->lazy() as $filetask)	
			@php					
				$vatreg = $filetask;
			@endphp	
				
			@if($vatreg->country == 'GB' || $vatreg->country == 'NO')

				@php					
					//$vatreg = $filetask;
					$vat_reg_id = $vatreg->id;	

					$client = $vatreg->client;
					$client_id = $client->id;
					$client_users = $client->userclient;

					$team_users = $vatreg->uservatreg;

					if($file_type == 'pivs')
					{
						$files = $vatreg->pivs;
						$_taskdate = $pivs_taskdate;
					}
					else if($file_type == 'cas')
					{
						$files = $vatreg->cas;
						$_taskdate = $cas_taskdate;
					}
					else if($file_type == 'dda')
					{
						$files = $vatreg->dda;
						$_taskdate = $dda_taskdate;
					}
					
					$frequency = $vatreg->frequency;
					if((count($files) < $vatreg->frequency))
					{
						$i = count($files);					
						$frequency = $vatreg->frequency;
					}

					if($authUser->role == 'team-user') 
				    { 				      
				        $show_vatreg = false;
				        $uservatregs = $vatreg->uservatreg;
				        
				        $filtered_uservatregs_result = $uservatregs->filter(function ($uservatreg, $key) use($authUser) {         
				            return ($uservatreg->user_id == $authUser->user_id); 
				        }); 
				        
				        if(count($filtered_uservatregs_result) > 0)
				        	$show_vatreg = true;			     
				    }
				@endphp		
				
				
				@for ($i = 0; $i < $frequency; $i++)
				{{--@for ($i = $frequency; $i > 0; --$i)--}}
					@php
						$file_exists = 0;
					@endphp
					@foreach ($files as $file)	
						@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('Ym') == \Carbon\Carbon::parse('01-'.$file->month_year)->format('Ym'))						
							@php
								$file_exists = 1;
								break;
							@endphp
						@endif									
					@endforeach

					@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('Ym') == \Carbon\Carbon::now()->format('Ym'))
						@if(\Carbon\Carbon::now()->format('d') < $_taskdate)
							@php
								$file_exists = 1;
								break;
							@endphp
						@endif
					@else
						@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('Ym') > \Carbon\Carbon::now()->format('Ym'))
							@php
								$file_exists = 1;
								break;
							@endphp
						@endif	
					@endif	

					{{--@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('Ym') > \Carbon\Carbon::now()->format('Ym'))
						@php
							$file_exists = 1;
							break;
						@endphp
					@endif
					--}}					

					@if(!$file_exists)

						@if($file_type == 'cas' || $file_type == 'dda')
							@php		
								$vat_reg_main = $vatreg->vatregmain;
								$cas_dda_months = $vat_reg_main->casddamonths;
							@endphp		

							@foreach ($cas_dda_months as $cas_dda_month)
								@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('Ym') == \Carbon\Carbon::parse('01-'.$cas_dda_month->month_year)->format('Ym'))	
									@php									
										$file_exists = 1;
										break;
									@endphp
								@endif
							@endforeach
						@else
							@php									
								$file_exists = 1;						
							@endphp		
						@endif

						@if($file_exists)
							<div class="accordion-item card sort-item" data-country="{{ $vatreg->country }}" data-vat_reg_main_id="{{ $vatreg->vat_reg_main_id }}" data-index="{{ $vatreg->statusorder }}" data-range="{{ \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('Y-m') }}"  {{ isset($show_vatreg) ? (($show_vatreg) ? 'data-all=true' : 'data-all=false') : '' }} style="{{ isset($show_vatreg) ? (($show_vatreg) ? '' : 'display: none;') : '' }}">
								<h2 class="accordion-header table-responsive text-nowrap">					
									<button type="button" class="accordion-button collapsed" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}" aria-expanded="false" id="accord-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}">
										<table class="table border-0">
											<!--<colgroup>			    										
												<col width="10%"/>
									            <col width="40%"/>
									            <col width="10%"/>
									            <col width="10%"/>
									            <col width="15%"/>
									            <col width="15%"/>
									             <col class="col-lg-1 col-md-2 col-sm-3 col-xs-4" />  
		                    					<col class="col-lg-5 col-md-4 col-sm-3 col-xs-8"/> 
		                    					<col class="col-lg-1 col-md-2 col-sm-3 col-xs-4" />  
		                    					<col class="col-lg-1 col-md-2 col-sm-3 col-xs-4" />  
		                    					<col class="col-lg-2 col-md-3 col-sm-4 col-xs-5" />  
		                    					<col class="col-lg-2 col-md-3 col-sm-4 col-xs-5" />
											</colgroup> -->

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
											<tbody>
												<tr>              
													<td class="border-bottom-0 p-0">										
														<img src="{{asset('assets/img/flags/'. $vatreg->country .'.png')}}" class="country-flag me-2"><span class="btn-group-vertical">{{ $vatreg->country }}</span>
													</td>
													<td class="border-bottom-0 p-0">
														{{ $client->client_name }}<br>		
														<span class="badge rounded-pill bg-label-{{ ($vatreg->vat_reg_main_type) ? (($vatreg->vat_reg_main_type == 'Basic') ? 'primary' : 'danger') : 'primary' }}">{{ ($vatreg->vat_reg_main_type) ? $vatreg->vat_reg_main_type : 'Basic' }}</span>
														<span class="badge rounded-pill bg-label-primary">{{ ($file_type == 'pivs') ? 'Postponed import VAT statement' : $file_type_title }}</span>
													</td>
													<td class="border-bottom-0 p-0"></td>
													<td class="border-bottom-0 p-0">											
														{{ \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('M y') }}
													</td>										
													<td class="border-bottom-0 p-0 text-center"></td>															
													<td class="border-bottom-0 p-0 status">
														<span class="badge bg-label-primary">Upload Document</span><br>
														@if(isset($authUser))
															@if($authUser->role == 'super-admin')
																@if($team_users)
																	@if(count($team_users) == 0)
																		<span class="badge rounded-pill bg-label-danger my-1 text-capitalize">Not assigned</span>
																	@else
																		@foreach ($team_users as $team_user)
																			@php
																				$user = $team_user->user;
																				$dvuser = $user->dvuser;
																			@endphp
																			<span class="badge rounded-pill bg-label-success my-1 text-capitalize">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</span>
																		@endforeach
																	@endif	
																@else
																	<span class="badge rounded-pill bg-label-danger my-1 text-capitalize">Not assigned</span>
																@endif
															@endif
														@endif													
													</td>              
												</tr>
											</tbody>
							            </table>								
									</button>
								</h2>
							</div>
							
							@include('_partials/_modals/modal-file-upload-single-lazy')
						@endif	
					@endif
				@endfor
			@endif	
		@endforeach

@if(!isset($morepage))		
	</div>
	<div id="{{ $file_type }}_tasks_block" class="h-px-20"></div>
</div>
@endif