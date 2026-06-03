<div class="offcanvas offcanvas-end w-80" tabindex="-1" id="offcanvasOcrInvoicePdfData" aria-labelledby="offcanvasOcrInvoicePdfDataLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasOcrInvoicePdfDataLabel" class="offcanvas-title">PDF Data's</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0" style="height: calc(100vh - 3rem);">
    <div class="row" style="height: 100vh;">
      <div class="col-9">
        <iframe id="docViewer" width="100%" height="100%"></iframe> 
      </div> 
      <div class="col-3">
        <form class="add-ocr-invoice-pdf pt-0" id="addOcrInvoicePdfForm">
          @csrf
          <input type="hidden" name="dv_invoice_ocr_pdf_id" id="dv_invoice_ocr_pdf_id" value="">  
          
          <div class="mb-3">
            <label class="form-label" for="invoice_type">Document Type</label>          
            <select id="invoice_type" class="form-select" name="invoice_type" required>             
              <option value="sales">Sales Invoice</option> 
              <option value="com">Commercial Invoice</option>          
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
            <input type="text" id="invoice_date" class="form-control" placeholder="d-M-Y" aria-label="01-01-2025" name="invoice_date" required value="" />  
          </div>
          <div class="mb-3">
            <label class="form-label" for="invoice_no">Invoice No.</label>          
            <input type="text" id="invoice_no" class="form-control" placeholder="25874" aria-label="25874" name="invoice_no" required value="" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="currency">Currency</label>          
            <select id="currency" class="form-select" name="currency" required>             
              <option value="DKK">DKK</option> 
              <option value="NOK">NOK</option>
              <option value="GBP">GBP</option> 
              <option value="USD">USD</option>         
            </select> 
          </div>

          <div class="mb-3">
            <input type="checkbox" id="credit_note" class="form-check-input" name="credit_note" required value="" />
            <label class="form-check-label" for="credit_note">Credit Note</label>                      
          </div>

          <div class="mb-3">
            <label class="form-label" for="net_amount">Net Amount</label>          
            <input type="text" id="net_amount" class="form-control" placeholder="0" aria-label="0" name="net_amount" required value="" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="vat_rate">Vat %</label>          
            <input type="text" id="vat_rate" class="form-control" placeholder="25" aria-label="25" name="vat_rate" required value="" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="vat_amount">Vat Amount</label>          
            <input type="text" id="vat_amount" class="form-control" placeholder="0" aria-label="0" name="vat_amount" required value="" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="total_amount">Total Amount</label>          
            <input type="text" id="total_amount" class="form-control" placeholder="0" aria-label="0" name="total_amount" required value="" />
          </div>

          <div class="mb-3">
            <label class="form-label" for="sales_invoice_ref_no">Sales Invoice Ref. No.</label>  
            <div class="form-salesinvoice-repeater h-px-240 overflow-scroll-y">
              <div data-repeater-list="sales-invoice">
                <div data-repeater-item>
                  <div class="row">
                    <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0 w-60">
                      <!-- <label class="form-label" for="form-salesinvoice-repeater-1-1">Username</label> -->
                      <input type="text" id="form-salesinvoice-repeater-1-1" class="form-control sales-invoice-ref-no" placeholder="123456" />
                    </div>

                    <div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
                      <button class="btn btn-label-danger" disabled="disabled" data-repeater-delete>
                        <i class="bx bx-x me-1"></i>
                        <span class="align-middle">Delete</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <button type="button" data-repeater-create style="display:none"></button>              
            </div>      
            <!-- <ul id="sales_invoice_ref_no">

            </ul> -->
          </div>
                               
          <!--<button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>-->
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>