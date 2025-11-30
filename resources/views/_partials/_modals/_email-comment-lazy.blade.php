<div class="divider">
  <div class="divider-text p-0"></div>
</div>

<div class="email-comment-item">            
  <div class="onboarding-content">
    <h4 class="onboarding-title text-body text-start">Include text in the email</h4>            
      <div class="row mt-3">
        <div class="col-sm-12">
          <div class="mb-3">                      
            <label for="email-message-{{ $vat_reg_id }}" class="form-label">Include text in the email</label>
            <textarea class="form-control email-message" id="email-message-{{ $vat_reg_id }}" name="email_message" data-vatid="{{ $vat_reg_id }}" rows="3"></textarea>
          </div>
        </div>

        @if($file_type == 'lock')
          <!-- Date Picker-->
          <div class="col-md-6 col-12 mb-4">
            <label for="payment_date-{{ $vat_reg_id }}" class="form-label">Last Payment Date</label>
            <input type="text" class="form-control payment-date" placeholder="DD-MM-YYYY" id="payment_date-{{ $vat_reg_id }}" name="payment_date" required onkeypress="return false;" />
          </div>
          <!-- /Date Picker -->
        @endif
                                                        
      </div>                 
  </div>
</div>