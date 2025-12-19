@extends('layouts/layoutMaster')

@section('title', ' VAT Registration - ' .$title)

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

<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
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
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.anyexcel_template_datas = [];    
    
    var result = { 'anyexcel_templates': {!! json_encode($anyexcel_templates) !!} };    
    anyexcel_template_datas = drawDtTable(result, 'anyexcel_template');  
});
</script>
<!-- <script src="{{asset('assets/js/intlTelInput.min.js')}}"></script> -->

<script src="{{asset('assets/js/form-layouts.js')}}"></script>
<!-- <script src="{{asset('js/dv-client-form-validation.js')}}"></script> -->
<script src="{{asset('js/dv-vat-registration-main-lazy.js')}}"></script>
<script src="{{asset('js/dv-erp-load.js')}}"></script>

<!-- DON'T DELETE - UNTIL MULTI-FILE-MULTI-SHEET -->
<!-- <script src="{{asset('js/dv-excel-column-template.js')}}"></script> 

<script src="{{asset('js/dv-excel-column-template-new.js')}}"></script>-->
<!-- <script src="{{asset('js/dv-anyexcel-template.js')}}"></script> -->
<script src="{{asset('js/dv-anyexcel-template-others.js')}}"></script>

<script src="{{asset('js/dv-modal-select-account-nos.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light"><a href="{{ url('company/'. (isset($vatRegMain) ? $vatRegMain->client_id : $client_id)) }}">{{ isset($vatRegMain) ? $vatRegMain->client_name : $client_name }}</a> / <a href="{{ url('company/'. (isset($vatRegMain) ? $vatRegMain->client_id : $client_id)) }}">VAT Registration</a> / </span> {{$title}}</h4>

