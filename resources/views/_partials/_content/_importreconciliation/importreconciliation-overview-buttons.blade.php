@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($vatreg->frequency)->format('Ymd') < \Carbon\Carbon::now()->format('Ymd'))
    @if($vatreg->status_import_re == 1)
    			    	
    	<div class="d-inline-block float-end m-2">
			<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
			<ul class="dropdown-menu dropdown-menu-end m-0">
				<li>
					<a href="javascript:;" class="dropdown-item btn-refresh" data-product_type="2" data-vat_reg_id="{{ $vat_reg_id }}" title="Refresh">Refresh data</a>
				</li>
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
						<a href="javascript:;" class="dropdown-item btn-disregard-period" data-product_type="2" data-vat_reg_id="{{ $vat_reg_id }}" data-vat_reg_period="{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y')) }}" title="{{ ($vatreg->is_disregard_import_re) ? 'Enable Period' : 'Disregard Period' }}">{{ ($vatreg->is_disregard_import_re) ? 'Enable Period' : 'Disregard Period' }}</a>
					</li>
					@endif
				@endif																		
			</ul>
		</div>

    	<button type="button" class="btn btn-secondary float-end disabled">Not assigned</button>		    	
    @elseif($vatreg->status_import_re == 2)			    	
    	
    	<div class="d-inline-block float-end m-2">
			<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
			<ul class="dropdown-menu dropdown-menu-end m-0">
				<li>
					<a href="javascript:;" class="dropdown-item btn-refresh" data-product_type="2" data-vat_reg_id="{{ $vat_reg_id }}" title="Refresh">Refresh data</a>
				</li>	
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
						<a href="javascript:;" class="dropdown-item btn-disregard-period" data-product_type="2" data-vat_reg_id="{{ $vat_reg_id }}" data-vat_reg_period="{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y')) }}" title="{{ ($vatreg->is_disregard_import_re) ? 'Enable Period' : 'Disregard Period' }}">{{ ($vatreg->is_disregard_import_re) ? 'Enable Period' : 'Disregard Period' }}</a>
					</li>
					@endif
				@endif											
			</ul>
		</div>

		{{--
    	<button type="button" id="btn-email-sent-{{ $vat_reg_id }}" class="btn btn-primary float-end {{ (count($client_users) > 0) ? '' : 'disabled' }}" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#sendModal-draft-{{ $vat_reg_id }}-0" {{ ($vatreg->is_disregard_import_re) ? 'disabled=disabled' : '' }}>Send for review</button>	--}}

    	<button type="button" class="btn btn-primary float-end disabled" data-bs-toggle="modal" data-bs-target="" {{ ($vatreg->is_disregard_import_re) ? 'disabled=disabled' : '' }}>Send for review</button>
	@elseif($vatreg->status_import_re == 7)
    	<span class="bg-label-danger float-end p-2 m-0 h6">Completed</span>		                
    @endif
@endif