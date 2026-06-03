<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="modalCrmReminder" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalCrmReminderControls" class="carousel slide" data-bs-interval="false">
        <div class="carousel-inner">          
          <div class="carousel-item active">            
            <div class="onboarding-content">
              <h4 class="onboarding-title text-body text-start">{{ (($module_type == 'lead') ? 'Lead' : 'Quote') }} Reminder</h4>
              <div class="onboarding-info text-start">Reason for {{ (($module_type == 'lead') ? 'no quote' : 'quote rejection') }}</div>
              <div class="row mt-2 text-start">
                <form id="frm-crm-reminder" class="m-0 frm-crm-reminder" method="delete" action="" enctype="multipart/form-data">
                  @csrf     
                  <input type="hidden" name="module_type" id="module_type" value="{{ $module_type }}" />
                  @if($module_type == 'lead')
                    <input type="hidden" name="crm_lead_id" id="crm_lead_id" />
                  @elseif($module_type == 'quote')
                    <input type="hidden" name="crm_quote_id" id="crm_quote_id" />
                  @endif                  
                  
                  <!-- Receipient-->
                  <div class="input-group mb-3">
                    <div class="form-floating">                      
                      <input type="email" id="crm_reminder_sentto" class="form-control" placeholder="john@gmail.com" aria-label="john@gmail.com" name="crm_reminder_sentto" required />
                      <label for="crm_reminder_sentto">Email</label>
                    </div>
                  </div>
                  <!-- /Receipient-->

                  <!-- Datetime Picker-->
                  <div class="input-group mb-3">
                    <div class="form-floating">
                      <input type="text" class="form-control mb-1" placeholder="YYYY-MM-DD HH:MM" id="crm_reminder_datetime" name="crm_reminder_datetime" required />
                      <label for="crm_reminder_datetime">DateTime</label>
                    </div>
                  </div>
                  <!-- /Datetime Picker-->

                  <!-- HTML Editor-->
                  <textarea name="crm_reminder_reason_quill" style="display: none;" id="crm-reminder-reason-quill"></textarea>
                  <div class="crm-reminder-compose-message">
                    <div class="d-flex justify-content-end">
                      <div id="crm-reminder-reason-editor-toolbar" class="border-bottom-0 w-100">
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
                    <div class="crm-reminder-reason-editor" id="crm-reminder-reason-editor"></div>
                  </div> 
                  <!--/ HTML Editor-->                                                           

                  <!-- Bottom Button/Attachment -->
                  <div class="declaration-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center"></div>
                    <div class="d-flex align-items-center">  
                      <div class="btn-group ms-2">
                        <button type="submit" class="btn btn-danger btn-crm-reminder-reason-save" id="btn-crm-reminder-reason-save">Save</button>
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
<!--/ Onboarding slider modals -->