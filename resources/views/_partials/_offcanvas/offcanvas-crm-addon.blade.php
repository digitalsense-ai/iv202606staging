<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddAddon" aria-labelledby="offcanvasAddAddonLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasAddonLabel" class="offcanvas-title">Addon</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form class="add-new-addon pt-0" id="addNewAddonForm">
      @csrf
      <input type="hidden" name="addon_id" id="addon_id" value="">  
      
      
      <div class="mb-3">
        <label class="form-label" for="addons">Addons</label>  
        <div class="form-addon-repeater h-px-240 overflow-scroll-y">
          <button type="button" class="btn btn-label-warning" data-repeater-create>+Add</button>
          <div data-repeater-list="addon">
            <div data-repeater-item>
              <div class="row">
                <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0 w-60">                  
                  <input type="text" name="name" class="form-control addon" placeholder="123456" />
                </div>

                <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0 w-60">                  
                  <input type="text" name="price" class="form-control addon" placeholder="123456" />
                </div>

                <select id="frequency" class="form-select addon" name="frequency">    
                  <option value="">Select</option>
                  <option value="One-time">One-time</option>          
                  <option value="Monthly">Monthly</option>
                  <option value="Bi-monthly">Bi-monthly</option> 
                  <option value="Quarterly">Quarterly</option>
                  <option value="Half-yearly">Half-yearly</option>
                  <option value="Yearly">Yearly</option>                
                </select>

                <div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
                  <button type="button" class="btn btn-label-danger" data-repeater-delete>
                    <i class="bx bx-x me-1"></i>
                    <span class="align-middle">Delete</span>
                  </button>
                </div>
              </div>
            </div>
          </div>       
        </div>              
      </div>

      <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
      <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
    </form>
  </div>
</div>