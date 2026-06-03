@extends('layouts/layoutMaster')

@section('title', ($vatreg->client->client_name) . ' - ' . (\Carbon\Carbon::parse($vatreg->service_start)->format('F Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods))

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

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
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

<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {  
    $(".card.invoices .sk-bounce").show();
    $(".card.invoices .card-datatable").hide(); 

    window.invoice_correct_datas = [];   
    window.invoice_wrong_datas = [];   
    window.invoice_managed_datas = [];   

    window.search_type = "{{ (request()->has('type')) ? request()->get('type') : '' }}";
    window.search_percentage = "{{ (request()->has('percentage')) ? request()->get('percentage') : '' }}";
    window.search_currency = "{{ (request()->has('currency')) ? request()->get('currency') : '' }}";

    var result = { 'invoices': {!! json_encode($invoices) !!} };    
    var invoice_datas = drawDtTable(result, 'invoice');
});
</script>
<script src="{{asset('js/dv-invoices-lazy.js')}}"></script>
@endsection

@section('content')

@php
$client = $vatreg->client;
$client_id = $client->client_id;
$vat_reg_id = $vatreg->id;

$vatregmain = $vatreg->vatregmain;
$clientapi = $vatregmain->clientapi;

$currency_code = ($clientapi) ? $clientapi->currency_code : '';
if($currency_code == '')
{
  $country = $vatreg->country;
  if($country == "DK")
    $currency_code = "DKK";      
  else if($country == "NO") 
    $currency_code = "NOK";
  else if($country == "SE") 
    $currency_code = "SEK";      
  else if($country == "GB")
    $currency_code = "GBP";      
  else if($country == "IN")  
    $currency_code = "INR";
  else if($country == "FR")  
    $currency_code = "EUR";
  else if($country == "CH")  
    $currency_code = "CHF";          
}
@endphp

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('company/' . $client_id)}}">{{ $client->client_name }}</a>/</span> {{ (\Carbon\Carbon::parse($vatreg->service_start)->format('F Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods) }}
  {{--<span class="float-end">Click <a href="javascript:;" class="btn-refresh bg-label-danger" data-vat_reg_id="{{ $vat_reg_id }}" title="Refresh">Refresh</a> to view the invoices</span>--}}

  <span id="invoice-loading" class="float-end text-danger d-none">Loading....</span>
</h4>

