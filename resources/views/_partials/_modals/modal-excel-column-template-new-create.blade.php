<!-- Excel Column Template Modal -->
<div class="modal fade excel-column-template-modal" id="excelColumnTemplateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Excel Column Template</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body text-start">
        
        <div class="row" id="column_mapping"> 
          <!-- Bounce -->
          <div class="sk-bounce sk-primary sk-center" style="display: none;">
            <div class="sk-bounce-dot"></div>
            <div class="sk-bounce-dot"></div>
          </div>     

          <!-- Form Repeater -->
          <div class="col-12 card-column-mapping" style="display: none;">
            <div class="card">
              <h5 class="card-header">Column Mapping</h5>
              <div class="card-body">
                <form class="form-repeater" id="formRepeater">
                  @csrf
                  <input type="hidden" name="template_id" id="template_id" value=""> 
                  <input type="hidden" name="edit_type" id="edit_type" value=""> 
                                
                  <div class="row">
                    <div class="mb-3 col-lg-6 col-xl-3 col-12">
                      <label class="form-label" for="template_name">Template Name</label>
                      <input type="text" id="template_name" name="template_name" class="form-control" placeholder="Template 1" required/>
                    </div>
                  
                    <div class="mb-3 col-lg-6 col-xl-9 col-12">
                      <label class="form-label" for="no_of_files">No. of files to be uploaded <span class="text-danger">(Grouped as single file)</span></label>
                      <input type="text" id="no_of_files" name="no_of_files" class="form-control no-of-files w-px-50" placeholder="1" value="1" maxlength="2" required onkeypress="return isNumber(event)"/>
                      </div>
                  </div>
                  
                  <hr>

                  @include('_partials/_modals/modal-excel-column-template-new-file-repeater')

                  <div class="text-end">
                    <button type="submit" class="btn btn-danger disabled btn-save-template" id="btn-save-template">Save Template</button>
                  </div>
                </form>                
              </div>
            </div>
          </div>
          <!-- /Form Repeater -->          
        </div>

      </div>

    </div>
  </div>
</div>
<!--/ Excel Column Template Modal -->