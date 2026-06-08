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
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.crm_addon_datas = [];
   
    var result = { 'addons': {!! json_encode($addons) !!} };    
    crm_addon_datas = drawDtTable(result, 'crm_addon');
});
</script>

<script src="{{asset('js/dv-crm.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('crm/addons') }}">{{ __('Addons') }}</a>/</span> {{ __('List') }}  
</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center addon-page">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<!-- Pricing List Table -->
<div class="card" style="display: none;" id="table-card">  
  <div class="card-datatable table-responsive">
    <table class="datatables-addons table border-top">
      <thead>
        <tr>     
          <th>S. No.</th>     
          <th>Product Name</th>
          <th>Price</th>
          <th>Frequency</th>          
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>  
</div>

@include('_partials/_offcanvas/offcanvas-crm-addon')
@endsection