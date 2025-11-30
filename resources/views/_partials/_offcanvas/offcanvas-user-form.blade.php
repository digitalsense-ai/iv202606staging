<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasUserLabel" class="offcanvas-title">User</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form class="add-new-user pt-0" id="addNewUserForm">
      @csrf
      <input type="hidden" name="user_id" id="user_id" value="">  
      <input type="hidden" id="user_contact_tab" name="user_contact_tab" value="{{ $user_contact_tab }}">
      <input type="hidden" id="user_contact_tab_client_id" name="user_contact_tab_client_id" value="">

      <div class="mb-3">
        <label class="form-label" for="user-role">Role</label>          
        <select id="user-role" class="form-select" name="role" required>   
          <!-- <option value="" selected="selected">--Select--</option>       
          <option value="super-admin">Super Admin</option> -->
          @if($authUser->role == 'super-admin')
          <option value="company-admin">Company Admin</option>
          <option value="team-user">Team User</option>
          @endif
          <option value="client-user">Client User</option>
        </select> 
      </div>    
      <div class="mb-3">
        <label class="form-label" for="user-firstname">First Name</label>          
        <input type="text" id="user-firstname" class="form-control" placeholder="John" aria-label="John" name="firstname" required value="" />  
      </div>
      <div class="mb-3">
        <label class="form-label" for="user-lastname">Last Name</label>          
        <input type="text" id="user-lastname" class="form-control" placeholder="John" aria-label="John" name="lastname" required value="" />  
      </div>
      <div class="mb-3">
        <label class="form-label" for="user-email">Email</label>          
        <input type="email" id="user-email" class="form-control" placeholder="john.doe" aria-label="john.doe" name="email" required value="" />  
      </div>
      <div class="mb-3">
        <label class="form-label" for="user-telephone">Telephone</label>          
        <input type="text" id="user-telephone" class="form-control phone-mask telephone" placeholder="00 00 00 00" aria-label="00 00 00 00" name="telephone" required value=""/> 
      </div> 
      <div class="mb-3">
        <label class="form-label" for="user-designation">Title</label>          
        <input type="text" id="user-designation" class="form-control" placeholder="CEO" aria-label="CEO" name="designation" value=""/> 
      </div>
      <!-- <div class="mb-3" style="display: none;">
        <label class="form-label" for="company">Company</label>          
        <select id="user-company" class="form-select" name="company" required>          
          <option value="InterVAT" selected="selected">InterVAT</option>            
        </select> 
      </div>  -->
      <div class="mb-3">
        <label class="form-label" for="user-lang">Language</label>          
        <select id="user-lang" class="form-select" name="lang" required>          
          <option value="en" selected="selected">English</option>
          <option value="dk">Danish</option>
        </select> 
      </div>      
      <div class="mb-3">
        <label class="form-label" for="user-status">Status</label>          
        <select id="user-status" class="form-select" name="status" required>          
          <option value="1" selected="selected">Active</option>
          <option value="0">In-Active</option>
        </select> 
      </div>                     
      <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
      <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
    </form>
  </div>
</div>