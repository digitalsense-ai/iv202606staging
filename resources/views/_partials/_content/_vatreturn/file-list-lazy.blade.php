<h4 class="onboarding-title text-body text-left">{{ $file_type_title }}	
	@if($file_type == 'vatcontrol' || $file_type == 'ircontrol')
		@if($files)
			@php		
				$controlfile = $files->first();
			@endphp	

			@if($controlfile)						
				@if($controlfile->file_id)
					<button type="button" class="btn btn-label-primary m-2 btn-download-file btn-download-missing-invoices" title="{{ ($file_type == 'vatcontrol' || $file_type == 'ircontrol') ? (($controlfile->file_id) ? 'Missing Invoices' : 'No Missing Invoices') : 'Download' }}" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $controlfile->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($controlfile->file_id) ? '' : 'disabled=disabled' }}>
						<i class="tf-icons bx bxs-download me-1"></i> Download Missing Invoices
					</button>	
				@else
					<span class="text-bg-danger ms-4 px-2 fs-6">No Missing Invoices</span>				
				@endif
			@else
				<span class="text-bg-danger ms-4 px-2 fs-6">No Missing Invoices</span>	
			@endif	
		@endif
	@endif	
</h4>

<div class="onboarding-info text-start">
	<ul class="list-group list-group-timeline">
		@if($file_type == 'documents' || $file_type == 'ci' || $file_type == 'vatreturn' || $file_type == 'vatcontrol' || $file_type == 'ircontrol' || $file_type == 'iranyexcel') 

			@foreach ($files as $dockey => $file)	
				{{--
				@if($file->file_id)		
				--}}
					<li class="list-group-item list-group-timeline-primary d-flex align-items-center flex-wrap" id="li-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}">
						<div class="col-sm-3">
							@if($file_type == 'documents') 
								{{ ++$dockey . '. ' . $file->doc_type . ' document.' }} 
								<span id="display-file-name-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}">@if($file->o_file_name){{ ' - ' . $file->o_file_name }}@endif</span>
							@elseif($file_type == 'ci') 							
								{!! ++$dockey . '. ' . $file_type_title . ' for Sale Invoices <span class="alert-primary text-end fs-tiny p-1 mx-2" title="' . $file->sale_invoice_nos . '">' . $file->invoice_count . '</span>' !!}
							@elseif($file_type == 'vatreturn' || $file_type == 'vatcontrol' || $file_type == 'ircontrol' || $file_type == 'iranyexcel')	
								{{ ++$dockey . '. ' . (($file->anyexcel_template_id) ? ' Excel/XML for ' : 'API VAT check for ') . (($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'))) }}<br>
								{{--
								@if($file->excel_column_template_id)								
									@if($file->excelcolumntemplate)
										<span class="small"><b class="text-bg-light">Template Name:</b> {{ $file->excelcolumntemplate->name }}</span>
									@endif							
								@endif
								--}}
								@if($file->anyexcel_template_id)								
									@if($file->anyexceltemplate)
										<span class="small"><b class="text-bg-light">Template Name:</b> {{ $file->anyexceltemplate->name }}</span>
									@endif							
								@endif
							@endif
						</div>
						
						<div class="col-sm-4">	
							<button class="btn btn-label-danger m-2 btn-delete-file" title="Delete" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
								<i class="bx bx-x me-1"></i>
								<span class="align-middle">Delete</span>
							</button>
							
							@if($file_type != 'vatcontrol' && $file_type != 'ircontrol')
							<button type="button" class="btn rounded-pill btn-icon btn-primary m-2 btn-download-file" title="{{ ($file_type == 'vatcontrol' || $file_type == 'ircontrol') ? (($file->file_id) ? 'Missing Invoices' : 'No Missing Invoices') : 'Download' }}" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($file->file_id) ? '' : 'disabled=disabled' }}>
								<span class="tf-icons bx bxs-download"></span>
							</button>
							@endif

							@if($file_type == 'documents') 
							<button type="button" class="btn rounded-pill btn-icon btn-warning m-2 btn-view-file" title="View" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($file->file_id) ? '' : 'disabled=disabled' }}>
								<span class="fa-solid fa-eye"></span>
							</button>
							@endif

							@if($file_type == 'ci') 
								<button type="button" class="btn rounded-pill btn-icon btn-warning m-2 btn-refresh-file" title="Refresh" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
									<span class="bx bx-refresh fs-large"></span>
								</button>
							@endif
							
							@if($file->vatreturnofiles)
								@if(count($file->vatreturnofiles) > 0)
									<select class="form-select btn-download-file w-50 d-inline-block" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" data-original_file="true">
										<option value="">Download Orginal File</option>
										@foreach($file->vatreturnofiles as $key => $vatreturnofile)
						                  <option value="{{ $vatreturnofile->id }}">{{ ($vatreturnofile->o_file_name) ? $vatreturnofile->o_file_name :  $vatreturnofile->file_name }}</option>
						                @endforeach									
									</select>
								@endif
							@endif

							@if($file->vatcontrolofiles)
								@if(count($file->vatcontrolofiles) > 0)
									<select class="form-select btn-download-file w-50 d-inline-block" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" data-original_file="true">
										<option value="">Download Orginal File</option>
										@foreach($file->vatcontrolofiles as $key => $vatcontrolofile)
						                  <option value="{{ $vatcontrolofile->id }}">{{ ($vatcontrolofile->o_file_name) ? $vatcontrolofile->o_file_name :  $vatcontrolofile->file_name }}</option>
						                @endforeach									
									</select>
								@endif
							@endif

							@if($file->ircontrolofiles)
								@if(count($file->ircontrolofiles) > 0)
									<select class="form-select btn-download-file w-50 d-inline-block" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" data-original_file="true">
										<option value="">Download Orginal File</option>
										@foreach($file->ircontrolofiles as $key => $ircontrolofile)
						                  <option value="{{ $ircontrolofile->id }}">{{ ($ircontrolofile->o_file_name) ? $ircontrolofile->o_file_name :  $ircontrolofile->file_name }}</option>
						                @endforeach									
									</select>
								@endif
							@endif	
						</div>
						<div class="col-sm-5">															
							@if($file_type == 'documents')
							<form id="formFileName-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" class="needs-validation m-0 formFileName" novalidate>
								@csrf 
								<div class="row">
									<div class="col-sm-2">
										<label for="file-name-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" class="col-form-label my-2">File Name: </label>
									</div>
									<div class="col-sm-5">
										<input type="hidden" name="file_type_for_file_name" id="file-type-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" value="{{ $file_type }}">										
										<input class="form-control file_name my-2" type="text" value="{{ $file->o_file_name }}" id="file-name-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" name="file_name" required />
									</div>
									<div class="col-sm-3">
										<button type="button" class="btn btn-primary my-2 btn-update-file-name" title="Rename" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
											<i class='bx bx-save me-1'></i>
											<span class="align-middle">Rename</span>
										</button>
									</div>
								</div>
							</form>							
							@endif
						</div>
						{{--						
						<div class="col-sm-5">	
							@if($file_type == 'documents')
							<form id="formNumber-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" class="needs-validation m-0 formNumber" novalidate>
								@csrf 
								<div class="row">
									<div class="col-sm-2">
										<label for="number-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" class="col-form-label my-2">Number: </label>
									</div>
									<div class="col-sm-5">
										<input type="hidden" name="file_type_for_number" id="file-type-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" value="{{ $file_type }}">
										<input class="form-control file_number my-2" type="text" value="{{ $file->doc_numbers }}" id="number-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" name="file_number" onkeypress="return isDecimal(event, this)" />
									</div>
									<div class="col-sm-3">
										<button type="button" class="btn btn-primary my-2 btn-update-file-number" title="Update" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
											<i class='bx bx-save me-1'></i>
											<span class="align-middle">Update</span>
										</button>
									</div>
								</div>
							</form>							
							@endif
						</div>
						--}}						
					</li>						
			@endforeach

		@else

			@php	
				$frequency = 0;			
				if((count($files) <= $vatreg->frequency))
				{
					$i = count($files);					
					$frequency = $vatreg->frequency;
				}
			@endphp

			@for ($i = 0; $i < $frequency; $i++) 
				@php
					$file_exists = 0;
					$file = [];
				@endphp
				@foreach ($files as $file)
					@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('Ym') == \Carbon\Carbon::parse('01-'.$file->month_year)->format('Ym'))			
						@php
							$file = $file;
							$file_exists = 1;
							break;
						@endphp
					@endif									
				@endforeach

				@if(!$file_exists)

					@if($file_type == 'cas' || $file_type == 'dda')
						@php
							//$cas_dda = ($file_type == 'cas') ? $vat_reg_main->cash_acc_stmt : $vat_reg_main->duty_defer_acc;
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
						<li class="list-group-item list-group-timeline-danger d-flex flex-wrap" id="li-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}">
							<div class="col-sm-4 d-flex align-items-center">
								{{ ($i+1) . '. ' . $file_type_title .' for ' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('F Y') }}
							</div>

							<div class="col-sm-8 d-flex align-items-center">	
								@if(\Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('Ym') <= \Carbon\Carbon::now()->format('Ym'))								
									<button class="btn btn-label-primary" title="Upload" data-bs-toggle="modal" data-bs-target="#uploadModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
										<i class='bx bx-up-arrow-circle me-1'></i>
										<span class="align-middle">Upload</span>
									</button>
								@else
									<button class="btn btn-label-primary" disabled="disabled" title="Upload">
										<i class='bx bx-up-arrow-circle me-1'></i>
										<span class="align-middle">Upload</span>
									</button>	
								@endif	
							</div>											
						</li>
						@include('_partials/_modals/modal-file-upload-lazy')
						@php
							$upload_type = 'upload';
						@endphp	
					@endif		
				@else
					@if($file->folder_id != NULL)
						<li class="list-group-item list-group-timeline-primary d-flex flex-wrap" id="li-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}">
							<div class="col-sm-4 d-flex align-items-center">
								{{ ($i+1) . '. ' . $file_type_title . ' for ' . \Carbon\Carbon::parse('01-'.$file->month_year)->format('F Y') }} 
							</div>

							<div class="col-sm-8 d-flex align-items-center flex-wrap">
								<button class="btn btn-label-warning m-2 ms-0" title="Overwrite" data-bs-toggle="modal" data-bs-target="#overwriteModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
									<i class='bx bxs-analyse me-1'></i>
									<span class="align-middle">Overwrite</span>
								</button>
								<button class="btn btn-label-danger m-2 btn-delete-file" title="Delete" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
									<i class="bx bx-x me-1"></i>
									<span class="align-middle">Delete</span>
								</button>
								
								<button type="button" class="btn rounded-pill btn-icon btn-primary m-2 btn-download-file" title="Download {{ ($file_type == 'ivf') ? 'XML' : '' }}" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" data-file_extension="{{ ($file_type == 'ivf') ? 'xml' : '' }}">
									<span class="tf-icons bx bxs-download"></span>
								</button>

								@if($file_type == 'pivs' || $file_type == 'documents' || $file_type == 'c79' || $file_type == 'dda') 
								<button type="button" class="btn rounded-pill btn-icon btn-warning m-2 btn-view-file" title="View" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" data-file_extension="{{ ($file_type == 'ivf') ? 'xml' : '' }}">
									<span class="fa-solid fa-eye"></span>
								</button>
								@endif

								@if($file_type == 'ivf')
									@if(isset($import_vat_files_all))
										@php
											$filtered_import_vat_file_pdf = $import_vat_files_all->filter(function ($import_vat_file, $key) use($file) {
									            return ($import_vat_file->file_type == 'pdf' && (\Carbon\Carbon::parse('01-'. $import_vat_file->month_year)->format('Ym') == \Carbon\Carbon::parse('01-'.$file->month_year)->format('Ym'))); 
									        })->first();									        
										@endphp
									
										@if(isset($filtered_import_vat_file_pdf))
											<button type="button" class="btn rounded-pill btn-icon btn-danger m-2 btn-download-file" title="Download PDF" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $filtered_import_vat_file_pdf->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" data-file_extension="pdf">
												<span class="tf-icons bx bxs-download"></span>
											</button>

											<button type="button" class="btn rounded-pill btn-icon btn-warning m-2 btn-view-file" title="View PDF" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $filtered_import_vat_file_pdf->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" data-file_extension="pdf">
												<span class="fa-solid fa-eye"></span>
											</button>
										@endif
									@endif
								@endif

								@if($file_type == 'pivs' || $file_type == 'c79')
								<form id="formNumber-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" class="needs-validation m-0 formNumber" novalidate>
									@csrf 
									<div class="row">
										<div class="col-sm-3">
											<label for="number-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" class="col-form-label my-2">{{ ($file_type == 'pivs') ? 'Month Total:' : 'Number:' }}</label>
										</div>
										<div class="col-sm-5">
											<input type="hidden" name="file_type_for_number" id="file-type-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ $file_type }}">
											<input class="form-control file_number my-2" type="text" value="{{ ($file->doc_numbers) ? $file->doc_numbers : $file->month_total }}" id="number-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" name="file_number" onkeypress="return isDecimal(event, this)" />
										</div>
										<div class="col-sm-3">
											<button type="button" class="btn btn-primary my-2 btn-update-file-number" title="Update" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
												<i class='bx bx-save me-1'></i>
												<span class="align-middle">Update</span>
											</button>
										</div>
									</div>
								</form>
								@endif
							</div>						
						</li>

						@if($file_type == 'ivf')
							@php 
								$import_vat_file = $file;
							@endphp
							<div id="import-vat-file-overview-{{ $import_vat_file->id }}" class="import-vat-file-overview" data-vat_reg_id="{{ $vat_reg_id }}" data-import_vat_file_id="{{ $import_vat_file->id }}">								
								<div class="sk-bounce sk-primary sk-center">
									<div class="sk-bounce-dot"></div>
									<div class="sk-bounce-dot"></div>
								</div>
								{{--@include('_partials/_content/_vatreturn/import-vat-file-overview-lazy')--}}
							</div>
							<div class="card import-vat" style="box-shadow: none;">
			          			<div class="card-body px-0">
									@include('_partials/_content/_vatreturn/import-vat-lazy')
								</div>
							</div>
						@endif	

						@php
							$upload_type = 'overwrite';
						@endphp	
					@elseif($file->folder_id == NULL)
						<li class="list-group-item list-group-timeline-danger d-flex flex-wrap" id="li-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}">
							<div class="col-sm-4 d-flex align-items-center">
								{{ ($i+1) . '. No doc uploaded for ' . \Carbon\Carbon::parse('01-'.$file->month_year)->format('F Y') }}
							</div>

							<div class="col-sm-8 d-flex align-items-center">									
								<button class="btn btn-label-primary" title="Upload" data-bs-toggle="modal" data-bs-target="#overwriteModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
									<i class='bx bx-up-arrow-circle me-1'></i>
									<span class="align-middle">Upload</span>
								</button>
							</div>											
						</li>
						@php
							$upload_type = 'upload';
						@endphp	
					@endif				
					@include('_partials/_modals/modal-file-overwrite-lazy')
				@endif
			@endfor

		@endif
	</ul>

	@if($file_type == 'ci') 
		<div id="load-datas-{{ $file_type }}-{{ $vat_reg_id }}" class="my-3 ci-missing-datas">
			@include('_partials/_content/_vatreturn/commercial-invoice-datas')
		</div>
	@endif
</div>       