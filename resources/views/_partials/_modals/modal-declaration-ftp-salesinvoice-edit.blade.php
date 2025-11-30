<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="modalDeclarationFtpSalesInvoiceEdit" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xxl modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalDeclarationFtpSalesInvoiceEditControls" class="carousel slide" data-bs-interval="false">
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Edit FTP Sales Invoice</h4>
              <div class="onboarding-info text-start">Note: {{-- (\Carbon\Carbon::parse($declarations->service_start)->format('M') . ' ' . $vatregmain->org_no) --}}</div>               
              <div class="row mt-2 text-start">
                <form id="frm-declaration-ftpsalesinvoice-edit" class="m-0 frm-declaration-ftpsalesinvoice-edit" method="post" action="" enctype="multipart/form-data">
                  @csrf     
                  
                  <!-- Bounce -->
                  <div class="sk-bounce sk-primary sk-center">
                    <div class="sk-bounce-dot"></div>
                    <div class="sk-bounce-dot"></div>
                  </div> 
                  
                  <div id="declaration_ftpsalesinvoice">
                  </div>

                  <!-- Bottom Button/Attachment -->
                  <div class="declaration-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-primary btn-declaration-ftpsalesinvoice-edit-save" id="btn-declaration-ftpsalesinvoice-edit-save">Save</button>
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