@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Register')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<!-- <script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/intlTelInput.min.js')}}"></script>

<script src="{{asset('js/dv-register.js')}}"></script>
<script src="{{asset('js/dv-register-file-upload-lazy.js')}}"></script>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row m-0">

    <!-- Left Text -->
    <div class="d-none d-lg-flex col-lg-4 align-items-center justify-content-end p-5 pe-0">
      <div class="w-px-400">
        <img src="{{asset('assets/img/illustrations/create-account-'.$configData['style'].'.png')}}" class="img-fluid scaleX-n1-rtl" alt="multi-steps" width="600" data-app-light-img="illustrations/create-account-light.png" data-app-dark-img="illustrations/create-account-dark.png">
      </div>
    </div>
    <!-- /Left Text -->

    <!--  Multi Steps Registration -->
    <div class="d-flex col-lg-8 authentication-bg p-sm-5 p-3 justify-content-center">
      <div class="d-flex flex-column w-px-700">
        <!-- Logo -->
        <div class="app-brand border-bottom mx-3 mb-4">
          <a href="{{url('/')}}" class="app-brand-link gap-2 mb-3">
            <span class="app-brand-text demo h3 mb-0 fw-bold">@include('_partials.macros')</span>
          </a>
        </div>
        <!-- /Logo -->

        <div class="my-auto">
          <div id="multiStepsValidation" class="bs-stepper shadow-none">
            <div class="bs-stepper-header">
              <div class="step" data-target="#userDetailsValidation">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle">
                    <i class="bx bx-home"></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">User</span>
                    <span class="bs-stepper-subtitle">User Details</span>
                  </span>
                </button>
              </div>
              
              <div class="line"></div>
              <div class="step" data-target="#companyInfoValidation">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle">
                    <i class="bx bx-user"></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">Company</span>
                    <span class="bs-stepper-subtitle">Company Information</span>
                  </span>
                </button>
              </div>
              <div class="line"></div>
              <div class="step" data-target="#otherDetailsValidation">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle">
                    <i class="bx bx-detail"></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">Legal Representative</span>
                    <span class="bs-stepper-subtitle">Other Details</span>
                  </span>
                </button>
              </div>
            </div>

            <div class="bs-stepper-content pt-4">
              <form id="multiStepsRegForm" onSubmit="return false">
                @csrf
                <!-- User Details --> 
                <div id="userDetailsValidation" class="content">
                  <div class="content-header mb-3">
                    <h4>User Information</h4>
                    <span>Enter User Details</span>
                  </div>
                  @include('_partials/_content/_company/register-user-tab')
                </div>
                <!--End  User Details -->

                <!-- Comapany Info -->
                <div id="companyInfoValidation" class="content">
                  <div class="content-header mb-3">
                    <h4>Comapny Information</h4>
                    <span>Enter Company Information</span>
                  </div>
                  @include('_partials/_content/_company/register-company-tab')
                </div>
                <!--End  Comapany Info -->

                <!-- Legal Representative -->
                <div id="otherDetailsValidation" class="content position-relative">
                  <div class="content-header mb-3">
                    <h4>Legal Representative</h4>                    
                  </div>   
                  @include('_partials/_content/_company/register-legal-tab')
                  @include('_partials/_content/_company/register-file-upload')
                </div>
                <!-- End Legal Representative -->  
              <!-- </form> -->
            </div> <!-- end of bs-stepper-content pt-4 -->
          </div> <!-- end of multiStepsValidation -->
        </div> <!-- end of auto div -->
      </div>
    </div>
    <!-- / Multi Steps Registration -->
  </div>
</div>

<script>
  // Check selected custom option
  window.Helpers.initCustomOptionCheck();
</script>
@endsection
