@extends('layouts/layoutMaster')

@section('title', ' Client - Creation')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/typeahead-js/typeahead.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/typeahead-js/typeahead.js')}}"></script>
<script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>

<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/intlTelInput.min.js')}}"></script>

<script src="{{asset('assets/js/form-layouts.js')}}"></script>
<script src="{{asset('js/dv-company-form-validation-lazy.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light"><a href="{{ url('companies') }}">Company</a> / </span> {{$title}}</h4>

<!-- Basic Layout -->
<form id="formClient" class="card-body needs-validation form-about-repeater" novalidate>
  @csrf               
  <input type="hidden" name="id" id="client_id" value="{{ ($client) ? $client->id : ''}}">
  <div class="row">
    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">Company Information</h5>
        <div class="card-body">          
          <div class="input-group">
            <div class="form-floating">
              <input type="text" class="form-control" id="vatno" name="vatno" placeholder="83097515" aria-describedby="vatnoHelp" required />
              <label for="vatno">Company registration number</label>          
            </div>
            <button class="btn btn-outline-primary" type="button" id="btn_vat_search">Search</button>          
          </div>
          <div id="clientNameHelp" class="form-text mb-3">Search for the Company registration number to fill the details.</div>

          <!-- <div class="input-group">
            <div class="form-floating">            
              <input type="text" class="form-control" id="client_name" name="client_name" placeholder="ACME Inc." aria-describedby="clientNameHelp" required />
              <label for="client_name">Company Name</label>            
            </div>          
            <button class="btn btn-outline-primary" type="button" id="btn_company_search">Search</button>          
          </div>
          <div id="clientNameHelp" class="form-text mb-3">Search for the company name to fill the details.</div> -->

          <div class="input-group mb-3">  
            <div class="form-floating">
              <input type="text" class="form-control" id="client_name" name="client_name" placeholder="ACME Inc." aria-describedby="clientNameHelp" required />
              <label for="client_name">Company Name</label>           
            </div>           
          </div>
          
          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="off_address" name="off_address" placeholder="Street name, house number etc.," aria-describedby="clientAddressHelp" required />
              <label for="off_address">Address</label>          
            </div>    
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="off_postcode" name="off_postcode" placeholder="2000" aria-describedby="offPostcodeHelp" required />
              <label for="off_postcode">Zipcode</label>          
            </div>          
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="off_city" name="off_city" placeholder="Copenhagen" aria-describedby="offCityHelp" required />
              <label for="off_city">City</label>          
            </div>         
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <select id="formValidationSelect2" class="form-select" data-allow-clear="true" name="off_country" required>
                <option value="">Select</option>
                <optgroup label="Europe">  
                  <option value="AT">Austria</option>
                  <option value="BE">Belgium</option>
                  <option value="BG">Bulgaria</option>
                  <option value="HR">Croatia</option>
                  <option value="CY">Cyprus</option>
                  <option value="CZ">Czech Republic</option>
                  <option value="DK" selected>Denmark</option>
                  <option value="EE">Estonia</option>
                  <option value="FI">Finland</option>
                  <option value="FR">France</option>
                  <option value="DE">Germany</option>
                  <option value="GR">Greece</option>
                  <option value="HU">Hungary</option>
                  <option value="IE">Ireland, Republic of (EIRE)</option>
                  <option value="IT">Italy</option>
                  <option value="LV">Latvia</option>
                  <option value="LT">Lithuania</option>
                  <option value="LU">Luxembourg</option>
                  <option value="MT">Malta</option>
                  <option value="NL">Netherlands</option>
                  <option value="NO">Norway</option>             
                  <option value="PL">Poland</option>
                  <option value="PT">Portugal</option>
                  <option value="RO">Romania</option>
                  <option value="SK">Slovakia</option>
                  <option value="SI">Slovenia</option>
                  <option value="ES">Spain</option>
                  <option value="SE">Sweden</option>
                  <option value="CH">Switzerland</option>
                  <option value="GB">United Kingdom</option>
                </optgroup>
                <optgroup label="Rest of the world">
                  <option value="US">United States of America</option>
                  <option value="HK">Hong Kong</option>
                </optgroup>
              </select>
              <label for="formValidationSelect2">Country</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="email" class="form-control" id="lrep_email" name="lrep_email" placeholder="john.doe" aria-describedby="lrepEmailHelp" required />
              <label for="lrep_email">Email</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control telephone" id="telephone" name="telephone" placeholder="658 799 8941" aria-describedby="telephoneHelp" required />
              <!-- <label for="telephone">Telephone</label>  -->         
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="short_desc" name="short_desc" placeholder="Anpartsselskab" aria-describedby="lrepAddressHelp" required />
              <label for="short_desc">Company Desc.</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="employees" name="employees" placeholder="10" aria-describedby="employeesHelp" />
              <label for="employees">Employees</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="start_date" name="start_date" placeholder="DD-MM-YYYY" aria-describedby="startDateHelp" />
              <label for="start_date">Start Date</label>          
            </div>          
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="end_date" name="end_date" placeholder="DD-MM-YYYY" aria-describedby="endDateHelp" />
              <label for="end_date">End Date</label>          
            </div>          
          </div>

        </div>
      </div>
    </div>

    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;" id="legalRepRepeater">
       
        <h5 class="card-header">Legal Representative
          <button class="btn btn-primary float-end" data-repeater-create>
            <i class="bx bx-plus me-1"></i>
            <span class="align-middle">Add</span>
          </button>   
        </h5>

        <div class="card-body">
          <div data-repeater-list="legalrep">
              @include('_partials/_content/_company/legalrep-row-repeater')
          </div>         
        </div>
            
      </div>
    </div>

    {{--
    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">Legal Representative</h5>
        <div class="card-body">

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="lrep_fname" name="lrep_fname" placeholder="John" aria-describedby="lrepFnameHelp" required />
              <label for="lrep_fname">First name</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="lrep_sname" name="lrep_sname" placeholder="Doe" aria-describedby="lrepLnameHelp" required />
              <label for="lrep_sname">Surname</label>
            </div>            
          </div>        

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="lrep_address" name="lrep_address" placeholder="Street name, house number etc.," aria-describedby="lrepAddressHelp" required />
              <label for="lrep_address">Address</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="lrep_postcode" name="lrep_postcode" placeholder="2000" aria-describedby="lrepPostcodeHelp" required />
              <label for="lrep_postcode">Zipcode</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="lrep_city" name="lrep_city" placeholder="Copenhagen" aria-describedby="lrepCityHelp" required />
              <label for="lrep_city">City</label>          
            </div>           
          </div> 

        </div>        
      </div>
    </div> 

    --}} 
  </div> 

  <div class="row">
    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">Additional Information</h5>
        <div class="card-body">

          <div class="input-group mb-3">             
            <div class="form-floating">
              <select id="risk_assessment" class="form-select" data-allow-clear="true" name="risk_assessment" required>
                <option value="">Select</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>              
              </select>
              <label for="risk_assessment">Risk Assessment</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <select id="use_trademark" class="form-select" data-allow-clear="true" name="use_trademark" required>
                <option value="">Select</option>
                <option value="1">Yes</option>
                <option value="0">No</option>              
              </select>
              <label for="use_trademark">Allowed to use trademark</label>          
            </div>            
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="trading_name" name="trading_name" placeholder="" aria-describedby="tradingNameHelp" />
              <label for="trading_name">Trading name</label>          
            </div>            
          </div>

        </div>        
      </div>
    </div>  

    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">Billing</h5>
        <div class="card-body">

          <div class="input-group mb-3">             
            <div class="form-floating">
              <input type="text" class="form-control" id="economics_id" name="economics_id" placeholder="" aria-describedby="economicsIdHelp" />
              <label for="economics_id">E-Conomics id</label>          
            </div>            
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="adm_fee" name="adm_fee" placeholder="" aria-describedby="admFeeHelp" />
              <label for="adm_fee">Adm. fee</label>          
            </div>            
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="consultancy_low" name="consultancy_low" placeholder="" aria-describedby="consultancyLowHelp" />
              <label for="consultancy_low">Consultancy low</label>          
            </div>           
          </div>

          <div class="input-group mb-3">
            <div class="form-floating">
              <input type="text" class="form-control" id="consultancy_high" name="consultancy_high" placeholder="" aria-describedby="consultancyHighHelp" />
              <label for="consultancy_high">Consultancy high</label>          
            </div>          
          </div>

        </div>

      </div>
    </div>
  </div>  

  <div class="row">
    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;" id="aboutRepeater">
        <h5 class="card-header">About
          <button class="btn btn-primary float-end" data-repeater-create>
            <i class="bx bx-plus me-1"></i>
            <span class="align-middle">Add</span>
          </button>   
        </h5>
        <div class="card-body">
          <div class="accordion mt-3 accordion-header-primary" id="accordionAboutCountry"> 
            <div data-repeater-list="about">                        
              @include('_partials/_content/_company/about-row-repeater')
            </div>
          </div>           
        </div>        
      </div>
    </div>

    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;" id="extraRepeater">
       
        <h5 class="card-header">Extra Fields
          <button class="btn btn-primary float-end" data-repeater-create>
            <i class="bx bx-plus me-1"></i>
            <span class="align-middle">Add</span>
          </button>   
        </h5>

        <div class="card-body">
          <div data-repeater-list="extra">                        
            @include('_partials/_content/_company/extra-row-repeater')
          </div>         
        </div>
       
        <div class="card-footer">          
          <button type="submit" class="btn btn-label-primary float-end" id="btn-save">Save</button>
        </div>
      </div>
    </div>
  </div>

</form>
  

  <div class="row">  
    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">File Upload</h5>
        <div class="card-body card-company-file-upload"> 
          <div class="progress mb-4" style="display: none;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="100" aria-valuemax="100">File uploads occur only on saving</div>
          </div>

          <!-- Multi  --> 
          <form method="post" action="{{ url('company/files/0') }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-multi-company" id="dropzone-multi-company-0" data-clientid="0">
            <div class="dz-message needsclick">
              Drop files here or click to upload
              <span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
            </div>
          </form> 
          <!--/ Multi  -->
        </div>
      </div>
    </div>

  </div> 


@endsection
