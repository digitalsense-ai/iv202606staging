<div data-qa_id="{{ isset($clientqa) ? $clientqa->id : ''}}" data-repeater-item>  
  <div class="accordion-item card">
    <input type="hidden" name="qa_id" value="{{ isset($clientqa) ? $clientqa->id : ''}}">
    <h2 class="accordion-header">
      <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionAboutCountry-{{ isset($clientqa) ? $clientqakey : '0'}}-2" aria-expanded="false">        
        <select id="form-about-repeater-{{ isset($clientqa) ? $clientqakey : '0'}}-1" class="form-select about-country" data-allow-clear="true" name="about_country" data-row="0" data-col="1" required>
          <option value="">-- Select Country --</option>
          <optgroup label="Europe">  
            <option value="AT" {{ isset($clientqa) ? (($clientqa->country == 'AT') ? 'selected' : '') : '' }}>Austria</option>
            <option value="BE" {{ isset($clientqa) ? (($clientqa->country == 'BE') ? 'selected' : '') : '' }}>Belgium</option>
            <option value="BG" {{ isset($clientqa) ? (($clientqa->country == 'BG') ? 'selected' : '') : '' }}>Bulgaria</option>
            <option value="HR" {{ isset($clientqa) ? (($clientqa->country == 'HR') ? 'selected' : '') : '' }}>Croatia</option>
            <option value="CY" {{ isset($clientqa) ? (($clientqa->country == 'CY') ? 'selected' : '') : '' }}>Cyprus</option>
            <option value="CZ" {{ isset($clientqa) ? (($clientqa->country == 'CZ') ? 'selected' : '') : '' }}>Czech Republic</option>
            <option value="DK" {{ isset($clientqa) ? (($clientqa->country == 'DK') ? 'selected' : '') : '' }}>Denmark</option>
            <option value="EE" {{ isset($clientqa) ? (($clientqa->country == 'EE') ? 'selected' : '') : '' }}>Estonia</option>
            <option value="FI" {{ isset($clientqa) ? (($clientqa->country == 'FI') ? 'selected' : '') : '' }}>Finland</option>
            <option value="FR" {{ isset($clientqa) ? (($clientqa->country == 'FR') ? 'selected' : '') : '' }}>France</option>
            <option value="DE" {{ isset($clientqa) ? (($clientqa->country == 'DE') ? 'selected' : '') : '' }}>Germany</option>
            <option value="GR" {{ isset($clientqa) ? (($clientqa->country == 'GR') ? 'selected' : '') : '' }}>Greece</option>
            <option value="HU" {{ isset($clientqa) ? (($clientqa->country == 'HU') ? 'selected' : '') : '' }}>Hungary</option>
            <option value="IE" {{ isset($clientqa) ? (($clientqa->country == 'IE') ? 'selected' : '') : '' }}>Ireland, Republic of (EIRE)</option>
            <option value="IT" {{ isset($clientqa) ? (($clientqa->country == 'IT') ? 'selected' : '') : '' }}>Italy</option>
            <option value="LV" {{ isset($clientqa) ? (($clientqa->country == 'LV') ? 'selected' : '') : '' }}>Latvia</option>
            <option value="LT" {{ isset($clientqa) ? (($clientqa->country == 'LT') ? 'selected' : '') : '' }}>Lithuania</option>
            <option value="LU" {{ isset($clientqa) ? (($clientqa->country == 'LU') ? 'selected' : '') : '' }}>Luxembourg</option>
            <option value="MT" {{ isset($clientqa) ? (($clientqa->country == 'MT') ? 'selected' : '') : '' }}>Malta</option>
            <option value="NL" {{ isset($clientqa) ? (($clientqa->country == 'NL') ? 'selected' : '') : '' }}>Netherlands</option>
            <option value="NO" {{ isset($clientqa) ? (($clientqa->country == 'NO') ? 'selected' : '') : '' }}>Norway</option>             
            <option value="PL" {{ isset($clientqa) ? (($clientqa->country == 'PL') ? 'selected' : '') : '' }}>Poland</option>
            <option value="PT" {{ isset($clientqa) ? (($clientqa->country == 'PT') ? 'selected' : '') : '' }}>Portugal</option>
            <option value="RO" {{ isset($clientqa) ? (($clientqa->country == 'RO') ? 'selected' : '') : '' }}>Romania</option>
            <option value="SK" {{ isset($clientqa) ? (($clientqa->country == 'SK') ? 'selected' : '') : '' }}>Slovakia</option>
            <option value="SI" {{ isset($clientqa) ? (($clientqa->country == 'SI') ? 'selected' : '') : '' }}>Slovenia</option>
            <option value="ES" {{ isset($clientqa) ? (($clientqa->country == 'ES') ? 'selected' : '') : '' }}>Spain</option>
            <option value="SE" {{ isset($clientqa) ? (($clientqa->country == 'SE') ? 'selected' : '') : '' }}>Sweden</option>
            <option value="CH" {{ isset($clientqa) ? (($clientqa->country == 'CH') ? 'selected' : '') : '' }}>Switzerland</option>
            <option value="GB" {{ isset($clientqa) ? (($clientqa->country == 'GB') ? 'selected' : '') : '' }}>United Kingdom</option>
          </optgroup>
          <optgroup label="Rest of the world">
            <option value="US" {{ isset($clientqa) ? (($clientqa->country == 'US') ? 'selected' : '') : '' }}>United States of America</option>
          </optgroup>
        </select>

        <span class="btn btn-label-danger float-end mx-2" data-repeater-delete>
          <i class="bx bx-x me-1"></i>
          <span class="align-middle">Delete</span>
        </span>        
      </button>
    </h2>    

    <div id="accordionAboutCountry-{{ isset($clientqa) ? $clientqakey : '0'}}-2" class="accordion-collapse collapse" data-bs-parent="#accordionAboutCountry">
      <div class="accordion-body">
        @include('_partials/_content/_company/about-q-and-a')
      </div>
    </div>
  </div>
</div>