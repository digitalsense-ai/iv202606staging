<!-- Assign Client User Modal -->
<div class="modal fade" id="assignClientUser" tabindex="-1" aria-hidden="true" data-client_id="{{ $client_id }}">
  <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      
      <div class="modal-body px-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-start">
          <h3 class="mb-2">Assign Client User</h3>
          <p>Assign Client User with {{ $client->client_name }}</p>
        </div>
      </div>
      <form class="assign-new-client-user pt-0" id="assignClientUserForm">         
        <h4 class="mb-4 pb-2" id="client-user-count">{{ count($clientusers) }} Client Users</h4>   
        <input name="client_id" class="form-input" type="hidden" value="{{ $client_id }}" /> 

        <!--Search-->
        <div class="input-group mb-4">
          <div class="form-floating">
            <input type="text" class="form-control search-modal-name-list" id="search_client_user" name="search_client_user" placeholder="" data-search_id="client-user" data-search_title="Client Users" />
            <label for="search_client_user">Search Client User</label>          
          </div>                
        </div>
        <!--/ Search-->

        <ul class="p-0 m-0" id="client-user-list">
          @foreach($clientusers as $key=>$clientuser)
            @php              
              $dvuser = $clientuser->dvuser;
            @endphp
            <li class="d-flex mb-3" data-search_name="{{ ($dvuser) ? (strtolower($dvuser->firstname . ' ' . $dvuser->lastname)) : strtolower($clientuser->name) }}" data-search_email="{{ strtolower($clientuser->email) }}">          
              <div class="avatar me-3">            
                <span class="avatar-initial rounded-circle bg-label-">{{ ($dvuser) ? (substr($dvuser->firstname, 0, 1) . ''. substr($dvuser->lastname, 0, 1)) : substr($clientuser->name, 0, 2) }}</span>
              </div>
              <div class="d-flex justify-content-between flex-grow-1">
                <div class="me-2">
                  <p class="mb-0">{{ ($dvuser) ? ($dvuser->firstname . ' ' . $dvuser->lastname) : $clientuser->name }}</p>
                  <p class="mb-0 text-muted">{{ $clientuser->email }}</p>
                </div>            
              </div>
              <div class="d-flex align-items-center">
                <div class="form-check form-check-inline">
                  @php
                    $selected = '';
                  
                    foreach($assiged_client_users as $assiged_client_user)
                    {
                      if($dvuser)
                      {
                        if($dvuser->user_id == $assiged_client_user->user_id)
                          $selected = 'checked=checked';
                      }
                      else
                      {
                        if($clientuser->id == $assiged_client_user->user_id)
                          $selected = 'checked=checked';
                      }
                    }
                  @endphp
                  <input name="chk_client_user[]" class="form-check-input chk-client-user" type="checkbox" value="{{ ($dvuser) ? $dvuser->user_id : $clientuser->id }}" {{ $selected }} />
                </div>
              </div>          
            </li>
          @endforeach        
        </ul> 
      </form>     
      <div class="d-flex align-items-center mt-4">
        <i class="bx bx-user me-2"></i>
        <div class="d-flex justify-content-between flex-grow-1 align-items-center">
          <h6 class="mb-0 selected-no">Selected {{ count($assiged_client_users) }}</h6>
          <button class="btn btn-primary assign-client-user">Assign</button>
        </div>
      </div>      
    </div>
  </div>
</div>
<!--/ Assign Client User Modal -->
