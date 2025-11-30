@extends('layouts/layoutMaster')

@section('title', ' Companies - List')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.company_datas = [];
    window.other_company_datas = [];

    var result = { 'companies': {!! json_encode($companies) !!} };
    company_datas = drawDtTable(result, 'companies');

    var other_companies_result = { 'other_companies': {!! json_encode($other_companies) !!} };    
    other_company_datas = drawDtTable(other_companies_result, 'other_companies');
});
</script>

<script src="{{asset('js/dv-companies-lazy.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Companies/</span> List</h4>

<div class="card" id="client-page" style="display: none;">
  <!-- Bounce -->
  <div class="sk-bounce sk-primary sk-center">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div>

  @if($authUser->role == 'team-user') 
    <!-- <div class="card-header p-0">
      <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom"> -->
      <div class="card shadow-none mb-3">  
        <div class="card-header border-bottom">
          <!-- TAB UL-->
          <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
            <li class="nav-item">
              <button type="button" id="btn-my-companies" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-my-companies" aria-controls="navs-my-companies" aria-selected="true">My Companies <span class="alert-primary text-end fs-tiny p-1 mx-2"></span></button>
            </li>

            <li class="nav-item">
              <button type="button" id="btn-other-companies" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-other-companies" aria-controls="navs-other-companies" aria-selected="false">Companies <span class="alert-primary text-end fs-tiny p-1 mx-2"></span></button>
            </li>            
          </ul>
          <!--end TAB UL-->
       </div> 
      <!--  </div> -->

      <!-- <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
        <div class="card shadow-none px-0"> -->

          <!-- TAB CONTENT-->
          <div class="tab-content p-0">
            <!-- MY COMPANIES -->
            <div class="tab-pane fade show active" id="navs-my-companies" role="tabpanel">
              <!-- <table class="datatables-my-companies table border-top" style="display: none;"> -->
              <table class="datatables-clients table border-top" style="display: none;">  
                <thead>        
                  <tr>           
                    <th></th>
                    <th>Country</th>
                    <th>Company</th>             
                           
                    <th>Trading Name</th>
                    <th>Status</th>
                  </tr>
                </thead>     
              </table>
            </div>
            <!--end MY COMPANIES -->

            <!-- OTHER COMPANIES -->
            <div class="tab-pane fade" id="navs-other-companies" role="tabpanel">
              <table class="datatables-other-companies table border-top" style="display: none;">
                <thead>        
                  <tr>           
                    <th></th>
                    <th>Country</th>
                    <th>Company</th>             
                           
                    <th>Trading Name</th>
                    <!-- <th>Status</th> -->
                  </tr>
                </thead>     
              </table>
            </div>
            <!--end OTHER COMPANIES -->
          </div> 
          <!--end TAB CONTENT-->

        <!-- </div>
      </div> -->

    </div>
  @else  
    <!-- DataTable with Buttons -->
    
      <div class="card-datatable table-responsive pt-0">        
        <table class="datatables-clients table border-top" style="display: none;">
          <thead>        
            <tr>           
              <th></th>
              <th>Country</th>
              <th>Company</th>             
                     
              <th>Trading Name</th>
              <th>Status</th>
            </tr>
          </thead>     
        </table>
      </div>
    
  @endif

</div>
@endsection
