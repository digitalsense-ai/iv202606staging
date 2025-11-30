@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-profile.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>

<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.js')}}"></script>

<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>

<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
@endsection

@section('page-script')
<script type="text/javascript">
    window.EchoConfig = {
        pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
        pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    };
</script>

<script src="{{asset('js/dv-submitting-fields-lazy.js')}}"></script>
<script src="{{asset('js/dv-upload.js')}}"></script>
<script src="{{asset('js/dv-all-tasks-lazy.js')}}"></script>
<!-- <script src="{{asset('js/dv-my-tasks.js')}}"></script> -->

<script src="{{asset('js/dv-common.js')}}"></script>
<script src="{{asset('js/dv-comments.js')}}"></script>
<script src="{{asset('js/dv-history.js')}}"></script>
<script src="{{asset('js/dv-import-vat-lazy.js')}}"></script>
<script src="{{asset('js/dv-import-vat-files-lazy.js')}}"></script>

<!-- <script src="{{asset('js/dv-vatreturn-files-lazy.js')}}"></script> -->

@endsection


@section('content')		
@php
  $full_result = $result;
@endphp

	<div class="col-md">					
		<h4 class="py-3 breadcrumb-wrapper mb-4">		  
		  	@if($authUser->role == 'team-user')
			<label class="switch float-end m-0">
				<input type="checkbox" class="switch-input all-tasks" id="all-tasks" />
				<span class="switch-toggle-slider">
					<span class="switch-on"></span>
					<span class="switch-off"></span>
				</span>
				<span class="switch-label">All Tasks</span>
			</label>
			@endif					
		</h4>
	</div>
	
	@include('_partials/_content/_tasks/upload-tasks-lazy')		

	<!-- VAT Returns -->               				
	<!-- Accordion Header Color -->
	<div class="col-md">					
		<h4 class="py-3 breadcrumb-wrapper mb-4">
		  <span class="text-muted fw-light">Pending Tasks</span>
			@php
			/* DON'T DELETE
		  	@if($authUser->role == 'team-user')
			<label class="switch float-end">
				<input type="checkbox" class="switch-input all-tasks" id="all-tasks" />
				<span class="switch-toggle-slider">
					<span class="switch-on"></span>
					<span class="switch-off"></span>
				</span>
				<span class="switch-label">All Tasks</span>
			</label>
			@endif
			*/
			@endphp				
		</h4>

		<table class="table border-0 m-0 tbl-header" id="tbl-header" style="display: none;">
			<colgroup>			    					
				<col width="11%"/>
				<col width="43.5%"/>
				<col width="9.5%"/>
				<col width="9.5%"/>
				<col width="9.5%"/>
				<col width="17%"/>
			</colgroup>
			<thead>
				<tr>              
					<th class="border-bottom-0 p-0">Country</th>
					<th class="border-bottom-0 p-0">Company</th>
					<th class="border-bottom-0 p-0">Frequency</th>              
					<th class="border-bottom-0 p-0">Period</th>
					<th class="border-bottom-0 p-0">Amount Due</th>
					<th class="border-bottom-0 p-0">Status</th>					                 
				</tr>
			</thead>              
      	</table>       

		<div class="accordion mt-0 accordion-header-primary" id="accordionStyleAllTasks">
			@php
	            $check_product_type = 1;
	            $accordion_name = 'All';

	            $filtered_result = $full_result->filter(function ($vatreg, $key) {                   
	              return ($vatreg->vatregmain->product_type != 4);
	            });
	            $result = $filtered_result;
	        @endphp			
			@include('_partials/_content/_vatreturn/vatreturns-all-tasks-lazy')	

			@php
	            $check_product_type = 4;
	            
	            $filtered_result = $full_result->filter(function ($vatreg, $key) {                   
	              return ($vatreg->vatregmain->product_type == 4);
	            });
	            $result = $filtered_result;
	        @endphp			
			@include('_partials/_content/_vatreturn/vatreturns-all-tasks-lazy')								
		</div>

		<div id="vatreturn_tasks_block" class="h-px-20"></div>

	</div>
	<!--/ Accordion Header Color -->
	<!--/ VAT Returns -->
	
@endsection