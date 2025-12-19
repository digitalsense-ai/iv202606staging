@extends('layouts/layoutMaster')

@section('title', 'Declaration')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/toastr/toastr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />

<!-- <link rel="stylesheet" href="{{asset('assets/css/scroller.dataTables.min.css')}}" /> -->

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>

<script src="{{asset('assets/vendor/libs/toastr/toastr.js')}}"></script>

<!-- Flat Picker -->
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<!-- <script type="text/javascript" language="javascript" src="{{asset('assets/js/dataTables.scroller.min.js')}}"></script> -->
<script src="{{asset('assets/js/xlsx.min.js')}}"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> -->

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {  
    $(".card.declarations .sk-bounce").show();
    $(".card.declarations .card-datatable").hide(); 

    window.declaration_first_datas = [];   
    window.declaration_second_datas = [];
    window.declaration_third_datas = [];
    
    var result = { 'declarations': {!! json_encode($declarations) !!} };    
    var declaration_datas = drawDtTable(result, 'declaration');  
});
</script>
<script src="{{asset('js/dv-declarations.js')}}"></script>
<script src="{{asset('js/dv-declaration-comment.js')}}"></script>
@endsection

@section('content')

@php
$client = $declarations->client;
$client_id = $client->client_id;
$vat_reg_id = $declarations->id;

$vatregmain = $declarations->vatregmain;

$org_no = '';
if($vatregmain->country == 'NO')
  $org_no = $vatregmain->org_no;
else
  $org_no = str_replace(['.', '-'], '', $vatregmain->vat_no);

@endphp

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('company/' . $client_id)}}">{{ $client->client_name }}</a>/</span> {{ (\Carbon\Carbon::parse($declarations->service_start)->format('F Y') . ' ' . $declarations->country . ' ' . $declarations->general_periods) }}
</h4>

<!-- Toast with Placements -->
<div class="bs-toast toast toast-placement m-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="10000">
  <div class="toast-header">
    <img src="{{asset('assets/img/avatars/1.png')}}" class="d-block w-px-20 h-auto rounded me-2" alt="" />
    <div class="me-auto fw-medium">Refresh Data</div>
    <small>11 mins ago</small>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body">
    Hello, world! This is a toast message.
  </div>
</div>
<!-- Toast with Placements -->

