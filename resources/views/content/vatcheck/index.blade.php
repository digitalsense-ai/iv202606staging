@extends('layouts/layoutMaster')

@section('title', 'VAT Check')

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

<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
@endsection

@section('page-script')
<script type="text/javascript">
    window.EchoConfig = {
        pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
        pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    };
</script>

<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.unmatched_invoice_datas = [];   
    
    var result = { 'unmatched_invoices': {!! json_encode($unmatched_invoices) !!} };        
    unmatched_invoice_datas = drawDtTable(result, 'vatcheck');      
});
</script>

<script src="{{asset('js/dv-upload.js')}}"></script>
<script src="{{asset('js/dv-all-tasks-lazy.js')}}"></script>
<!-- <script src="{{asset('js/dv-excel-column-template-new.js')}}"></script> -->

<script src="{{asset('js/dv-anyexcel-template-others.js')}}"></script>

<script src="{{asset('js/dv-vatcheck.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">VAT Check/</span> List</h4>

	<!-- Bounce -->
	<div class="sk-bounce sk-primary sk-center">
	  <div class="sk-bounce-dot"></div>
	  <div class="sk-bounce-dot"></div>
	</div>

	<input type="hidden" name="vat_reg_id" id="vat_reg_id" value="{{ $vat_reg_id }}">	

	<!-- UnMatched Invoice List Table -->
	<div class="card" style="display: none;" id="unmatched-invoice-card">
		<div class="card-datatable table-responsive">
			<table class="datatables-unmatched-invoice table border-top">
				<thead>       					
			        <tr>            
						<th></th>  
						<th>Invoice date</th>
						<th>Invoice number</th>
						<th>Currency code</th>
						<th>Total NET</th>
						<th>VAT rate</th>
						<th>Total VAT</th>
						<th>Total GROSS</th>						
			        </tr>
				</thead>				
			</table>
		</div>
	</div>	
	

	@php
		$file_type = 'vatcheck';
		$file_type_title = 'VAT Check';		
	@endphp
	{{--@include('_partials/_modals/modal-excel-template-selection')	--}}
	@include('_partials/_modals/modal-file-upload-single-lazy')
@endsection
