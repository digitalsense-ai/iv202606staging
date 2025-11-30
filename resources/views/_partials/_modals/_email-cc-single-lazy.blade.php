<div class="carousel-item {{ $modal_item_active }}">            
  <div class="onboarding-content">
    <h4 class="onboarding-title text-body text-start">{{ $send_to_title }}</h4> 
    <div class="onboarding-info text-start">{{ $send_to_info }}</div>                            
      <div class="row mt-5">
        
        @foreach($client_users as $key=>$client_user)
          @php
            $user = $client_user->user;
            $dvuser = $user->dvuser;
          @endphp

          @if(!$dvuser->is_deleted)
          <!-- Custom option-->
          <div class="col-sm-12 mb-3">
            <div class="form-check custom-option custom-option-basic">
              <label class="form-check-label custom-option-content" for="send-to-{{ $vat_reg_id }}-{{ $user->user_id }}-{{ $modal_for }}">
                <input name="send_to" class="form-check-input send-to" type="radio" data-id="{{ $user->user_id }}" data-vatid="{{ $vat_reg_id }}" value="{{ $user->email }}" data-modal_for="{{ $modal_for }}" id="send-to-{{ $vat_reg_id }}-{{ $user->user_id }}-{{ $modal_for }}" />
                <span class="custom-option-header">
                  <span class="h6 mb-0">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</span>                            
                </span>
                <span class="custom-option-body">
                  <small>{{ $user->email }}</small>
                </span>
              </label>
            </div>
          </div>
          <!-- Custom option-->  
          @endif                  
        @endforeach

      </div>                
  </div>
</div>
<div class="carousel-item">            
  <div class="onboarding-content">
    <h4 class="onboarding-title text-body text-start">Send email copy to</h4>
    <div class="onboarding-info text-start">CC to other client users.</div>               
      <div class="row mt-5">         

        @foreach($client_users as $key=>$client_user)
          @php
            $user = $client_user->user;
            $dvuser = $user->dvuser;
          @endphp

          @if(!$dvuser->is_deleted)
          <!-- Custom option-->
          <div class="col-sm-12 mb-3">
            <div class="form-check custom-option custom-option-basic">
              <label class="switch form-check-label custom-option-content" for="cc-to-{{ $vat_reg_id }}-{{ $user->user_id }}-{{ $modal_for }}">
                <input name="chk_cc[]" class="switch-input form-check-input chk_cc" type="checkbox" data-id="{{ $user->user_id }}" data-vatid="{{ $vat_reg_id }}" value="{{ $user->email }}" id="cc-to-{{ $vat_reg_id }}-{{ $user->user_id }}-{{ $modal_for }}" />
                <span class="switch-toggle-slider">
                  <span class="switch-on"></span>
                  <span class="switch-off"></span>
                </span>
                <span class="custom-option-header switch-label">
                  <span class="h6 mb-0">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</span>                            
                </span>
                <span class="custom-option-body switch-label">
                  <small>{{ $user->email }}</small>
                </span>
              </label>
            </div>
          </div>
          <!-- Custom option--> 
          @endif                   
        @endforeach

      </div>                
  </div>
</div>
<div class="carousel-item">            
  <div class="onboarding-content">
    <h4 class="onboarding-title text-body text-start">Send email copy to</h4>
    <div class="onboarding-info text-start">CC to me.</div>               
      <div class="row mt-5">
       
        <!-- Custom option-->
        <div class="col-sm-12 mb-3">
          <div class="form-check custom-option custom-option-basic">
            <label class="switch form-check-label custom-option-content" for="self-cc-to-{{ $vat_reg_id }}-{{ $authUser->user_id }}-{{ $modal_for }}">
              <input name="chk_cc[]" class="switch-input form-check-input chk_cc self" type="checkbox" data-id="{{ $authUser->user_id }}" data-vatid="{{ $vat_reg_id }}" value="{{ $authUser->email }}" id="self-cc-to-{{ $vat_reg_id }}-{{ $authUser->user_id }}-{{ $modal_for }}" />
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="custom-option-header switch-label">
                <span class="h6 mb-0">{{ $authUser->firstname . ' ' . $authUser->lastname }}</span>                            
              </span>
              <span class="custom-option-body switch-label">
                <small>{{ $authUser->email }}</small>
              </span>
            </label>
          </div>
        </div>
        <!-- Custom option-->

      </div> 

      @if($modal_for == "" || $modal_for == "comment")
        <div class="border-0 float-end modal-bottom-button">                    
          <button type="button" class="btn btn-danger btn-comment disabled btn-submit" id="btn-comment-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}">Send</button>
        </div>
      @endif
  </div>
</div>
       