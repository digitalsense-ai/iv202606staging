<!-- Large Modal -->
<div class="modal fade modal-file" id="mailboxAssignAnyExcelTemplateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">      
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">            
        <h4 class="onboarding-title text-body text-start">Choose Period And Assign Any Excel Template</h4>
        <form id="formMailboxAssignAnyExcelTemplate" class="needs-validation m-0 formMailboxAssignAnyExcelTemplate" novalidate>
          @csrf
          
          <input type="hidden" name="mailbox_file_id" id="mailbox_file_id">
          <div class="row">
            <div class="col-md-2">
              <div class="mb-3">                       
                <label class="form-label" for="vatreg_period">Periods</label>
                <select class="form-select" id="vatreg_period" name="vatreg_period" required>
                  <option value="" selected>--Select Period--</option>                                      
                </select>
              </div>
            </div>
          </div>

          <div class="row" style="display: none;">          
            <div class="col-12" id="anyexcel-template-selection">
              <div class="card-datatable table-responsive">
                <table class="datatables-anyexceltemplate table border-top" data-vat_reg_id="0" data-file_type="vatreturn">
                  <thead>        
                    <tr>            
                      <th></th>  
                      <th>Name</th>                   
                      <th>Columns</th>                       
                    </tr>
                  </thead>
                </table>
              </div>  

            </div>          
          </div>

          <div class="row mt-3">
            <div class="col-sm-12 text-end">          
              <button type="button" class="btn btn-danger disabled btn-mailbox-assign-anyexcel-template">Assign</button>
            </div>                                                      
          </div> 
        </form>  
        
      </div>      
    </div>
  </div>
</div>