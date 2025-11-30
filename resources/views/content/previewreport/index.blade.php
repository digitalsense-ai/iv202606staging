@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutConfirm')

@section('title', 'Preview Report')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')
{{-- Page Css files --}}
<!-- <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}"> -->
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-faq.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-preview-report.js')}}"></script>
@endsection

@section('content')

@php
  $client = $vatreg->client;      
@endphp

<div class="preview-report row mt-4">
  
  <!-- FAQ's -->
  <div class="col-lg-9 col-md-8 col-12">
    <div class="tab-content py-0">
      <div class="tab-pane fade" id="cover" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-credit-card fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">COVER</span>
            </h5>
            <span>Cover Page</span>
          </div>
        </div>
 
        <div id="accordionCover" class="accordion accordion-header-primary">
          <div class="card accordion-item active">            
            <div id="accordionCover" class="accordion-collapse rounded-0 intravat-bg text-center text-white collapse show h-px-800">
              <div class="accordion-body">
                <span>Import Reconciliation</span>
              
                <div class="app-brand justify-content-end">
                  <a href="{{url('/')}}" class="app-brand-link">                    
                    <span class="app-brand-text demo h3 mb-0 fw-bold text-end">@include('_partials.macroswhite')</span>
                  </a>
                </div> 

                <div class="cover-content">  
                  <h1 class="text-white my-5">Import Reconciliation</h1>
                  <h2 class="text-white fw-normal">For</h2>
                  <h2 class="text-white fw-normal">{{ $client->client_name }}</h2>
                  <h2 class="text-white fw-normal">Cvr. no.{{ $client->vatno }}</h2>
                  <h2 class="text-white fw-normal">{{ \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                      \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}
                  </h2>  
                </div>  
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade show active" id="declaration" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-cart fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">Declaration</span>
            </h5>
            <span>Numbers...</span>
          </div>
        </div>       
        <div id="accordionDeclaration" class="accordion accordion-header-primary accordion-page">
          <div class="card accordion-item">            
            <div id="accordionDeclaration" class="accordion-collapse rounded-0 collapse show">
              <div class="accordion-body">               
                @include('_partials/_content/_previewreport/header-view')

                @php
                  $importvatfiles = $vatreg->importvatfiles;
                @endphp
                <div class="row {{ (count($importvatfiles) > 15) ? '' : 'vh-100' }} mb-5 p-sm-3 p-0">                  
                  @include('_partials/_content/_previewreport/declaration-view')
                </div>                

                @php
                  $page_no = 1;
                @endphp
                @include('_partials/_content/_previewreport/footer-view')
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="cominvoice" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-revision fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0"><span class="align-middle">Com. Invoice</span></h5>
            <span>Numbers...</span>
          </div>
        </div>
        <div id="accordionCominvoice" class="accordion accordion-header-primary">
          <div class="card accordion-item">            
            <div id="accordionCominvoice" class="accordion-collapse rounded-0 collapse show">
              <div class="accordion-body">                
                @include('_partials/_content/_previewreport/header-view')

                @php
                  $importreconciliationcominvoices = $vatreg->importreconciliationcominvoices;
                @endphp
                <div class="row {{ (count($importreconciliationcominvoices) > 15) ? '' : 'vh-100' }} mb-5 p-sm-3 p-0">
                  
                  @include('_partials/_content/_previewreport/cominvoice-view')
                </div>
                
                @php
                  $page_no = 2;
                @endphp
                @include('_partials/_content/_previewreport/footer-view')
              </div>
            </div>
          </div>
        </div>
      </div>
      @if(count($vatreg->vatreturns) > 0)
      <div class="tab-pane fade" id="vatreturn" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-box fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">VAT return</span>
            </h5>
            <span>Numbers...</span>
          </div>
        </div>
        <div id="accordionVatreturn" class="accordion accordion-header-primary">
          <div class="card accordion-item">            
            <div id="accordionVatreturn" class="accordion-collapse rounded-0 collapse show">
              <div class="accordion-body">               
                @include('_partials/_content/_previewreport/header-view')
                
                <div class="row mb-5 p-sm-3 p-0">
                  <div class="my-3" id="load-previewreport-vatreturns-footer"></div>
                  @php
                    $tab_name = "previewreport";
                    $vatreturns = $vatreg->vatreturns;

                    $pivs_files = ($vatreg->pivs) ? $vatreg->pivs : [];                     
                    $c79_documents = ($vatreg->c79) ? $vatreg->c79: [];  
                  @endphp
                  @include('_partials/_content/_vatreturn/vatreturn-overview-lazy')
                </div>
               
                @php
                  $page_no = 3;
                @endphp
                @include('_partials/_content/_previewreport/footer-view')
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
      <div class="tab-pane fade" id="back" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-camera fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">BACK</span>
            </h5>
            <span>Back Page</span>
          </div>
        </div>        
        <div id="accordionBack" class="accordion accordion-header-primary">
          <div class="card accordion-item">            
            <div id="accordionBack" class="accordion-collapse rounded-0 intravat-bg text-center text-white collapse show h-px-800">
              <div class="accordion-body">
                <span>Import Reconciliation</span>
              
                <div class="app-brand justify-content-end">
                  <a href="{{url('/')}}" class="app-brand-link">                    
                    <span class="app-brand-text demo h3 mb-0 fw-bold text-end">@include('_partials.macroswhite')</span>
                  </a>
                </div> 
                              
                <div class="cover-content">                   
                  <h2 class="text-white fw-normal">~~~~~ End ~~~~~</h2>  
                </div>  
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /FAQ's -->

  <!-- Navigation -->
  <div class="col-lg-3 col-md-4 col-12 mb-md-0 mb-3">
    <div class="d-flex justify-content-between flex-column mb-2 mb-md-0">
      <ul class="nav nav-align-left nav-pills flex-column">
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cover" disabled="disabled">
            <i class="bx bx-credit-card faq-nav-icon me-1"></i>
            <span class="align-middle">COVER</span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#declaration">
            <i class='bx bx-shopping-bag faq-nav-icon me-1'></i>
            <span class="align-middle">Declaration</span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cominvoice">
            <i class='bx bx-rotate-left faq-nav-icon me-1'></i>
            <span class="align-middle">Com. Invoice</span>
          </button>
        </li>       
        <li class="nav-item">
          <button class="nav-link {{ (count($vatreg->vatreturns) > 0) ? '' : 'disabled'}}" data-bs-toggle="tab" data-bs-target="#vatreturn">
            <i class='bx bx-cube faq-nav-icon me-1'></i>
            <span class="align-middle">VAT return</span>
          </button>
        </li>       
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#back" disabled="disabled">
            <i class='bx bx-cog faq-nav-icon me-1'></i>
            <span class="align-middle">BACK</span>
          </button>
        </li>
      </ul>
      <button class="btn btn-label-secondary w-100 btn-export-pdf-previewreport" data-vat_reg_id="{{ $vat_reg_id }}">
        <i class='bx bx-up-arrow-circle me-1'></i>
        <span class="align-middle">Export to PDF</span>
      </button>     
    </div>
  </div>
  <!-- /Navigation -->
</div>
@endsection