<!-- Ajax Sourced Server-side -->
<div class="card invoices" id="card-invoice-block">

  <!-- Bounce -->
  <div class="sk-bounce sk-primary sk-center">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div> 
  
  <div class="card-header p-0">    
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom">
      @if((request()->has('type')) && (request()->has('percentage')) && (request()->has('currency')))  
      <div class="col-md-8">
        <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
          <li class="nav-item">
            <button type="button" id="btn-invoice-correct" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-correct" aria-controls="navs-invoice-correct" aria-selected="true">Invoices <span class="alert-primary text-end fs-tiny p-1 mx-2"></span></button>
          </li>

          <li class="nav-item">
            <button type="button" id="btn-invoice-wrong" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-wrong" aria-controls="navs-invoice-wrong" aria-selected="false">Mismatched Invoices <span class="alert-danger text-end fs-tiny p-1 mx-2"></span></button>
          </li>

          <li class="nav-item">
            <button type="button" id="btn-invoice-managed" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-managed" aria-controls="navs-invoice-managed" aria-selected="false">Managed Invoices <span class="alert-warning text-end fs-tiny p-1 mx-2"></span></button>
          </li>
        </ul>
      </div>    
      <div class="col-md-4 dt-invoice-export text-end">
        <div class="correct-invoice-export"></div>
        <div class="wrong-invoice-export d-none">                    
          <button type="button" id="btn-convert-currency" class="btn btn-primary" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#currencyConvertModal-{{ $vat_reg_id }}" disabled="disabled">Convert</button>
        </div>
        <div class="managed-invoice-export d-none"></div>
      </div>
      @else      
      <div class="col-md-7">
        <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
          <li class="nav-item">
            <button type="button" id="btn-invoice-correct" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-correct" aria-controls="navs-invoice-correct" aria-selected="true">Invoices <span class="alert-primary text-end fs-tiny p-1 mx-2"></span></button>
          </li>

          <li class="nav-item">
            <button type="button" id="btn-invoice-wrong" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-wrong" aria-controls="navs-invoice-wrong" aria-selected="false">Mismatched Invoices <span class="alert-danger text-end fs-tiny p-1 mx-2"></span></button>
          </li>

          <li class="nav-item">
            <button type="button" id="btn-invoice-managed" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-managed" aria-controls="navs-invoice-managed" aria-selected="false">Managed Invoices <span class="alert-warning text-end fs-tiny p-1 mx-2"></span></button>
          </li>
        </ul>
      </div> 
      <div class="col-md-2 dt-invoice-type">
        <div class="correct-invoice-type"></div>
        <div class="wrong-invoice-type d-none"></div>
      </div>  
      <div class="col-md-3 dt-invoice-export text-end">
        <div class="correct-invoice-export"></div>
        <div class="wrong-invoice-export d-none">          
          <button type="button" id="btn-convert-currency" class="btn btn-primary" data-vat_reg_id="{{ $vat_reg_id }}" data-bs-toggle="modal" data-bs-target="#currencyConvertModal-{{ $vat_reg_id }}" disabled="disabled">Convert</button>
        </div>
        <div class="managed-invoice-export d-none"></div>
      </div>
      @endif        
    </div>

    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
      <div class="card shadow-none px-0">
        <div class="card-header border-bottom p-2"><!-- pt-1-1 pb-4-1 -->
          <!-- <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
              <li class="nav-item">
                <button type="button" id="btn-invoice-correct" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-correct" aria-controls="navs-invoice-correct" aria-selected="true">Invoices <span class="bg-primary text-white text-end fs-tiny p-1 mx-2"></span></button>
              </li>

              <li class="nav-item">
                <button type="button" id="btn-invoice-wrong" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-wrong" aria-controls="navs-invoice-wrong" aria-selected="false">Mismatched Invoices <span class="bg-danger text-white text-end fs-tiny p-1 mx-2"></span></button>
              </li>
          </ul>
          <div class="dt-search-filter w-25 float-end align-middle my-n4-1">
          </div> -->

          <div class="dt-search-filter text-end align-middle">
          </div>
        </div>

        <div class="tab-content px-0 pb-0">
            <div class="tab-pane fade show active" id="navs-invoice-correct" role="tabpanel">
              <div id="top-scroll-navs-invoice-correct" class="dt-top-scroll">
                <div class="dt-top-scroll-inner"></div>
              </div>

              <table class="datatables-correct-invoices table">     
                  <thead class="bg-label-primary">
                    <tr>
                      <th class="invoice-th-w25"></th>
                      <th class="invoice-th-w50">No.</th>
                      <th>Invoice Type</th>
                      <th class="invoice-th-w100">Tax code</th>
                      <th class="invoice-th-w200">Invoice date</th>
                      <th class="invoice-th-w200">Account number</th>
                      <th class="invoice-th-w200">Invoice number</th>
                      <th class="invoice-th-w200">Currency code</th>
                      <th class="invoice-th-w250">Total NET (invoice currency)</th>
                      <th class="invoice-th-w100">VAT rate</th>
                      <th class="invoice-th-w250">Total VAT (invoice currency)</th>
                      <th class="invoice-th-w250">Total GROSS (invoice currency)</th>
                      <th class="invoice-th-w200">Local currency code</th>
                      <th class="invoice-th-w200">Exchange rate</th>
                      <th class="invoice-th-w250">Total NET (local currency)</th>
                      <th class="invoice-th-w250">Total VAT (local currency)</th>
                      <th class="invoice-th-w250">Total GROSS (local currency)</th>
                      <th>N</th>
                      <th>O</th>
                      <th>P</th>
                      <th>Q</th>
                      <th class="invoice-th-w200">Name</th>
                      <th class="invoice-th-w200">VAT number (if applicable)</th>
                      <th class="invoice-th-w200">Street</th>
                      <th class="invoice-th-w200">House and office no.</th>
                      <th class="invoice-th-w200">City</th>
                      <th class="invoice-th-w100">Postal code</th>
                      <th class="invoice-th-w200">Country code</th>
                      <th class="invoice-th-w100">PDF</th>
                    </tr>
                  </thead>
              </table>
            </div><!--/ navs-invoice-correct-->
            <div class="tab-pane fade" id="navs-invoice-wrong" role="tabpanel">
              <div id="top-scroll-navs-invoice-wrong" class="dt-top-scroll">
                <div class="dt-top-scroll-inner"></div>
              </div>

              <table class="datatables-wrong-invoices table">     
                  <thead class="bg-label-primary">
                    <tr>
                      <th class="invoice-th-w25"></th>
                      <th class="invoice-th-w50">No.</th>
                      <th>Invoice Type</th>
                      <th class="invoice-th-w100">Tax code</th>
                      <th class="invoice-th-w200">Invoice date</th>
                      <th class="invoice-th-w200">Account number</th>
                      <th class="invoice-th-w200">Invoice number</th>                      
                      <th class="invoice-th-w200">Currency code</th>
                      <th class="invoice-th-w250">Total NET (invoice currency)</th>
                      <th class="invoice-th-w100">VAT rate</th>
                      <th class="invoice-th-w250">Total VAT (invoice currency)</th>
                      <th class="invoice-th-w250">Total GROSS (invoice currency)</th>
                      <th class="invoice-th-w200">Local currency code</th>
                      <th class="invoice-th-w200">Exchange rate</th>
                      <th class="invoice-th-w250">Total NET (local currency)</th>
                      <th class="invoice-th-w250">Total VAT (local currency)</th>
                      <th class="invoice-th-w250">Total GROSS (local currency)</th>
                      <th>N</th>
                      <th>O</th>
                      <th>P</th>
                      <th>Q</th>
                      <th class="invoice-th-w200">Name</th>
                      <th class="invoice-th-w200">VAT number (if applicable)</th>
                      <th class="invoice-th-w200">Street</th>
                      <th class="invoice-th-w200">House and office no.</th>
                      <th class="invoice-th-w200">City</th>
                      <th class="invoice-th-w100">Postal code</th>
                      <th class="invoice-th-w200">Country code</th>
                      <th class="invoice-th-w100">PDF</th>
                    </tr>
                  </thead>
              </table>    
            </div><!--/ navs-invoice-wrong-->
            <div class="tab-pane fade" id="navs-invoice-managed" role="tabpanel">
              <div id="top-scroll-navs-invoice-managed" class="dt-top-scroll">
                <div class="dt-top-scroll-inner"></div>
              </div>
              <table class="datatables-managed-invoices table">     
                  <thead class="bg-label-primary">
                    <tr>
                      <th class="invoice-th-w25"></th>
                      <th class="invoice-th-w50">No.</th>
                      <th>Invoice Type</th>
                      <th class="invoice-th-w100">Tax code</th>
                      <th class="invoice-th-w200">Invoice date</th>
                      <th class="invoice-th-w200">Account number</th>
                      <th class="invoice-th-w200">Invoice number</th>
                      <th class="invoice-th-w200">Currency code</th>
                      <th class="invoice-th-w250">Total NET (invoice currency)</th>
                      <th class="invoice-th-w100">VAT rate</th>
                      <th class="invoice-th-w250">Total VAT (invoice currency)</th>
                      <th class="invoice-th-w250">Total GROSS (invoice currency)</th>
                      <th class="invoice-th-w200">Local currency code</th>
                      <th class="invoice-th-w200">Exchange rate</th>
                      <th class="invoice-th-w250">Total NET (local currency)</th>
                      <th class="invoice-th-w250">Total VAT (local currency)</th>
                      <th class="invoice-th-w250">Total GROSS (local currency)</th>
                      <th>N</th>
                      <th>O</th>
                      <th>P</th>
                      <th>Q</th>
                      <th class="invoice-th-w200">Name</th>
                      <th class="invoice-th-w200">VAT number (if applicable)</th>
                      <th class="invoice-th-w200">Street</th>
                      <th class="invoice-th-w200">House and office no.</th>
                      <th class="invoice-th-w200">City</th>
                      <th class="invoice-th-w100">Postal code</th>
                      <th class="invoice-th-w200">Country code</th>
                      <th class="invoice-th-w100">PDF</th>
                    </tr>
                  </thead>
              </table>
            </div><!--/ navs-invoice-managed-->
        </div>
      </div>
    </div>
  </div>

  

  <div class="card-datatable"> 
    <input type="hidden" name="client_id" id="client_id" value="{{ $client_id }}">
    <input type="hidden" name="vat_reg_id" id="vat_reg_id" value="{{ $vat_reg_id }}">
    <input type="hidden" name="currency_code" id="currency_code" value="{{ $currency_code }}">
    <input type="hidden" name="client_country" id="client_country" value="{{ $vatreg->country }}">
    <input type="hidden" name="client_api_name" id="client_api_name" value="{{ ($clientapi) ? $clientapi->api_name : '' }}">
    {{--<input type="hidden" name="is_reverse" id="is_reverse" value="{{ ($clientapi) ? $clientapi->is_reverse : '' }}">--}}

    <input type="hidden" name="invoice_refresh" id="invoice_refresh" value="{{ isset($refresh) ? (($refresh) ? 1 : 0) : 0 }}">
   
    <input type="hidden" name="invoice_period" id="invoice_period" value="{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('F') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('F')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('F')) }}">
    <input type="hidden" name="invoice_year" id="invoice_year" value="{{ \Carbon\Carbon::parse($vatreg->service_start)->format('Y') }}">   
  </div>
  <!--/ Ajax Sourced Server-side -->
</div>

@include('_partials/_offcanvas/offcanvas-column-settings')
@include('_partials/_offcanvas/offcanvas-invoice-filter')

@include('_partials/_modals/modal-currency-convert')
@include('_partials/_modals/modal-invoice-disregard')

@endsection
