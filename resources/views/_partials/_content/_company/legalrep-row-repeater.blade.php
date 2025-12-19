<div data-legalrep_id="{{ isset($clientlegalrep) ? $clientlegalrep->id : ''}}" data-repeater-item>
  <input type="hidden" name="lrep_id" value="{{ isset($clientlegalrep) ? $clientlegalrep->id : ''}}">
  <div class="row mb-3">
    <div class="col-lg-2 col-xl-2 col-2 fs-large sl-no">
      {{ isset($clientlegalrepkey) ? (($clientlegalrepkey+1) . '.') : '1.' }}
    </div>
    <div class="col-lg-4 col-xl-4 col-4">      
      <div class="input-group mb-3">
        <div class="form-floating">
          <select id="lrep_role" class="form-select" data-allow-clear="true" name="{{ isset($clientlegalrep) ? 'legalrep['.$clientlegalrepkey.'][lrep_role]' : 'lrep_role' }}" required>
            <option value="">Select</option>
            <option value="ultimate-beneficial-owner" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_role == 'ultimate-beneficial-owner') ? 'selected' : '') : '' }}>Ultimate beneficial owner</option>
            <option value="legal-owner" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_role == 'legal-owner') ? 'selected' : '') : '' }}>Legal owner</option>
            <option value="director" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_role == 'director') ? 'selected' : '') : '' }}>Director</option>
          </select>
          <label for="lrep_role">Role</label>          
        </div>           
      </div>
    </div>

    <div class="col-lg-6 col-xl-6 col-6">
      <div class="input-group mb-3">
        <div class="form-floating">
          <input type="text" class="form-control" id="lrep_fname" name="{{ isset($clientlegalrep) ? 'legalrep['.$clientlegalrepkey.'][lrep_fname]' : 'lrep_fname' }}" placeholder="John" aria-describedby="lrepFnameHelp" value="{{ isset($clientlegalrep) ? $clientlegalrep->lrep_fname : ''}}" required />
          <label for="lrep_fname">First name</label>          
        </div>           
      </div>
    </div>

    <div class="col-lg-6 col-xl-6 col-6">
      <div class="input-group mb-3">
        <div class="form-floating">
          <input type="text" class="form-control" id="lrep_sname" name="{{ isset($clientlegalrep) ? 'legalrep['.$clientlegalrepkey.'][lrep_sname]' : 'lrep_sname' }}" placeholder="Doe" aria-describedby="lrepLnameHelp" value="{{ isset($clientlegalrep) ? $clientlegalrep->lrep_sname : ''}}" />
          <label for="lrep_sname">Surname</label>
        </div>            
      </div>        
    </div>

    <div class="col-lg-6 col-xl-6 col-6">
      <div class="input-group mb-3">
        <div class="form-floating">
          <input type="text" class="form-control" id="lrep_address" name="{{ isset($clientlegalrep) ? 'legalrep['.$clientlegalrepkey.'][lrep_address]' : 'lrep_address' }}" placeholder="Street name, house number etc.," aria-describedby="lrepAddressHelp" value="{{ isset($clientlegalrep) ? $clientlegalrep->lrep_address : ''}}" required />
          <label for="lrep_address">Address</label>          
        </div>           
      </div>
    </div>

    <div class="col-lg-6 col-xl-6 col-6">
      <div class="input-group mb-3">
        <div class="form-floating">
          <input type="text" class="form-control" id="lrep_postcode" name="{{ isset($clientlegalrep) ? 'legalrep['.$clientlegalrepkey.'][lrep_postcode]' : 'lrep_postcode' }}" placeholder="2000" aria-describedby="lrepPostcodeHelp" value="{{ isset($clientlegalrep) ? $clientlegalrep->lrep_postcode : ''}}" required />
          <label for="lrep_postcode">Zipcode</label>          
        </div>           
      </div>
    </div>

    <div class="col-lg-6 col-xl-6 col-6">
      <div class="input-group mb-3">
        <div class="form-floating">
          <input type="text" class="form-control" id="lrep_city" name="{{ isset($clientlegalrep) ? 'legalrep['.$clientlegalrepkey.'][lrep_city]' : 'lrep_city' }}" placeholder="Copenhagen" aria-describedby="lrepCityHelp" value="{{ isset($clientlegalrep) ? $clientlegalrep->lrep_city : ''}}" required />
          <label for="lrep_city">City</label>          
        </div>           
      </div> 
    </div>

    <div class="col-lg-6 col-xl-6 col-6">
      <div class="input-group mb-3">
        <div class="form-floating">
          <select id="lrep_country" class="form-select" data-allow-clear="true" name="{{ isset($clientlegalrep) ? 'legalrep['.$clientlegalrepkey.'][lrep_country]' : 'lrep_country' }}" required>
            <option value="">Select</option>
            <optgroup label="Europe">  
              <option value="AT" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'AT') ? 'selected' : '') : '' }}>Austria</option>
              <option value="BE" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'BE') ? 'selected' : '') : '' }}>Belgium</option>
              <option value="BG" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'BG') ? 'selected' : '') : '' }}>Bulgaria</option>
              <option value="HR" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'HR') ? 'selected' : '') : '' }}>Croatia</option>
              <option value="CY" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'CY') ? 'selected' : '') : '' }}>Cyprus</option>
              <option value="CZ" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'CZ') ? 'selected' : '') : '' }}>Czech Republic</option>
              <option value="DK" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'DK') ? 'selected' : '') : '' }}>Denmark</option>
              <option value="EE" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'EE') ? 'selected' : '') : '' }}>Estonia</option>
              <option value="FI" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'FI') ? 'selected' : '') : '' }}>Finland</option>
              <option value="FR" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'FR') ? 'selected' : '') : '' }}>France</option>
              <option value="DE" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'DE') ? 'selected' : '') : '' }}>Germany</option>
              <option value="GR" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'GR') ? 'selected' : '') : '' }}>Greece</option>
              <option value="HU" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'HU') ? 'selected' : '') : '' }}>Hungary</option>
              <option value="IE" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'IE') ? 'selected' : '') : '' }}>Ireland, Republic of (EIRE)</option>
              <option value="IT" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'IT') ? 'selected' : '') : '' }}>Italy</option>
              <option value="LV" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'LV') ? 'selected' : '') : '' }}>Latvia</option>
              <option value="LT" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'LT') ? 'selected' : '') : '' }}>Lithuania</option>
              <option value="LU" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'LU') ? 'selected' : '') : '' }}>Luxembourg</option>
              <option value="MT" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'MT') ? 'selected' : '') : '' }}>Malta</option>
              <option value="NL" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'NL') ? 'selected' : '') : '' }}>Netherlands</option>
              <option value="NO" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'NO') ? 'selected' : '') : '' }}>Norway</option>             
              <option value="PL" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'PL') ? 'selected' : '') : '' }}>Poland</option>
              <option value="PT" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'PT') ? 'selected' : '') : '' }}>Portugal</option>
              <option value="RO" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'RO') ? 'selected' : '') : '' }}>Romania</option>
              <option value="SK" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'SK') ? 'selected' : '') : '' }}>Slovakia</option>
              <option value="SI" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'SI') ? 'selected' : '') : '' }}>Slovenia</option>
              <option value="ES" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'ES') ? 'selected' : '') : '' }}>Spain</option>
              <option value="SE" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'SE') ? 'selected' : '') : '' }}>Sweden</option>
              <option value="CH" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'CH') ? 'selected' : '') : '' }}>Switzerland</option>
              <option value="GB" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'GB') ? 'selected' : '') : '' }}>United Kingdom</option>
            </optgroup>
            <optgroup label="Rest of the world">
              <option value="US" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'US') ? 'selected' : '') : '' }}>United States of America</option>
              <option value="HK" {{ isset($clientlegalrep) ? (($clientlegalrep->lrep_country == 'HK') ? 'selected' : '') : '' }}>Hong Kong</option>
            </optgroup>
          </select>
          <label for="lrep_country">Country</label>          
        </div>           
      </div>
    </div>

    <!-- <div class="col-lg-2 col-xl-2 col-2 p-0 d-flex align-items-center"> -->
    <div class="col-lg-6 col-xl-6 col-6 d-flex align-items-center">      
      <span class="btn btn-label-danger px-2" data-repeater-delete>
        <i class="bx bx-x me-1"></i>
        <span class="align-middle">Delete</span>
      </span>
    </div> 
  </div>
  <hr>
</div>