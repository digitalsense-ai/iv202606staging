<div class="currency-convert-item">            
  <div class="onboarding-content">    
      <div class="row mt-3"> 

        <!--Invoice date-->
        <div class="col-sm-12 mb-3">
          <div class="form-check custom-option custom-option-basic">          
            <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="currency-convert-invoice-date-{{ $vat_reg_id }}-{{ $from_currency }}">
              <input name="chk_currency_convert_dates_{{ $vat_reg_id }}_{{ $from_currency }}" class="form-check-input switch-input currency-convert-date" type="radio" data-d_id="{{ $from_currency }}" data-vat_reg_id="{{ $vat_reg_id }}" value="invoice date" id="currency-convert-invoice-date-{{ $vat_reg_id }}-{{ $from_currency }}" />
              <span class="switch-toggle-slider right-3">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="custom-option-header switch-label px-0">
                <span class="h6 mb-0">Invoice date</span>                            
              </span>
              <span class="custom-option-body switch-label text-start px-0 w-100">
                <small>Convert to each invoice date rate (different rates for each invoice)</small>
              </span>
            </label>
          </div>
        </div>

        <!--Todays date-->
        <div class="col-sm-12 mb-3">
          <div class="form-check custom-option custom-option-basic">          
            <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="currency-convert-todays-date-{{ $vat_reg_id }}-{{ $from_currency }}">
              <input name="chk_currency_convert_dates_{{ $vat_reg_id }}_{{ $from_currency }}" class="form-check-input switch-input currency-convert-date" type="radio" data-d_id="{{ $from_currency }}" data-vat_reg_id="{{ $vat_reg_id }}" value="todays date" id="currency-convert-todays-date-{{ $vat_reg_id }}-{{ $from_currency }}" />
              <span class="switch-toggle-slider right-3">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="custom-option-header switch-label px-0">
                <span class="h6 mb-0">Todays date</span>                            
              </span>
              <span class="custom-option-body switch-label text-start px-0 w-100">
                <small>Convert all selected invoices to a specific rate</small>

                <div class="form-floating d-inline-block mx-3">
                  <input type="text" class="form-control" id="currency_convert_todays_rate_{{ $vat_reg_id }}_{{ $from_currency }}" name="currency_convert_todays_rate_{{ $vat_reg_id }}_{{ $from_currency }}" placeholder="11.232" value="{{ ($todays_rate) ? (isset($todays_rate[$from_currency]) ? $todays_rate[$from_currency] : '') : '' }}" />
                  <label for="currency_convert_todays_rate_{{ $vat_reg_id }}_{{ $from_currency }}">Todays rate</label> 
                </div>
              </span>
            </label>
          </div>
        </div>

        <!--Last exchange date-->
        @if(isset($last_exchange_rates))
        <div class="col-sm-12 mb-3" style="{{ isset($last_exchange_rates) ? '' : 'display:none;' }}">
          <div class="form-check custom-option custom-option-basic">          
            <label class="switch form-check-label custom-option-content text-end px-3 fs-big">              
              <span class="custom-option-header switch-label px-0">
                <span class="h6 mb-0">Last exchange rate</span>                            
              </span>
              @foreach($last_exchange_rates as $last_exchange_key => $last_exchange_rate)
                <span class="custom-option-body switch-label text-start px-0 w-100 last-exchange-rate" data-last_exchange_monthyear="{{ $last_exchange_key }}" data-last_exchange_rate="{{ isset($last_exchange_rate) ? $last_exchange_rate : '-' }}">
                  <small>{{ isset($last_exchange_rate) ? $last_exchange_rate : '-' }}</small>                
                </span>
              @endforeach
            </label>
          </div>
        </div>
        @endif

      </div>                
  </div>
</div>      