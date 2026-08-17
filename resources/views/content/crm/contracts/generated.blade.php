@extends('layouts/layoutMaster')

@section('title', 'CRM - Generate')

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
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>


<script src="{{asset('js/dv-crm.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('crm/reminders') }}">{{ __('CRM Reminders') }}</a>/</span> {{ __('List') }}  
</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center crm-reminder-page">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<!-- Reminder List Table -->
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
    <table>
  <thead>
    <tr>
      <th>Service</th>
      <th>Description</th>
      <th>Interval</th>
      <th class="text-end">Price excl. VAT</th>
    </tr>
  </thead>
  <tbody>
    @forelse($productRows as $row)
      <tr>
        <td>{{ $row['service'] }}</td>
        <td>{{ $row['description'] }}</td>
        <td>{{ ucfirst(str_replace('_', ' ', $row['interval'])) }}</td>
        <td class="text-end">{{ number_format($row['price'], 2) }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="4">No selected products.</td>
      </tr>
    @endforelse
  </tbody>
</table>

  </div>  
</div>
@endsection