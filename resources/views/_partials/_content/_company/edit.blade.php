<div class="row">
  <div class="col-xl-6 col-lg-5 col-md-5 d-flex">
    <!-- About Client -->
    <div class="card mb-4" style="flex: 1;">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Company</h5> <small class="text-muted float-end">Company</small>
      </div>
      <div class="card-body">                
        <form id="frmClient" class="needs-validation" novalidate>
          @csrf
          <input type="hidden" name="frmClient_client_id" id="frmClient_client_id" value="{{ $client_id }}">

          <div class="mb-3">
            <label class="form-label" for="vatno">Company registration number</label>
            <input type="text" class="form-control" id="vatno" name="vatno" placeholder="83097515" value="{{ ($client) ? $client->vatno : ''}}" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="clientname">Company Name</label>
            <input type="text" class="form-control" id="clientname" name="clientname" placeholder="John Doe" value="{{ ($client) ? $client->client_name : ''}}" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="off_address">Address</label>
            <input type="text" class="form-control" id="off_address" name="off_address" placeholder="Street name, house number etc.," value="{{ ($client) ? $client->off_address : ''}}" required />
          </div>

          <div class="mb-3">
            <label class="form-label" for="off_postcode">Zipcode</label>
            <input type="text" class="form-control" id="off_postcode" name="off_postcode" placeholder="2000" value="{{ ($client) ? $client->off_postcode : ''}}" />
          </div>

          <!-- <div class="mb-3">
            <label class="form-label" for="off_houseno">House number</label>
            <input type="text" class="form-control" id="txt-off_houseno" name="off_houseno" placeholder="" value="{{ ($client) ? $client->off_houseno : ''}}" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="off_street">Street name</label>
            <input type="text" class="form-control" id="off_street" name="off_street" placeholder="" value="{{ ($client) ? $client->off_street : ''}}" />
          </div> -->                  

          <div class="mb-3">
            <label class="form-label" for="off_city">City</label>
            <input type="text" class="form-control" id="off_city" name="off_city" placeholder="Copenhagen" value="{{ ($client) ? $client->off_city : ''}}" />
          </div>                 

          <div class="mb-3">
            <label class="form-label" for="formValidationSelect2">Country</label>
            <select id="formValidationSelect2" class="form-select" data-allow-clear="true" name="off_country" required>
              <option value="">Select</option>
              <optgroup label="Europe"> 
                <option value="AT" {{ ($client) ? (($client->off_country == 'AT') ? 'selected' : '') : '' }}>Austria</option>
                <option value="BE" {{ ($client) ? (($client->off_country == 'BE') ? 'selected' : '') : '' }}>Belgium</option>
                <option value="BG" {{ ($client) ? (($client->off_country == 'BG') ? 'selected' : '') : '' }}>Bulgaria</option>
                <option value="HR" {{ ($client) ? (($client->off_country == 'HR') ? 'selected' : '') : '' }}>Croatia</option>
                <option value="CY" {{ ($client) ? (($client->off_country == 'CY') ? 'selected' : '') : '' }}>Cyprus</option>
                <option value="CZ" {{ ($client) ? (($client->off_country == 'CZ') ? 'selected' : '') : '' }}>Czech Republic</option>
                <option value="DK" {{ ($client) ? (($client->off_country == 'DK') ? 'selected' : '') : '' }}>Denmark</option>
                <option value="EE" {{ ($client) ? (($client->off_country == 'EE') ? 'selected' : '') : '' }}>Estonia</option>
                <option value="FI" {{ ($client) ? (($client->off_country == 'FI') ? 'selected' : '') : '' }}>Finland</option>
                <option value="FR" {{ ($client) ? (($client->off_country == 'FR') ? 'selected' : '') : '' }}>France</option>
                <option value="DE" {{ ($client) ? (($client->off_country == 'DE') ? 'selected' : '') : '' }}>Germany</option>
                <option value="GR" {{ ($client) ? (($client->off_country == 'GR') ? 'selected' : '') : '' }}>Greece</option>
                <option value="HU" {{ ($client) ? (($client->off_country == 'HU') ? 'selected' : '') : '' }}>Hungary</option>
                <option value="IE" {{ ($client) ? (($client->off_country == 'IE') ? 'selected' : '') : '' }}>Ireland, Republic of (EIRE)</option>
                <option value="IT" {{ ($client) ? (($client->off_country == 'IT') ? 'selected' : '') : '' }}>Italy</option>
                <option value="LV" {{ ($client) ? (($client->off_country == 'LV') ? 'selected' : '') : '' }}>Latvia</option>
                <option value="LT" {{ ($client) ? (($client->off_country == 'LT') ? 'selected' : '') : '' }}>Lithuania</option>
                <option value="LU" {{ ($client) ? (($client->off_country == 'LU') ? 'selected' : '') : '' }}>Luxembourg</option>
                <option value="MT" {{ ($client) ? (($client->off_country == 'MT') ? 'selected' : '') : '' }}>Malta</option>
                <option value="NL" {{ ($client) ? (($client->off_country == 'NL') ? 'selected' : '') : '' }}>Netherlands</option>
                <option value="NO" {{ ($client) ? (($client->off_country == 'NO') ? 'selected' : '') : '' }}>Norway</option>             
                <option value="PL" {{ ($client) ? (($client->off_country == 'PL') ? 'selected' : '') : '' }}>Poland</option>
                <option value="PT" {{ ($client) ? (($client->off_country == 'PT') ? 'selected' : '') : '' }}>Portugal</option>
                <option value="RO" {{ ($client) ? (($client->off_country == 'RO') ? 'selected' : '') : '' }}>Romania</option>
                <option value="SK" {{ ($client) ? (($client->off_country == 'SK') ? 'selected' : '') : '' }}>Slovakia</option>
                <option value="SI" {{ ($client) ? (($client->off_country == 'SI') ? 'selected' : '') : '' }}>Slovenia</option>
                <option value="ES" {{ ($client) ? (($client->off_country == 'ES') ? 'selected' : '') : '' }}>Spain</option>
                <option value="SE" {{ ($client) ? (($client->off_country == 'SE') ? 'selected' : '') : '' }}>Sweden</option>
                <option value="CH" {{ ($client) ? (($client->off_country == 'CH') ? 'selected' : '') : '' }}>Switzerland</option>
                <option value="GB" {{ ($client) ? (($client->off_country == 'GB') ? 'selected' : '') : '' }}>United Kingdom</option>
              </optgroup>
              <optgroup label="Rest of the world">
                <option value="US" {{ isset($client) ? (($client->off_country == 'US') ? 'selected' : '') : '' }}>United States of America</option>
                <option value="HK" {{ isset($client) ? (($client->off_country == 'HK') ? 'selected' : '') : '' }}>Hong Kong</option>
              </optgroup>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label" for="lrep_email">Email</label>
            <input type="text" class="form-control" id="lrep_email" name="lrep_email" placeholder="john.doe" value="{{ ($client) ? $client->lrep_email : ''}}" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="telephone">Phone</label>
            <input type="text" class="form-control telephone" id="telephone" name="telephone" placeholder="658 799 8941" value="{{ ($client) ? $client->telephone : ''}}" />
          </div> 

          <div class="mb-3">
            <label class="form-label" for="lrep_address">Company Desc.</label>          
            <input type="text" class="form-control" id="short_desc" name="short_desc" placeholder="Anpartsselskab" value="{{ ($client) ? $client->short_desc : ''}}" required />
          </div>

          <div class="mb-3">
            <label class="form-label" for="employees">Employees</label>
            <input type="text" class="form-control" id="employees" name="employees" placeholder="10" value="{{ ($client) ? $client->employees : ''}}" />
          </div> 

          <div class="mb-3">
            <label class="form-label" for="start_date">Start Date</label>
            <input type="text" class="form-control" id="start_date" name="start_date" placeholder="DD-MM-YYYY" value="{{ ($client) ? \Carbon\Carbon::parse($client->start_date)->format('d-m-Y') : ''}}" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="end_date">End Date</label>
            <input type="text" class="form-control" id="end_date" name="end_date" placeholder="DD-MM-YYYY" value="{{ ($client->end_date) ? \Carbon\Carbon::parse($client->end_date)->format('d-m-Y') : ''}}" />
          </div>                   

          <button type="submit" class="btn btn-label-primary float-end">Save</button>
        </form>                          
      </div>
    </div>
    <!--/ About Client -->  
  </div>

  <div class="col-xl-6 col-lg-5 col-md-5 d-flex">
    <!-- Legal Representative -->

    
    <div class="card mb-4" style="flex: 1;">
       <form id="frmLegalRep" class="needs-validation form-legalrep-repeater" novalidate enctype="multipart/form-data">
        @csrf  
        @method('PUT')   
        <input type="hidden" name="frmLegalRep_client_id" id="frmLegalRep_client_id" value="{{ $client_id }}">   
        <h5 class="card-header mb-4">Legal Representative
          <button class="btn btn-primary float-end" data-repeater-create>
            <i class="bx bx-plus me-1"></i>
            <span class="align-middle">Add</span>
          </button>   
        </h5>
        <div class="card-body">
          <div data-repeater-list="legalrep">
            @if(count($clientlegalreps) > 0)
              @foreach($clientlegalreps as $clientlegalrepkey => $clientlegalrep)
                @include('_partials/_content/_company/legalrep-row-repeater')
              @endforeach
            @else
              @include('_partials/_content/_company/legalrep-row-repeater')                
            @endif
          </div>
          <button type="submit" class="btn btn-label-primary float-end my-2">Save</button>               
        </div>        
      </form>           
    </div>    
    
    {{--
    <div class="card mb-4" style="flex: 1;">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Legal Representative</h5> <small class="text-muted float-end">Legal Representative</small>
      </div>
      <div class="card-body">                
        <form id="frmLegalRep" class="needs-validation" novalidate>
          @csrf
          <input type="hidden" name="frmLegalRep_client_id" id="frmLegalRep_client_id" value="{{ $client_id }}">

          <div class="mb-3">
            <label class="form-label" for="lrep_fname">First name</label>
            <input type="text" class="form-control" id="lrep_fname" name="lrep_fname" placeholder="John Doe" value="{{ ($client) ? $client->lrep_fname : ''}}" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="lrep_sname">Surname</label>
            <input type="text" class="form-control" id="lrep_sname" name="lrep_sname" placeholder="John Doe" value="{{ ($client) ? $client->lrep_sname : ''}}" />
          </div>                 
            
          <div class="mb-3">
            <label class="form-label" for="lrep_address">Address</label>
            <input type="text" class="form-control" id="lrep_address" name="lrep_address" placeholder="Street name, house number etc.," value="{{ ($client) ? $client->lrep_address : ''}}" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="lrep_postcode">Zipcode</label>
            <input type="text" class="form-control" id="lrep_postcode" name="lrep_postcode" placeholder="2000" value="{{ ($client) ? $client->lrep_postcode : ''}}" />
          </div>
           
          <div class="mb-3">
            <label class="form-label" for="lrep_city">City</label>
            <input type="text" class="form-control" id="lrep_city" name="lrep_city" placeholder="Copenhagen" value="{{ ($client) ? $client->lrep_city : ''}}" />
          </div>                    

          <button type="submit" class="btn btn-label-primary float-end">Save</button>
        </form>                          
      </div>
    </div>
    --}}
    <!--/ Legal Representative -->    
  </div> 
