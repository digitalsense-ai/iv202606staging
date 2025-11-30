@extends('layouts/layoutMaster')

@section('title', 'Cargo Declaration Files - NO')

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
    $(".card.cargodeclarationfiles .sk-bounce").show();
    $(".card.cargodeclarationfiles .card-datatable").hide(); 

    window.cargodeclarationfile_datas = [];   
    
    var result = { 'cargodeclarationfiles': {!! json_encode($cargodeclarationfiles) !!} };    
    var cargodeclarationfile_datas = drawDtTable(result, 'cargodeclarationfiles');
});
</script>
<script src="{{asset('js/dv-cargo-declaration-files.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Cargo Declaration Files</span> 
</h4>

<!-- Ajax Sourced Server-side -->
<div class="card cargodeclarationfiles">

  <!-- Bounce -->
  <div class="sk-bounce sk-primary sk-center">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div> 
  
  <div class="card-header p-0">    
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom">
             
      <div class="col-md-12 dt-cargodeclarationfiles-export text-end">
        <div class="cargodeclarationfiles-export my-2"></div>       
      </div>
              
    </div>

    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
      <div class="card shadow-none px-0">
        <div class="card-header border-bottom p-2">         
          <div class="dt-search-filter text-end align-middle">
          </div>
        </div>

        <table class="datatables-cargodeclarationfiles table">     
            <thead class="bg-label-primary">
              <tr>
                <th></th>           
                <th>Client Name</th>   
                <th>Lope No.</th>        
                <th>Date</th>     
                <th>Date and Time</th>
                <th>Email sender mail</th>
                <th>Mail Title</th>
                <th>File Title</th>
                <th>Preview</th>                      
                <th>Actions</th>
              </tr>
            </thead>
        </table>
      </div>
    </div>
  </div>
 
  <!--/ Ajax Sourced Server-side -->
</div>
@endsection