<!-- Basic Layout -->
<form id="formVatRegistrationMain" class="card-body needs-validation" novalidate>
  @csrf               
  <input type="hidden" name="client_id" id="client_id" value="{{ isset($vatRegMain) ? $vatRegMain->client_id : $client_id }}">  
  <input type="hidden" name="vat_reg_main_id" id="vat_reg_main_id" value="{{ isset($vatRegMain) ? $vatRegMain->id : '' }}">  
  <input type="hidden" name="selected_acc_nos" id="selected_acc_nos" value="">
  <div class="row">
    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">VAT Registration</h5>
        <div class="card-body">          
          
          <div class="form-floating mb-3">
            <select id="country" class="form-select" data-allow-clear="true" name="country" required>
              <option value="">Select</option>
              <optgroup label="Europe">
                <option value="AT" {{ isset($vatRegMain) ? (($vatRegMain->country == 'AT') ? 'selected' : '') : '' }}>Austria</option>
                <option value="BE" {{ isset($vatRegMain) ? (($vatRegMain->country == 'BE') ? 'selected' : '') : '' }}>Belgium</option>
                <option value="BG" {{ isset($vatRegMain) ? (($vatRegMain->country == 'BG') ? 'selected' : '') : '' }}>Bulgaria</option>
                <option value="HR" {{ isset($vatRegMain) ? (($vatRegMain->country == 'HR') ? 'selected' : '') : '' }}>Croatia</option>
                <option value="CY" {{ isset($vatRegMain) ? (($vatRegMain->country == 'CY') ? 'selected' : '') : '' }}>Cyprus</option>
                <option value="CZ" {{ isset($vatRegMain) ? (($vatRegMain->country == 'CZ') ? 'selected' : '') : '' }}>Czech Republic</option>
                <option value="DK" {{ isset($vatRegMain) ? (($vatRegMain->country == 'DK') ? 'selected' : '') : '' }}>Denmark</option>
                <option value="EE" {{ isset($vatRegMain) ? (($vatRegMain->country == 'EE') ? 'selected' : '') : '' }}>Estonia</option>
                <option value="FI" {{ isset($vatRegMain) ? (($vatRegMain->country == 'FI') ? 'selected' : '') : '' }}>Finland</option>
                <option value="FR" {{ isset($vatRegMain) ? (($vatRegMain->country == 'FR') ? 'selected' : '') : '' }}>France</option>
                <option value="DE" {{ isset($vatRegMain) ? (($vatRegMain->country == 'DE') ? 'selected' : '') : '' }}>Germany</option>
                <option value="GR" {{ isset($vatRegMain) ? (($vatRegMain->country == 'GR') ? 'selected' : '') : '' }}>Greece</option>
                <option value="HU" {{ isset($vatRegMain) ? (($vatRegMain->country == 'HU') ? 'selected' : '') : '' }}>Hungary</option>
                <option value="IE" {{ isset($vatRegMain) ? (($vatRegMain->country == 'IE') ? 'selected' : '') : '' }}>Ireland, Republic of (EIRE)</option>
                <option value="IT" {{ isset($vatRegMain) ? (($vatRegMain->country == 'IT') ? 'selected' : '') : '' }}>Italy</option>
                <option value="LV" {{ isset($vatRegMain) ? (($vatRegMain->country == 'LV') ? 'selected' : '') : '' }}>Latvia</option>
                <option value="LT" {{ isset($vatRegMain) ? (($vatRegMain->country == 'LT') ? 'selected' : '') : '' }}>Lithuania</option>
                <option value="LU" {{ isset($vatRegMain) ? (($vatRegMain->country == 'LU') ? 'selected' : '') : '' }}>Luxembourg</option>
                <option value="MT" {{ isset($vatRegMain) ? (($vatRegMain->country == 'MT') ? 'selected' : '') : '' }}>Malta</option>
                <option value="NL" {{ isset($vatRegMain) ? (($vatRegMain->country == 'NL') ? 'selected' : '') : '' }}>Netherlands</option>
                <option value="NO" {{ isset($vatRegMain) ? (($vatRegMain->country == 'NO') ? 'selected' : '') : '' }}>Norway</option>             
                <option value="PL" {{ isset($vatRegMain) ? (($vatRegMain->country == 'PL') ? 'selected' : '') : '' }}>Poland</option>
                <option value="PT" {{ isset($vatRegMain) ? (($vatRegMain->country == 'PT') ? 'selected' : '') : '' }}>Portugal</option>
                <option value="RO" {{ isset($vatRegMain) ? (($vatRegMain->country == 'RO') ? 'selected' : '') : '' }}>Romania</option>
                <option value="SK" {{ isset($vatRegMain) ? (($vatRegMain->country == 'SK') ? 'selected' : '') : '' }}>Slovakia</option>
                <option value="SI" {{ isset($vatRegMain) ? (($vatRegMain->country == 'SI') ? 'selected' : '') : '' }}>Slovenia</option>
                <option value="ES" {{ isset($vatRegMain) ? (($vatRegMain->country == 'ES') ? 'selected' : '') : '' }}>Spain</option>
                <option value="SE" {{ isset($vatRegMain) ? (($vatRegMain->country == 'SE') ? 'selected' : '') : '' }}>Sweden</option>
                <option value="CH" {{ isset($vatRegMain) ? (($vatRegMain->country == 'CH') ? 'selected' : '') : '' }}>Switzerland</option>
                <option value="GB" {{ isset($vatRegMain) ? (($vatRegMain->country == 'GB') ? 'selected' : '') : '' }}>United Kingdom</option>
              </optgroup>
              <optgroup label="Rest of the world">
                <option value="US" {{ isset($vatRegMain) ? (($vatRegMain->country == 'US') ? 'selected' : '') : '' }}>United States of America</option>
                <option value="HK" {{ isset($vatRegMain) ? (($vatRegMain->country == 'HK') ? 'selected' : '') : '' }}>Hong Kong</option>                
              </optgroup>
            </select>
            <label for="country">Country</label>          
          </div>
          
          <div class="form-floating mb-3">
            <input type="text" id="bs-datepicker-service_start" placeholder="mm/yyyy" class="form-control" name="service_start" value="{{ isset($vatRegMain) ? \Carbon\Carbon::parse($vatRegMain->service_start)->format('m/Y') : '' }}" required {{ isset($vatRegMain) ? 'disabled="disabled"' : '' }} />
            <label for="bs-datepicker-service_start">Start month and year</label>
          </div>

          {{-- //DON'T DELETE
          <div class="form-floating mb-3">                    
            <input type="text" id="bs-datepicker-turnover_date" placeholder="mm/dd/yyyy" class="form-control" name="turnover_date" value="{{ isset($vatRegMain) ? \Carbon\Carbon::parse($vatRegMain->turnover_date)->format('m/d/Y') : '' }}" required />
            <label for="bs-datepicker-turnover_date">Turnover Date</label>  
          </div>
          --}}

          <div class="form-floating mb-3">
            <select id="general_periods" class="form-select" name="general_periods" required {{ isset($vatRegMain) ? 'disabled="disabled"' : '' }}>
              <option value="">Select</option>          
              <option value="monthly" {{ isset($vatRegMain) ? (($vatRegMain->general_periods == 'monthly') ? 'selected' : '') : '' }}>Monthly</option>
              <option value="bi-monthly" {{ isset($vatRegMain) ? (($vatRegMain->general_periods == 'bi-monthly') ? 'selected' : '') : '' }}>Bi-Monthly</option>
              <option value="quarterly" {{ isset($vatRegMain) ? (($vatRegMain->general_periods == 'quarterly') ? 'selected' : '') : '' }}>Quarterly</option> 
              <option value="half-yearly" {{ isset($vatRegMain) ? (($vatRegMain->general_periods == 'half-yearly') ? 'selected' : '') : '' }}>Half Yearly</option>
              <option value="yearly" {{ isset($vatRegMain) ? (($vatRegMain->general_periods == 'yearly') ? 'selected' : '') : '' }}>Yearly</option>
            </select>
            <label for="general_periods">Select Periods</label>      
          </div>
          
          {{--
          <div class="form-floating mb-3">            
            <select id="vat_reg_type" class="form-select" name="vat_reg_type" required>
              <option value="">Select</option>
              <option value="Basic" {{ isset($vatRegMain) ? (($vatRegMain->vat_reg_type == 'Basic') ? 'selected' : '') : '' }}>Basic</option>
              <option value="Pro" {{ isset($vatRegMain) ? (($vatRegMain->vat_reg_type == 'Pro') ? 'selected' : '') : '' }}>Pro</option>
            </select>

            <label for="vat_reg_type">Select Type</label>         
          </div>
          --}}

          <div class="mb-3">
            <label for="product_type[]">Select Product</label>   
            <div class="row">
                @php
                  $product_type_vat_return = '';
                  $product_type_import_reconciliation = '';   
                  $product_type_voec_vat_return = '';             
                  if(isset($vatRegMain))
                  {                  
                    if(isset($vatRegMain->product_type))
                    {
                      if($vatRegMain->product_type == 1)                      
                        $product_type_vat_return = 'checked';                          
                      else if($vatRegMain->product_type == 2)
                        $product_type_import_reconciliation = 'checked'; 
                      else if($vatRegMain->product_type == 4)                      
                        $product_type_voec_vat_return = 'checked';                       
                      else if($vatRegMain->product_type == 3)   
                      {                        
                        $product_type_vat_return = 'checked';
                        $product_type_import_reconciliation = 'checked'; 
                      }
                      else if($vatRegMain->product_type == 5)   
                      {                        
                        $product_type_voec_vat_return = 'checked';
                        $product_type_import_reconciliation = 'checked'; 
                      }
                    }
                  }               
                @endphp
                <div class="col-md-4 mb-md-0 mb-2 product_type_vat_return product-type" style="{{ isset($vatRegMain) ? (($vatRegMain->country == 'NO') ? (($vatRegMain->general_periods == 'bi-monthly') ? '' : 'display: none;') : '') : '' }}">
                  <div class="form-check custom-option custom-option-icon">
                    <label class="form-check-label custom-option-content" for="product_type_vat_return">
                      <span class="custom-option-body">
                        <i class="bx bx-server"></i>
                        <span class="custom-option-title"> {{ isset($vatRegMain) ? (($vatRegMain->country == 'NO') ? 'NUF' : '') : '' }} VAT Return </span>
                        <small> {{ isset($vatRegMain) ? (($vatRegMain->country == 'NO') ? 'NUF' : '') : '' }} VAT Return. </small>
                      </span>
                      <input class="form-check-input" type="radio" value="1" id="product_type_vat_return" name="product_type[]" required {{ $product_type_vat_return }} />
                    </label>
                  </div>
                </div>               
                <div class="col-md-4 mb-md-0 mb-2 product_type_voec_vat_return product-type" style="{{ isset($vatRegMain) ? (($vatRegMain->country == 'NO') ? (($vatRegMain->general_periods == 'quarterly') ? '' : 'display: none;') : 'display: none;') : '' }}">
                  <div class="form-check custom-option custom-option-icon">
                    <label class="form-check-label custom-option-content" for="product_type_voec_vat_return">
                      <span class="custom-option-body">
                        <i class="bx bx-server"></i>
                        <span class="custom-option-title"> VOEC VAT Return </span>
                        <small> VOEC VAT Return. </small>
                      </span>
                      <input class="form-check-input" type="radio" value="4" id="product_type_voec_vat_return" name="product_type[]" required {{ $product_type_voec_vat_return }} />
                    </label>
                  </div>
                </div>                
                <div class="col-md-4 mb-md-0 mb-2 product_type_import_reconciliation">
                  <div class="form-check custom-option custom-option-icon">
                    <label class="form-check-label custom-option-content" for="product_type_import_reconciliation">
                      <span class="custom-option-body">
                        <i class="bx bx-import"></i>
                        <span class="custom-option-title"> Import Reconciliation </span>
                        <small> Import Reconciliation. </small>
                      </span>
                      <input class="form-check-input" type="checkbox" value="2" id="product_type_import_reconciliation" name="product_type[]" required {{ $product_type_import_reconciliation }} />
                    </label>
                  </div>
                </div>
            </div>

            <div class="fv-plugins-message-container invalid-feedback"></div>
          </div>

          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="vat_no" name="vat_no" placeholder="VAT Number" value="{{ isset($vatRegMain) ? $vatRegMain->vat_no : '' }}" />
            <label for="vat_no">VAT Number</label>
          </div>

          <div class="form-floating">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" name="oss" id="oss" {{ isset($vatRegMain) ? (($vatRegMain->oss) ? 'checked' : '') : '' }}>
              <label for="oss">OSS</label>         
            </div> 
          </div>         
        </div>        

        <!-- For GB -->
        <div class="card-body border-top" id="for_GB" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'GB') ? 'display:none;' : '') : '' }}">   
          <div class="form-check mb-3" id="cash_account_statement" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'GB') ? 'display:none;' : '') : '' }}">
            <input class="form-check-input" type="checkbox" value="1" name="cash_acc_stmt" id="cash_acc_stmt" {{ isset($vatRegMain) ? (($vatRegMain->cash_acc_stmt) ? 'checked' : '') : '' }}>
            <label for="cash_acc_stmt">Cash Account Statement</label>         
          </div>

          {{--
          <div class="form-floating mb-3" id="parent_gb_vat" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'GB') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="gb_vat" name="gb_vat" placeholder="GB VAT" value="{{ isset($vatRegMain) ? $vatRegMain->gb_vat : '' }}" />
            <label for="gb_vat">GB VAT</label>          
          </div>
          --}}

          <div class="form-floating mb-3" id="parent_eori_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'GB') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="eori_no" name="eori_no" placeholder="Eori Number" value="{{ isset($vatRegMain) ? $vatRegMain->eori_no : '' }}" />
            <label for="eori_no">Eori Number</label>          
          </div>

          <div class="form-floating" id="parent_cash_account_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'GB') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="cash_account_no" name="cash_account_no" placeholder="Cash Account Number" value="{{ isset($vatRegMain) ? $vatRegMain->cash_account_no : '' }}" />
            <label for="cash_account_no">Cash Account Number</label>          
          </div>        
        </div>
        <!--/ For GB -->

        <!-- For NO, SE -->
        <div class="card-body border-top" id="for_NO" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'NO' && $vatRegMain->country != 'SE') ? 'display:none;' : '') : '' }}"> 
          <div class="form-check mb-3" id="duty_deferment_account" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'NO') ? 'display:none;' : '') : '' }}">
            <input class="form-check-input" type="checkbox" value="1" name="duty_defer_acc" id="duty_defer_acc" {{ isset($vatRegMain) ? (($vatRegMain->duty_defer_acc) ? 'checked' : '') : '' }}>
            <label for="duty_defer_acc">Duty Deferment Account</label>         
          </div>

          <div class="form-floating mb-3" id="parent_dda_acc_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'NO') ? 'display:none;' : (($vatRegMain->duty_defer_acc) ? '' : 'display:none;')) : '' }}">
            <input type="text" class="form-control" id="dda_acc_no" name="dda_acc_no" placeholder="Duty Derfement Account Number" value="{{ isset($vatRegMain) ? $vatRegMain->dda_acc_no : '' }}" />
            <label for="dda_acc_no">Duty Derfement Account Number</label>          
          </div>

          <div class="form-floating mb-3" id="parent_dda_acc_limit" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'NO') ? 'display:none;' : (($vatRegMain->duty_defer_acc) ? '' : 'display:none;')) : '' }}">
            <input type="text" class="form-control" id="dda_acc_limit" name="dda_acc_limit" placeholder="Duty Derfement Account Limit" value="{{ isset($vatRegMain) ? $vatRegMain->dda_acc_limit : '' }}" />
            <label for="dda_acc_limit">Duty Derfement Account Limit</label>          
          </div>

          <div class="form-floating mb-3" id="parent_mva_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'NO') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="mva_no" name="mva_no" placeholder="MVA Number" value="{{ isset($vatRegMain) ? $vatRegMain->mva_no : '' }}" />
            <label for="mva_no">MVA Number</label>          
          </div>

          <div class="form-floating" id="parent_org_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'NO' && $vatRegMain->country != 'SE') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="org_no" name="org_no" placeholder="Organization Number" value="{{ isset($vatRegMain) ? $vatRegMain->org_no : '' }}" />
            <label for="org_no">Organization Number</label>          
          </div>
        </div>
        <!--/ For NO, SE -->

        <!-- For CH -->
        <div class="card-body border-top" id="for_CH" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'CH') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating" id="parent_zaz_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'CH') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="zaz_no" name="zaz_no" placeholder="ZAZ Number" value="{{ isset($vatRegMain) ? $vatRegMain->zaz_no : '' }}" />
            <label for="zaz_no">ZAZ Number</label>          
          </div>         
        </div>
        <!--/ For CH -->

        <!-- For DE -->
        <div class="card-body border-top" id="for_DE" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'DE') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating" id="parent_steuer_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'DE') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="steuer_no" name="steuer_no" placeholder="Steuer Number" value="{{ isset($vatRegMain) ? $vatRegMain->steuer_no : '' }}" />
            <label for="steuer_no">Steuer Number</label>          
          </div>         
        </div>
        <!--/ For DE -->

        <!-- For DK -->
        <div class="card-body border-top" id="for_DK" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'DK') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating mb-3" id="parent_cvr_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'DK') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="cvr_no" name="cvr_no" placeholder="CVR Number" value="{{ isset($vatRegMain) ? $vatRegMain->cvr_no : '' }}" />
            <label for="cvr_no">CVR Number</label>          
          </div>

          <div class="form-floating" id="parent_excise_duty" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'DK') ? 'display:none;' : '') : '' }}">
            <div class="form-check" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'DK') ? 'display:none;' : '') : '' }}">
             <input class="form-check-input" type="checkbox" value="1" name="excise_duty" id="excise_duty" {{ isset($vatRegMain) ? (($vatRegMain->excise_duty) ? 'checked' : '') : '' }}>
              <label for="excise_duty">Excise Duty</label>   
              </div>       
          </div>          
        </div>
        <!--/ For DK -->

        <!-- For NL -->
        <div class="card-body border-top" id="for_NL" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'NL') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating" id="parent_omz_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'NL') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="omz_no" name="omz_no" placeholder="Omz. Number" value="{{ isset($vatRegMain) ? $vatRegMain->omz_no : '' }}" />
            <label for="omz_no">Omz. Number</label>          
          </div>         
        </div>
        <!--/ For NL -->

        <!-- For PL -->
        <div class="card-body border-top" id="for_PL" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'PL') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating" id="parent_nip_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'PL') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="nip_no" name="nip_no" placeholder="NIP Number" value="{{ isset($vatRegMain) ? $vatRegMain->nip_no : '' }}" />
            <label for="nip_no">NIP Number</label>          
          </div>         
        </div>
        <!--/ For PL -->

        <!-- For FI -->
        <div class="card-body border-top" id="for_FI" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'FI') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating" id="parent_fo_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'FI') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="fo_no" name="fo_no" placeholder="FO Number" value="{{ isset($vatRegMain) ? $vatRegMain->fo_no : '' }}" />
            <label for="fo_no">FO Number</label>          
          </div>         
        </div>
        <!--/ For FI -->

        <!-- For FR -->
        <div class="card-body border-top" id="for_FR" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'FR') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating" id="parent_siret_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'FR') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="siret_no" name="siret_no" placeholder="SIRET Number" value="{{ isset($vatRegMain) ? $vatRegMain->siret_no : '' }}" />
            <label for="siret_no">SIRET Number</label>          
          </div>         
        </div>
        <!--/ For FR -->

        <!-- For ES -->
        <div class="card-body border-top" id="for_ES" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'ES') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating" id="parent_nif_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'ES') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="nif_no" name="nif_no" placeholder="NIF Number" value="{{ isset($vatRegMain) ? $vatRegMain->nif_no : '' }}" />
            <label for="nif_no">NIF Number</label>          
          </div>         
        </div>
        <!--/ For ES -->

        <!-- For PT -->
        <div class="card-body border-top" id="for_PT" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'PT') ? 'display:none;' : '') : '' }}">           
          <div class="form-floating" id="parent_nipc_no" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'PT') ? 'display:none;' : '') : '' }}">
            <input type="text" class="form-control" id="nipc_no" name="nipc_no" placeholder="NIPC Number" value="{{ isset($vatRegMain) ? $vatRegMain->nipc_no : '' }}" />
            <label for="nipc_no">NIPC Number</label>          
          </div>         
        </div>
        <!--/ For PT -->

      </div>
    </div>

    <div class="col-md-6 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">Connection</h5>
        <div class="card-body">
          <div class="form-floating mb-3">
            @php
              $show_connection_select = true;
            
              if($authUser->role == "client-user")
              {
                if(isset($showSelectbox))
                {
                  if($showSelectbox == "true")
                    $show_connection_select = false;                            
                }        
              }
            @endphp  
             
            @if($show_connection_select)
              <select id="erp_options" class="form-select" data-allow-clear="true" name="erp_options">
                <option value="">Select</option>
                <option value="Dynamics 365" {{ isset($vatRegMain) ? (($vatRegMain->api_name == 'Dynamics 365') ? 'selected' : '') : '' }}>Dynamics 365</option>
                <option value="Dynamics 365 via SmartApi" {{ isset($vatRegMain) ? (($vatRegMain->api_name == 'Dynamics 365 via SmartApi') ? 'selected' : '') : '' }}>Dynamics 365 via SmartAPI</option>
                <option value="E-conomic" {{ isset($vatRegMain) ? (($vatRegMain->api_name == 'E-conomic') ? 'selected' : '') : '' }}>E-conomic</option>
                <option value="Uniconta" {{ isset($vatRegMain) ? (($vatRegMain->api_name == 'Uniconta') ? 'selected' : '') : '' }}>Uniconta</option> 
                <option value="Shopify" {{ isset($vatRegMain) ? (($vatRegMain->api_name == 'Shopify') ? 'selected' : '') : '' }}>Shopify</option>
                <option value="Billy" {{ isset($vatRegMain) ? (($vatRegMain->api_name == 'Billy') ? 'selected' : '') : '' }}>Billy</option>
                <option value="Excel Upload" {{ isset($vatRegMain) ? (($vatRegMain->api_name == null) ? 'selected' : '') : '' }}>Excel Upload</option>
                <option value="FTP" {{ isset($vatRegMain) ? (($vatRegMain->api_name == 'FTP') ? 'selected' : '') : '' }}>FTP</option>
              </select>
              <label for="erp_options">ERP</label>
            @else
              <select id="established_connection" class="form-select" data-allow-clear="true" name="established_connection">
                <option value="" selected=>Select</option>
                @foreach($client_connections as $key=>$clientconnection)
                  @php
                    $apiconnection = $clientconnection;
                  @endphp
                  <option value="{{ $apiconnection->id . ',' . $apiconnection->api_name }}" data-id="{{ $apiconnection->id }}" data-name="{{ ($apiconnection->connection_name) ? $apiconnection->connection_name  : $apiconnection->api_name }}">{{ ($apiconnection->connection_name) ? $apiconnection->connection_name  : $apiconnection->api_name }}</option>
                @endforeach
              </select>
              <label for="erp_options">Established Connection</label>
            @endif
          </div>

          <div id="load-erp-fields"></div> 

          @if($authUser->role != "client-user")
          {{--
          <div class="form-floating mb-3" id="parent_excel_column_template">
            <select id="excel_column_template" class="form-select excel-column-template" name="excel_column_template">                
              <option value="0" {{ isset($vatRegMain) ? (($vatRegMain->excel_column_template_id == NULL) ? 'selected' : '') : '' }}>Default Template</option>
              
              @if($excelcolumntemplates)
                @foreach($excelcolumntemplates as $key => $excel_column_template)
                  <option value="{{ $excel_column_template->id }}" {{ isset($vatRegMain) ? (($vatRegMain->excel_column_template_id == $excel_column_template->id) ? 'selected' : '') : '' }}>{{ $excel_column_template->name }}</option>
                @endforeach
              @endif
              
              <optgroup label="-- Create New Template --">  
                <option value="">New Template</option>
              </optgroup>
            </select>
            <label for="excel_column_template">Select Excel Template</label>         
          </div>
          --}}

          <div class="form-floating mb-3" id="parent_anyexcel_template">
            <select id="anyexcel_template" class="form-select anyexcel-template" name="anyexcel_template">  
              <option value="0" {{ isset($vatRegMain) ? (($vatRegMain->anyexcel_template_id == NULL) ? 'selected' : '') : '' }}>Default Template</option>  
              @if($anyexcel_templates)
                @foreach($anyexcel_templates as $key => $anyexcel_template)
                  <option value="{{ $anyexcel_template->id }}" {{ isset($vatRegMain) ? (($vatRegMain->anyexcel_template_id == $anyexcel_template->id) ? 'selected' : '') : '' }}>{{ $anyexcel_template->name }}</option>
                @endforeach
              @endif

              <optgroup label="-- Create New Template --">  
                <option value="">New Template</option>
              </optgroup>
            </select>
            <label for="anyexcel_template">Select Excel Template</label>         
          </div>
          @endif

          <div id="parent_account_nos" style="display:none;">
            <div class="form-check">   
              <input type="checkbox" class="form-check-input" name="account_nos" id="account_nos" data-bs-toggle="modal" data-bs-target="#selectAccountNos" data-client_id="{{ isset($vatRegMain) ? $vatRegMain->client_id : $client_id }}" data-vat_reg_main_id="{{ isset($vatRegMain_accnos) ? ((count($vatRegMain_accnos) > 0) ? $vatRegMain_accnos[0]->vat_reg_main_id : '') : '' }}" />
              <label for="account_nos">Include Account Nos.</label>
            </div>                      
          </div>

          <div id="selected_account_nos" style="display:none;">
            
          </div>

          <!-- For GB -->
          <div id="for_gateway" style="{{ isset($vatRegMain) ? (($vatRegMain->country != 'GB') ? 'display:none;' : '') : '' }}">
            <div class="card-body border-top">              
              <h5>Gov. UK Profile</h5> 
              <div class="form-floating mb-3" id="parent_uk_gateway_userid">
                <input type="text" class="form-control" id="uk_gateway_userid" name="uk_gateway_userid" placeholder="Government Gateway user ID" value="{{ isset($vatRegMain) ? $vatRegMain->uk_gateway_userid : '' }}" />
                <label for="uk_gateway_userid">Government Gateway user ID</label>          
              </div>  

              <div class="form-floating" id="parent_uk_gateway_password">
                <input type="text" class="form-control" id="uk_gateway_password" name="uk_gateway_password" placeholder="Password" value="{{ isset($vatRegMain) ? $vatRegMain->uk_gateway_password : '' }}" />
                <label for="uk_gateway_password">Password</label>          
              </div>      

              <h5 class="mt-3">CDS access</h5> 
              <div class="form-floating mb-3" id="parent_cds_gateway_userid">
                <input type="text" class="form-control" id="cds_gateway_userid" name="cds_gateway_userid" placeholder="Government Gateway user ID" value="{{ isset($vatRegMain) ? $vatRegMain->cds_gateway_userid : '' }}" />
                <label for="cds_gateway_userid">Government Gateway user ID</label>          
              </div>  

              <div class="form-floating" id="parent_cds_gateway_password">
                <input type="text" class="form-control" id="cds_gateway_password" name="cds_gateway_password" placeholder="Password" value="{{ isset($vatRegMain) ? $vatRegMain->cds_gateway_password : '' }}" />
                <label for="cds_gateway_password">Password</label>          
              </div>      
            </div>            
          </div>
          <!--/ For GB -->

        </div>

        <div class="card-footer">          
          <button type="submit" class="btn btn-label-primary float-end" id="btn-save">Save</button>
        </div>
      </div>
    </div>

  </div> 
</form>

<!-- DON'T DELETE - UNTIL MULTI-FILE-MULTI-SHEET -->
{{--@include('_partials/_modals/modal-excel-column-template-single')--}}

@include('_partials/_modals/modal-excel-column-template-new-create')
@include('_partials/_modals/modal-select-account-nos')
@endsection
