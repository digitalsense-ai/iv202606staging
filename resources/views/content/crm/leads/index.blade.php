@extends('layouts/layoutMaster')

@section('title', 'CRM - Leads')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.crm_lead_datas = [];
   
    var result = { 'leads': {!! json_encode($leads) !!} };    
    crm_lead_datas = drawDtTable(result, 'crm_lead');
});
</script>

<script src="{{asset('assets/js/intlTelInput.min.js')}}"></script>

<script src="{{asset('js/dv-crm.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('crm/leads') }}">{{ __('Leads') }}</a>/</span> {{ __('List') }}  
</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center lead-page">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<!-- Lead List Table -->
<div class="card" style="display: none;" id="table-card">
  <!-- <div class="card-header border-bottom">
    <h5 class="card-title">Search Filter</h5>
    <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">      
      <div class="col-md-6"></div>
      <div class="col-md-2 user_role"></div>
      <div class="col-md-2 user_lang"></div>
      <div class="col-md-2 user_status"></div>
    </div>
  </div> -->
  <div class="card-datatable table-responsive">
    <table class="datatables-leads table border-top">
      <thead>
        <tr>     
          <th></th>     
          <th>CVR No.</th>
          <th>Company</th>
          <th>Website</th>
          <th>Contact</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>  
</div>

{{--
<div class="card">
  <div class="card-header">
      <h4>Leads</h4>
      <a href="{{ route('leads.create') }}" class="btn btn-primary float-end">
          Add Lead
      </a>
  </div>

  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
        <th>Company</th>
        <th>Contact</th>
        <th>Status</th>
        <th>Action</th>
        </tr>
      </thead>

      <tbody>
        @foreach($leads as $lead)
          <tr>
            <td>{{ $lead->company_name }}</td>
            <td>{{ $lead->contact->first_name ?? '' }}</td>
            <td>{{ $lead->status }}</td>
            <td>
              <a href="{{ route('leads.edit',$lead) }}" class="btn btn-sm btn-warning">Edit</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>--}}
@endsection