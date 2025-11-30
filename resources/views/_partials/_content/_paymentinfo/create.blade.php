<!-- Basic Layout -->
<div class="row">
  
  <div class="col-xl">
    <div class="mb-4">          
      <form id="formPaymentInfo-{{ $payment_info_id }}" class="card-body needs-validation formPaymentInfo" novalidate>
        @csrf               
        <input type="hidden" name="id" id="payment_info_id" value="{{ $payment_info_id }}">
        <input type="hidden" name="countrycode" id="countrycode" value="{{ ($paymentinfo) ? $paymentinfo->countrycode : ''}}">
        <h5>From UK local bank account:</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-sortcode">Sort code</label>
              <div class="input-group input-group-merge">              
                <input type="text" id="basic-icon-default-sortcode" class="form-control" placeholder="" aria-label="" aria-describedby="basic-icon-default-sortcode2" name="sortcode" required value="{{ ($paymentinfo) ? $paymentinfo->sortcode : ''}}" />                
              </div>
              <div class="valid-feedback"> Looks good! </div>
              <div class="invalid-feedback"> Please enter Sort Code. </div>
            </div>            
          </div>
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-accountno">Account Number</label>
              <div class="input-group input-group-merge">               
                <input type="text" class="form-control" id="basic-icon-default-accountno" placeholder="" aria-label="" aria-describedby="basic-icon-default-accountno2" name="accountno" required value="{{ ($paymentinfo) ? $paymentinfo->accountno : ''}}" />                
              </div>
              <div class="valid-feedback"> Looks good! </div>
              <div class="invalid-feedback"> Please enter Account Number. </div>
            </div>
          </div>
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-accountname">Account Name</label>
              <div class="input-group input-group-merge">               
                <input type="text" class="form-control" id="basic-icon-default-accountname" placeholder="" aria-label="" aria-describedby="basic-icon-default-accountname2" name="accountname" required value="{{ ($paymentinfo) ? $paymentinfo->accountname : ''}}"/>                
              </div>
              <div class="valid-feedback"> Looks good! </div>
              <div class="invalid-feedback"> Please enter Account Name. </div>
            </div>
          </div>
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-paymentref">Payment reference</label>
              <div class="input-group input-group-merge">               
                <input type="text" id="basic-icon-default-paymentref" class="form-control" placeholder="" aria-label="" aria-describedby="basic-icon-default-paymentref2" name="paymentref" required value="{{ ($paymentinfo) ? $paymentinfo->paymentref : ''}}"/>
              </div>
              <div class="valid-feedback"> Looks good! </div>
              <div class="invalid-feedback"> Please enter Payment reference. </div>              
            </div>
          </div>  
          <div class="col-md-6"></div>         
        </div>

        <hr class="my-4 mx-n4" />    

        <h5>From a foreign bank account:</h5>   
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-bic">Bank identifier code (BIC)</label>
              <div class="input-group input-group-merge">              
                <input type="text" id="basic-icon-default-bic" class="form-control" placeholder="" aria-label="" aria-describedby="basic-icon-default-bic2" name="bic" required value="{{ ($paymentinfo) ? $paymentinfo->bic : ''}}" />                
              </div>
              <div class="valid-feedback"> Looks good! </div>
              <div class="invalid-feedback"> Please enter Bank identifier code (BIC). </div>
            </div>            
          </div>
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-iban">Account number (IBAN)</label>
              <div class="input-group input-group-merge">               
                <input type="text" class="form-control" id="basic-icon-default-iban" placeholder="" aria-label="" aria-describedby="basic-icon-default-iban2" name="iban" required value="{{ ($paymentinfo) ? $paymentinfo->iban : ''}}" />                
              </div>
              <div class="valid-feedback"> Looks good! </div>
              <div class="invalid-feedback"> Please enter Account number (IBAN). </div>
            </div>
          </div>
          <div class="col-md-6"></div>                
        </div>

        <hr class="my-4 mx-n4" />    
            
        <h5>HMRC's bank address:</h5>         
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-bankname">Bank Name</label>
              <div class="input-group input-group-merge">               
                <input type="text" class="form-control" id="basic-icon-default-bankname" placeholder="" aria-label="" aria-describedby="basic-icon-default-off-bankname2" name="bankname" required value="{{ ($paymentinfo) ? $paymentinfo->bankname : ''}}"/>
              </div>
            </div>
          </div>  
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-address">Address</label>
              <div class="input-group input-group-merge">             
                <input type="text" class="form-control" id="basic-icon-default-address" placeholder="" aria-label="" aria-describedby="basic-icon-default-address2" name="address" required value="{{ ($paymentinfo) ? $paymentinfo->address : ''}}"/>
              </div>
            </div>
          </div>
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-city">City</label>
              <div class="input-group input-group-merge">             
                <input type="text" class="form-control" id="basic-icon-default-city" placeholder="" aria-label="" aria-describedby="basic-icon-default-city2" name="city" required value="{{ ($paymentinfo) ? $paymentinfo->city : ''}}"/>
              </div>
            </div>
          </div>
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <label class="form-label" for="formValidationSelect2">Country</label>
            <select id="formValidationSelect2" class="form-select mb-3" data-allow-clear="true" name="country" required>
              <option value="">Select</option>
              <option value="Austria" {{ ($paymentinfo) ? (($paymentinfo->country == 'Austria') ? 'selected' : '') : '' }}>Austria</option>
              <option value="Belgium" {{ ($paymentinfo) ? (($paymentinfo->country == 'Belgium') ? 'selected' : '') : '' }}>Belgium</option>
              <option value="Bulgaria" {{ ($paymentinfo) ? (($paymentinfo->country == 'Bulgaria') ? 'selected' : '') : '' }}>Bulgaria</option>
              <option value="Croatia" {{ ($paymentinfo) ? (($paymentinfo->country == 'Croatia') ? 'selected' : '') : '' }}>Croatia</option>
              <option value="Cyprus" {{ ($paymentinfo) ? (($paymentinfo->country == 'Cyprus') ? 'selected' : '') : '' }}>Cyprus</option>
              <option value="Czech Republic" {{ ($paymentinfo) ? (($paymentinfo->country == 'Czech Republic') ? 'selected' : '') : '' }}>Czech Republic</option>
              <option value="Denmark" {{ ($paymentinfo) ? (($paymentinfo->country == 'Denmark') ? 'selected' : '') : '' }}>Denmark</option>
              <option value="Estonia" {{ ($paymentinfo) ? (($paymentinfo->country == 'Estonia') ? 'selected' : '') : '' }}>Estonia</option>
              <option value="Finland" {{ ($paymentinfo) ? (($paymentinfo->country == 'Finland') ? 'selected' : '') : '' }}>Finland</option>
              <option value="France" {{ ($paymentinfo) ? (($paymentinfo->country == 'France') ? 'selected' : '') : '' }}>France</option>
              <option value="Germany" {{ ($paymentinfo) ? (($paymentinfo->country == 'Germany') ? 'selected' : '') : '' }}>Germany</option>
              <option value="Greece" {{ ($paymentinfo) ? (($paymentinfo->country == 'Greece') ? 'selected' : '') : '' }}>Greece</option>
              <option value="Hungary" {{ ($paymentinfo) ? (($paymentinfo->country == 'Hungary') ? 'selected' : '') : '' }}>Hungary</option>
              <option value="Ireland, Republic of (EIRE)" {{ ($paymentinfo) ? (($paymentinfo->country == 'Ireland, Republic of (EIRE)') ? 'selected' : '') : '' }}>Ireland, Republic of (EIRE)</option>
              <option value="Italy" {{ ($paymentinfo) ? (($paymentinfo->country == 'Italy') ? 'selected' : '') : '' }}>Italy</option>
              <option value="Latvia" {{ ($paymentinfo) ? (($paymentinfo->country == 'Latvia') ? 'selected' : '') : '' }}>Latvia</option>
              <option value="Lithuania" {{ ($paymentinfo) ? (($paymentinfo->country == 'Lithuania') ? 'selected' : '') : '' }}>Lithuania</option>
              <option value="Luxembourg" {{ ($paymentinfo) ? (($paymentinfo->country == 'Luxembourg') ? 'selected' : '') : '' }}>Luxembourg</option>
              <option value="Malta" {{ ($paymentinfo) ? (($paymentinfo->country == 'Malta') ? 'selected' : '') : '' }}>Malta</option>
              <option value="Netherlands" {{ ($paymentinfo) ? (($paymentinfo->country == 'Netherlands') ? 'selected' : '') : '' }}>Netherlands</option>
              <option value="Norway" {{ ($paymentinfo) ? (($paymentinfo->country == 'Norway') ? 'selected' : '') : '' }}>Norway</option>             
              <option value="Poland" {{ ($paymentinfo) ? (($paymentinfo->country == 'Poland') ? 'selected' : '') : '' }}>Poland</option>
              <option value="Portugal" {{ ($paymentinfo) ? (($paymentinfo->country == 'Portugal') ? 'selected' : '') : '' }}>Portugal</option>
              <option value="Romania" {{ ($paymentinfo) ? (($paymentinfo->country == 'Romania') ? 'selected' : '') : '' }}>Romania</option>
              <option value="Slovakia" {{ ($paymentinfo) ? (($paymentinfo->country == 'Slovakia') ? 'selected' : '') : '' }}>Slovakia</option>
              <option value="Slovenia" {{ ($paymentinfo) ? (($paymentinfo->country == 'Slovenia') ? 'selected' : '') : '' }}>Slovenia</option>
              <option value="Spain" {{ ($paymentinfo) ? (($paymentinfo->country == 'Spain') ? 'selected' : '') : '' }}>Spain</option>
              <option value="Sweden" {{ ($paymentinfo) ? (($paymentinfo->country == 'Sweden') ? 'selected' : '') : '' }}>Sweden</option>
              <option value="Switzerland" {{ ($paymentinfo) ? (($paymentinfo->country == 'Switzerland') ? 'selected' : '') : '' }}>Switzerland</option>
              <option value="United Kingdom" {{ ($paymentinfo) ? (($paymentinfo->country == 'United Kingdom') ? 'selected' : '') : '' }}>United Kingdom</option>
            </select>
          </div>   
          <div class="col-md-6"></div>                
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="basic-icon-default-postcode">Post code</label>
              <div class="input-group input-group-merge">             
                <input type="text" class="form-control" id="basic-icon-default-postcode" placeholder="" aria-label="" aria-describedby="basic-icon-default-postcode2" name="postcode" required value="{{ ($paymentinfo) ? $paymentinfo->postcode : ''}}"/>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 pt-4 text-end">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>