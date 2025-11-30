{{--@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))--}}
    @if($vatreg->status == 1)
    			    	
    	<div class="d-inline-block float-end m-2">
			<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
			<ul class="dropdown-menu dropdown-menu-end m-0">
				<li>
					<a href="javascript:;" class="dropdown-item btn-refresh {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled' : '' }}" data-product_type="1" data-vat_reg_id="{{ $vat_reg_id }}" title="Refresh">Refresh data</a>
				</li>
				@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
					@if (Auth::check())
						@php
							$rolename = Auth::user()->roles()->first()->name;
						@endphp
						@if (isset($authUser))									  
							@php
								$rolename = $authUser->rolename;
							@endphp                
						@endif							
						@if($rolename == 'super-admin' || $rolename == 'Super admin')
						<li>
							<a href="javascript:;" class="dropdown-item btn-disregard-period {{ (!$vatregmain_status) ? 'disabled' : '' }}" data-product_type="1" data-client_id="{{ $vatreg->client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-vat_reg_period="{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y')) }}" title="{{ ($vatreg->is_disregard) ? 'Enable Period' : 'Disregard Period' }}">{{ ($vatreg->is_disregard) ? 'Enable Period' : 'Disregard Period' }}</a>
						</li>
						@endif
					@endif
				@endif																			
			</ul>
		</div>

		@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
    		<button type="button" class="btn btn-secondary float-end disabled">Not assigned</button>
    	@endif
    @elseif($vatreg->status == 2)			    	
    	
    	<div class="d-inline-block float-end m-2">
			<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
			<ul class="dropdown-menu dropdown-menu-end m-0">
				<li>
					<a href="javascript:;" class="dropdown-item btn-refresh {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled' : '' }}" data-product_type="1" data-vat_reg_id="{{ $vat_reg_id }}" title="Refresh">Refresh data</a>
				</li>	

				@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
					@if (Auth::check())
						@php
							$rolename = Auth::user()->roles()->first()->name;
						@endphp
						@if (isset($authUser))									  
							@php
								$rolename = $authUser->rolename;
							@endphp                
						@endif							
						@if($rolename == 'super-admin' || $rolename == 'Super admin')
						<li>
							<a href="javascript:;" class="dropdown-item btn-disregard-period {{ (!$vatregmain_status) ? 'disabled' : '' }}" data-product_type="1" data-client_id="{{ $vatreg->client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-vat_reg_period="{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y')) }}" title="{{ ($vatreg->is_disregard) ? 'Enable Period' : 'Disregard Period' }}">{{ ($vatreg->is_disregard) ? 'Enable Period' : 'Disregard Period' }}</a>
						</li>
						@endif
					@endif	
				@endif											
			</ul>
		</div>

		@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
    		<button type="button" id="btn-email-sent-{{ $vat_reg_id }}" class="btn btn-primary float-end {{ (count($client_users) > 0) ? '' : 'disabled' }}" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#sendModal-draft-{{ $vat_reg_id }}-0" {{ ($vatreg->is_disregard || !$vatregmain_status) ? 'disabled=disabled' : '' }}>Send for review</button>	
    	@endif
    @elseif($vatreg->status == 3)
    	<div class="d-inline-block float-end m-2">
			<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
			<ul class="dropdown-menu dropdown-menu-end m-0">				
				@if($vatreg->declined_at == NULL)			    	
					<li>
						<a href="javascript:;" class="dropdown-item btn-cancel-pending-review {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled' : '' }}" id="btn-cancel-pending-review-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" title="Cancel the pending review">Cancel the pending review</a>
	    			</li>
	    		@else
	    			<li>
						<a href="javascript:;" class="dropdown-item btn-refresh {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled' : '' }}" data-product_type="1" data-vat_reg_id="{{ $vat_reg_id }}" title="Refresh">Refresh data</a>
					</li>				    		
				@endif

				@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
					<li>
						<a href="javascript:;" class="dropdown-item text-danger {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled' : '' }}" id="btn-email-sent-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#sendModal-draft-{{ $vat_reg_id }}-0" title="Re-send email for review">Re-send email</a>
					</li>
				@endif
			</ul>
		</div>

		@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
    		<button type="button" class="btn btn-warning float-end disabled">{{ ($vatreg->declined_at == NULL) ? 'Email sent' : $vatreg->statustext }}</button>	
    	@endif
    @elseif($vatreg->status == 4)	
    	@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
    		<button type="button" id="btn-upload-receipt-{{ $vat_reg_id }}" class="btn btn-warning float-end btn-upload-receipt" data-vat_reg_id="{{ $vat_reg_id }}" {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>Upload Receipt</button>
    	@endif
    @elseif($vatreg->status == 5)
    	@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
	    	<button type="button" class="btn btn-info float-end disabled" id="btn-upload-receipt-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>Receipt Uploaded</button>						
			
			<button type="button" id="btn-open-lock-{{ $vat_reg_id }}" class="btn btn-danger float-end mx-2 btn-open-lock" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#sendModal-lock-{{ $vat_reg_id }}-0" {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>Lock</button>
		@endif
	@elseif($vatreg->status == 6)
		@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
    		<span class="bg-label-danger float-end p-2 m-0 h6">Locked</span>
    	@endif			                	
    @endif
{{--@endif--}}