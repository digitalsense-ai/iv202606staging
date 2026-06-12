@extends('layouts/layoutMaster')

@section('title', 'Search PDF')

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
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
@endsection

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
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

<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>
@endsection

@section('page-script')
<script type="text/javascript">
    window.EchoConfig = {
        pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
        pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    };
</script>

<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {  
    $(".card.analyzepdf .sk-bounce").show();
    $(".card.analyzepdf .card-datatable").hide(); 

    window.analyzepdf_commercial_invoice_datas = [];
    window.analyzepdf_sales_invoice_datas = [];       
    window.analyzepdf_declaration_datas = [];
    
    var result = { 'analyzepdfs': {!! json_encode($analyzepdfs) !!}, 'vatregmains': {!! json_encode($vatregmains) !!} };    
    var analyzepdf_datas = drawDtTable(result, 'analyzepdf_search');  
});
</script>
<script src="{{asset('js/dv-analyze-pdf.js')}}"></script>
<script src="{{asset('js/dv-analyze-pdf-search.js')}}"></script>
@endsection

@section('content')		

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ route('analyze.pdf.index')}}">{{ __('OCR Capture') }}</a>/{{ __('Search') }}</span>
</h4>

@php
    $end_table = 2;
@endphp
    {{-- Search Data's --}}
    @if($analyzepdfs)
    <!-- Ajax Sourced Server-side -->
    <div class="card analyzepdfsearch mt-4">

      <!-- Bounce -->
      <div class="sk-bounce sk-primary sk-center">
        <div class="sk-bounce-dot"></div>
        <div class="sk-bounce-dot"></div>
      </div>

      <h5 class="m-0 p-3">Search Data's</h5>

      <div class="card-header p-0">    
        <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom">         
          <div class="col-md-8">
            <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">

              @for ($i = 0; $i < $end_table; $i++)
                @php
                    $tab_div_id = '';
                    $tab_div_name = '';
                    $tab_span_class_name = '';
                    if ($i === 0)
                    {
                        $tab_div_id = 'commercial-invoice';
                        $tab_div_name = 'Commercial Invoices';
                        $tab_span_class_name = 'primary';
                    }
                    elseif ($i === 1)
                    {
                        $tab_div_id = 'sales-invoice';
                        $tab_div_name = 'Sales Invoices';
                        $tab_span_class_name = 'warning';
                    }
                    elseif ($i === 2)
                    {
                        $tab_div_id = 'declaration';
                        $tab_div_name = 'Declaration';
                        $tab_span_class_name = 'danger';
                    }
                @endphp

                <li class="nav-item">
                  <button type="button" id="btn-analyzepdfsearch-{{ $tab_div_id }}" class="nav-link {{ ($i === 0) ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-analyzepdfsearch-{{ $tab_div_id }}" aria-controls="navs-analyzepdfsearch-{{ $tab_div_id }}" aria-selected="{{ ($i === 0) ? 'true' : 'false' }}"> {{ ucfirst($tab_div_name) }}<span class="alert-{{ $tab_span_class_name }} text-end fs-tiny p-1 mx-2"></span></button>                 
                </li>
              @endfor                
            </ul>
          </div>  
         
          <div class="col-md-4 dt-analyzepdfsearch-export text-end">
            @for ($i = 0; $i < $end_table; $i++)
                @php
                  $tab_div_id = '';            
                  if ($i === 0)
                    $tab_div_id = 'commercial-invoice';
                  elseif ($i === 1)
                    $tab_div_id = 'sales-invoice';
                  elseif ($i === 2)
                    $tab_div_id = 'declaration';
                @endphp

                <div class="{{ $tab_div_id }}-analyzepdfsearch-export {{ ($i === 0) ? '' : 'd-none' }}"></div>
            @endfor
          </div> 
         
        </div>
       
        <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
          <div class="card shadow-none px-0">
            
            <div class="card-header border-bottom p-2">        
              <div class="dt-search-filter text-end align-middle">
                <div class="dt-dropdown-filter w-auto d-inline-block">
                    <div class="w-auto d-inline-block me-1 client_name"></div>                    
                </div>
              </div>
            </div>

            <div class="tab-content p-0 pb-4">

              @for ($i = 0; $i < $end_table; $i++)
                @php
                  $tab_div_id = '';            
                  if ($i === 0)
                    $tab_div_id = 'commercial-invoice';
                  elseif ($i === 1)
                    $tab_div_id = 'sales-invoice';
                  elseif ($i === 2)
                    $tab_div_id = 'declaration';
                @endphp
                <div class="tab-pane fade {{ ($i === 0) ? 'show active' : '' }}" id="navs-analyzepdfsearch-{{ $tab_div_id }}" role="tabpanel">
                  <div id="top-scroll-navs-analyzepdfsearch-{{ $tab_div_id }}" class="dt-top-scroll">
                    <div class="dt-top-scroll-inner"></div>
                  </div>

                  <table class="datatables-analyzepdfsearch datatables-{{ $tab_div_id }}-analyzepdfsearch table" data-analyzepdfsearch_name="{{ $tab_div_id }}">         

                    <thead class="bg-label-primary">
                        <tr>
                            <th>Sl. No.</th>           
                            <th>Client No.</th>
                            <th>Client Name</th>
                            <th>Invoice No.</th>
                            <th>Invoice Date</th>
                            <th>Currency</th>
                            @if ($i === 1)
                                <th>Credit Note</th>
                            @endif
                            <th>Net Amount</th>
                            @if ($i === 1)
                                <th>VAT Rate</th>
                                <th>VAT Amount</th>
                                <th>Variance</th>
                                <th>Freight</th>
                                <th>Discount Amount</th>
                                <th>Total Amount</th>                           
                            @endif
                            @if ($i === 0)
                                <th>Sales Invoices</th>
                            @endif
                            <th>Fetch Date</th>     
                            <th>Actions</th>
                        </tr>
                    </thead>

                    {{--                
                    <thead class="bg-label-primary">
                        <tr>
                            <th class="invoice-th-w50">Sl. No.</th>           
                            <th class="invoice-th-w100">Client No.</th>
                            <th class="invoice-th-w150">Client Name</th>
                            <th class="invoice-th-w100">Invoice No.</th>
                            <th class="invoice-th-w100">Invoice Date</th>
                            <th class="invoice-th-w50">Currency</th>
                            @if ($i === 1)
                                <th class="invoice-th-w50">Credit Note</th>
                            @endif
                            <th class="invoice-th-w100">Net Amount</th>
                            @if ($i === 1)
                                <th class="invoice-th-w50">VAT Rate</th>
                                <th class="invoice-th-w100">VAT Amount</th>
                                <th class="invoice-th-w100">Variance</th>
                                <th class="invoice-th-w100">Freight</th>
                                <th class="invoice-th-w100">Discount Amount</th>
                                <th class="invoice-th-w100">Total Amount</th>                           
                            @endif
                            @if ($i === 0)
                                <th class="invoice-th-w250">Sales Invoices</th>
                            @endif
                            <th class="invoice-th-w150">Fetch Date</th>     
                            <th class="invoice-th-w50">Actions</th>
                        </tr>
                    </thead>
                    --}}                   
                  </table>    
                </div><!--/ navs-analyzepdfsearch-{{ $tab_div_id }}--> 
              @endfor
            </div>
             
          </div>
        </div>
      </div>

    </div>
    @endif

@include('_partials/_offcanvas/offcanvas-analyzepdf-form')
@include('_partials/_offcanvas/offcanvas-analyzepdf-filter')

@endsection