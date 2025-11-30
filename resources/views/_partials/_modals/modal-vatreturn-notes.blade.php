<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="vatreturnNotesModal-{{ $vat_reg_id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalCarouselVatreturnNotesControls-{{ $vat_reg_id }}" class="carousel slide" data-bs-interval="false">        
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Add Notes</h4>                      
              <div class="row mt-5">
                <form id="frm-vatreturn-notes-{{ $vat_reg_id }}" class="m-0 frm-vatreturn-notes" method="post" action="" enctype="multipart/form-data" data-vat_reg_id="{{ $vat_reg_id }}">
                  @csrf   
                  <input type="hidden" name="note_id" id="note-id-{{ $vat_reg_id }}">
                  <div class="row mb-3">
                    <div class="col-6">
                      <!-- <label class="form-label" for="vatreturn_note_type">Type</label> -->
                      <select class="form-select vatreturn-note-type w-50 d-inline-block" name="vatreturn_note_type" id="vatreturn-note-type-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" required>
                        <option value="">Select Type</option>                                          
                        <option value="general">General Notes</option>  
                        <option value="specific">Specific Notes</option>  
                      </select>                
                    </div>
                  </div>

                  <!-- HTML Editor-->
                  <textarea name="vatreturn_note_quill" style="display: none;" id="vatreturn-note-quill-{{ $vat_reg_id }}"></textarea>
                  <div class="email-compose-message">
                    <div class="d-flex justify-content-end">
                      <div id="vatreturn-note-editor-toolbar-{{ $vat_reg_id }}" class="border-bottom-0 w-100">
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
                    <div class="vatreturn-note-editor" id="vatreturn-note-editor-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}"></div>
                  </div> 
                  <!--/ HTML Editor-->                         
                  
                  <!-- Bottom Button/Attachment -->
                  <div class="email-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-primary btn-vatreturn-note-save" id="btn-vatreturn-note-save-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}">Save</button>
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