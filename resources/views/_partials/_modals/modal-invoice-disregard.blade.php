<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="modalInvoiceDisregard" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalInvoiceDisregardControls" class="carousel slide" data-bs-interval="false">
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Disregard Invoice</h4>
              <div class="onboarding-info text-start">{{--Note: {{ (\Carbon\Carbon::parse($vatreg->service_start)->format('M') . ' ' . $vatregmain->org_no) }}--}}</div>               
              <div class="row mt-2 text-start">
                <form id="frm-invoice-disregard" class="m-0 frm-invoice-disregard" method="post" action="" enctype="multipart/form-data">
                  @csrf     
                  <input type="hidden" name="invoice_vat_reg_id" id="invoice_vat_reg_id" />
                  <!-- <input type="hidden" name="month_year" id="month_year" /> -->
                  <input type="hidden" name="invoice_id" id="invoice_id" />
                  <input type="hidden" name="invoice_no" id="invoice_no" />
                  <!-- <input type="hidden" name="invoice_name" id="invoice_name" /> -->
                  <input type="hidden" name="tab_name" id="tab_name" />
                  <!-- <input type="hidden" name="comment_visiblity" id="comment_visiblity" /> -->
                  <input type="hidden" name="is_disregard" id="is_disregard" />
                  <!-- <input type="hidden" name="disregard_type" id="disregard_type" /> -->

                  {{--
                  <!-- Checkboxes and Radios -->
                  <div class="row g-0 invoice-disregard-invoices-select">
                    <div class="col-md p-4">
                      <label class="form-label" for="invoice-disregard-invoices">Select invoices to disregard from view</label>                    
                      <div class="row invoice-disregard-invoices"></div>
                    </div>
                  </div>
                  

                  <div class="compose-invoice-disregard-switch d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="col-md-6">
                      <div class="mb-3">                       
                        <label class="form-label" for="invoice-disregard-reason">Reason</label>
                        <select class="form-select" id="invoice-disregard-reason" name="invoice_disregard" required>
                          <option value="" selected>--Select Reason--</option>                                                   
                            <option value="double-customs-clearance">Double customs clearance</option>  
                            <option value="incorrect-currency">Incorrect currency</option>
                            <option value="period-shift">Period shift</option> 
                            <option value="not-cleared">Not cleared</option> 
                            <option value="complaint">Complaint</option>
                            <option value="replacement-goods">Replacement goods</option>
                            <option value="withdrawal-vat">Withdrawal VAT</option>
                            <option value="other">Other</option>   
                        </select>
                      </div>
                    </div>
                  </div>
--}}
                  <!-- HTML Editor-->
                  <textarea name="invoice_disregard_quill" style="display: none;" id="invoice-disregard-quill"></textarea>
                  <div class="invoice-disregard-compose-message">
                    <div class="d-flex justify-content-end">
                      <div id="invoice-disregard-editor-toolbar" class="border-bottom-0 w-100">
                        <span class="ql-formats me-0">
                          <button class="ql-bold"></button>
                          <button class="ql-italic"></button>
                          <button class="ql-underline"></button>
                          <button class="ql-list" value="ordered"></button>
                          <button class="ql-list" value="bullet"></button>
                          <button class="ql-link"></button>                          
                        </span>
                      </div>
                    </div>
                    <div class="invoice-disregard-editor" id="invoice-disregard-editor"></div>
                  </div> 
                  <!--/ HTML Editor-->                         
                  
                  {{--
                  <div class="com-invoice-visible-switch d-flex justify-content-between align-items-center mt-3 mb-3">
                    <label class="switch">
                      <input type="checkbox" class="switch-input invoice-visible-switch" id="invoice-visible-switch" checked />
                      <span class="switch-toggle-slider">
                        <span class="switch-on"></span>
                        <span class="switch-off"></span>
                      </span>
                      <span class="switch-label">Public</span>
                    </label>
                  </div>
                  --}}

                  <!-- Bottom Button/Attachment -->
                  <div class="compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-primary btn-invoice-disregard-save" id="btn-invoice-disregard-save">Save</button>
                      </div>                                          
                    </div>                      
                  </div>
                  <!-- Bottom Button/Attachment --> 
                </form>         
              </div>
            </div>
          </div>
                    
        </div>        
      </div>      
    </div>
  </div>
</div>
<!--/ Onboarding slider modals-->