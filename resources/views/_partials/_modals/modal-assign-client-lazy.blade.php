<!-- Assign Client Modal -->
<div class="modal fade" id="assignClient" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      
      <div class="modal-body px-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-start">
          <h3 class="mb-2">Assign Company</h3>
          <p>Assign Company with a client user</p>
        </div>
      </div>
      <form class="assign-new-client pt-0" id="assignClientForm"> 
        <div class="col-12 mb-4 pb-2">
          <label for="select2Client" class="form-label">Choose Client User</label>        
          <select id="select2Client" class="form-select form-select-lg client-user-select" data-allow-clear="true" name="client_user_id">
            @foreach($client_users as $key=>$clientuser)
              @php
                $dvuser = $clientuser->dvuser;
              @endphp
              <option value="{{ $clientuser->id }}" data-id="{{ $clientuser->id }}" data-name="{{ ($dvuser) ? ($dvuser->firstname . ' ' . $dvuser->lastname) : $clientuser->name }}" data-image="{{ substr($clientuser->name, 0, 2) }}">{{ ($dvuser) ? ($dvuser->firstname . ' ' . $dvuser->lastname) : $clientuser->name }}</option>
            @endforeach
          </select>
        </div>
        <h4 class="mb-2 pb-2" id="client-count">{{ count($clients) }} Companies</h4>   
        
        <!--Search-->
        <div class="input-group mb-4">
          <div class="form-floating">
            <input type="text" class="form-control search-modal-name-list" id="search_client" name="search_client" placeholder="" data-search_id="client" data-search_title="Clients" />
            <label for="search_client">Search Company</label>          
          </div>                
        </div>
        <!--/ Search-->

        <ul class="p-0 m-0" id="client-list">
          @foreach($clients as $key=>$client)
            @php
              $vatreg_countries = '';
            
              foreach($client->vatregmain as $vatregmain)
              {
                if($vatreg_countries == '')
                  $vatreg_countries = $vatregmain->country;
                else
                  $vatreg_countries .= ", " . $vatregmain->country;
              }
            @endphp
            <li class="d-flex mb-3" data-search_name="{{ strtolower($client->client_name) }}" data-search_email="{{ strtolower($client->email) }}">          
              <div class="avatar me-3">            
                <span class="avatar-initial rounded-circle bg-label-">{{ substr($client->client_name, 0, 2) }}</span>
              </div>
              <div class="d-flex justify-content-between flex-grow-1">
                <div class="me-2">
                  <p class="mb-0">{{ $client->client_name . ' (' . $vatreg_countries . ')' }}</p>
                  <p class="mb-0 text-muted">{{ $client->email }}</p>
                </div>            
              </div>
              <div class="d-flex align-items-center">
                <div class="form-check form-check-inline">
                  <input name="chk_client[]" class="form-check-input chk-client" type="checkbox" value="{{ $client->id }}" />
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
          <button class="btn btn-primary assign-client">Assign</button>
        </div>
      </div>      
    </div>
  </div>
</div>
<!--/ Assign Client Modal -->
