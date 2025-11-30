@extends('layouts/layoutMaster')

@section('title', 'Compliance')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">

<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />

@endsection

@section('page-style')

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>

<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.matched_user_datas = [];   
    
    var result = { 'matched_users': {!! json_encode($matched_users) !!}, 'matched_cvr_users': {!! json_encode($matched_cvr_users) !!} };    
    matched_user_datas = drawDtTable(result, 'compliance');      
});
</script>


<script type="text/javascript">
$(function () {      
    window.compliance_datas = [];
});
</script>

<script src="{{asset('js/dv-compliance.js')}}"></script>
<script src="{{asset('js/dv-modal-select-client-vatnos.js')}}"></script>
@endsection


@section('content')	
	
	<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Compliance/</span> List</h4>

	<!-- Bounce -->
	<div class="sk-bounce sk-primary sk-center">
	  <div class="sk-bounce-dot"></div>
	  <div class="sk-bounce-dot"></div>
	</div>

	<!-- Matched User List Table -->
	<div class="card" style="display: none;" id="matched-user-card">
		<div class="card-datatable table-responsive">
			<table class="datatables-matched-user table border-top">
				<thead>       
					<!-- <tr>            
			          <th>id</th>         
			          <th>User</th>  
			          <th>Excel User</th>           			             
			          <th>Role</th>          
			          <th>Telephone</th>
			          <th>Language</th>			             
			          <th>Status</th>			          
			        </tr> -->
			        <tr>            
						<th></th>         
						<th>Company Name</th>  
						<th>Company registration number</th>           			          
						<th>First name(s)</th>          
						<th>Middle name(s)</th>
						<th>Surname</th>			             
						<th>Relation to company (title etc.)</th>		
						<th>Political Exposed Person (x marks yes)</th>	
						<th>EU Sancation list (x marks yes)</th>	
						<th>UNSC  list (x marks yes)</th>				                
						<th>Comment</th>
			        </tr>
				</thead>
				<!-- <tfoot>
					<tr>
						<td colspan="10">This report was approved by:</td>
					</tr>
					<tr>
						<td colspan="1">Name:</td>
						<td colspan="9" align="left">_____________________________</td>
					</tr>
					<tr>
						<td colspan="1">Signature:</td>
						<td colspan="9" align="left">_____________________________</td>
					</tr>
					<tr>
						<td colspan="1">Date of approval:</td>
						<td colspan="9" align="left">_____________________________</td>
					</tr>
				</tfoot> -->
			</table>
		</div>
	</div>

	<!-- Compliance List Table -->
	<div class="card" style="display: none;" id="compliance-card">
		<div class="card-datatable table-responsive">
			<table class="datatables-compliance table border-top">
				<thead>       
					<tr>          						
						<th>No.</th>         
						<th>File Name</th>  												     
						<th>File Size</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>

	@php
		$file_type = 'compliance';
		$file_type_title = 'Compliance';		
	@endphp
	@include('_partials/_modals/modal-file-compliance-lazy')

	@include('_partials/_modals/modal-select-client-vatnos')
@endsection