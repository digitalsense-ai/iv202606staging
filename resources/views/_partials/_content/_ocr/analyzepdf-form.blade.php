<div class="col"> <!-- col-12 col-xl-9-->
  <div class="card h-100">
    @if(isset($ismanual) && $ismanual)
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 pb-2">
      <div>
        <h5 id="manualInputTitle" class="mb-0">Select an item</h5>
        <small id="manualInputSubtitle" class="text-danger">PDF and correction fields will appear here.</small>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <button id="btnDeleteItem" type="button" class="btn btn-label-danger me-xl-3" disabled>
          <i class="bx bx-trash"></i> Delete
        </button>
        <div class="btn-group" role="group" aria-label="Queue navigation">
          <button id="btnPreviousItem" type="button" class="btn btn-label-primary" disabled>
            <i class="bx bx-chevron-left"></i> Previous
          </button>
          <button id="btnNextItem" type="button" class="btn btn-label-primary" disabled>
            Next <i class="bx bx-chevron-right"></i>
          </button>
        </div>        
      </div>
    </div>
    @endif

    <div class="card-body">
      <div id="manualInputEmpty" class="manual-input-empty text-muted">
        @if(isset($ismanual) && $ismanual)
          Select an error item from the queue.
        @else
          Loading...
        @endif  
      </div>
     
      <div id="manualInputDetail" class="row g-3 d-none manual-input-detail">    
        <div class="col-12 col-lg-9">
          <iframe id="manualPdfViewer" class="manual-input-pdf-frame"></iframe>
        </div>

        <div class="col-12 col-lg-3">
          <form id="manualInputForm" class="manual-input-form">
            @csrf
            <input type="hidden" name="id" id="manual_invoice_id">

            <div class="mb-2">
              <label class="form-label" for="invoice_type">Document Type</label>
              <select id="invoice_type" class="form-select" name="invoice_type" required>
                <option value="">Select</option>
                <option value="com">Commercial Invoice</option>
                <option value="sales">Sales Invoice</option>
              </select>
              <!-- <small class="text-muted">Multiple-invoices is intentionally hidden on manual input.</small> -->
            </div>

            <div class="row">
              <div class="col-6">
                <label class="form-label" for="client_no">Client No.</label>
                <input type="text" id="client_no" class="form-control" name="client_no" required>
              </div>

              <div class="col-6">
                <label class="form-label" for="client_name">Client Name</label>
                <input type="text" id="client_name" class="form-control" name="client_name" readonly required>                  
              </div>
            </div>

            <div class="row">
              <div class="col-12 mb-2 lh-1">
                <small id="clientLookupStatus" class="text-muted">Client name is populated from the PDF<!--client database-->.</small>
              </div>
            </div>

            <div class="row">
              <div class="col-6 mb-2">
                <label class="form-label" for="invoice_date">Invoice Date</label>
                <input type="text" id="invoice_date" class="form-control only-date" name="invoice_date" placeholder="YYYY-MM-DD" required>
              </div>

              <div class="col-6 mb-2">
                <label class="form-label" for="invoice_no">Invoice No.</label>
                <input type="text" id="invoice_no" class="form-control" name="invoice_no" required>
              </div>
            </div>

            <div class="form-check mb-2">
              <input type="checkbox" id="credit_note" class="form-check-input" name="credit_note" value="1">
              <label class="form-check-label" for="credit_note">Credit Note</label>
            </div>

            <div class="row">
              <div class="col-6 mb-2">
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
              <div class="col-6 mb-2">
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
              <div class="col-6 mb-2">
                <label class="form-label" for="vat_rate">VAT %</label>
                <input type="text" id="vat_rate" class="form-control only-amount" name="vat_rate">
              </div>
              <div class="col-6 mb-2">
                <label class="form-label" for="exchange_rate">Exchange Rate</label>
                <input type="text" id="exchange_rate" class="form-control only-amount" name="exchange_rate">
              </div>
            </div>

            <div class="row">
              <div class="col-6 {{ (isset($ismanual) && $ismanual) ? '' : 'mb-2' }}">
                <label class="form-label" for="net_amount">Net Amount</label>
                <input type="text" id="net_amount" class="form-control only-amount" name="net_amount">
              </div>
              <div class="col-6 {{ (isset($ismanual) && $ismanual) ? '' : 'mb-2' }}">
                <label class="form-label" for="exchange_net_amount">Exchange Net</label>
                <input type="text" id="exchange_net_amount" class="form-control only-amount" name="exchange_net_amount">
              </div>
            </div>
            @if(isset($ismanual) && $ismanual)
            <div class="row">
              <div class="col-12 mb-2 lh-1">
                <small class="text-muted">Fill in the actual net amount from the PDF. Do not use the amount after any adjustments.</small>
              </div>
            </div>
            @endif

            <div class="row">
              <div class="col-6 mb-2">
                <label class="form-label" for="vat_amount">VAT Amount</label>
                <input type="text" id="vat_amount" class="form-control only-amount" name="vat_amount">
              </div>
              <div class="col-6 mb-2">
                <label class="form-label" for="exchange_vat_amount">Exchange VAT</label>
                <input type="text" id="exchange_vat_amount" class="form-control only-amount" name="exchange_vat_amount">
              </div>
            </div>

            <div class="row">
              <div class="col-6 mb-2">
                <label class="form-label" for="total_amount">Total Amount</label>
                <input type="text" id="total_amount" class="form-control only-amount" name="total_amount">
              </div>
              <div class="col-6 mb-2">
                <label class="form-label" for="exchange_total_amount">Exchange Total</label>
                <input type="text" id="exchange_total_amount" class="form-control only-amount" name="exchange_total_amount">
              </div>
            </div>

            @if(!isset($ismanual))
              @if(isset($issearch) && $issearch)
                <div class="row">
                  <div class="col-6 mb-2">
                    <label class="form-label" for="original_net_amount">Net Goods Amount</label>
                    <input type="text" id="original_net_amount" class="form-control only-amount" name="original_net_amount">
                  </div>
                  <div class="col-6 mb-2">
                    <label class="form-label" for="discount_amount">Discount Amount</label>
                    <input type="text" id="discount_amount" class="form-control only-amount" name="discount_amount">
                  </div>
                </div>
                <div class="row">              
                  <div class="col-6 mb-2">
                    <label class="form-label" for="additional_amount">Shipping Amount</label>
                    <input type="text" id="additional_amount" class="form-control only-amount" name="additional_amount">
                  </div>
                  <div class="col-6 mb-2">
                    <label class="form-label" for="variance_amount">Variance Amount</label>
                    <input type="text" id="variance_amount" class="form-control only-amount" name="variance_amount">
                  </div>              
                </div>
              @endif
            @endif

            <div class="mb-2">
              <label class="form-label" for="sales_invoice_ref_no">Sales Invoice Ref. No.</label>
              <div class="form-salesinvoice-repeater manual-input-salesinvoice-repeater">
                <button type="button" class="btn btn-label-warning mb-2 py-0" data-repeater-create>+Add</button>
                <div data-repeater-list="sales-invoice" class="h-px-180 overflow-scroll-y">
                  <div data-repeater-item>
                    <div class="row">
                      <div class="mb-2 col-8 mb-0">
                        <input type="text" name="number" class="form-control sales-invoice-ref-no" placeholder="123456" />
                      </div>
                      <div class="mb-2 col-4 d-flex align-items-center mb-0">
                        <button type="button" class="btn btn-label-danger px-2" data-repeater-delete>
                          <i class="bx bx-x me-1"></i>
                          <span class="align-middle">Delete</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label" for="note">Note</label>
              <textarea id="note" class="form-control manual-input-note" name="note" placeholder="Add internal correction note"></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-2">
              @if(isset($ismanual) && $ismanual)
              <button id="btnForceSubmit" type="button" class="btn btn-warning" disabled>Input</button>
              @endif
              <div class="d-flex gap-2">
                @if(isset($ismanual) && $ismanual)
                  <button type="button" class="btn btn-label-secondary" onclick="window.location.href='{{ route('analyze.pdf.index') }}'">Cancel</button>
                @else
                  <button type="button" class="btn btn-label-secondary btn-cancel-analyzepdf-form">Close</button>
                @endif  
              </div>  
              @if(isset($ismanual) && $ismanual)
                <button id="btnSaveManualInput" type="submit" class="btn btn-primary" disabled>Save</button>                  
              @else
                @if(isset($issearch))
                  <input type="hidden" id="searchSave" name="searchSave" value="1">
                  @if(isset($issynced))
                    <input type="hidden" id="syncedPage" name="syncedPage" value="1">
                  @endif
                  @if($issearch)                                          
                    <button id="btnSearchSave" type="submit" class="btn btn-primary" disabled>Save</button>
                  @endif  
                @endif  
              @endif    
              
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>