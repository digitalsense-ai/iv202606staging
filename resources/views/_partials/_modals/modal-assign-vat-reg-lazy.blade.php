<!-- Assign VAT Reg. Modal -->
<div class="modal fade" id="assignVATReg" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      
      <div class="modal-body px-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-start">
          <h3 class="mb-2">Assign VAT Reg.</h3>
          <p>Assign VAT Reg. with a team member</p>
        </div>
      </div>
      <form class="assign-new-vat-reg pt-0" id="assignVATRegForm"> 
        <div class="col-12 mb-4 pb-2">
          <label for="select2Basic" class="form-label">Choose Team User</label>        
          <select id="select2Basic" class="form-select form-select-lg team-user-select" data-allow-clear="true" name="team_user_id">
            @foreach($team_users as $key=>$teamuser)
              @php
                $dvuser = $teamuser->dvuser;
              @endphp
              <option value="{{ $teamuser->id }}" data-id="{{ $teamuser->id }}" data-name="{{ $dvuser->firstname . ' ' . $dvuser->lastname }}" data-image="{{ substr($teamuser->name, 0, 2) }}">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</option>
            @endforeach
          </select>
        </div>
        <h4 class="mb-4 pb-2" id="vat-reg-count">{{ count($vatregmains) }} VAT Reg.</h4>   

        <!--Search-->
        <div class="input-group mb-4">
          <div class="form-floating">
            <input type="text" class="form-control search-modal-name-list" id="search_vat_reg" name="search_vat_reg" placeholder="" data-search_id="vat-reg" data-search_title="VAT Reg." />
            <label for="search_vat_reg">Search VAT Reg.</label>          
          </div>                
        </div>
        <!--/ Search-->

        <ul class="p-0 m-0" id="vat-reg-list">
          @foreach($vatregmains as $key=>$vatregmain)
            @php
              $client = $vatregmain->client;
            @endphp
            <li class="d-flex mb-3" data-search_name="{{ strtolower($client->client_name) }}" data-search_email="{{ strtolower($vatregmain->country) }}">
              <div class="avatar me-3">            
                <span class="avatar-initial rounded-circle bg-label-">{{ substr($client->client_name, 0, 2) }}</span>
              </div>
              <div class="d-flex justify-content-between flex-grow-1">
                <div class="me-2">                
                  <p class="mb-0">{{ $client->client_name }}</p>
                  <p class="mb-0 text-muted">{{ $vatregmain->country }}</p>
                </div>            
              </div>
              <div class="d-flex align-items-center">
                <div class="form-check form-check-inline">
                  <input name="chk_vatreg[]" class="form-check-input chk-vatreg" type="checkbox" value="{{ $vatregmain->id }}" />
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
          <button class="btn btn-primary assign-vatreg">Assign</button>
        </div>
      </div>      
    </div>
  </div>
</div>
<!--/ Assign VAT Reg. Modal -->
