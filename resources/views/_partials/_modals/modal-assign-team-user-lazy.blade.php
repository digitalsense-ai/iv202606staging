<!-- Assign Team User Modal -->
<div class="modal fade" id="assignTeamUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      
      <div class="modal-body px-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-start">
          <h3 class="mb-2">Assign Team Users</h3>
          <p>Assign Team Users with a Company Admin</p>
        </div>
      </div>
      <form class="assign-new-team-user pt-0" id="assignTeamUserForm"> 
        <div class="col-12 mb-4 pb-2">
          <label for="select2Company" class="form-label">Choose Company Admin</label>        
          <select id="select2Company" class="form-select form-select-lg company-select" data-allow-clear="true" name="company_id">
            @foreach($company_admin_users as $key=>$companyadmin)
              @php
                $dvuser = $companyadmin->dvuser;
              @endphp
              <option value="{{ $companyadmin->id }}" data-id="{{ $companyadmin->id }}" data-name="{{ $dvuser->firstname . ' ' . $dvuser->lastname }}" data-image="{{ substr($companyadmin->name, 0, 2) }}">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</option>
            @endforeach
          </select>
        </div>
        <h4 class="mb-4 pb-2" id="team-user-count">{{ count($team_users) }} Team Users</h4>     

        <!--Search-->
        <div class="input-group mb-4">
          <div class="form-floating">
            <input type="text" class="form-control search-modal-name-list" id="search_team_user" name="search_team_user" placeholder="" data-search_id="team-user" data-search_title="Team Users" />
            <label for="search_team_user">Search Team User</label>          
          </div>                
        </div>
        <!--/ Search-->

        <ul class="p-0 m-0" id="team-user-list">
          @foreach($team_users as $key=>$teamuser)
            @php
              $dvuser = $teamuser->dvuser;
            @endphp
            <li class="d-flex mb-3" data-search_name="{{ strtolower($dvuser->firstname . ' ' . $dvuser->lastname) }}" data-search_email="{{ strtolower($teamuser->email) }}">          
              <div class="avatar me-3">            
                <span class="avatar-initial rounded-circle bg-label-">{{ substr($teamuser->name, 0, 2) }}</span>
              </div>
              <div class="d-flex justify-content-between flex-grow-1">
                <div class="me-2">
                  <p class="mb-0">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</p>
                  <p class="mb-0 text-muted">{{ $teamuser->email }}</p>
                </div>            
              </div>
              <div class="d-flex align-items-center">
                <div class="form-check form-check-inline">
                  <input name="chk_team_user[]" class="form-check-input chk-team-user" type="checkbox" value="{{ $teamuser->id }}" />
                </div>
              </div>          
            </li>
          @endforeach        
        </ul> 
      </form>     
      <div class="d-flex align-items-center mt-4">
        <i class="bx bx-user me-2"></i>
        <div class="d-flex justify-content-between flex-grow-1 align-items-center">
          <h6 class="mb-0 selected-no">Selected 0</h6>
          <button class="btn btn-primary assign-team-user">Assign</button>
        </div>
      </div>      
    </div>
  </div>
</div>
<!--/ Assign Team User Modal -->
