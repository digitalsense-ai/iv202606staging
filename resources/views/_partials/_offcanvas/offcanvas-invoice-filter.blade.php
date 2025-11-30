<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasInvoiceFilter" aria-labelledby="offcanvasInvoiceFilterLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasInvoiceFilterLabel" class="offcanvas-title">Invoice Filter</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body my-auto mx-0 py-0">  <!-- flex-grow-0 -->
    <form method="post" class="form-invoice-filter">  
      @csrf
      <input type="hidden" id="user-id" name="user_id" value="{{ $authUser->user_id }}">

      {{--
      <div class="row">
        <div class="col-md-12 invoice-filter">
          
          <small class="text-light fw-medium">Invoice Filter</small>
          <div class="form-check mt-2 mb-3">
            <input class="form-check-input" type="checkbox" value="" id="chk-invoice-filter-show-err-lines" name="chk_invoice_filter_show_err_lines" />
            <label class="form-check-label" for="chk_invoice_filter_show_err_lines">
               Show only error lines
            </label>
          </div>

        </div><!--/ col -->
      </div>
      --}}

      <div class="row">
        <div class="col-md-12 invoice-filter">         
          <div class="form-check mt-2 mb-3">
            <input class="form-check-input" type="checkbox" value="" id="chk-invoice-filter-show-disregarded-invoices" name="chk_invoice_filter_show_disregarded_invoices" />
            <label class="form-check-label" for="chk_invoice_filter_show_disregarded_invoices">
               Show disregarded invoices
            </label>
          </div>

        </div><!--/ col -->
      </div>
        
    </form>
  </div>
</div>