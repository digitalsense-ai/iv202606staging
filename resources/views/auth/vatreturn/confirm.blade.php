@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutConfirm')

@section('title', 'Comfirm Numbers')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-confirm-numbers.js')}}"></script>
@endsection

@section('content')

@if($client)
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row accept-numbers">

      <!-- <div class="col-xl-9 col-md-8 col-12 mb-md-0 py-0 alert"> -->
      @php
        $disable = false;
      @endphp              
      @if(isset($message)) 
        @if(($message['error_message']!="") || ($message['success_message']!="") || ($message['warning_message']!=""))
          @php
            $disable = true;

            if($message['error_message'] !="" )
              $alertClassName = 'danger';
            elseif($message['success_message'] !="" )
              $alertClassName = 'success';   
            elseif($message['warning_message'] !="" )
              $alertClassName = 'warning'; 

          @endphp
          <div class="alert alert-{{ $alertClassName }}" role="alert">
              {{ ($message['error_message'] != '') ? $message['error_message'] : (($message['success_message'] != '') ? $message['success_message'] : (($message['warning_message'] != '') ? $message['warning_message'] : '')) }}
          </div>            
        @endif
      @endif                 
      
      <!-- Invoice -->
      <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-4 accept-numbers-card">    
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between flex-xl-row flex-md-column flex-sm-row flex-column p-sm-3 p-0">   
              <div class="col-xl-6 col-md-12 col-sm-7 col-12">               
                <table>
                  <tbody>
                    <tr>
                      <td class="pe-3">Company Name:</td>
                      <td>{{ $client->client_name }}</td>
                    </tr>
                    <tr>
                      <td class="pe-3">VAT No.:</td>
                      <td>{{ $client->vatno }}</td>
                    </tr>                  
                  </tbody>
                </table>
              </div>           
              <div class="mb-xl-0 mb-4">
                <h4 class="mb-0">VAT Return</h4>
                <p class="mb-1">{{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                    \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</p>
              </div>
              
            </div>

            <div class="" id="load-confirm-vatreturns-footer">                          
            </div>
          </div>          
          <hr class="my-0" />          
          <div class="p-31" id="load-vatreturns-overview">
            @include('_partials/_content/_vatreturn/vatreturn-overview-lazy')          
          </div>

          <!-- <div class="card-body">
            <div class="row">
              <div class="col-12">
                <span class="fw-semibold">Note:</span>
                <span>It was a pleasure working with you and your team. We hope you will keep us in mind for future freelance
                  projects. Thank You!</span>
              </div>
            </div>
          </div> -->
        </div>
      </div>
      <!-- /Invoice -->

      <!-- Invoice Actions -->
      <div class="col-xl-3 col-md-4 col-12 invoice-actions">
        {{--
        <div class="card mb-3">
          <div class="card-body">
            <button class="btn btn-primary w-100 btn-export-pdf-confirmview" data-vat_reg_id="{{ $vat_reg_id }}">
              <i class='bx bx-up-arrow-circle me-1'></i>
              <span class="align-middle">Export to PDF</span>
            </button>
          </div>
        </div>
        --}}
        <div class="card">
          <div class="card-body">
            @if($client->approved_by)
              @if($vatreg->approved_at == null && $vatreg->declined_at == null)
                <form id="formConfirmNumbers" class="mb-3" data-vatid="{{ $vat_reg_id }}">
                  @csrf
                  {{--
                  <div class="mb-3">
                    <label for="company-vat" class="form-label">Enter VAT Number</label>
                    <input type="text" class="form-control" id="company-vat" name="company_vat" value="{{ old('company-vat') }}" placeholder="83097515" required {{ ($disable) ? 'disabled="disabled"' : '' }} />
                  </div>
                  --}}  
                  <div class="mb-3">                    
                    <input type="checkbox" class="form-check-input" id="confirm-data" name="confirm_data" value="1" required {{ ($disable) ? 'disabled="disabled"' : '' }} />                    
                    <label for="confirm-data" class="form-check-label w-90">I hereby confirm that the data is correct and IntraVAT ApS may submit the data to the relevant authorities.</label>
                  </div>              
                  <div class="mb-3 d-flex flex-wrap">
                    <button type="submit" class="btn btn-label-success w-100 btn-accept-numbers disabled" {{ ($disable) ? 'disabled="disabled"' : '' }}><span class="d-flex align-items-center justify-content-center text-nowrap">Approve</span></button>              
                  </div>
                </form>
              @endif  

              @if($vatreg->approved_at != null)
                <div class="mb-3 d-flex flex-wrap">
                  <button class="btn btn-success w-100 btn-accept-numbers" disabled="disabled"><span class="d-flex align-items-center justify-content-center text-nowrap">Approved</span></button>              
                </div>

                <div id="load-approved-details">
                  <ul class="list-unstyled m-0">
                    <li class="d-flex align-items-center">
                      <span class="fw-semibold mx-2">Date Time:</span> <span class="text-end p-2 m-0">{{ \Carbon\Carbon::parse($vatreg->approved_at)->timezone('Europe/Copenhagen')->format('d-m-Y H:i') }}</span>
                    </li>
                    <li class="d-flex align-items-center">
                      <span class="fw-semibold mx-2">Approve By:</span> <span class="text-end p-2 m-0">{{ $client->approved_by->approved_by_firstname }} {{ $client->approved_by->approved_by_lastname }}</span>
                    </li>                                   
                  </ul>
                </div>
              @endif            
              
              <button class="btn btn-label-secondary w-100 {{ ($client->approved_by) ? 'mb-3' : '' }} btn-export-pdf-confirmview" data-vat_reg_id="{{ $vat_reg_id }}">
                <i class='bx bx-up-arrow-circle me-1'></i>
                <span class="align-middle">Export to PDF</span>
              </button>

              @if($vatreg->approved_at == null && $vatreg->declined_at == null)
                <button class="btn btn-label-danger w-100 btn-decline-numbers" data-bs-toggle="offcanvas" data-bs-target="#declineNumbersOffcanvas" {{ ($disable) ? 'disabled="disabled"' : '' }}>
                  <span class="d-flex align-items-center justify-content-center text-nowrap">Decline</span>
                </button>
              @endif 

              @if($vatreg->declined_at != null)
                <span class="cursor-pointer text-decoration-underline d-grid text-center">Decline</span>
                <div id="load-approved-details">
                  <ul class="list-unstyled m-0">
                    <li class="d-flex align-items-center">
                      <span class="fw-semibold mx-2">Date Time:</span> <span class="text-end p-2 m-0">{{ \Carbon\Carbon::parse($vatreg->declined_at)->timezone('Europe/Copenhagen')->format('d-m-Y H:i') }}</span>
                    </li>
                    <li class="d-flex align-items-center">
                      <span class="fw-semibold mx-2">Decline By:</span> <span class="text-end p-2 m-0">{{ $client->approved_by->approved_by_firstname }} {{ $client->approved_by->approved_by_lastname }}</span>
                    </li>                                   
                  </ul>
                </div>
              @endif 
            @endif
            
          </div>
        </div>
      </div>
      <!-- /Invoice Actions -->
    </div>

    <!-- Offcanvas -->   
    @include('_partials/_offcanvas/offcanvas-decline-numbers')
    <!-- /Offcanvas -->
  </div>
@endif
@endsection