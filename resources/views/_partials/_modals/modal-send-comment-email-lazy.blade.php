<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="onboardingSlideCommentModal-{{ $vat_reg_id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalCarouselCommentControls-{{ $vat_reg_id }}" class="carousel slide" data-bs-interval="false">
        <ol class="carousel-indicators">
          <li data-bs-target="#modalCarouselCommentControls-{{ $vat_reg_id }}" data-bs-slide-to="0" class="active"></li>
          <li data-bs-target="#modalCarouselCommentControls-{{ $vat_reg_id }}" data-bs-slide-to="1"></li>
          <li data-bs-target="#modalCarouselCommentControls-{{ $vat_reg_id }}" data-bs-slide-to="2"></li>
          <li data-bs-target="#modalCarouselCommentControls-{{ $vat_reg_id }}" data-bs-slide-to="3"></li>            
        </ol>
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">Add Comment</h4>
              <div class="onboarding-info text-start">Note to re-open folder.</div>               
              <div class="row mt-5">
                <form id="frm-comment-{{ $vat_reg_id }}" class="m-0 frm-comment" method="post" action="" enctype="multipart/form-data" data-vatid="{{ $vat_reg_id }}" data-client_id="{{ $client_id }}">
                  @csrf 
                  <input type="hidden" class="comment-status" name="comment-status" id="comment-status-{{ $vat_reg_id }}" value="0">
                  <!-- HTML Editor-->
                  <textarea name="comment_quill" style="display: none;" id="comment-quill-{{ $vat_reg_id }}"></textarea>
                  <div class="email-compose-message">
                    <div class="d-flex justify-content-end">
                      <div class="email-editor-toolbar border-bottom-0 w-100" id="email-editor-toolbar-{{ $vat_reg_id }}">
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
                    <div class="email-editor" id="email-editor-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}"></div>
                  </div> 
                  <!--/ HTML Editor-->                         
                  <div class="comment-files-list" id="comment-files-list-{{ $vat_reg_id }}"></div>
                  <!-- Bottom Button/Attachment -->
                  <div class="email-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">

                      <label for="attach-comment-file-{{ $vat_reg_id }}"><i class="bx bx-paperclip cursor-pointer"></i></label>
                      <input type="file" name="attach-comment-file[]" class="d-none attach-comment-file" id="attach-comment-file-{{ $vat_reg_id }}" data-vatid="{{ $vat_reg_id }}" multiple>  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-primary btn-comment-save" id="btn-comment-save-{{ $vat_reg_id }}" data-vatid="{{ $vat_reg_id }}" data-client_id="{{ $client_id }}">Save</button>
                      </div>                                          
                    </div>                      
                  </div>
                  <!-- Bottom Button/Attachment --> 
                </form>         
              </div>
            </div>
          </div>
          
          <form id="formCommentEmail-{{ $vat_reg_id }}" class="needs-validation m-0 formEmail" novalidate>
            @csrf 
            <input type="hidden" name="vat_reg_id" id="vat_reg_id" value="{{ $vat_reg_id }}">
            <input type="hidden" name="comment_id" id="comment_id" value="">

            @php
              $modal_for = "comment";  
              $modal_item_active = "";           
              $send_to_title = "Send comment email to";
              $send_to_info = "The client user who will receive comment email.";
            @endphp

            @include('_partials/_modals/_email-cc-single-lazy')       
          </form>            
        </div>
        <a class="carousel-control-prev carousel-control btn btn-label-secondary" href="#modalCarouselCommentControls-{{ $vat_reg_id }}" role="button" data-bs-slide="prev">
          <i class="bx bx-chevrons-left lh-1"></i><span>Previous</span>
        </a>
        <a class="carousel-control-next carousel-control btn btn-primary" href="#modalCarouselCommentControls-{{ $vat_reg_id }}" role="button" data-bs-slide="next">
          <span>Next</span><i class="bx bx-chevrons-right lh-1"></i>
        </a>
      </div>      
    </div>
  </div>
</div>
<!--/ Onboarding slider modals-->