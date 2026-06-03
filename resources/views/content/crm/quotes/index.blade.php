@extends('layouts/layoutMaster')

@section('title', 'CRM - Quotes')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {  
    $(".card.quote .sk-bounce").show();
    $(".card.quote .card-datatable").hide(); 

    window.crm_active_quote_datas = [];   
    window.crm_negotiate_quote_datas = [];
    window.crm_approved_quote_datas = [];
    window.crm_rejected_quote_datas = [];
       
    var result = { 'quotes': {!! json_encode($quotes) !!} };    
    var crm_quote_datas = drawDtTable(result, 'crm_quote');
});
</script>

<script src="{{asset('js/dv-crm.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('crm/quotes') }}">{{ __('Quotes') }}</a>/</span> {{ __('List') }}  
</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center quote-page">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

{{-- Quotes Management --}}
@if($quotes)
<!-- Ajax Sourced Server-side -->
<div class="card quotes mt-4">

  <!-- Bounce -->
  <div class="sk-bounce sk-primary sk-center">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div>

  <h5 class="m-0 p-3">Quotes Management</h5>

  <div class="card-header p-0">    
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom">         
      <div class="col-md-8">
        <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">

          @for ($i = 0; $i < 4; $i++)
            @php
                $tab_div_name = '';
                $tab_span_class_name = '';
                if ($i === 0)
                {
                    $tab_div_name = 'active';
                    $tab_span_class_name = 'primary';
                }
                elseif ($i === 1)
                {
                    $tab_div_name = 'negotiate';
                    $tab_span_class_name = 'warning';
                }
                elseif ($i === 2)
                {
                    $tab_div_name = 'approved';
                    $tab_span_class_name = 'success';
                }
                elseif ($i === 3)
                {
                    $tab_div_name = 'rejected';
                    $tab_span_class_name = 'danger';
                }

                $tab_active = 0;
                if(isset($tabName))
                {
                  if($tabName == 'approved')
                    $tab_active = 2;
                  elseif($tabName == 'rejected')
                    $tab_active = 3;  
                }
            @endphp

            <li class="nav-item">
              <button type="button" id="btn-quote-{{ $tab_div_name }}" class="nav-link {{ ($i === $tab_active) ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-quote-{{ $tab_div_name }}" aria-controls="navs-quote-{{ $tab_div_name }}" aria-selected="{{ ($i === 0) ? 'true' : 'false' }}"> {{ ucfirst($tab_div_name) }}<span class="alert-{{ $tab_span_class_name }} text-end fs-tiny p-1 mx-2"></span></button>                 
            </li>
          @endfor                
        </ul>
      </div>  
     
      <div class="col-md-4 dt-quote-export text-end">
        @for ($i = 0; $i < 4; $i++)
            @php
              $tab_div_name = '';            
              if ($i === 0)
                $tab_div_name = 'active';
              elseif ($i === 1)
                $tab_div_name = 'negotiate';
              elseif ($i === 2)
                $tab_div_name = 'approved';
            elseif ($i === 3)
                $tab_div_name = 'rejected';
            @endphp

            <div class="{{ $tab_div_name }}-quote-export {{ ($i === 0) ? '' : 'd-none' }}">         
            </div>
        @endfor
      </div> 
     
    </div>
   
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
      <div class="card shadow-none px-0">
        
        <div class="card-header border-bottom p-2">        
          <div class="dt-search-filter text-end align-middle">
            <!-- <div class="dt-dropdown-filter w-auto d-inline-block">
                <div class="w-auto d-inline-block me-2 invoice_type"></div>
                <div class="w-auto d-inline-block me-2 invoice_status"></div>
            </div> -->
          </div>          
        </div>

        <div class="tab-content p-0 pb-4">

          @for ($i = 0; $i < 4; $i++)
            @php
              $tab_div_name = '';                
              if ($i === 0)
              {
                $tab_div_name = 'active';
                $tab_active = 0;
              }
              elseif ($i === 1)
                $tab_div_name = 'negotiate';
              elseif ($i === 2)
                $tab_div_name = 'approved';
              elseif ($i === 3)
              {
                $tab_div_name = 'rejected';  
              }

              $tab_active = 0;
              if(isset($tabName))
              {
                if($tabName == 'approved')
                  $tab_active = 2;
                elseif($tabName == 'rejected')
                  $tab_active = 3;  
              }
            @endphp
            <div class="tab-pane fade {{ ($i === $tab_active) ? 'show active' : '' }}" id="navs-quote-{{ $tab_div_name }}" role="tabpanel">
              <table class="datatables-quote datatables-{{ $tab_div_name }}-quote table" data-quote_name="{{ $tab_div_name }}">                         
                <thead class="bg-label-primary">
                    <tr>
                        <!-- <th></th>
                        <th>Sl. No.</th>   -->         
                        <th>Company</th>
                        <th>Package</th>
                        <th>Version</th>
                        <th>Price</th>
                        <!-- <th>Registration</th>
                        <th>Monthly</th>
                        <th>Yearly</th> -->
                        <th>Created at</th>                           
                        <th>Actions</th>
                    </tr>
                </thead>                   
              </table>    
            </div><!--/ navs-quote-{{ $tab_div_name }}--> 
          @endfor
        </div>
         
      </div>
    </div>
  </div>

</div>
@endif

@php
  $module_type = 'quote';
@endphp 
@include('_partials/_modals/modal-crm-reminder')
@endsection