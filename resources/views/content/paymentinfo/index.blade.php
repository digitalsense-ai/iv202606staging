@extends('layouts/layoutMaster')

@section('title', ' Payment Info - List')

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
<script src="{{asset('js/dv-payment-info-form-validation.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Payment Info/</span> List</h4>

@if(count($paymentinfos) == 0)
  <!-- No Payment Info -->
  <div class="container-xxl container-p-y">
    <div class="misc-wrapper text-center">    
      <div class="mt-5">
        <img src="{{asset('assets/img/illustrations/girl-doing-yoga-light.png')}}" alt="page-misc-not-authorized-light" width="450" class="img-fluid" data-app-light-img="illustrations/girl-doing-yoga-light.png" data-app-dark-img="illustrations/girl-doing-yoga-dark.png">
      </div>
      <h1 class="mb-2 mx-2">Hooray!!</h1>
      <p class="mb-4 mx-2">You don´t have any payment info</p>        
    </div>
  </div>
  <!-- / No Payment Info -->
@else

  <!-- Payment Info -->                      
  <!-- Accordion Header Color -->
  <div class="col-md">          
    
    <table class="table border-0 m-0 tbl-header" id="tbl-header">
      <colgroup>                    
        <col width="100%"/>       
      </colgroup>
      <thead>
        <tr>              
          <th class="border-bottom-0 p-0">Country</th>                                
        </tr>
      </thead>              
    </table>       

    <div class="accordion mt-0 accordion-header-primary" id="accordionStyle">
      @foreach ($paymentinfos as $paymentinfo)  
        @if($paymentinfo->countrycode == 'GB')      
          @php
            $payment_info_id = $paymentinfo->id;
          @endphp          
          <div class="accordion-item card sort-item">
            <h2 class="accordion-header">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionStyle-{{ $payment_info_id }}" aria-expanded="false" id="btn-accordion-{{ $payment_info_id }}">
                <table class="table border-0">
                  <colgroup>          
                    <col width="100%"/>                   
                  </colgroup>
                  <tbody>
                    <tr>              
                      <td class="border-bottom-0 p-0">                       
                        <img src="{{asset('assets/img/flags/'. $paymentinfo->countrycode .'.png')}}" class="country-flag me-2"><span class="btn-group-vertical">{{ $paymentinfo->countrycode }}</span>
                      </td>             
                    </tr>
                  </tbody>
                </table>                
              </button>
            </h2>

            <div id="accordionStyle-{{ $payment_info_id }}" class="accordion-collapse collapse" data-bs-parent="#accordionStyle">
              <div class="accordion-body">
                                
                  @include('_partials/_content/_paymentinfo/create')
                
              </div>
            </div>
          </div>
        @endif  
      @endforeach
    </div>

  </div>
  <!--/ Accordion Header Color --> 
  <!--/ Payment Info -->                       
@endif  
@endsection
