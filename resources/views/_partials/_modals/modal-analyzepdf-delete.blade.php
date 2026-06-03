<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="modalAnalyzePdfDelete" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalAnalyzePdfDeleteControls" class="carousel slide" data-bs-interval="false">
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Delete Analysed PDF</h4>
              <div class="onboarding-info text-start">Reason for delete</div>
              <div class="row mt-2 text-start">
                <form id="frm-analyzepdf-delete" class="m-0 frm-analyzepdf-delete" method="delete" action="" enctype="multipart/form-data">
                  @csrf     
                  <input type="hidden" name="analyzepdf_delete_id" id="analyzepdf_delete_id" />
                  <input type="hidden" name="analyzepdf_delete_invoice_no" id="analyzepdf_delete_invoice_no" />
                  <input type="hidden" name="analyzepdf_delete_tab_name" id="analyzepdf_delete_tab_name" />                  
                  
                  <!-- HTML Editor-->
                  <textarea name="analyzepdf_delete_reason_quill" style="display: none;" id="analyzepdf-delete-reason-quill"></textarea>
                  <div class="analyzepdf-delete-compose-message">
                    <div class="d-flex justify-content-end">
                      <div id="analyzepdf-delete-reason-editor-toolbar" class="border-bottom-0 w-100">
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
                    <div class="analyzepdf-delete-reason-editor" id="analyzepdf-delete-reason-editor"></div>
                  </div> 
                  <!--/ HTML Editor-->                                                           

                  <!-- Bottom Button/Attachment -->
                  <div class="declaration-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-danger btn-analyzepdf-delete-reason-save" id="btn-analyzepdf-delete-reason-save">Delete</button>
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