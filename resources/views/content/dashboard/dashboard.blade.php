
@extends('layouts/layoutMaster')
@php
  $configData = Helper::appClasses();
@endphp

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {    
  @if($authUser->role == 'client-user')
    window.connection_datas = []; 
    var result = { 'clientconnection': {!! json_encode($clientconnection) !!} };    
    connection_datas = drawDtTable(result, 'clientconnection');
  @endif
});
</script>
<script src="{{asset('js/dv-connection.js')}}"></script>
<script src="{{asset('js/dv-erp-load.js')}}"></script>
@endsection

@section('content')


@if(isset($message)) 
  <div class="alert alert-solid-{{ ($message['error_message'] != '') ? 'danger' : (($message['success_message'] != '') ? 'success' : (($message['warning_message'] != '') ? 'warning' : '')) }}" role="alert">
    {{ ($message['error_message'] != '') ? $message['error_message'] : (($message['success_message'] != '') ? $message['success_message'] : (($message['warning_message'] != '') ? $message['warning_message'] : '')) }}
  </div>
@endif

<!-- Gamification Card -->
<div class="col-lg-4 col-md-6 col-12 mb-4">
<div class="card h-100">
  <div class="card-header">
    <h3 class="card-title mb-2">Welcome {{ (isset($authUser)) ? ($authUser->firstname . ' ' . $authUser->lastname) : '' }}  !</h3>        
  </div>
  <div class="card-body">
    <div class="row align-items-end">
      
      <div class="col-6">
        <img src="{{asset('assets/img/illustrations/prize-'.$configData['style'].'.png')}}" width="140" height="150" class="rounded-start" alt="View Sales" data-app-light-img="illustrations/prize-light.png" data-app-dark-img="illustrations/prize-dark.png">
      </div>
    </div>
  </div>
</div>
</div>
<!--/ Gamification Card -->

<!-- Api Connection Status -->
@if($authUser->role == 'client-user')  
  <form id="formNewConnection" onSubmit="return false">
    @csrf 
    <input type="hidden" name="client_id" id="client_id" value="{{ isset($authUser) ? $authUser->id : $client_id }}">        
    <h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Connection/</span> List</h4>

    <div class="card" id="client-connection-page">
      <!-- Bounce -->
      <div class="sk-bounce sk-primary sk-center">
        <div class="sk-bounce-dot"></div>
        <div class="sk-bounce-dot"></div>
      </div>

      <!-- DataTable with Buttons -->    
      <div class="card-datatable table-responsive pt-0">        
        <table class="datatables-client-connection table border-top">
          <thead>        
            <tr>           
              <th></th>
              <th>Connection Name</th>
              <th>Status</th>                                 
              <th>Remarks</th>              
            </tr>
          </thead>     
        </table>
      </div>    
    </div>

    @include('_partials/_modals/modal-client-connection')
  </form>
@endif
<!--/ Api Connection Status -->

@endsection