</div>

<div class="row">
  <div class="col-xl-6 col-lg-5 col-md-5 d-flex">
    <!-- Additional Information -->
    <div class="card mb-4" style="flex: 1;">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Additional Information</h5> <small class="text-muted float-end">Additional Information</small>
      </div>
      <div class="card-body">
        <form id="frmAdditional" class="needs-validation" novalidate>
          @csrf
          <input type="hidden" name="frmAdditional_client_id" id="frmAdditional_client_id" value="{{ $client_id }}">

          <div class="mb-3">
            <label class="form-label" for="risk_assessment">Risk Assessment</label>    
            <select id="risk_assessment" class="form-select" data-allow-clear="true" name="risk_assessment" required>
              <option value="">Select</option>
              <option value="Low" {{ ($client) ? (($client->risk_assessment == 'Low') ? 'selected' : '') : '' }}>Low</option>
              <option value="Medium" {{ ($client) ? (($client->risk_assessment == 'Medium') ? 'selected' : '') : '' }}>Medium</option>
              <option value="High" {{ ($client) ? (($client->risk_assessment == 'High') ? 'selected' : '') : '' }}>High</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="use_trademark">Allowed to use trademark</label>
            <select id="use_trademark" class="form-select" data-allow-clear="true" name="use_trademark" required>
              <option value="">Select</option>
              <option value="1" {{ ($client) ? (($client->use_trademark == '1') ? 'selected' : '') : '' }}>Yes</option>
              <option value="0" {{ ($client) ? (($client->use_trademark == '0') ? 'selected' : '') : '' }}>No</option>              
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="trading_name">Trading name</label>    
            <input type="text" class="form-control" id="trading_name" name="trading_name" placeholder="" value="{{ ($client) ? $client->trading_name : ''}}" />
          </div>

          <button type="submit" class="btn btn-label-primary float-end">Save</button>
        </form>
      </div>
    </div>
    <!--/ Additional Information -->  
  </div>

  
  <div class="col-xl-6 col-lg-5 col-md-5 d-flex">
    <div class="card mb-4" style="flex: 1;">              
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Billing</h5> <small class="text-muted float-end">Billing</small>
      </div>
      <div class="card-body">
        <form id="frmBilling" class="needs-validation" novalidate>
          @csrf
          <input type="hidden" name="frmBilling_client_id" id="frmBilling_client_id" value="{{ $client_id }}"> 

          <div class="mb-3">
            <label class="form-label" for="economics_id">E-Conomics id</label>                          
            <input type="text" class="form-control" id="economics_id" name="economics_id" placeholder="" value="{{ ($client) ? $client->economics_id : ''}}" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="adm_fee">Adm. fee</label>                          
            <input type="text" class="form-control" id="adm_fee" name="adm_fee" placeholder="" value="{{ ($client) ? $client->adm_fee : ''}}" />           
          </div>

          <div class="mb-3">
            <label class="form-label" for="consultancy_low">Consultancy low</label>                          
            <input type="text" class="form-control" id="consultancy_low" name="consultancy_low" placeholder="" value="{{ ($client) ? $client->consultancy_low : ''}}" />           
          </div>

          <div class="mb-3">
            <label class="form-label" for="consultancy_high">Consultancy high</label>                          
            <input type="text" class="form-control" id="consultancy_high" name="consultancy_high" placeholder="" value="{{ ($client) ? $client->consultancy_high : ''}}" />           
          </div>

          <button type="submit" class="btn btn-label-primary float-end">Save</button>
        </form>
      </div>
    </div>
  </div>

