@extends('layouts/layoutMaster')

@section('title', 'CRM - Addon')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
<!-- <link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}" /> -->
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<!-- <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script> -->
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.crm_pricing_datas = [];
   
    var result = { 'pricings': {!! json_encode($pricings) !!} };    
    crm_pricing_datas = drawDtTable(result, 'crm_pricing');
});
</script>

<!-- <script src="{{asset('assets/js/intlTelInput.min.js')}}"></script> -->

<script src="{{asset('js/dv-crm.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('crm/pricings') }}">{{ __('Pricings') }}</a>/</span> {{ __('List') }}  
</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center pricing-page">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<!-- Pricing List Table -->
<div class="card" style="display: none;" id="table-card">  
  <div class="card-datatable table-responsive">
    <table class="datatables-pricings table border-top">
      <thead>
        <tr>     
          <th></th>     
          <th>Product Name</th>
          <th>Price</th>
          <th>Frrequency</th>          
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>  
</div>
@endsection