<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="modalDeclarationComInvoiceRematch" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalDeclarationComInvoiceRematchControls" class="carousel slide" data-bs-interval="false">
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Rematch Com.Invoice</h4>
              <div class="onboarding-info text-start">Note: {{ (\Carbon\Carbon::parse($declarations->service_start)->format('M') . ' ' . $vatregmain->org_no) }}</div>               
              <div class="row mt-2 text-start">
                <form id="frm-declaration-cominvoice-rematch" class="m-0 frm-declaration-cominvoice-rematch" method="post" action="" enctype="multipart/form-data">
                  @csrf     
                  <input type="hidden" name="rematch_invoice_vat_reg_id" id="rematch_invoice_vat_reg_id" />
                  <input type="hidden" name="rematch_month_year" id="rematch_month_year" />
                  <input type="hidden" name="rematch_invoice_id" id="rematch_invoice_id" />
                  <input type="hidden" name="rematch_invoice_no" id="rematch_invoice_no" />
                  <input type="hidden" name="rematch_invoice_name" id="rematch_invoice_name" />
                  <input type="hidden" name="rematch_tab_name" id="rematch_tab_name" />
                  <!-- <input type="hidden" name="comment_visiblity" id="comment_visiblity" />
                  <input type="hidden" name="is_disregard" id="is_disregard" /> -->

                  <div class="row mb-3 declaration-cominvoice-rematch-split">
                    <div class="col-md-6">                                          
                        <label class="form-label" for="no_of_split">No. of splits</label>
                        <input type="textbox" name="no_of_split" id="no_of_split" class="form-control small">                     
                    </div>
                  </div>

                  <div class="declaration-compose-cominvoice-rematch-switch d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="col-md-6">
                      <div class="mb-3">                       
                        <label class="form-label" for="declaration-cominvoice-rematch">Com. Invoices</label>
                        <select class="form-select" id="declaration-cominvoice-rematch" name="declaration_cominvoice_rematch" required>
                          <option value="" selected>--Select Com. Invoices--</option>                           
                        </select>
                      </div>
                    </div>
                  </div>                                                                      

                  <!-- Bottom Button/Attachment -->
                  <div class="declaration-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-primary btn-declaration-cominvoice-rematch-save" id="btn-declaration-cominvoice-rematch-save">Re-match</button>
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
<!--/ Onboarding slider modals