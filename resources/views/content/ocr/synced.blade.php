@extends('layouts/layoutMaster')

@section('title', 'Synced PDF')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/css/scroller.dataTables.min.css')}}" />
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

<script type="text/javascript" language="javascript" src="{{asset('assets/js/dataTables.scroller.min.js')}}"></script>
<script src="{{asset('assets/js/xlsx.min.js')}}"></script>

<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
@endsection

@section('page-script')
<script type="text/javascript">
  $(".card.analyzepdfsynced .sk-bounce").show();
  
    window.EchoConfig = {
        pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
        pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    };
</script>
<script src="{{asset('js/dv-common.js')}}"></script>
<script src="{{asset('js/dv-analyze-pdf-manual-input.js')}}"></script>
<script src="{{asset('js/dv-analyze-pdf-synced.js')}}"></script>
@endsection

@section('content')		

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ route('analyze.pdf.index')}}">{{ __('OCR Capture') }}</a>/{{ __('Synced DB') }}</span>

  <a class="btn btn-primary btn-sync-db float-end" href="javascript:;">Sync DB</a>
</h4>

@php
    $end_table = 2;
@endphp
    {{-- Synced Data's --}}
    
    <!-- Ajax Sourced Server-side -->
    <div class="card analyzepdfsynced mt-4">

      <!-- Bounce -->
      <div class="sk-bounce sk-primary sk-center">
        <div class="sk-bounce-dot"></div>
        <div class="sk-bounce-dot"></div>
      </div>

      <!-- <h5 class="m-0 p-3">Synced Data's</h5> -->
      <div class="d-flex align-items-center gap-2 p-3">
        <h5 class="m-0">Synced Data's</h5>
        <span class="text-danger fs-6">
          <i class="bx bx-filter-alt me-1"></i>
          Use Filter to check data
        </span>
      </div>


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
                        $tab_span_class_name = 'primary';
                    }
                    elseif ($i === 2)
                    {
                        $tab_div_id = 'declaration';
                        $tab_div_name = 'Declaration';
                        $tab_span_class_name = 'primary';
                    }
                @endphp

                <li class="nav-item">
                  <button type="button" id="btn-analyzepdfsynced-{{ $tab_div_id }}" class="nav-link {{ ($i === 0) ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-analyzepdfsynced-{{ $tab_div_id }}" aria-controls="navs-analyzepdfsynced-{{ $tab_div_id }}" aria-selected="{{ ($i === 0) ? 'true' : 'false' }}"> {{ ucfirst($tab_div_name) }}<span class="alert-{{ $tab_span_class_name }} text-end fs-tiny p-1 mx-2"></span></button>                 
                </li>
              @endfor                
            </ul>
          </div>  
         
          <div class="col-md-4 dt-analyzepdfsynced-export text-end">
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

                <div class="{{ $tab_div_id }}-analyzepdfsynced-export {{ ($i === 0) ? '' : 'd-none' }}"></div>
            @endfor
          </div> 
         
        </div>
       
        <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
          <div class="card shadow-none px-0">
            
            <div class="card-header border-bottom p-2">        
              <div class="dt-synced-filter text-end align-middle">
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
                <div class="tab-pane fade {{ ($i === 0) ? 'show active' : '' }}" id="navs-analyzepdfsynced-{{ $tab_div_id }}" role="tabpanel">
                  <div id="top-scroll-navs-analyzepdfsynced-{{ $tab_div_id }}" class="dt-top-scroll">
                    <div class="dt-top-scroll-inner"></div>
                  </div>

                  <table class="datatables-analyzepdfsynced datatables-{{ $tab_div_id }}-analyzepdfsynced table" data-analyzepdfsynced_name="{{ $tab_div_id }}">         

                    <thead class="bg-label-primary">
                        <tr>
                            <th>Sl. No.</th>           
                            <th data-field="client_no">Client No.</th>
                            <th data-field="client_name">Client Name</th>
                            <th data-field="invoice_no">Invoice No.</th>
                            <th data-field="invoice_date">Invoice Date</th>
                            <th data-field="currency">Currency</th>   
                            @if ($i === 0)
                                <th data-field="net_amount">Net Amount</th>
                                <th>Sales Invoices</th>
                            @endif                         
                            @if ($i === 1)
                                <th data-field="credit_note">Credit Note</th>
                                <th data-field="calc_net_amount">Net Amount</th>
                                <th data-field="net_amount">Net Goods Amount</th>
                                <th data-field="vat_rate">VAT Rate</th>
                                <th data-field="vat_amount">VAT Amount</th>
                                <th data-field="variance">Variance</th>
                                <th data-field="additional_amount">Freight</th>
                                <th data-field="adjustment_amount">Discount Amount</th>
                                <th data-field="total_amount">Total Amount</th>

                                <th data-field="exchange_currency">Ex. Currency</th> 
                                <th data-field="exchange_rate">Ex. Rate</th>                                
                                <th data-field="exchange_net_amount">Ex. Net Amount</th>  
                                <th data-field="exchange_vat_amount">Ex. VAT Amount</th> 
                                <th data-field="exchange_total_amount">Ex. Total Amount</th>                       
                            @endif                            
                            <th>Fetch Date</th>     
                            <th>Actions</th>
                        </tr>
                    </thead>                      
                  </table>    
                </div><!--/ navs-analyzepdfsynced-{{ $tab_div_id }}--> 
              @endfor
            </div>
             
          </div>
        </div>
      </div>

    </div>

@php
    $issearch =  false;
    $issynced =  true;
@endphp    
@include('_partials/_offcanvas/offcanvas-analyzepdf-form')    
@include('_partials/_offcanvas/offcanvas-analyzepdf-filter')

@endsection