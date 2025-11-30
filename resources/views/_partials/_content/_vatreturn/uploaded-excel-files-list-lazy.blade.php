<h4 class="onboarding-title text-body text-left">Upload Excel/XML File</h4>			
<div class="onboarding-info text-start">
	<ul class="list-group list-group-timeline">	
		@php
			$excel_exists = 0;	
			$vatreturn_file = "";	

			$vat_reg_id = $vatreg->id;
			$client_id = $vatreg->client_id;
			$vatreturnfiles = $vatreg->vatreturnfiles;
		@endphp
		@if (count($vatreturnfiles) > 0)				
			@php					
				$excel_exists = 1;	
				$vatreturn_file = $vatreturnfiles->first();			
			@endphp
		@endif									
		
		@if(!$excel_exists)				
			<li class="list-group-item list-group-timeline-danger d-flex" id="li-{{ $vat_reg_id }}">
				<div class="col-sm-3 d-flex align-items-center excel-text">					
					{{ 'Excel/XML for ' . (($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'))) }}
				</div>

				<div class="col-sm-3 d-flex align-items-center">									
					<button class="btn btn-label-primary btn-upload-new-excel-file" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#onboardingSlideExcelModal-{{ $vat_reg_id }}">
						<i class='bx bx-up-arrow-circle me-1'></i>
						<span class="align-middle">Upload</span>
					</button>
				</div>											
			</li>			
			@php
				$excel_type = 'upload';
			@endphp		
		@else
			@if($vatreturn_file->folder_id != NULL)
				<li class="list-group-item list-group-timeline-primary d-flex" id="li-{{ $vat_reg_id }}-{{ $vatreturn_file->id }}">
					<div class="col-sm-3 d-flex align-items-center excel-text">
						{{ 'Excel/XML for ' . (($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'))) }}
					</div>

					<div class="col-sm-9 d-flex align-items-center">
						<button class="btn btn-label-warning me-2 btn-overwrite-new-excel-file" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#onboardingSlideExcelModal-{{ $vat_reg_id }}">
							<i class='bx bxs-analyse me-1'></i>
							<span class="align-middle">Overwrite File</span>
						</button>
						<button class="btn btn-label-danger me-2 btn-delete-excel-file" data-vat_reg_id="{{ $vat_reg_id }}" data-excelid="{{ $vatreturn_file->id }}" data-client_id="{{ $client_id }}">
							<i class="bx bx-x me-1"></i>
							<span class="align-middle">Delete</span>
						</button>
						
						<button type="button" class="btn rounded-pill btn-icon btn-primary me-2 btn-download-excel-file" data-vat_reg_id="{{ $vat_reg_id }}" data-excelid="{{ $vatreturn_file->id }}">
							<span class="tf-icons bx bxs-download"></span>
						</button>
					</div>						
				</li>
				@php
					$excel_type = 'overwrite';
				@endphp	
			@elseif($vatreturn_file->folder_id == NULL)
				<li class="list-group-item list-group-timeline-danger d-flex" id="li-{{ $vat_reg_id }}-{{ $vatreturn_file->id }}">
					<div class="col-sm-3 d-flex align-items-center excel-text">
						{{ 'Excel/XML for ' . (($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'))) }}
					</div>

					<div class="col-sm-3 d-flex align-items-center">									
						<button class="btn btn-label-primary btn-upload-new-excel-file" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#onboardingSlideExcelModal-{{ $vat_reg_id }}">
							<i class='bx bx-up-arrow-circle me-1'></i>
							<span class="align-middle">Upload</span>
						</button>
					</div>											
				</li>
				@php
					$excel_type = 'upload';
				@endphp	
			@endif			
		@endif
		@include('_partials/_modals/modal-upload-excel-file-lazy')
	</ul>
</div>       