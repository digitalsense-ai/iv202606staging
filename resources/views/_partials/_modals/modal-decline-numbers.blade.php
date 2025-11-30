<!-- Decline Numbers Modal -->
<div class="modal fade" id="modalDeclineNumbers" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      
      <div class="modal-body px-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-start">
          <h3 class="mb-2">Decline Numbers</h3>
          <p>Enter the reason for declining numbers</p>
        </div>
      </div>
      <form class="assign-new-vat-reg pt-0" id="formDeclineNumbers" data-vatid="{{ $vat_reg_id }}"> 
          
        <!-- <h4 class="mb-4 pb-2"> VAT Account No.'s</h4>      --> 
        <div class="col-sm-12">
          <div class="mb-3">                      
            <label for="reason-for-decline-numbers" class="form-label">Add reason</label>
            <input type="hidden" id="decline-company-vat" name="decline_company_vat" required>
            <textarea class="form-control reason-for-decline-numbers" id="reason-for-decline-numbers" name="reason_for_decline_numbers" data-vatid="{{ $vat_reg_id }}" rows="5" required></textarea>
          </div>
        </div> 

        <button class="btn btn-primary btn-decline-numbers float-end" type="submit">Submit</button>
      </form>               
    </div>
  </div>
</div>
<!--/ Decline Numbers Modal -->
