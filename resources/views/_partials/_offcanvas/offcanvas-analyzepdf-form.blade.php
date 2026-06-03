<div class="offcanvas offcanvas-end w-80" tabindex="-1" id="offcanvasAnalyzePdfData" aria-labelledby="offcanvasAnalyzePdfDataLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasAnalyzePdfDataLabel" class="offcanvas-title">PDF Data's</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0" style="height: calc(100vh - 3rem);">
    <div class="row" style="height: 100vh;">
      <div class="col-9">
        <iframe id="docViewer" width="100%" height="100%"></iframe> 
      </div> 
      <div class="col-3">
        <form class="add-analyzepdf pt-0" id="addAnalyzePdfForm">
          @csrf
          <input type="hidden" name="analyzepdf_id" id="analyzepdf_id" value=""> 
          <input type="hidden" name="analyzepdf_status" id="analyzepdf_status" value="">  
          
          <div class="mb-3">
            <label class="form-label" for="invoice_type">Document Type</label>          
            <select id="invoice_type" class="form-select" name="invoice_type" required>
              <option value="">Select</option>
              <option value="com">Commercial Invoice</option>             
              <option value="multi-invoices">Multi invoices in single PDF</option>
              <option value="sales">Sales Invoice</option>
            </select> 
          </div>    
          <div class="mb-3">
            <label class="form-label" for="client_no">Client No.</label>          
            <input type="text" id="client_no" class="form-control" placeholder="987654321" aria-label="987654321" name="client_no" required value="" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="client_name">Client Name</label>          
            <input type="text" id="client_name" class="form-control" placeholder="John" aria-label="John" name="client_name" required value="" />
          </div>
          
          <div class="mb-3">
            <label class="form-label" for="invoice_date">Invoice Date</label>          
            <input type="text" id="invoice_date" class="form-control" placeholder="Y-M-D" aria-label="2025-01-01" name="invoice_date" required value="" />  
          </div>
          <div class="mb-3">
            <label class="form-label" for="invoice_no">Invoice No.</label>          
            <input type="text" id="invoice_no" class="form-control" placeholder="25874" aria-label="25874" name="invoice_no" required value="" />
          </div>

          <div class="mb-3">
            <input type="checkbox" id="credit_note" class="form-check-input" name="credit_note" value="" />
            <label class="form-check-label" for="credit_note">Credit Note</label>                      
          </div>          

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="currency">Currency</label>          
              <select id="currency" class="form-select" name="currency" required> 
                <option value="">Select</option>            
                <option value="CHF">CHF</option> 
                <option value="DKK">DKK</option> 
                <option value="EUR">EUR</option> 
                <option value="NOK">NOK</option>
                <option value="GBP">GBP</option> 
                <option value="PLN">PLN</option>
                <option value="SEK">SEK</option>
                <option value="USD">USD</option>         
              </select> 
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label" for="exchange_currency">Exchange Currency</label>          
              <select id="exchange_currency" class="form-select" name="exchange_currency">    
                <option value="">Select</option>
                <option value="CHF">CHF</option>          
                <option value="DKK">DKK</option>
                <option value="EUR">EUR</option> 
                <option value="NOK">NOK</option>
                <option value="GBP">GBP</option>
                <option value="PLN">PLN</option>
                <option value="SEK">SEK</option> 
                <option value="USD">USD</option>         
              </select> 
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="vat_rate">Vat %</label>          
              <input type="text" id="vat_rate" class="form-control" placeholder="25" aria-label="25" name="vat_rate" value="" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label" for="exchange_rate">Exchange Rate</label>          
              <input type="text" id="exchange_rate" class="form-control" placeholder="0" aria-label="0" name="exchange_rate" value="" />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="net_amount">Net Amount</label>          
              <input type="text" id="net_amount" class="form-control" placeholder="0" aria-label="0" name="net_amount" value="" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label" for="exchange_net_amount">Exchange Net Amount</label>          
              <input type="text" id="exchange_net_amount" class="form-control" placeholder="0" aria-label="0" name="exchange_net_amount" value="" />
            </div>
          </div>

          <div class="row">          
            <div class="col-md-6 mb-3">
              <label class="form-label" for="vat_amount">Vat Amount</label>          
              <input type="text" id="vat_amount" class="form-control" placeholder="0" aria-label="0" name="vat_amount" value="" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label" for="exchange_vat_amount">Exchange Vat Amount</label>          
              <input type="text" id="exchange_vat_amount" class="form-control" placeholder="0" aria-label="0" name="exchange_vat_amount" value="" />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="total_amount">Total Amount</label>          
              <input type="text" id="total_amount" class="form-control" placeholder="0" aria-label="0" name="total_amount" required value="" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label" for="exchange_total_amount">Exchange Total Amount</label>          
              <input type="text" id="exchange_total_amount" class="form-control" placeholder="0" aria-label="0" name="exchange_total_amount" value="" />
            </div>
          </div>          

          <div class="mb-3">
            <label class="form-label" for="sales_invoice_ref_no">Sales Invoice Ref. No.</label>  
            <div class="form-salesinvoice-repeater h-px-240 overflow-scroll-y">
              <button type="button" class="btn btn-label-warning" data-repeater-create>+Add</button>
              <div data-repeater-list="sales-invoice">
                <div data-repeater-item>
                  <div class="row">
                    <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0 w-60">
                      <!-- <label class="form-label" for="form-salesinvoice-repeater-1-1">Username</label> -->
                      <!-- <input type="text" id="form-salesinvoice-repeater-1-1" name="number" class="form-control sales-invoice-ref-no" placeholder="123456" /> -->
                      <input type="text" name="number" class="form-control sales-invoice-ref-no" placeholder="123456" />
                    </div>

                    <div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
                      <button type="button" class="btn btn-label-danger" data-repeater-delete>
                        <i class="bx bx-x me-1"></i>
                        <span class="align-middle">Delete</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
                            
            </div>      
            <!-- <ul id="sales_invoice_ref_no">

            </ul> -->
          </div>
                               
          <button type="submit" class="btn btn-primary me-sm-3 me-1 btn-save-analyze-data">Save</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>