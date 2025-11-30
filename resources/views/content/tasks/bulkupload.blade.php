@extends('layouts/layoutMaster')

@section('title', 'Bulk Upload')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">


<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.bulkupload_datas = [];    
    //var bulkupload_start = 1;

    var result = { 'vatregs': {!! json_encode($vatregs) !!} };    
    bulkupload_datas = drawDtTable(result, 'bulkupload');    
});
</script>
<script src="{{asset('js/dv-bulk-upload.js')}}"></script>
@endsection


@section('content')	
	
	<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Bulk upload/</span> List</h4>

	<!-- Bounce -->
	<div class="sk-bounce sk-primary sk-center">
	  <div class="sk-bounce-dot"></div>
	  <div class="sk-bounce-dot"></div>
	</div>

	<!-- Bulk upload List Table -->
	<div class="card" style="display: none;" id="bulkupload-card">
		<div class="card-datatable table-responsive">
			<table class="datatables-bulkupload table border-top">
				<thead>       
					<!-- <tr>          
						<th></th>  
						<th>No.</th>         
						<th>Client Name</th>  						
						<th>Month/Year</th>         
						<th>File Type</th>         												
						<th>Fee Number</th>       
						<th>Statistical Number</th>
						<th>Adjustment Number</th>
						<th>Invoice Total</th>												
					</tr> -->
					<tr>          
						<th></th>
						<th></th>
						<th>Company Name</th>  						
						<th>VAT period</th>  	
						<th>Month/Year</th>   
						<th>Users</th>      
						<th>Files</th>         																		
						<!-- <th>Action</th> -->												
					</tr>
				</thead>
			</table>
		</div>
	</div>

	@php
		$file_type = 'ivf';
		$file_type_title = 'Import VAT';		
	@endphp
	@include('_partials/_modals/modal-file-bulk-upload-lazy')

@endsection