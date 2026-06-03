<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAnalyzePdfFilter" aria-labelledby="offcanvasAnalyzePdfFilterLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasAnalyzePdfFilterLabel" class="offcanvas-title">PDF Filter</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body my-auto mx-0 py-0">  <!-- flex-grow-0 -->
    <form method="post" class="form-analyzepdf-filter">  
      @csrf
      <input type="hidden" id="user-id" name="user_id" value="{{ $authUser->user_id }}">

      <div class="mb-3">
        <label class="form-label" for="filter_invoice_type">Document Type</label>          
        <select id="filter_invoice_type" class="form-select" name="filter_invoice_type">
          <option value="">Select</option>    
          <option value="multi-invoices">Multi invoices in single PDF</option> 
          <option value="sales">Sales Invoice</option>
          <option value="com">Commercial Invoice</option>
        </select> 
      </div>    
      <div class="mb-3">
        <label class="form-label" for="filter_client_no">Client No.</label>          
        <input type="text" id="filter_client_no" class="form-control" placeholder="987654321" aria-label="987654321" name="filter_client_no" value="" />
      </div>
      <div class="mb-3">
        <label class="form-label" for="filter_client_name">Client Name</label>          
        <input type="text" id="filter_client_name" class="form-control" placeholder="John" aria-label="John" name="filter_client_name" value="" />
      </div>

      <div class="mb-3">
        <label class="form-label" for="filter_invoice_date">Invoice Date</label>          
        <input type="date" id="filter_invoice_date" class="form-control" placeholder="dd/mm/yyyy" aria-label="01-01-2000" name="filter_invoice_date" value="" />
      </div>
      <div class="mb-3">
        <label class="form-label" for="filter_invoice_no">Invoice No.</label>          
        <input type="text" id="filter_invoice_no" class="form-control" placeholder="" aria-label="" name="filter_invoice_no" value="" />
      </div>

      <div class="mb-3">
        <label class="form-label" for="filter_currency">Currency</label>          
        <select id="filter_currency" class="form-select" name="filter_currency">
          <option value="">Select</option>
          <option value="CHF">CHF</option>               
          <option value="DKK">DKK</option> 
          <option value="EUR">EUR</option>          
          <option value="GBP">GBP</option>
          <option value="NOK">NOK</option> 
          <option value="PLN">PLN</option>
          <option value="SEK">SEK</option>
          <option value="USD">USD</option>         
        </select> 
      </div>

      <div class="mb-3">
        <input type="checkbox" id="filter_credit_note" class="form-check-input" name="filter_credit_note" value="" />
        <label class="form-check-label" for="filter_credit_note">Credit Note</label>                      
      </div>

      <div class="mb-3">
        <label class="form-label" for="filter_net_amount">Net Amount</label>          
        <input type="text" id="filter_net_amount" class="form-control" placeholder="0" aria-label="0" name="filter_net_amount" value="" />
      </div>      
      <div class="mb-3">
        <label class="form-label" for="filter_vat_amount">Vat Amount</label>          
        <input type="text" id="filter_vat_amount" class="form-control" placeholder="0" aria-label="0" name="filter_vat_amount" value="" />
      </div>
      <div class="mb-3">
        <label class="form-label" for="filter_total_amount">Total Amount</label>          
        <input type="text" id="filter_total_amount" class="form-control" placeholder="0" aria-label="0" name="filter_total_amount" value="" />
      </div>
    
      <button type="button" class="btn btn-primary mb-2 w-100 btn-analyzepdf-filter">Filter</button>
      <button type="button" class="btn btn-warning mb-2 w-100 btn-analyzepdf-clear-filter">Clear</button>
      <button type="button" class="btn btn-label-secondary d-grid w-100" data-bs-dismiss="offcanvas">Cancel</button>
    </form>
  </div>
</div>