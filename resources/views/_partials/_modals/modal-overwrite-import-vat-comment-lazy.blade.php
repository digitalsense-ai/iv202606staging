<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="onboardingSlideOverwriteImportVatCommentModal-{{ $vat_reg_id }}-{{ $timeline['fileid'] }}-{{ $timeline['lineno'] }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalCarouselOverwriteImportVatCommentControls-{{ $vat_reg_id }}-{{ $timeline['fileid'] }}-{{ $timeline['lineno'] }}" class="carousel slide" data-bs-interval="false">
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Add Comment</h4>
              <div class="onboarding-info text-start">Note: Import Vat File Line No. {{ $timeline['lineno'] }}.</div>               
              <div class="row mt-5 text-start">
                <form id="frm-import-vat-comment-{{ $vat_reg_id }}-{{ $timeline['fileid'] }}-{{ $timeline['lineno'] }}" class="m-0 frm-import-vat-comment" method="post" action="" enctype="multipart/form-data" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-import_vat_file_id="{{ $timeline['fileid'] }}" data-import_vat_line_no="{{ $timeline['lineno'] }}" data-comment_type="overwrite">
                  @csrf                   
                  <!-- HTML Editor-->
                  <textarea name="import_vat_comment_quill" style="display: none;" id="import-vat-comment-quill-{{ $vat_reg_id }}-{{ $timeline['fileid'] }}-{{ $timeline['lineno'] }}"></textarea>
                  <div class="email-compose-message">
                    <div class="d-flex justify-content-end">
                      <div id="import-vat-comment-editor-toolbar-{{ $vat_reg_id }}-{{ $timeline['fileid'] }}-{{ $timeline['lineno'] }}" class="border-bottom-0 w-100">
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
                    <div class="import-vat-comment-editor" id="import-vat-comment-editor-{{ $vat_reg_id }}-{{ $timeline['fileid'] }}-{{ $timeline['lineno'] }}" data-vat_reg_id="{{ $vat_reg_id }}" data-import_vat_file_id="{{ $timeline['fileid'] }}" data-import_vat_line_no="{{ $timeline['lineno'] }}">{!! $timeline['message'] !!}</div>
                  </div> 
                  <!--/ HTML Editor-->                         
                  
                  <!-- Bottom Button/Attachment -->
                  <div class="email-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-primary btn-import-vat-comment-save" id="btn-import-vat-comment-save-{{ $vat_reg_id }}-{{ $timeline['fileid'] }}-{{ $timeline['lineno'] }}" data-vat_reg_id="{{ $vat_reg_id }}" data-import_vat_file_id="{{ $timeline['fileid'] }}" data-import_vat_line_no="{{ $timeline['lineno'] }}">Save</button>
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