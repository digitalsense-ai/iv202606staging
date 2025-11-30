@extends('layouts/layoutMaster')

@section('title', 'Mail Box Files')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/css/scroller.dataTables.min.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<!-- Flat Picker -->
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script type="text/javascript" language="javascript" src="{{asset('assets/js/dataTables.scroller.min.js')}}"></script>
<script src="{{asset('assets/js/xlsx.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {  
    $(".card.mailboxfiles .sk-bounce").show();
    $(".card.mailboxfiles .card-datatable").hide(); 

    window.mailboxfile_new_datas = [];   
    window.mailboxfile_active_datas = [];   
    window.mailboxfile_dismissed_datas = [];   
    
    var result = { 'mailboxfiles': {!! json_encode($mailboxfiles) !!} };    
    var mailboxfile_datas = drawDtTable(result, 'mailbox');

    window.anyexcel_template_datas = [];    
    
    var result = { 'anyexcel_templates': {!! json_encode($anyexcel_templates) !!} };    
    anyexcel_template_datas = drawDtTable(result, 'anyexcel_template');   
});
</script>
<script src="{{asset('js/dv-mailbox-files.js')}}"></script>

<script src="{{asset('js/dv-anyexcel-template-others.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Mail Box Files</span> 
</h4>

<!-- Ajax Sourced Server-side -->
<div class="card mailboxfiles">

  <!-- Bounce -->
  <div class="sk-bounce sk-primary sk-center">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div> 
  
  <div class="card-header p-0">    
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom">
           
      <div class="col-md-9">
        <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
          <li class="nav-item">
            <button type="button" id="btn-mailboxfile-new" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-mailboxfile-new" aria-controls="navs-mailboxfile-new" aria-selected="true">New Files <span class="alert-primary text-end fs-tiny p-1 mx-2"></span></button>
          </li>

          <li class="nav-item">
            <button type="button" id="btn-mailboxfile-active" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-mailboxfile-active" aria-controls="navs-mailboxfile-active" aria-selected="false">Active Files <span class="alert-warning text-end fs-tiny p-1 mx-2"></span></button>
          </li>

          <li class="nav-item">
            <button type="button" id="btn-mailboxfile-dismissed" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-mailboxfile-dismissed" aria-controls="navs-mailboxfile-dismissed" aria-selected="false">Dismissed Files <span class="alert-danger text-end fs-tiny p-1 mx-2"></span></button>
          </li>
        </ul>
      </div>       
      <div class="col-md-3 dt-mailboxfile-export text-end">
        <div class="new-mailboxfile-export"></div>
        <div class="active-mailboxfile-export d-none"></div>
        <div class="dismissed-mailboxfile-export d-none"></div>
      </div>
              
    </div>

    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
      <div class="card shadow-none px-0">
        <div class="card-header border-bottom p-2">         
          <div class="dt-search-filter text-end align-middle">
          </div>
        </div>

        <div class="tab-content px-0 pb-0">
            <div class="tab-pane fade show active" id="navs-mailboxfile-new" role="tabpanel">
              <table class="datatables-new-mailboxfiles table">     
                  <thead class="bg-label-primary">
                    <tr>
                      <th></th>           
                      <th>Client Name</th>           
                      <th>Date and Time</th>
                      <th>Email sender mail</th>
                      <th>Mail Title</th>
                      <th>File Title</th>
                      <th>Preview</th>                      
                      <th>Actions</th>
                    </tr>
                  </thead>
              </table>
            </div><!--/ navs-mailboxfile-new-->
            <div class="tab-pane fade" id="navs-mailboxfile-active" role="tabpanel">
              <table class="datatables-active-mailboxfiles table">     
                  <thead class="bg-label-primary">
                    <tr>
                      <th></th>           
                      <th>Client Name</th>                      
                      <th>Date and Time</th>
                      <th>Email sender mail</th>
                      <th>Mail Title</th>
                      <th>File Title</th>
                      <th>Preview</th>                      
                      <th>Actions</th>
                    </tr>
                  </thead>
              </table>    
            </div><!--/ navs-mailboxfile-active-->
            <div class="tab-pane fade" id="navs-mailboxfile-dismissed" role="tabpanel">
              <table class="datatables-dismissed-mailboxfiles table">     
                  <thead class="bg-label-primary">
                    <tr>
                      <th></th>        
                      <th>Client Name</th>                         
                      <th>Date and Time</th>
                      <th>Email sender mail</th>
                      <th>Mail Title</th>
                      <th>File Title</th>
                      <th>Preview</th>                      
                      <th>Actions</th>
                    </tr>
                  </thead>
              </table>
            </div><!--/ navs-mailboxfile-dismissed-->
        </div>
      </div>
    </div>
  </div>

  <div class="card-datatable"> 
    
  </div>
  <!--/ Ajax Sourced Server-side -->
</div>

@include('_partials/_modals/modal-mailbox-assign-anyexcel-template')

@endsection