</div>

<div class="row">
  <div class="col-xl-6 col-lg-5 col-md-5 d-flex">
    <!-- File Upload -->
    <div class="card mb-4" style="flex: 1;">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">File Upload</h5> <small class="text-muted float-end">File Upload</small>
      </div>
      <div class="card-body"> 
        <!-- Multi  --> 
        <form method="post" action="{{ url('company/files/' . $client_id) }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-multi-company" id="dropzone-multi-company-{{ $client_id }}" data-clientid="{{ $client_id }}">
          
          <div class="dz-message needsclick">
            Drop files here and click upload
            <span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
          </div>
        </form> 
        <!--/ Multi  -->

        <button type="submit" id="btn-upload" class="btn btn-label-primary mt-2 float-end">Upload</button>
      </div>
    </div>
    <!--/ File Upload -->    
  </div>

  <div class="col-xl-6 col-lg-5 col-md-5 d-flex">    
    <div class="card mb-4" style="flex: 1;">
       <form id="frmAbout" class="needs-validation form-about-repeater" novalidate enctype="multipart/form-data">
        @csrf  
        @method('PUT')   
        <input type="hidden" name="frmAbout_client_id" id="frmAbout_client_id" value="{{ $client_id }}">   
        <h5 class="card-header">About
          <button class="btn btn-primary float-end" data-repeater-create>
            <i class="bx bx-plus me-1"></i>
            <span class="align-middle">Add</span>
          </button>   
        </h5>
        <div class="card-body">
          <div class="accordion mt-3 accordion-header-primary" id="accordionAboutCountry"> 
            <div data-repeater-list="about">
              @if(count($clientqas) > 0)
                @foreach($clientqas as $clientqakey => $clientqa)
                  @include('_partials/_content/_company/about-row-repeater')
                @endforeach
              @else
                @include('_partials/_content/_company/about-row-repeater')                
              @endif
            </div>
          </div>
          <button type="submit" class="btn btn-label-primary float-end my-2">Save</button>               
        </div>        
      </form>           
    </div>    
  </div>

  <div class="col-xl-6 col-lg-5 col-md-5 d-flex">    
    <div class="card mb-4" style="flex: 1;">
       <form id="frmExtraField" class="needs-validation form-extra-repeater" novalidate enctype="multipart/form-data">
        @csrf  
        @method('PUT')   
        <input type="hidden" name="frmExtraField_client_id" id="frmExtraField_client_id" value="{{ $client_id }}">   
        <h5 class="card-header mb-4">Extra Fields
          <button class="btn btn-primary float-end" data-repeater-create>
            <i class="bx bx-plus me-1"></i>
            <span class="align-middle">Add</span>
          </button>   
        </h5>
        <div class="card-body">
          <div data-repeater-list="extra">
            @if(count($clientextrafields) > 0)
              @foreach($clientextrafields as $clientextrafieldkey => $clientextrafield)
                @include('_partials/_content/_company/extra-row-repeater')
              @endforeach
            @else
              @include('_partials/_content/_company/extra-row-repeater')                
            @endif
          </div>
          <button type="submit" class="btn btn-label-primary float-end my-2">Save</button>               
        </div>        
      </form>           
    </div>    
  </div>
</div>