<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="onboardingSlideClientCommentModal-{{ $client_id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalCarouselClientCommentControls-{{ $client_id }}" class="carousel slide" data-bs-interval="false">        
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Add Comment</h4>
              <!-- <div class="onboarding-info text-start">Note to re-open folder.</div>  -->              
              <div class="row mt-5">
                <form id="frm-client-comment-{{ $client_id }}" class="m-0 frm-client-comment" method="post" action="" enctype="multipart/form-data" data-client_id="{{ $client_id }}">
                  @csrf                   
                  <!-- HTML Editor-->
                  <textarea name="client_comment_quill" style="display: none;" id="client-comment-quill-{{ $client_id }}"></textarea>
                  <div class="email-compose-message">
                    <div class="d-flex justify-content-end">
                      <div class="client-comment-editor-toolbar border-bottom-0 w-100">
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
                    <div class="client-comment-editor"></div>
                  </div> 
                  <!--/ HTML Editor-->                         
                  
                  <!-- Bottom Button/Attachment -->
                  <div class="email-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-primary btn-client-comment-save" id="btn-client-comment-save-{{ $client_id }}" data-client_id="{{ $client_id }}">Save</button>
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