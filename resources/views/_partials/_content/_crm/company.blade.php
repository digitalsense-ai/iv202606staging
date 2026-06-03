<div class="input-group mb-3">  
  <div class="form-floating">
    <input type="text" class="form-control" id="crm_client_name" name="crm_client_name" placeholder="ACME Inc." aria-describedby="crmClientNameHelp"  value="{{ isset($lead) ? $lead->company_name : (isset($company) ? $company['name'] : '') }}" required />
    <label for="crm_client_name">Company Name</label>           
  </div>           
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="crm_off_address" name="crm_off_address" placeholder="Street name, house number etc.," aria-describedby="crmClientAddressHelp" value="{{ isset($lead) ? $lead->company_address : (isset($company) ? $company['address'] : '') }}" required />
    <label for="crm_off_address">Address</label>          
  </div>    
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="crm_off_postcode" name="crm_off_postcode" placeholder="2000" aria-describedby="crmOffPostcodeHelp" value="{{ isset($lead) ? $lead->company_postcode : (isset($company) ? $company['postcode'] : '') }}" required />
    <label for="crm_off_postcode">Zipcode</label>          
  </div>          
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="crm_off_city" name="crm_off_city" placeholder="Copenhagen" aria-describedby="crmOffCityHelp" value="{{ isset($lead) ? $lead->company_city : (isset($company) ? $company['city'] : '') }}" required />
    <label for="crm_off_city">City</label>          
  </div>         
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <select id="formValidationSelect2" class="form-select" data-allow-clear="true" name="crm_off_country" required>
      <option value="">Select</option>
      <optgroup label="Europe">  
        <option value="AT" {{
            (isset($lead) && $lead->company_country == 'AT') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'AT')
                ? 'selected'
                : ''
        }}>Austria</option>
        <option value="BE" {{
            (isset($lead) && $lead->company_country == 'BE') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'BE')
                ? 'selected'
                : ''
        }}>Belgium</option>
        <option value="BG" {{
            (isset($lead) && $lead->company_country == 'BG') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'BG')
                ? 'selected'
                : ''
        }}>Bulgaria</option>
        <option value="HR" {{
            (isset($lead) && $lead->company_country == 'HR') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'HR')
                ? 'selected'
                : ''
        }}>Croatia</option>
        <option value="CY" {{
            (isset($lead) && $lead->company_country == 'CY') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'CY')
                ? 'selected'
                : ''
        }}>Cyprus</option>
        <option value="CZ" {{
            (isset($lead) && $lead->company_country == 'CZ') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'CZ')
                ? 'selected'
                : ''
        }}>Czech Republic</option>
        <option value="DK" {{
            (isset($lead) && $lead->company_country == 'DK') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'DK')
                ? 'selected'
                : ''
        }}>Denmark</option>
        <option value="EE" {{
            (isset($lead) && $lead->company_country == 'EE') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'EE')
                ? 'selected'
                : ''
        }}>Estonia</option>
        <option value="FI" {{
            (isset($lead) && $lead->company_country == 'FI') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'FI')
                ? 'selected'
                : ''
        }}>Finland</option>
        <option value="FR" {{
            (isset($lead) && $lead->company_country == 'FR') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'FR')
                ? 'selected'
                : ''
        }}>France</option>
        <option value="DE" {{
            (isset($lead) && $lead->company_country == 'DE') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'DE')
                ? 'selected'
                : ''
        }}>Germany</option>
        <option value="GR" {{
            (isset($lead) && $lead->company_country == 'GR') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'GR')
                ? 'selected'
                : ''
        }}>Greece</option>
        <option value="HU" {{
            (isset($lead) && $lead->company_country == 'HU') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'HU')
                ? 'selected'
                : ''
        }}>Hungary</option>
        <option value="IE" {{
            (isset($lead) && $lead->company_country == 'IE') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'IE')
                ? 'selected'
                : ''
        }}>Ireland, Republic of (EIRE)</option>
        <option value="IT" {{
            (isset($lead) && $lead->company_country == 'IT') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'IT')
                ? 'selected'
                : ''
        }}>Italy</option>
        <option value="LV" {{
            (isset($lead) && $lead->company_country == 'LV') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'LV')
                ? 'selected'
                : ''
        }}>Latvia</option>
        <option value="LT" {{
            (isset($lead) && $lead->company_country == 'LT') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'LT')
                ? 'selected'
                : ''
        }}>Lithuania</option>
        <option value="LU" {{
            (isset($lead) && $lead->company_country == 'LU') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'LU')
                ? 'selected'
                : ''
        }}>Luxembourg</option>
        <option value="MT" {{
            (isset($lead) && $lead->company_country == 'MT') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'MT')
                ? 'selected'
                : ''
        }}>Malta</option>
        <option value="NL" {{
            (isset($lead) && $lead->company_country == 'NL') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'NL')
                ? 'selected'
                : ''
        }}>Netherlands</option>
        <option value="NO" {{
            (isset($lead) && $lead->company_country == 'NO') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'NO')
                ? 'selected'
                : ''
        }}>Norway</option>             
        <option value="PL" {{
            (isset($lead) && $lead->company_country == 'PL') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'PL')
                ? 'selected'
                : ''
        }}>Poland</option>
        <option value="PT" {{
            (isset($lead) && $lead->company_country == 'PT') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'PT')
                ? 'selected'
                : ''
        }}>Portugal</option>
        <option value="RO" {{
            (isset($lead) && $lead->company_country == 'RO') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'RO')
                ? 'selected'
                : ''
        }}>Romania</option>
        <option value="SK" {{
            (isset($lead) && $lead->company_country == 'SK') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'SK')
                ? 'selected'
                : ''
        }}>Slovakia</option>
        <option value="SI" {{
            (isset($lead) && $lead->company_country == 'SI') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'SI')
                ? 'selected'
                : ''
        }}>Slovenia</option>
        <option value="ES" {{
            (isset($lead) && $lead->company_country == 'ES') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'ES')
                ? 'selected'
                : ''
        }}>Spain</option>
        <option value="SE" {{
            (isset($lead) && $lead->company_country == 'SE') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'SE')
                ? 'selected'
                : ''
        }}>Sweden</option>
        <option value="CH" {{
            (isset($lead) && $lead->company_country == 'CH') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'CH')
                ? 'selected'
                : ''
        }}>Switzerland</option>
        <option value="GB" {{
            (isset($lead) && $lead->company_country == 'GB') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'GB')
                ? 'selected'
                : ''
        }}>United Kingdom</option>
      </optgroup>
      <optgroup label="Rest of the world">
        <option value="US" {{
            (isset($lead) && $lead->company_country == 'US') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'US')
                ? 'selected'
                : ''
        }}>United States of America</option>
        <option value="HK" {{
            (isset($lead) && $lead->company_country == 'HK') ||
            (isset($company) && ($company['countrycode'] ?? '') == 'HK')
                ? 'selected'
                : ''
        }}>Hong Kong</option>
      </optgroup>
    </select>
    <label for="formValidationSelect2">Country</label>          
  </div>           
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="crm_telephone" name="crm_telephone" placeholder="658 799 8941" aria-describedby="crmTelephoneHelp" value="{{ isset($lead) ? $lead->company_telephone : (isset($company) ? $company['telephone'] : '') }}" required />            
  </div>           
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="email" class="form-control" id="crm_lrep_email" name="crm_lrep_email" placeholder="john.doe" aria-describedby="crmLrepEmailHelp" value="{{ isset($lead) ? $lead->company_email : (isset($company) ? $company['email'] : '') }}" required />
    <label for="crm_lrep_email">Email</label>          
  </div>           
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="crm_website" name="crm_website" placeholder="wwww.mydomain.com" aria-describedby="crmWebsiteHelp" value="{{ isset($lead) ? $lead->company_website : (isset($company) ? $company['website'] : '') }}" required />
    <label for="crm_website">Website</label>          
  </div>           
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="crm_short_desc" name="crm_short_desc" placeholder="Anpartsselskab" aria-describedby="crmLrepAddressHelp" value="{{ isset($lead) ? $lead->company_desc : (isset($company) ? $company['desc'] : '') }}" required />
    <label for="crm_short_desc">Company Desc.</label>          
  </div>           
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="crm_employees" name="crm_employees" placeholder="10" aria-describedby="crmEmployeesHelp" value="{{ isset($lead) ? $lead->company_employees : (isset($company) ? $company['employees'] : '') }}" />
    <label for="crm_employees">Employees</label>          
  </div>           
</div>

{{--
<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="start_date" name="start_date" placeholder="DD-MM-YYYY" aria-describedby="startDateHelp" value="{{ isset($lead) ? $lead->start_date : (isset($company) ? $company->name : '') }}" />
    <label for="start_date">Start Date</label>          
  </div>          
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" class="form-control" id="end_date" name="end_date" placeholder="DD-MM-YYYY" aria-describedby="endDateHelp" value="{{ isset($lead) ? $lead->end_date : (isset($company) ? $company->name : '') }}" />
    <label for="end_date">End Date</label>          
  </div>          
</div>
--}}