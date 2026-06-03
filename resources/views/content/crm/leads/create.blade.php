@extends('layouts/layoutMaster')

@section('title', 'CRM - Leads')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/intlTelInput.min.js')}}"></script>

<!-- <script src="{{asset('assets/js/form-layouts.js')}}"></script> -->
<script src="{{asset('js/dv-crm.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('crm/leads') }}">{{ __('Leads') }}</a>/</span> {{ isset($lead) ? __('Edit') : __('Create') }}
</h4>

<!-- Basic Layout -->
<form id="formCrmLeads" method="post" action="{{ url('crm/leads') }}" class="card-body needs-validation form-crm-leads">
  @csrf               
  
  <input type="hidden" id="crm_lead_id" name="crm_lead_id" value="{{ isset($lead) ? $lead->id : '' }}" />

  <div class="row">
    <div class="col-md-4 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">Company Information</h5>
        <div class="card-body">          
          <div class="input-group">
            <div class="form-floating">
              <input type="text" class="form-control" id="crm_cvr_no" name="crm_cvr_no" placeholder="83097515" aria-describedby="vatnoHelp" value="{{ isset($lead) ? $lead->cvr_number : '' }}" />
              <label for="crm_cvr_no">Company registration number</label>          
            </div>
            <button class="btn btn-outline-primary" type="button" id="btn_crm_cvr_no_search">Search</button>          
          </div>
          <div id="clientNameHelp" class="form-text mb-3">Search for the Company registration number to fill the details.</div>
          
          <div class="company-details">
            @include('_partials/_content/_crm/company')
          </div>

        </div>
      </div>
    </div>

    <div class="col-md-4">
        <div class="card">            
            <h5 class="card-header">Contact Person</h5>
            <div class="card-body">

                {{--<div class="input-group mb-3">
                    <div class="form-floating">
                      <select id="formUserList" class="form-select" data-allow-clear="true" name="user_list" required>
                        <option value="">Select</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->dvuser->firstname . ' ' . $user->dvuser->lastname }}</option>
                        @endforeach
                      </select>
                      <label for="formUserList">User</label>          
                    </div>           
                </div>

                <div class="user-contact-details"></div> --}}

               <div class="input-group mb-3">  
                  <div class="form-floating">        
                    <select id="crm_user_role" class="form-select" name="crm_user_role" required>                      
                      <option value="lead-user">Lead User</option>
                    </select>
                    <label for="crm_user_role">Role</label> 
                  </div>
                </div>

                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input type="text" id="crm_user_firstname" class="form-control" placeholder="John" aria-label="John" name="crm_user_firstname" value="{{ isset($lead) ? $lead->contact->first_name : '' }}" required />
                    <label for="crm_user_firstname">First Name</label>
                  </div>
                </div>

                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input type="text" id="crm_user_lastname" class="form-control" placeholder="John" aria-label="John" name="crm_user_lastname" value="{{ isset($lead) ? $lead->contact->last_name : '' }}" required />
                    <label for="crm_user_lastname">Last Name</label>
                  </div>
                </div>

                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input type="email" id="crm_user_email" class="form-control" placeholder="john.doe" aria-label="john.doe" name="crm_user_email" value="{{ isset($lead) ? $lead->contact->email : '' }}" required />
                    <label for="crm_user_email">Email</label>
                  </div>
                </div>

                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input type="text" id="crm_user_telephone" class="form-control phone-mask telephone" placeholder="00 00 00 00" aria-label="00 00 00 00" name="crm_user_telephone" value="{{ isset($lead) ? $lead->contact->phone : '' }}" required />
                    <label for="crm_user_telephone">Telephone</label>
                  </div>
                </div> 

                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input type="text" id="crm_user_designation" class="form-control" placeholder="CEO" aria-label="CEO" name="crm_user_designation" value="{{ isset($lead) ? $lead->contact->designation : '' }}" />
                    <label for="crm_user_designation">Title</label>
                  </div>
                </div>      

                <div class="input-group mb-3">
                  <div class="form-floating">
                    <select id="crm_user_lang" class="form-select" name="crm_user_lang" required>          
                      <option value="en" {{ isset($lead) && $lead->contact->lang == 'en' ? 'selected' : '' }}>English</option>
                      <option value="dk" {{ isset($lead) && $lead->contact->lang == 'dk' ? 'selected' : '' }}>Danish</option>
                    </select>
                    <label for="crm_user_lang">Language</label>
                  </div>
                </div>
                
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">            
            <h5 class="card-header">Action</h5>
            <div class="card-body">
                
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input type="text" class="form-control crm_financial_year" id="crm_financial_year" name="crm_financial_year" placeholder="2026" aria-describedby="financialYearHelp" value="{{ isset($lead) ? $lead->financial_year : '' }}" />
                    <label for="crm_financial_year">Financial Year</label>
                  </div>           
                </div>

                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="crm_revenue" name="crm_revenue" placeholder="25000" aria-describedby="revenueHelp" value="{{ isset($lead) ? $lead->revenue : '' }}" />
                    <label for="crm_revenue">Revenue</label>
                  </div>           
                </div>

                <div class="input-group mb-3">
                    <div class="form-floating">
                      <select id="crm_rating" class="form-select" data-allow-clear="true" name="crm_rating" required>
                        <option value="">Select</option>
                        <option value="good" {{ isset($lead) && $lead->rating == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="medium" {{ isset($lead) && $lead->rating == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="poor" {{ isset($lead) && $lead->rating == 'poor' ? 'selected' : '' }}>Poor</option>
                      </select>
                      <label for="crm_rating">Rating</label>          
                    </div>           
                </div>

                <div class="input-group mb-3">
                    <div class="form-floating">
                      <select id="crm_potential_countries" data-allow-clear="true" name="crm_potential_countries[]" class="select2 form-select" multiple required>
                        <option value="">Select</option>
                        <optgroup label="Europe">  
                          <option value="AT" {{ isset($lead) && in_array('AT', $lead->potential_countries ?? []) ? 'selected' : '' }}>Austria</option>
                          <option value="BE" {{ isset($lead) && in_array('BE', $lead->potential_countries ?? []) ? 'selected' : '' }}>Belgium</option>
                          <option value="BG" {{ isset($lead) && in_array('BG', $lead->potential_countries ?? []) ? 'selected' : '' }}>Bulgaria</option>
                          <option value="HR" {{ isset($lead) && in_array('HR', $lead->potential_countries ?? []) ? 'selected' : '' }}>Croatia</option>
                          <option value="CY" {{ isset($lead) && in_array('CY', $lead->potential_countries ?? []) ? 'selected' : '' }}>Cyprus</option>
                          <option value="CZ" {{ isset($lead) && in_array('CZ', $lead->potential_countries ?? []) ? 'selected' : '' }}>Czech Republic</option>
                          <option value="DK" {{ isset($lead) && in_array('DK', $lead->potential_countries ?? []) ? 'selected' : '' }}>Denmark</option>
                          <option value="EE" {{ isset($lead) && in_array('EE', $lead->potential_countries ?? []) ? 'selected' : '' }}>Estonia</option>
                          <option value="FI" {{ isset($lead) && in_array('FI', $lead->potential_countries ?? []) ? 'selected' : '' }}>Finland</option>
                          <option value="FR" {{ isset($lead) && in_array('FR', $lead->potential_countries ?? []) ? 'selected' : '' }}>France</option>
                          <option value="DE" {{ isset($lead) && in_array('DE', $lead->potential_countries ?? []) ? 'selected' : '' }}>Germany</option>
                          <option value="GR" {{ isset($lead) && in_array('GR', $lead->potential_countries ?? []) ? 'selected' : '' }}>Greece</option>
                          <option value="HU" {{ isset($lead) && in_array('HU', $lead->potential_countries ?? []) ? 'selected' : '' }}>Hungary</option>
                          <option value="IE" {{ isset($lead) && in_array('IE', $lead->potential_countries ?? []) ? 'selected' : '' }}>Ireland, Republic of (EIRE)</option>
                          <option value="IT" {{ isset($lead) && in_array('IT', $lead->potential_countries ?? []) ? 'selected' : '' }}>Italy</option>
                          <option value="LV" {{ isset($lead) && in_array('LV', $lead->potential_countries ?? []) ? 'selected' : '' }}>Latvia</option>
                          <option value="LT" {{ isset($lead) && in_array('LT', $lead->potential_countries ?? []) ? 'selected' : '' }}>Lithuania</option>
                          <option value="LU" {{ isset($lead) && in_array('LU', $lead->potential_countries ?? []) ? 'selected' : '' }}>Luxembourg</option>
                          <option value="MT" {{ isset($lead) && in_array('MT', $lead->potential_countries ?? []) ? 'selected' : '' }}>Malta</option>
                          <option value="NL" {{ isset($lead) && in_array('NL', $lead->potential_countries ?? []) ? 'selected' : '' }}>Netherlands</option>
                          <option value="NO" {{ isset($lead) && in_array('NO', $lead->potential_countries ?? []) ? 'selected' : '' }}>Norway</option>             
                          <option value="PL" {{ isset($lead) && in_array('PL', $lead->potential_countries ?? []) ? 'selected' : '' }}>Poland</option>
                          <option value="PT" {{ isset($lead) && in_array('PT', $lead->potential_countries ?? []) ? 'selected' : '' }}>Portugal</option>
                          <option value="RO" {{ isset($lead) && in_array('RO', $lead->potential_countries ?? []) ? 'selected' : '' }}>Romania</option>
                          <option value="SK" {{ isset($lead) && in_array('SK', $lead->potential_countries ?? []) ? 'selected' : '' }}>Slovakia</option>
                          <option value="SI" {{ isset($lead) && in_array('SI', $lead->potential_countries ?? []) ? 'selected' : '' }}>Slovenia</option>
                          <option value="ES" {{ isset($lead) && in_array('ES', $lead->potential_countries ?? []) ? 'selected' : '' }}>Spain</option>
                          <option value="SE" {{ isset($lead) && in_array('SE', $lead->potential_countries ?? []) ? 'selected' : '' }}>Sweden</option>
                          <option value="CH" {{ isset($lead) && in_array('CH', $lead->potential_countries ?? []) ? 'selected' : '' }}>Switzerland</option>
                          <option value="GB" {{ isset($lead) && in_array('GB', $lead->potential_countries ?? []) ? 'selected' : '' }}>United Kingdom</option>
                        </optgroup>
                        <optgroup label="Rest of the world">
                          <option value="US" {{ isset($lead) && in_array('US', $lead->potential_countries ?? []) ? 'selected' : '' }}>United States of America</option>
                          <option value="HK" {{ isset($lead) && in_array('HK', $lead->potential_countries ?? []) ? 'selected' : '' }}>Hong Kong</option>
                        </optgroup>
                      </select>
                      <!-- <label for="crm_potential_countries">Potential Countries</label>           -->
                    </div>           
                </div>

                <div class="input-group mb-3">
                    <div class="form-floating">
                      <select id="crm_potential_products" data-allow-clear="true" name="crm_potential_products[]" class="select2 form-select" multiple required>
                        <option value="">Select</option>                        
                        <option value="product_1" {{ isset($lead) && in_array('product_1', $lead->potential_products ?? []) ? 'selected' : '' }}>Product 1</option>
                        <option value="product_2" {{ isset($lead) && in_array('product_2', $lead->potential_products ?? []) ? 'selected' : '' }}>Product 2</option>                        
                      </select>
                      <!-- <label for="crm_potential_products">Potential Products</label>           -->
                    </div>           
                </div>

                <!-- Inline Picker-->
                <div class="input-group mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control mb-1" placeholder="YYYY-MM-DD" id="crm_lead_date" name="crm_lead_date" value="{{ isset($lead) ? $lead->lead_date : '' }}" required />
                        <label for="crm_lead_date">Date</label>
                    </div>
                </div>
                <!-- /Inline Picker-->

                <div class="row">
                    <div class="col-md-6">
                        <button id="no-quote" class="btn btn-danger w-100">No Quote</button>
                    </div>
                    <div class="col-md-6">
                        <button id="create-quote" class="btn btn-success w-100">Create Quote</button>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

  </div> 
 
</form>
  
@php
  $module_type = 'lead';
@endphp  
@include('_partials/_modals/modal-crm-reminder')
@endsection