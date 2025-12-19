<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="modalDeclarationMoveInvoiceFile" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalDeclarationMoveInvoiceFileControls" class="carousel slide" data-bs-interval="false">
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Move Sales Invoice File</h4>
              <div class="onboarding-info text-start">Note: {{ (\Carbon\Carbon::parse($declarations->service_start)->format('M') . ' ' . $vatregmain->org_no) }}</div>               
              <div class="row mt-2 text-start">
                <form id="frm-declaration-salesinvoicefile-move" class="m-0 frm-declaration-salesinvoicefile-move" method="post" action="" enctype="multipart/form-data">
                  @csrf     
                  <input type="hidden" name="move_invoice_file_vat_reg_id" id="move_invoice_file_vat_reg_id" />
                  <input type="hidden" name="move_invoice_file_month_year" id="move_invoice_file_month_year" />
                  <input type="hidden" name="move_invoice_file_invoice_id" id="move_invoice_file_invoice_id" />
                  <input type="hidden" name="move_invoice_file_invoice_no" id="move_invoice_file_invoice_no" />
                  <input type="hidden" name="move_invoice_file_invoice_name" id="move_invoice_file_invoice_name" />
                  <input type="hidden" name="move_invoice_file_tab_name" id="move_invoice_file_tab_name" />
                  <!-- <input type="hidden" name="move_invoice_file_cominvoice_id" id="move_invoice_file_cominvoice_id" />
                  <input type="hidden" name="move_invoice_file_cominvoice_no" id="move_invoice_file_cominvoice_no" /> -->
                 
                  <div class="declaration-compose-salesinvoicefile-move-switch d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="col-md-6">
                      <div class="mb-3">                       
                        <label class="form-label" for="declaration-salesinvoicefile-move">Period</label>
                        <select class="form-select" id="declaration-salesinvoicefile-move" name="declaration_cominvoice_move" required>
                          <option value="" selected>--Select Period--</option>
                          @foreach($vatreg_periods as $key => $vatreg_period)  
                            <option value="{{ $key }}">{{ $vatreg_period }}</option>
                          @endforeach                      
                        </select>
                      </div>
                    </div>
                  </div>                                                                      

                  <!-- Bottom Button/Attachment -->
                  <div class="declaration-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-primary btn-declaration-salesinvoicefile-move-save" id="btn-declaration-salesinvoicefile-move-save">Move</button>
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