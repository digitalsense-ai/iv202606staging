@extends('layouts/layoutMaster')

@section('title', 'Excel Column Template - List')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<!-- <script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>

<!-- <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>

<script src="{{asset('assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js')}}"></script>
<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
@endsection

@section('page-script')
<!-- <script src="{{asset('assets/js/ui-popover.js')}}"></script> -->
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.excelcolumntemplate_datas = [];    
    
    var result = { 'excelcolumntemplates': {!! json_encode($excelcolumntemplates) !!} };    
    excelcolumntemplate_datas = drawDtTable(result, 'excelcolumntemplate');    
});
</script>
<script src="{{asset('js/dv-upload.js')}}"></script>
<!-- DON'T DELETE - UNTIL MULTI-FILE-MULTI-SHEET -->
<!-- <script src="{{asset('js/dv-excel-column-template.js')}}"></script> -->

<script src="{{asset('js/dv-excel-column-template-new.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Excel Template/</span> List</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<!-- Excel Column Template List Table -->
<div class="card" style="display: none;" id="excelcolumntemplate-card">  
  <div class="card-datatable table-responsive">
    <table class="datatables-excelcolumntemplate table border-top">
      <thead>        
        <tr>            
          <th></th>  
          <th>Name</th>
          <th>Version</th>       
          <th>Columns</th>          
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- DON'T DELETE - UNTIL MULTI-FILE-MULTI-SHEET -->
  {{--@include('_partials/_modals/modal-excel-column-template-single')--}}

  @include('_partials/_modals/modal-excel-column-template-new-create')
</div>

@endsection
