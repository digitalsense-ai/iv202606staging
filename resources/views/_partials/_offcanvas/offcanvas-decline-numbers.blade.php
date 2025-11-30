<!-- Decline Numbers Sidebar -->
<div class="offcanvas offcanvas-end" id="declineNumbersOffcanvas" aria-hidden="true">
  <div class="offcanvas-header border-bottom">
    <h6 class="offcanvas-title">Decline Numbers</h6>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-1">
    <form id="formDeclineNumbers" class="mb-3" data-vatid="{{ $vat_reg_id }}">
      @csrf
      <div class="mb-3">
        <label for="reason-for-decline-numbers" class="form-label">Add reason</label>
        <input type="hidden" id="decline-company-vat" name="decline_company_vat" required value="{{ $client->vatno }}">
          <textarea class="form-control reason-for-decline-numbers" id="reason-for-decline-numbers" name="reason_for_decline_numbers" data-vatid="{{ $vat_reg_id }}" rows="5" required></textarea>
      </div>                
      <div class="mb-3 d-flex flex-wrap">
        <button type="submit" class="btn btn-primary me-3 btn-decline-numbers">Submit</button>
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>
  </div>
</div>
<!-- /Decline Numbers Sidebar -->
