<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasTaskFilter" aria-labelledby="offcanvasTaskFilterLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasTaskFilterLabel" class="offcanvas-title">Task Filter</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-0 py-0">
    <form method="post" class="form-task-filter">  
      @csrf      
      <div class="row">
        <div class="col-md-6">
          <div class="form-floating mb-3">
            <input type="text" id="bs-datepicker-from_date" placeholder="yyyy-mm" class="form-control" name="from_date" value="" />
            <label for="bs-datepicker-from_date">From</label>
          </div>
        </div>
        <div class="col-md-6">  
          <div class="form-floating mb-3">
            <input type="text" id="bs-datepicker-to_date" placeholder="yyyy-mm" class="form-control" name="to_date" value="" />
            <label for="bs-datepicker-to_date">To</label>
          </div>
        </div><!--/ col -->
      </div>
    
      <div class="row">
        <div class="col-md-12">
          <button type="submit" class="btn btn-primary mb-2 w-100 btn-task-filter">Filter</button>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <button type="button" class="btn btn-label-secondary d-grid w-100 btn-reset-filter">Reset</button>
        </div>
        <div class="col-md-6">
          <button type="button" class="btn btn-label-secondary d-grid w-100" data-bs-dismiss="offcanvas">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>