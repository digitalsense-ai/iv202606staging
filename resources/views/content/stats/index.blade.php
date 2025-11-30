@extends('layouts/layoutMaster')

@section('title', ' Stats ')

@section('vendor-style')

@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')

@endsection

@section('page-script')

@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Stats</span> </h4>

@if(count($stats) == 0)
  <!-- No Stats -->
  <div class="container-xxl container-p-y">
    <div class="misc-wrapper text-center">    
      <div class="mt-5">
        <img src="{{asset('assets/img/illustrations/girl-doing-yoga-light.png')}}" alt="page-misc-not-authorized-light" width="450" class="img-fluid" data-app-light-img="illustrations/girl-doing-yoga-light.png" data-app-dark-img="illustrations/girl-doing-yoga-dark.png">
      </div>
      <h1 class="mb-2 mx-2">Hooray!!</h1>
      <p class="mb-4 mx-2">You don´t have any stats</p>        
    </div>
  </div>
  <!-- / No Stats -->
@else

  <!-- Stats -->
  <!-- Accordion Header Color -->
  <div class="col-md">          
    <div class="row g-4 mb-4">

      <!-- Client Stats -->
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span>Companies</span>
                <div class="d-flex align-items-end mt-2">
                  <h4 class="mb-0 me-2">{{ $stats['client']['total'] }}</h4>
                </div>
                <small>Total</small><br/>
                <small class="text-success me-5">Active - {{ $stats['client']['active'] }}</small>
                <small class="text-danger">InActive - {{ $stats['client']['inactive'] }}</small>
              </div>
              <span class="badge bg-label-primary rounded p-2">
                <i class="bx bx-cog bx-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
      <!--/ Client Stats -->

      <!-- Vat Reg. Stats -->
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span>VAT Reg.</span>
                <div class="d-flex align-items-end mt-2">
                  <h4 class="mb-0 me-2">{{ $stats['vat_reg_main']['total'] }}</h4>
                </div>
                <small>Total</small><br/>
                <small class="text-success me-5">Active - {{ $stats['vat_reg_main']['active'] }}</small>
                <small class="text-danger">InActive - {{ $stats['vat_reg_main']['inactive'] }}</small>
              </div>
              <span class="badge bg-label-primary rounded p-2">
                <i class="bx bx-cog bx-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
      <!--/ Vat Reg. Stats -->

    </div>
  </div>
  <!--/ Accordion Header Color --> 
  <!--/ Stats -->                       
@endif  
@endsection
