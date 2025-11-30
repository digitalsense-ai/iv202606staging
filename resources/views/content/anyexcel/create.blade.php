@extends('layouts/layoutMaster')

@section('title', 'Any Excel Template - Create')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.css')}}" />
<!-- <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" /> -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.js')}}"></script>
<!-- <script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js')}}"></script>

<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')

<script src="{{asset('js/dv-anyexcel-template.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Any Excel Template/</span> Create</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<!-- Any Excel Template List Table -->
<div class="row">  
  <!-- Modern Wizard -->
  <div class="col-12 mb-4">
    <!-- <small class="text-light fw-medium mt-2">Basic</small> -->
    <div id="anyexcel-wizard-validation" class="bs-stepper wizard-modern mt-2">
      <div class="bs-stepper-header">
        <div class="step" data-target="#basic-details-validation">
          <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">1</span>
            <span class="bs-stepper-label">Basic Details</span>
          </button>
        </div>
        <div class="line"></div>
        <div class="step" data-target="#file-upload-mapping-validation">
          <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">2</span>
            <span class="bs-stepper-label">File Upload and Mapping</span>
          </button>
        </div>
        <div class="line"></div>
        <div class="step" data-target="#overview-validation">
          <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">3</span>
            <span class="bs-stepper-label">Overview</span>
          </button>
        </div>
      </div>
      <div class="bs-stepper-content">
        <form id="anyexcel-wizard-validation-form" onSubmit="return false">
          @csrf 
          <!-- Basic Details -->
          <div id="basic-details-validation" class="content">
            <div class="content-header mb-3">
              <h6 class="mb-0">Basic Details</h6>
              <small>Enter Template Details.</small>
            </div>
            <div class="row g-3">
              <div class="col-sm-4">
                <label class="form-label" for="client_id">Client Name</label>                
                <select id="client_id" class="form-select" data-allow-clear="true" name="client_id" required>
                  <option value="">--Select Company--</option>
                  @foreach($clients as $key=>$client)
                    @foreach($client->vatregmain as $vrmain) 
                      <option value="{{ $client->id }}" {{ isset($anyexceltemplate) ? (($anyexceltemplate->client_id == $client->id) ? 'selected' : '') : (isset($vatreturnfile) ? ( ($vatreturnfile->anyexceltemplate) ? (($vatreturnfile->anyexceltemplate->client_id  == $client->id) ? 'selected' : '' ) : '') : '') }}>{{ $client->client_name . ' - ' . $vrmain->country }}</option>
                    @endforeach   
                  @endforeach 
                </select>
                <input type="hidden" name="client_name" id="client_name" value="{{ (isset($vatreturnfile) ? ( ($vatreturnfile->vatreg) ? (($vatreturnfile->vatreg->client) ? $vatreturnfile->vatreg->client->client_name : '') : '') : (isset($anyexceltemplate) ? $anyexceltemplate->client->client_name : '')) }}" />
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="template_name">Template Name</label>                
                <input type="text" id="template_name" name="template_name" class="form-control" placeholder="Template 1" value="{{ isset($anyexceltemplate) ? $anyexceltemplate->name : (isset($vatreturnfile) ? ( ($vatreturnfile->anyexceltemplate) ? ($vatreturnfile->anyexceltemplate->name . ' ' . \Carbon\Carbon::now()->format('dmYHis')) : '') : '') }}" required {{ isset($anyexceltemplate) ? 'readonly' : '' }}/>
              </div>                                                    
              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-label-secondary btn-prev" disabled> <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                  <span class="d-sm-inline-block d-none">Previous</span>
                </button>
                <button class="btn btn-primary btn-next" {{ isset($anyexceltemplate) ? 'disabled' : '' }}> <span class="d-sm-inline-block d-none me-sm-1">Next</span> <i class="bx bx-chevron-right bx-sm me-sm-n2"></i></button>
              </div>
            </div>
          </div>
          <!-- Personal Info -->
          <div id="file-upload-mapping-validation" class="content">
            <div class="content-header mb-3">
              <h6 class="mb-0">File Upload and Mapping</h6>
              <small>Upload file and map columns.</small>
            </div>
            <div class="row g-3">
              <div class="col-sm-12">
                <input type="hidden" name="anyexcel_template_file" id="anyexcel_template_file" /> 
                <input type="hidden" name="vatreturn_file_id" id="vatreturn_file_id" /> 
                <input type="hidden" name="anyexcel_template_id" id="anyexcel_template_id" value="{{ isset($anyexcel_template_id) ? $anyexcel_template_id : (isset($vatreturnfile) ? (($vatreturnfile->anyexcel_template_id) ? $vatreturnfile->anyexcel_template_id : '') : '') }}" />
                <div class="dropzone needsclick dropzone-anyexcel" id="upload_anyexcel_template_file">  
                  <div class="dz-message needsclick">
                    Drop files here or click to upload
                    <span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
                  </div>
{{--
                  @if(isset($vatreturnfile))
                  <div class="dz-preview dz-file-preview dz-processing dz-success dz-complete">
                    <div class="dz-details">
                      <div class="dz-thumbnail">
                        <img data-dz-thumbnail>
                        <span class="dz-nopreview">No preview</span>
                        <div class="dz-success-mark"></div>
                        <div class="dz-error-mark"></div>
                        <div class="dz-error-message"><span data-dz-errormessage></span></div>
                        <div class="progress">
                          <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
                        </div>
                      </div>
                      <div class="dz-filename" data-dz-name>{{ $vatreturnfile->vatreturnofiles->first()->file_name }}</div>
                      <div class="dz-size" data-dz-size>{{ $vatreturnfile->vatreturnofiles->first()->file_size . ' KB' }}</div>
                    </div>
                    <a class="dz-remove" href="javascript:undefined;" data-dz-remove="">Remove file</a>
                  </div>
                  @endif
--}}                  

                </div>               
              </div>               
              <div class="col-sm-12">
                <div id="excel-preview" class="mt-4 table-responsive" style="display: none;">
                  @include('_partials/_content/_vatreturn/anyexcel-template-preview-tab')
                </div>   
              </div>          
              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-primary btn-prev"> <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                  <span class="d-sm-inline-block d-none">Previous</span>
                </button>
                <button class="btn btn-primary btn-next"> <span class="d-sm-inline-block d-none me-sm-1">Next</span> <i class="bx bx-chevron-right bx-sm me-sm-n2"></i></button>
              </div>
            </div>
          </div>
          <!-- Overview -->
          <div id="overview-validation" class="content">
            <div class="content-header mb-3">
              <h6 class="mb-0">Overview</h6>
              <small>Check and submit.</small>
            </div>
            <div class="row g-3">              
              <div class="col-sm-12">
                <div id="excel-overview"></div>
              </div>
              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-primary btn-prev"> <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                  <span class="d-sm-inline-block d-none">Previous</span>
                </button>
                <button class="btn btn-success btn-next btn-submit">Submit</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- /Modern Wizard -->  
</div>

@include('_partials/_modals/modal-anyexcel-formula')

@endsection
