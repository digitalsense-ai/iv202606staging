<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="modalDeclarationSalesInvoiceMove" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalDeclarationSalesInvoiceMoveControls" class="carousel slide" data-bs-interval="false">
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Move Sales Invoice</h4>
              <div class="onboarding-info text-start">Note: {{ (\Carbon\Carbon::parse($declarations->service_start)->format('M') . ' ' . $vatregmain->org_no) }}</div>               
              <div class="row mt-2 text-start">
                <form id="frm-declaration-salesinvoice-move" class="m-0 frm-declaration-salesinvoice-move" method="post" action="" enctype="multipart/form-data">
                  @csrf     
                  <input type="hidden" name="move_invoice_vat_reg_id" id="move_invoice_vat_reg_id" />
                  <input type="hidden" name="move_month_year" id="move_month_year" />
                  <input type="hidden" name="move_invoice_id" id="move_invoice_id" />
                  <input type="hidden" name="move_invoice_no" id="move_invoice_no" />
                  <input type="hidden" name="move_invoice_name" id="move_invoice_name" />
                  <input type="hidden" name="move_tab_name" id="move_tab_name" />
                  <input type="hidden" name="move_cominvoice_id" id="move_cominvoice_id" />
                  <input type="hidden" name="move_cominvoice_no" id="move_cominvoice_no" />
                  
                  <div class="declaration-compose-cominvoice-move-switch d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="col-md-6">
                      <div class="mb-3">                       
                        <label class="form-label" for="declaration-cominvoice-move">Com. Invoices</label>
                        <select class="form-select" id="declaration-cominvoice-move" name="declaration_cominvoice_move" required>
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
                        <button type="submit" class="btn btn-primary btn-declaration-salesinvoice-move-save" id="btn-declaration-salesinvoice-move-save">Move</button>
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