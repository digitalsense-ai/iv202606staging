<!-- Tabs -->  
<div class="card shadow-none {{ ($page_type == 'my-tasks') ? '' : 'border mb-3' }}" id="vat-returns-main-{{ $vat_reg_id }}">	
	<div class="card-header border-bottom">
	    <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
	    	@if($check_product_type == 1 || $check_product_type == 4)
		        <li class="nav-item">
		          <button type="button" id="btn-overview-{{ $vat_reg_id }}" class="btn-overview nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-overview-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-overview-{{ $vat_reg_id }}" aria-selected="true">Overview</button>
		        </li>

		        <li class="nav-item">	         
		          <button type="button" id="btn-invoices-{{ $vat_reg_id }}" class="btn-invoices nav-link {{ ($show_vatreturn) ? '' : 'disabled' }}" role="tab" data-bs-toggle="tab" aria-controls="navs-vatreturns-invoices-{{ $vat_reg_id }}" aria-selected="true" data-vat_reg_id="{{ $vat_reg_id }}">Invoices<i class="fa-solid fa-arrow-up-right-from-square ms-2"></i></button>
		        </li>	       	       
		        
		        @if($vatreg->status == 6)
		        <li class="nav-item">
			      <button type="button" id="btn-archive-{{ $vat_reg_id }}" class="btn-archive nav-link {{ ($vatreg->status == 6) ? '' : 'disabled' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-archive-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-archive-{{ $vat_reg_id }}" aria-selected="false">Archive</button>
			    </li>
			    @endif	       	       

		        <li class="nav-item">
		          <button type="button" id="btn-documents-{{ $vat_reg_id }}" class="nav-link btn-documents" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-documents-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-documents-{{ $vat_reg_id }}" aria-selected="false" data-vat_reg_id="{{ $vat_reg_id }}">Documents</button>
		        </li>	       	       
		        
		        @if($vatreg->country == 'NO')
		        <li class="nav-item">
		          <button type="button" id="btn-importvat-{{ $vat_reg_id }}" class="btn-importvat nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-importvat-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-importvat-{{ $vat_reg_id }}" aria-selected="false">Import VAT</button>
		        </li>
		        @endif

		        @if($vatreg->country == 'GB' || $vatreg->country == 'NO' || $vatreg->country == 'CH')	                
		        <li class="nav-item">
		          <button type="button" id="btn-submitting-fields-{{ $vat_reg_id }}" class="btn-submitting-fields nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-submittingfields-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-submittingfields-{{ $vat_reg_id }}" aria-selected="false">Submitting Fields</button>
		        </li>
		        @endif

		        <li class="nav-item">
		          <button type="button" id="btn-timeline-{{ $vat_reg_id }}" class="btn-timeline nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-timeline-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-timeline-{{ $vat_reg_id }}" aria-selected="false">History</button>
		        </li>

		        <li class="nav-item">
		          <button type="button" id="btn-notes-{{ $vat_reg_id }}" class="btn-notes nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-notes-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-notes-{{ $vat_reg_id }}" aria-selected="false">Notes</button>
		        </li>

		        @if($vatreg->country == 'GB')
		        <li class="nav-item">
		          <button type="button" id="btn-govuk-{{ $vat_reg_id }}" class="btn-govuk nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-govuk-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-govuk-{{ $vat_reg_id }}" aria-selected="false">Gov. UK</button>
		        </li>
		        @endif

		        <li class="nav-item">
		          <button type="button" id="btn-vatreturn-control-{{ $vat_reg_id }}" class="btn-vatreturn-control nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-control-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-control-{{ $vat_reg_id }}" aria-selected="false">Control</button>
		        </li>

		        {{-- //DON'T DELETE
		        @if($vatreg->country == 'NO')
		        	@php					
						$missing_commercial_invoice_arr = explode(',', $missing_commercial_invoices);
						$missing_commercial_invoice_arr = array_map('trim', $missing_commercial_invoice_arr);
						$missing_commercial_invoice_count = count($missing_commercial_invoice_arr);
					@endphp
		        <li class="nav-item">
		          <button type="button" id="btn-commercial-invoices-{{ $vat_reg_id }}" class="btn-commercial-invoices nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-commercial-invoices-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-commercial-invoices-{{ $vat_reg_id }}" aria-selected="false">Commercial Invoices <span class="alert-danger text-end fs-tiny px-1 mx-2">{{ ($missing_commercial_invoices == '') ? '0' : $missing_commercial_invoice_count }}</span></button>
		        </li>
		        @endif
 				--}}	
 				
 				{{-- //DON'T DELETE
 				@if($authUser->role != "client-user")
		        <li class="nav-item">	         
		          <button type="button" id="btn-vatcheck-{{ $vat_reg_id }}" class="btn-vatcheck nav-link {{ ($show_vatreturn) ? '' : 'disabled' }}" role="tab" data-bs-toggle="tab" aria-controls="navs-vatreturns-vatcheck-{{ $vat_reg_id }}" aria-selected="true" data-vat_reg_id="{{ $vat_reg_id }}">VAT Check<i class="fa-solid fa-arrow-up-right-from-square ms-2"></i></button>
		        </li> 
		        @endif 
		        --}}
		    @elseif($check_product_type == 2)
		    	<li class="nav-item">
		          <button type="button" id="btn-import-reconciliation-overview-{{ $vat_reg_id }}" class="btn-import-reconciliation-overview nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-import-reconciliation-overview-{{ $vat_reg_id }}" aria-controls="navs-import-reconciliation-overview-{{ $vat_reg_id }}" aria-selected="true">Overview</button>
		        </li>

		        {{-- //DON'T DELETE
		        @if($vatreg->country == 'NO')
		        	@php					
						$missing_commercial_invoice_arr = explode(',', $missing_commercial_invoices);
						$missing_commercial_invoice_arr = array_map('trim', $missing_commercial_invoice_arr);
						$missing_commercial_invoice_count = count($missing_commercial_invoice_arr);
					@endphp
		        <li class="nav-item">
		          <button type="button" id="btn-commercial-invoices-{{ $vat_reg_id }}" class="btn-commercial-invoices nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-commercial-invoices-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-commercial-invoices-{{ $vat_reg_id }}" aria-selected="false">Commercial Invoices <span class="alert-danger text-end fs-tiny px-1 mx-2">{{ ($missing_commercial_invoices == '') ? '0' : $missing_commercial_invoice_count }}</span></button>
		        </li>
		        @endif
		        --}}

		    	@if(strtoupper($vatreg->country) == 'NO' || strtoupper($vatreg->country) == 'CH')
		        <li class="nav-item">	         
		          <button type="button" id="btn-declarations-{{ $vat_reg_id }}" class="btn-declarations nav-link {{ ($show_importreconciliation) ? '' : 'disabled' }}" role="tab" data-bs-toggle="tab" aria-controls="navs-vatreturns-declarations-{{ $vat_reg_id }}" aria-selected="true" data-vat_reg_id="{{ $vat_reg_id }}">Declaration view <sup class="alert-danger">beta</sup><i class="fa-solid fa-arrow-up-right-from-square ms-2"></i></button>
		        </li>
		        @endif

		        @if(strtoupper($vatreg->country) == 'CH')
		        {{--<li class="nav-item">	         
		          <button type="button" id="btn-swiss-documents-{{ $vat_reg_id }}" class="btn-swiss-documents nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-importreconciliation-swiss-documents-{{ $vat_reg_id }}" aria-controls="navs-importreconciliation-swiss-documents-{{ $vat_reg_id }}" aria-selected="true" data-vat_reg_id="{{ $vat_reg_id }}">Upload Swiss Documents</button>
		        </li>
		        --}}

		        <li class="nav-item">
		          <button type="button" id="btn-importreconciliation-documents-{{ $vat_reg_id }}" class="nav-link btn-importreconciliation-documents" role="tab" data-bs-toggle="tab" data-bs-target="#navs-importreconciliation-documents-{{ $vat_reg_id }}" aria-controls="navs-importreconciliation-documents-{{ $vat_reg_id }}" aria-selected="false" data-vat_reg_id="{{ $vat_reg_id }}">Documents</button>
		        </li>
		        @endif

		        <li class="nav-item">
		          <button type="button" id="btn-preview-report-{{ $vat_reg_id }}" class="btn-preview-report nav-link {{ ($show_importreconciliation) ? '' : 'disabled' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-preview-report-{{ $vat_reg_id }}" aria-controls="navs-vatreturns-preview-report-{{ $vat_reg_id }}" aria-selected="true" data-vat_reg_id="{{ $vat_reg_id }}">Preview report <i class="fa-solid fa-arrow-up-right-from-square ms-2"></i></button>
		        </li>

		        <li class="nav-item">
		          <button type="button" id="btn-importreconciliation-timeline-{{ $vat_reg_id }}" class="btn-timeline nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-importreconciliation-timeline-{{ $vat_reg_id }}" aria-controls="navs-importreconciliation-timeline-{{ $vat_reg_id }}" aria-selected="false">History</button>
		        </li>

		        <li class="nav-item">
		          <button type="button" id="btn-importreconciliation-notes-{{ $vat_reg_id }}" class="btn-importreconciliation-notes nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-importreconciliation-notes-{{ $vat_reg_id }}" aria-controls="navs-importreconciliation-notes-{{ $vat_reg_id }}" aria-selected="false">Notes</button>
		        </li> 

		        <li class="nav-item">
		          <button type="button" id="btn-importreconciliation-control-{{ $vat_reg_id }}" class="btn-importreconciliation-control nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-importreconciliation-control-{{ $vat_reg_id }}" aria-controls="navs-importreconciliation-control-{{ $vat_reg_id }}" aria-selected="false">Control</button>
		        </li>  
		    @endif		   
	    </ul>
	</div>    
    <div class="tab-content">
    	@if($check_product_type == 1 || $check_product_type == 4)
	    	<!-- Overview -->
	        <div class="tab-pane fade show active" id="navs-vatreturns-overview-{{ $vat_reg_id }}" role="tabpanel">        	
	        	@php
	        		$tab_name = "overview";
	        	@endphp
	        	@include('_partials/_content/_vatreturn/vatreturn-overview-lazy')        	
	        </div>
	        <!--/ Overview -->

	        {{-- //DON'T DELETE
	        <!-- Invoices -->
	        <div class="tab-pane fade show active" id="navs-vatreturns-invoices-{{ $vat_reg_id }}" role="tabpanel">	
	        </div>
	        <!--/ Invoices -->
	        --}}

	        <!-- Archive -->
	        @if($vatreg->status == 6)
	        <div class="tab-pane fade" id="navs-vatreturns-archive-{{ $vat_reg_id }}" role="tabpanel">        	
	        	@php
	        		$tab_name = "archive";
	        	@endphp
	        	@include('_partials/_content/_vatreturn/vatreturn-overview-lazy')         	     
	        </div>
	        <!--/ Archive -->
	        @endif

	        <!-- Documents - Income/Purchase/C79/Receipt/Excel Upload -->
	        <div class="tab-pane fade navs-vatreturns-documents" id="navs-vatreturns-documents-{{ $vat_reg_id }}" role="tabpanel">
	        	<!-- Uploaded Excel Files --> 
	  			@if($client_api === null)
{{--	  			
					@php
						$file_type = 'vatreturn';
						$file_type_title = 'Excel/XML';

						$files = $vatreturnfiles;    

						$i = 0;         			
					@endphp      	
					<div class="divider divider-dashed divider-dark">
				      <div class="divider-text">
				        <i class="bx bx-star"></i> {{ $file_type_title }} <i class="bx bx-star"></i>
				      </div>
				    </div>
				    		        
					<div class="col-sm-12 text-end">					
						<div class="d-inline-block align-top mx-2" id="excel-template-select-{{ $vat_reg_id }}">
							@include('_partials/_content/_vatreturn/excel-template-select')	
						</div>
						@include('_partials/_modals/modal-excel-column-template')
						@include('_partials/_modals/modal-excel-template-selection')	
													
						<button class="btn btn-primary" id="btn-upload-{{ $file_type }}-{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}">
							<i class='bx bx-up-arrow-circle me-1'></i>
							<span class="align-middle">Upload {{ $file_type_title }} File</span>
						</button>
					</div>
					@include('_partials/_modals/modal-file-upload-single-lazy')

					<div class="col-sm-12" id="load-{{ $file_type }}-list">
						@include('_partials/_content/_vatreturn/file-list-lazy')	        		
					</div>	
--}}
					<!-- Uploaded AnyExcel Files --> 
					@php
						$file_type = 'vatreturn';
						$file_type_title = 'Excel/XML';

						$files = $vatreturnfiles;    

						$i = 0;         			
					@endphp
					<div class="divider divider-dashed divider-dark">
				      <div class="divider-text">
				        <i class="bx bx-star"></i> {{ $file_type_title }} <i class="bx bx-star"></i>
				      </div>
				    </div>

					<div class="col-sm-12 text-end">					
						<div class="d-inline-block align-top mx-2" id="anyexcel-template-select-{{ $file_type }}-{{ $vat_reg_id }}">
							@include('_partials/_content/_vatreturn/anyexcel-template-select')							
						</div>												
						@include('_partials/_modals/modal-anyexcel-template-filter-selection')
													
						<button class="btn btn-primary" id="btn-upload-{{ $file_type }}-{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
							<i class='bx bx-up-arrow-circle me-1'></i>
							<span class="align-middle">Upload {{ $file_type_title }} File</span>
						</button>
					</div>
					@include('_partials/_modals/modal-file-upload-single-lazy')					

					<div class="col-sm-12" id="load-{{ $file_type }}-list">
						@include('_partials/_content/_vatreturn/file-list-lazy')
					</div>
					<!-- Uploaded AnyExcel Files --> 

					<div class="divider divider-dashed divider-dark">
				      <div class="divider-text">
				        <i class="bx bx-star"></i> Receipt <i class="bx bx-star"></i>
				      </div>
				    </div>
				@endif
				<!--/ Uploaded Excel Files -->

	        	<!-- Receipt -->         	
	        	<div class="row"> 
	        		<!-- Multi  -->
					<div class="col-12">
						<h4 class="onboarding-title text-body text-left receipt-download">Receipt
						{{--@if($vatreg->receipt)
							@if(count($vatreg->receipt) > 0)								
								@foreach($vatreg->receipt as $key => $receipt)
					                <button type="button" class="btn rounded-pill btn-icon btn-primary m-2 btn-download-file" title="Download" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $receipt->id }}" data-file_type="receipt" data-file_type_title="Receipt" {{ ($receipt->file_id) ? '' : 'disabled=disabled' }}>
										<span class="tf-icons bx bxs-download"></span>
									</button>  
					            @endforeach	
					            
							@endif
						@endif--}}
						</h4>							
						@include('_partials/_content/_vatreturn/file-receipt-list')
						
						<form method="post" action="{{ url('vat-return/receipt/' . $vat_reg_id) }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-multi {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'dropzone-disabled' : '' }}" id="dropzone-multi-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
							
							<div class="dz-message needsclick">
								Drop files here or click to upload
								<span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
							</div>
						</form>
					</div>
					<!-- Multi  -->
				</div>
				<!--/ Receipt -->			

				<!-- PIVS -->
	        	@if($vatreg->country == 'GB')
	        	<div class="divider divider-dashed divider-dark">
		          <div class="divider-text">
		            <i class="bx bx-star"></i> Postponed import VAT statement <i class="bx bx-star"></i>
		          </div>
		        </div>
		      
				<div class="row file-list">  
					@php
	        			$file_type = 'pivs';
	        			$file_type_title = 'Postponed import VAT statement';

	        			$files = $pivs_files;
	        		@endphp    	
		        	<div class="col-sm-12" id="load-{{ $file_type }}-list">	        			        	
		        		@include('_partials/_content/_vatreturn/file-list-lazy')	        		
		        	</div>
				</div>
				@endif	
				<!--/ PIVS -->

				<!-- Documents -->
				<div class="divider divider-dashed divider-dark">
		          <div class="divider-text">
		            <i class="bx bx-star"></i> Documents <i class="bx bx-star"></i>
		          </div>
		        </div>
			
				<div class="row file-list">  
					@php
	        			$file_type = 'documents';
	        			$file_type_title = 'Documents';

	        			$files = $documents;    

	        			$i = 0; 
	        			//$vatreg = (isset($client)) ? $client : $vatreg;   			
	        		@endphp
					<div class="col-sm-12 text-end">									
						<button class="btn btn-primary btn-upload-documents" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
							<i class='bx bx-up-arrow-circle me-1'></i>
							<span class="align-middle">Upload/Overwrite Documents</span>
						</button>
					</div>
					@include('_partials/_modals/modal-file-upload-single-lazy')

		        	<div class="col-sm-12" id="load-{{ $file_type }}-list">
		        		@include('_partials/_content/_vatreturn/file-list-lazy')	        		
		        	</div>	        	
				</div>
				<!--/ Documents -->

				<!-- C79 -->
				@if($vatreg->country == 'GB')
				<div class="divider divider-dashed divider-dark">
		          <div class="divider-text">
		            <i class="bx bx-star"></i> C79 Documents (optional) <i class="bx bx-star"></i>
		          </div>
		        </div>

				<div class="row file-list"> 
					@php
	        			$file_type = 'c79';
	        			$file_type_title = 'C79';

	        			$files = $c79_documents;
	        		@endphp     	
		        	<div class="col-sm-12" id="load-{{ $file_type }}-list">	        			        	
		        		@include('_partials/_content/_vatreturn/file-list-lazy')	        		
		        	</div>
				</div>
				@endif	
				<!--/ C79 -->

				<!-- Cash Account Statement -->
	        	@if($vatreg->country == 'GB' && $vat_reg_main->cash_acc_stmt == 1)
	        		@php
	        			$file_type = 'cas';
	        			$file_type_title = 'Cash Account Statement';

	        			$files = $cash_account_statement_files;
	        		@endphp
	        	<div class="divider divider-dashed divider-dark {{ $file_type }}">
		          <div class="divider-text">
		            <i class="bx bx-star"></i> Cash Account Statement <i class="bx bx-star"></i>
		          </div>
		        </div>
		        	        
				<div class="row file-list">   					   
		        	<div class="col-sm-12" id="load-{{ $file_type }}-list">	        			        	
		        		@include('_partials/_content/_vatreturn/file-list-lazy')	        		
		        	</div>
				</div>
				@endif	
				<!--/ Cash Account Statement -->

				<!-- Duty Deferment Account -->
	        	@if($vatreg->country == 'NO' && $vat_reg_main->duty_defer_acc == 1)
	        		@php
	        			$file_type = 'dda';
	        			$file_type_title = 'Duty Deferment Account';

	        			$files = $duty_deferment_account_files;
	        		@endphp
	        	<div class="divider divider-dashed divider-dark {{ $file_type }}">
		          <div class="divider-text">
		            <i class="bx bx-star"></i> Duty Deferment Account <i class="bx bx-star"></i>
		          </div>
		        </div>
		        
				<div class="row file-list"> 					     
		        	<div class="col-sm-12" id="load-{{ $file_type }}-list">	        			        	
		        		@include('_partials/_content/_vatreturn/file-list-lazy')	        		
		        	</div>
				</div>
				@endif	
				<!--/ Duty Deferment Account -->
	        </div>
	        <!--/ Documents - Income/Purchase/C79/Receipt/Excel Upload -->
	                  
	        @if($vatreg->country == 'NO')
	        <!-- Import VAT -->
	        <div class="tab-pane fade navs-vatreturns-importvat" id="navs-vatreturns-importvat-{{ $vat_reg_id }}" role="tabpanel">
				<div class="card file-list" style="box-shadow: none;">   
					@php
						$file_type = 'ivf';
						$file_type_title = 'Import VAT';

						$files = $import_vat_files;
					@endphp	 					
					<div class="card-body px-0" id="load-{{ $file_type }}-list">				        	
						@include('_partials/_content/_vatreturn/file-list-lazy')	        		
					</div>
				</div>
	        </div>
	        <!--/ Import VAT -->
	        @endif
	            
	        <!-- Submitting Fields -->
	        <div class="tab-pane fade navs-vatreturns-submittingfields" id="navs-vatreturns-submittingfields-{{ $vat_reg_id }}" role="tabpanel"  data-vat_reg_id="{{ $vat_reg_id }}">        	
	        	@if($vatreg->country == 'GB' || $vatreg->country == 'CH')
	        		@if($vatreg->country == 'GB')
		        	<div class="col-sm-12 text-end">									
						<button class="btn btn-primary btn-export-excel-submittingfields" data-vat_reg_id="{{ $vat_reg_id }}" {{ ($vatreg->status == 6) ? 'disabled' : '' }}>
							<i class='bx bx-up-arrow-circle me-1'></i>
							<span class="align-middle">Export to excel</span>
						</button>
					</div>
					@endif
											
					<div class="card" style="box-shadow: none;">					
						<div class="card-body px-0" id="load-submitting-fields">
							{{--@if($vatreg->country == 'GB')
								@include('_partials/_content/_vatreturn/submitting-fields-lazy')
							@elseif($vatreg->country == 'CH')	
								@include('_partials/_content/_vatreturn/submitting-fields-CH')
							@endif--}}
						</div>            
					</div>				
				 @elseif($vatreg->country == 'NO')	
				 	<div class="col-sm-12 text-end">									
						<button class="btn btn-primary btn-export-saft-submittingfields" data-vat_reg_id="{{ $vat_reg_id }}" disabled="disabled">
							<i class='bx bx-up-arrow-circle me-1'></i>
							<span class="align-middle">Export to SAF-T</span>
						</button>
					</div>
										
					<div class="card" style="box-shadow: none;">					
						<div class="card-body px-0" id="load-submitting-fields">
							{{--@include('_partials/_content/_vatreturn/submitting-fields-NO-lazy')--}}
						</div>            
					</div>				
				 @endif        	
	        </div>
	        <!--/ Submitting Fields -->  
		                    
	        <!-- Timeline/History -->
	        <div class="tab-pane fade navs-vatreturns-timeline" id="navs-vatreturns-timeline-{{ $vat_reg_id }}" role="tabpanel" data-vat_reg_id="{{ $vat_reg_id }}">        	
	        	<div class="sk-bounce sk-primary sk-center">
					<div class="sk-bounce-dot"></div>
					<div class="sk-bounce-dot"></div>
				</div>		
	        </div>
	        <!--/ Timeline/History -->

	        <!-- Notes -->
	        <div class="tab-pane fade navs-vatreturns-notes" id="navs-vatreturns-notes-{{ $vat_reg_id }}" role="tabpanel" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}">
				<button type="button" id="btn-vatreturn-notes-{{ $vat_reg_id }}" class="btn btn-dark float-end mx-2 btn-open-vatreturn-notes my-n1" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#vatreturnNotesModal-{{ $vat_reg_id }}" {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>Add Notes</button>

	            @include('_partials/_modals/modal-vatreturn-notes')

	            <div id="load-vatreturn-notes">	             
	            </div>			
	        </div>
	        <!--/ Notes -->

	        <!-- Gov. UK -->
	        @if($vatreg->country == 'GB')
	        <div class="tab-pane fade navs-vatreturns-govuk" id="navs-vatreturns-govuk-{{ $vat_reg_id }}" role="tabpanel" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}">
	            @include('_partials/_content/_vatreturn/govuk')      	
	        </div>
	        @endif
	        <!--/ Gov. UK -->

	        <!-- Control -->	        
	        <div class="tab-pane fade navs-vatreturns-control" id="navs-vatreturns-control-{{ $vat_reg_id }}" role="tabpanel" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}">
	        	<!-- Uploaded AnyExcel Files --> 
				@php
					$file_type = 'vatcontrol';
					$file_type_title = 'VAT Control';

					$files = $vatcontrolfiles;    

					$i = 0;         			
				@endphp
				
				<div class="col-sm-12 text-end">					
					<div class="d-inline-block align-top mx-2" id="anyexcel-template-select-{{ $file_type }}-{{ $vat_reg_id }}">
						@include('_partials/_content/_vatreturn/anyexcel-template-select')	
					</div>												
					@include('_partials/_modals/modal-anyexcel-template-filter-selection')
												
					<button class="btn btn-primary" id="btn-upload-{{ $file_type }}-{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
						<i class='bx bx-up-arrow-circle me-1'></i>
						<span class="align-middle">Upload {{ $file_type_title }} File</span>
					</button>
				</div>
				@include('_partials/_modals/modal-file-upload-single-lazy')					

				<div class="col-sm-12" id="load-{{ $file_type }}-list">
					@include('_partials/_content/_vatreturn/file-list-lazy')
				</div>
				<!-- Uploaded AnyExcel Files --> 
	        </div>	        
	        <!--/ Control -->

	        {{-- //DON'T DELETE
	        @if($vatreg->country == 'NO')
	        <!-- Commercial Invoices -->        
	        <div class="tab-pane fade navs-vatreturns-commercial-invoices" id="navs-vatreturns-commercial-invoices-{{ $vat_reg_id }}" role="tabpanel" data-vat_reg_id="{{ $vat_reg_id }}"> 
				<div class="row file-list">   
					@php
	        			$file_type = 'ci';
	        			$file_type_title = 'Commercial Invoices';

	        			$files = $commercial_invoices_files;

	        			$i = 0;
	        		@endphp   
	        		<div class="col-sm-12 text-end">									
						<button class="btn btn-primary btn-upload-documents" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}">
							<i class='bx bx-up-arrow-circle me-1'></i>
							<span class="align-middle">Upload Commercial Invoices</span>
						</button>
					</div>	
					@include('_partials/_modals/modal-file-upload-single-lazy')
		        	<div class="col-sm-12" id="load-{{ $file_type }}-list">	        			        	
		        		@include('_partials/_content/_vatreturn/file-list-lazy')	        		
		        	</div>
				</div>			
	        </div>        
	        <!--/ Commercial Invoices -->
	        @endif
	        --}}	

	        {{-- //DON'T DELETE
	        <!-- VAT Check -->
	        <div class="tab-pane fade" id="navs-vatreturns-vatcheck-{{ $vat_reg_id }}" role="tabpanel">        	
	        </div>
	        <!--/ VAT Check -->
	        --}}	        
		@elseif($check_product_type == 2)
			<!-- Control -->
	        <div class="tab-pane fade show active" id="navs-import-reconciliation-overview-{{ $vat_reg_id }}" role="tabpanel">
	        	@include('_partials/_content/_importreconciliation/importreconciliation-overview')        	        	
	        </div>
	        <!--/ Control -->

	        {{-- //DON'T DELETE
	        @if($vatreg->country == 'NO')
	        <!-- Commercial Invoices -->        
	        <div class="tab-pane fade navs-vatreturns-commercial-invoices" id="navs-vatreturns-commercial-invoices-{{ $vat_reg_id }}" role="tabpanel" data-vat_reg_id="{{ $vat_reg_id }}"> 
				<div class="row file-list">   
					@php
	        			$file_type = 'ci';
	        			$file_type_title = 'Commercial Invoices';

	        			$files = $commercial_invoices_files;

	        			$i = 0;
	        		@endphp   
	        		<div class="col-sm-12 text-end">									
						<button class="btn btn-primary btn-upload-documents" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}">
							<i class='bx bx-up-arrow-circle me-1'></i>
							<span class="align-middle">Upload Commercial Invoices</span>
						</button>
					</div>	
					@include('_partials/_modals/modal-file-upload-single-lazy')
		        	<div class="col-sm-12" id="load-{{ $file_type }}-list">	        			        	
		        		@include('_partials/_content/_vatreturn/file-list-lazy')	        		
		        	</div>
				</div>			
	        </div>        
	        <!--/ Commercial Invoices -->
	        @endif
	        --}}
	        
	        {{-- //DON'T DELETE
	        <!-- Declarations -->
	        <div class="tab-pane fade" id="navs-vatreturns-declarations-{{ $vat_reg_id }}" role="tabpanel">	
	        </div>
	        <!--/ Declarations -->
	        --}}

	        {{-- //DON'T DELETE
	        <!-- Preview Report -->
	        <div class="tab-pane fade" id="navs-vatreturns-preview-report-{{ $vat_reg_id }}" role="tabpanel">        	
	        </div>
	        <!--/ Preview Report -->
	        --}}

	        {{--
	        <!-- Upload Swiss Documents -->
	        <div class="tab-pane fade navs-importreconciliation-swiss-documents" id="navs-importreconciliation-swiss-documents-{{ $vat_reg_id }}"  role="tabpanel" data-vat_reg_id="{{ $vat_reg_id }}">        	
	        	
	        	@include('_partials/_content/_vatreturn/file-swiss-list')			        	
				
	    		<!-- Multi  -->
				<div class="col-12">
					<!-- <h4 class="onboarding-title text-body text-left">Swiss Import Reconciliation</h4> -->	
					<form method="post" action="{{ url('file/' . $vat_reg_id) }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-swiss-multi" id="dropzone-swiss-multi-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="swiss_import_reconciliation" data-file_type_title="Swiss Import Reconciliation" {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
						<div class="dz-message needsclick">
							Drop files here or click to upload
							<span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
						</div>
					</form>
				</div>
				<!-- Multi  -->				
	        </div>
	        <!--/ Upload Swiss Documents -->
	        --}}

	        <!-- Documents - Swiss/Any Excel Upload -->
	        <div class="tab-pane fade navs-importreconciliation-documents" id="navs-importreconciliation-documents-{{ $vat_reg_id }}" role="tabpanel">
	        	{{--
	        	<!-- Uploaded Excel Files --> 	  			
				@php
					$file_type = 'iranyexcel';
					$file_type_title = 'Any Excel';

					$files = $importreconciliationanyexcelfiles;    

					$i = 0;
				@endphp      	
				<div class="divider divider-dashed divider-dark">
			      <div class="divider-text">
			        <i class="bx bx-star"></i> {{ $file_type_title }} <i class="bx bx-star"></i>
			      </div>
			    </div>
			    		        
				<div class="col-sm-12 text-end">					
					<div class="d-inline-block align-top mx-2" id="excel-template-select-{{ $file_type }}-{{ $vat_reg_id }}">
						@include('_partials/_content/_vatreturn/excel-template-select')	
					</div>
					@include('_partials/_modals/modal-excel-column-template')
					@include('_partials/_modals/modal-excel-template-selection')
												
					<button class="btn btn-primary" id="btn-upload-{{ $file_type }}-{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" {{ (!$vatregmain_status || $vatreg->is_disregard_import_re) ? 'disabled=disabled' : '' }}>
						<i class='bx bx-up-arrow-circle me-1'></i>
						<span class="align-middle">Upload {{ $file_type_title }} File</span>
					</button>
				</div>
					
				@include('_partials/_modals/modal-file-upload-single-lazy')

				<div class="col-sm-12" id="load-{{ $file_type }}-list">
					@include('_partials/_content/_vatreturn/file-list-lazy')
				</div>		       		

				<div class="divider divider-dashed divider-dark">
			      <div class="divider-text">
			        <i class="bx bx-star"></i> Swiss Documents <i class="bx bx-star"></i>
			      </div>
			    </div>				
				<!--/ Uploaded Excel Files -->
				--}}

	        	<!-- Upload Swiss Documents -->         	
	        	<div class="row"> 
	        		<!-- Multi  -->
					<div class="col-12">
						<h4 class="onboarding-title text-body text-left">Swiss Documents</h4>	
						
						@include('_partials/_content/_vatreturn/file-swiss-list')			        	
				
			    		<form method="post" action="{{ url('file/' . $vat_reg_id) }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-swiss-multi {{ (!$vatregmain_status || $vatreg->is_disregard_import_re) ? 'dropzone-disabled' : '' }}" id="dropzone-swiss-multi-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="swiss_import_reconciliation" data-file_type_title="Swiss Import Reconciliation" {{ (!$vatregmain_status || $vatreg->is_disregard_import_re) ? 'disabled=disabled' : '' }}>
							<div class="dz-message needsclick">
								Drop files here or click to upload
								<span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
							</div>
						</form>	
					</div>
					<!-- Multi  -->
				</div>
				<!--/ Upload Swiss Documents -->
			</div>
			<!--/ Documents - Swiss/Any Excel Upload -->

	        <!-- Timeline/History -->
	        <div class="tab-pane fade navs-importreconciliation-timeline" id="navs-importreconciliation-timeline-{{ $vat_reg_id }}" role="tabpanel" data-vat_reg_id="{{ $vat_reg_id }}">        	
	        	<div class="sk-bounce sk-primary sk-center">
					<div class="sk-bounce-dot"></div>
					<div class="sk-bounce-dot"></div>
				</div>		
	        </div>
	        <!--/ Timeline/History -->

	        <!-- Notes -->
	        <div class="tab-pane fade navs-importreconciliation-notes" id="navs-importreconciliation-notes-{{ $vat_reg_id }}" role="tabpanel" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}">
				<button type="button" id="btn-importreconciliation-notes-{{ $vat_reg_id }}" class="btn btn-dark float-end mx-2 btn-open-importreconciliation-notes my-n1" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#importreconciliationNotesModal-{{ $vat_reg_id }}" {{ (!$vatregmain_status || $vatreg->is_disregard_import_re) ? 'disabled=disabled' : '' }}>Add Notes</button>

	            @include('_partials/_modals/modal-importreconciliation-notes')

	            <div id="load-importreconciliation-notes">	             
	            </div>			
	        </div>
	        <!--/ Notes -->

	        <!-- Control -->	        
	        <div class="tab-pane fade navs-importreconciliation-control" id="navs-importreconciliation-control-{{ $vat_reg_id }}" role="tabpanel" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}">
	        	<!-- Uploaded AnyExcel Files --> 
				@php
					$file_type = 'ircontrol';
					$file_type_title = 'Import Reconciliation Control';

					$files = $ircontrolfiles;    

					$i = 0;         			
				@endphp
				
				<div class="col-sm-12 text-end">					
					<div class="d-inline-block align-top mx-2" id="anyexcel-template-select-{{ $file_type }}-{{ $vat_reg_id }}">
						@include('_partials/_content/_vatreturn/anyexcel-template-select')	
					</div>												
					@include('_partials/_modals/modal-anyexcel-template-filter-selection')
												
					<button class="btn btn-primary" id="btn-upload-{{ $file_type }}-{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" {{ (!$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
						<i class='bx bx-up-arrow-circle me-1'></i>
						<span class="align-middle">Upload {{ $file_type_title }} File</span>
					</button>
				</div>
				@include('_partials/_modals/modal-file-upload-single-lazy')					

				<div class="col-sm-12" id="load-{{ $file_type }}-list">
					@include('_partials/_content/_vatreturn/file-list-lazy')
				</div>
				<!-- Uploaded AnyExcel Files --> 
	        </div>	        
	        <!--/ Control -->
	       
		@endif		
    </div>
</div>
<!--/ Tabs --> 

@if($check_product_type == 1 || $check_product_type == 4) 
	@if($vatreg->status == 2 || $vatreg->status == 3)  
		@php
			$file_type = 'draft';
			$file_type_title = 'Draft';

			$i = 0;
			$modal_title = ($vatreg->status == 3) ? 'Re-send for review' : 'Send for review';		
		@endphp		
		@include('_partials/_modals/modal-send-email-lazy')
	@endif	

	@include('_partials/_modals/modal-send-comment-email-lazy')

	@php
		$file_type = 'lock';
		$file_type_title = 'Lock';

		$i = 0;
		$modal_title = 'Lock folder';
	@endphp	
	@include('_partials/_modals/modal-send-email-lazy')
@endif	