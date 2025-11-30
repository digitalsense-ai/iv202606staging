<!-- Large Modal -->
<div class="modal fade modal-connection" id="connectionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Create Connection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">               
        <div class="card mb-4" style="flex: 1;">
          <h5 class="card-header">Connection</h5>
          <div class="card-body">
             <div class="form-floating mb-3">                          
                <select id="select2Client" class="form-select form-select-lg client-user-select" data-allow-clear="true" name="client_id">
                  @foreach($companies as $key=>$company)                       
                    <option value="{{ $company->id }}" data-id="{{ $company->id }}" data-name="{{ $company->client_name }}" data-image="{{ substr($company->client_name, 0, 2) }}">{{ $company->client_name }}</option>
                  @endforeach
                </select>
                <label for="select2Client" class="form-label">Choose Client User</label> 
            </div>
             <div class="form-floating mb-3">                   
                <input type="text" name="connection_name" id="connection_name" class="form-control" placeholder="" value="" />
                 <label for="connection_name">Connection Name</label>
              </div>
            <div class="form-floating mb-3">
              <select id="erp_options" class="form-select" data-allow-clear="true" name="erp_options">
                <option value="">Select</option>
                <option value="Dynamics 365">Dynamics 365</option>
                <option value="Dynamics 365 via SmartApi">Dynamics 365 via SmartAPI</option>
                <option value="E-conomic">E-conomic</option>
                <option value="Uniconta">Uniconta</option> 
                <option value="Shopify">Shopify</option>
                <option value="Billy">Billy</option>
                <option value="Excel Upload">Excel Upload</option>
                <option value="FTP">FTP</option>
              </select>
              <label for="erp_options">ERP</label>          
            </div>

            <div id="load-erp-fields"></div>         
          </div>

          <div class="card-footer">          
            <button type="submit" class="btn btn-label-primary float-end btn-create-connection" id="btn-save">Test Connection</button>
          </div>
        </div>              
      </div>      
    </div>
  </div>
</div>