<!-- Ajax Sourced Server-side -->
<div class="card declarations">

  <!-- Bounce -->
  <div class="sk-bounce sk-primary sk-center">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div> 
  
  <input type="hidden" name="vat_reg_id" id="vat_reg_id" value="{{ $vat_reg_id }}" />
  <input type="hidden" name="org_no" id="org_no" value="{{ $org_no }}" />
  <input type="hidden" name="client_name" id="client_name" value="{{ $client->client_name }}" />
  <input type="hidden" name="month_year_period" id="month_year_period" value="{{ (\Carbon\Carbon::parse($declarations->service_start)->format('M y')) . '-' . (\Carbon\Carbon::parse($declarations->service_start)->addMonth($declarations->frequency)->format('M y')) }}" />

  <div class="card-header p-0">    
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom">         
      <div class="col-md-8">
        <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">

          @for ($i = 0; $i < $declarations->frequency; $i++)
            @php
              $tab_div_name = '';            
              if ($i === 0)
                $tab_div_name = 'first';
              elseif ($i === 1)
                $tab_div_name = 'second';
              elseif ($i === 2)
                $tab_div_name = 'third';
            @endphp

            <li class="nav-item">
              <button type="button" id="btn-declaration-{{ $tab_div_name }}" class="nav-link {{ ($i === 0) ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-declaration-{{ $tab_div_name }}" aria-controls="navs-declaration-{{ $tab_div_name }}" aria-selected="{{ ($i === 0) ? 'true' : 'false' }}">{{ (\Carbon\Carbon::parse($declarations->service_start)->addMonth($i)->format('M') . ' ' . $org_no) }} <span class="alert-primary text-end fs-tiny p-1 mx-2"></span></button>
              <input type="hidden" name="declaration_{{ $tab_div_name }}_monthyear" id="declaration_{{ $tab_div_name }}_monthyear" value="{{ (\Carbon\Carbon::parse($declarations->service_start)->addMonth($i)->format('m-Y')) }}" />
            </li>
          @endfor                
        </ul>
      </div>       
      <div class="col-md-4 dt-declaration-export text-end">
        @for ($i = 0; $i < $declarations->frequency; $i++)
            @php
              $tab_div_name = '';            
              if ($i === 0)
                $tab_div_name = 'first';
              elseif ($i === 1)
                $tab_div_name = 'second';
              elseif ($i === 2)
                $tab_div_name = 'third';
            @endphp

            <div class="{{ $tab_div_name }}-declaration-export {{ ($i === 0) ? '' : 'd-none' }}">
              <button type="button" id="btn-gs-refresh" class="btn btn-primary">Refresh Data</button>

              <button type="button" id="btn-control" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDeclarationControl">Control</button>

              @if($vatregmain->country == 'CH')
                @if(isset($from_currencies))
                  @if($from_currencies)
                    <button type="button" id="btn-convert-currency" class="btn btn-primary" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#currencyConvertModal-{{ $vat_reg_id }}">Convert</button>
                  @endif  
                @endif    
              @endif
            </div>
        @endfor
      </div>      
    </div>
   
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
      <div class="card shadow-none px-0">
        
        <div class="card-header border-bottom p-2">        
          <div class="dt-search-filter text-end align-middle">
          </div>
        </div>

        <div class="tab-content px-0">

          @for ($i = 0; $i < $declarations->frequency; $i++)
            @php
              $tab_div_name = '';            
              if ($i === 0)
                $tab_div_name = 'first';
              elseif ($i === 1)
                $tab_div_name = 'second';
              elseif ($i === 2)
                $tab_div_name = 'third';
            @endphp
            <div class="tab-pane fade {{ ($i === 0) ? 'show active' : '' }}" id="navs-declaration-{{ $tab_div_name }}" role="tabpanel">
              <table class="datatables-declarations datatables-{{ $tab_div_name }}-declarations table accordion" data-declaration_name="{{ $tab_div_name }}">     
                <thead>
                  <tr class="detail-control">
                    <th class="declaration-th-w20"></th>                  
                    <th class="declaration-th-w150 text-start align-top" colspan="2">Declarations</th>                      
                    <th class="declaration-th-w150 text-end align-top">Statistical value</th>
                    <th class="declaration-th-w150 text-end align-top" colspan="{{ ($vatregmain->country == 'CH') ? 4 : 3 }}">Net Amount</th>
                    <th class="declaration-th-w150 text-end align-top" colspan="{{ ($vatregmain->country == 'CH') ? 2 : 1 }}">Import VAT</th>
                    <th class="declaration-th-w150 text-end align-top" colspan="{{ ($vatregmain->country == 'CH') ? 2 : 1 }}">Duties</th>
                    <th class="declaration-th-w150 text-end align-top">VAT on duties</th>
                    <th class="declaration-th-w150 text-end align-top">Adjustment</th>                  
                    <th class="declaration-th-w150 text-end align-top">VAT on adjustment</th>                
                    <th class="text-center align-top">Action</th>
                  </tr>
                </thead>
              </table>    
            </div><!--/ navs-declaration-{{ $tab_div_name }}--> 
          @endfor
        </div>
         
      </div>
    </div>
  </div>

</div>


@include('_partials/_offcanvas/offcanvas-declaration-control')
@include('_partials/_offcanvas/offcanvas-declaration-filter')

@include('_partials/_modals/modal-declaration-invoice-disregard')
@include('_partials/_modals/modal-declaration-move-invoice-file')

@include('_partials/_modals/modal-declaration-cominvoice-rematch')
@include('_partials/_modals/modal-declaration-salesinvoice-move')

@include('_partials/_modals/modal-declaration-ftp-salesinvoice-edit')

@if($vatregmain->country == 'CH')
  @if(isset($from_currencies))
    @if($from_currencies)
      @include('_partials/_modals/modal-currency-convert')
    @endif
  @endif 
@endif

@endsection
