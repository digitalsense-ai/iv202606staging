<div class="email-cc-item">            
  <div class="onboarding-content">    
      <div class="row mt-3">         

        @foreach($client_users as $key=>$client_user)
          @php
            $user = $client_user->user;
            $notificationsettings = ($file_type == 'lock') ? '' : $user->notificationsettings;
            $dvuser = $user->dvuser;
          @endphp

          @if(!$dvuser->is_deleted)
            @if($notificationsettings)
              @foreach($notificationsettings as $notificationsetting)
                @if($notificationsetting->file_type == $file_type && $notificationsetting->email_notification == 1)
                  <!-- Custom option-->
                  <div class="col-sm-12 mb-3">
                    <div class="form-check custom-option custom-option-basic">
                      <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="cc-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $user->user_id }}">
                        <input name="chk_cc[]" class="switch-input form-check-input chk_cc" type="checkbox" data-id="{{ $user->user_id }}" data-vatid="{{ $vat_reg_id }}" value="{{ $user->email }}" id="cc-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $user->user_id }}" />
                        <span class="switch-toggle-slider right-3">
                          <span class="switch-on"></span>
                          <span class="switch-off"></span>
                        </span>
                        <span class="custom-option-header switch-label px-0">
                          <span class="h6 mb-0">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</span>                            
                        </span>
                        <span class="custom-option-body switch-label text-start px-0 w-100">
                          <small>{{ $user->email }}</small>
                        </span>
                      </label>
                    </div>
                  </div>
                  <!-- Custom option-->          
                @endif  
              @endforeach 
            @else
              <!-- Custom option-->
              <div class="col-sm-12 mb-3">
                <div class="form-check custom-option custom-option-basic">
                  <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="cc-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $user->user_id }}">
                    <input name="chk_cc[]" class="switch-input form-check-input chk_cc" type="checkbox" data-id="{{ $user->user_id }}" data-vatid="{{ $vat_reg_id }}" value="{{ $user->email }}" id="cc-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $user->user_id }}" />
                    <span class="switch-toggle-slider right-3">
                      <span class="switch-on"></span>
                      <span class="switch-off"></span>
                    </span>
                    <span class="custom-option-header switch-label px-0">
                      <span class="h6 mb-0">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</span>                            
                    </span>
                    <span class="custom-option-body switch-label text-start px-0 w-100">
                      <small>{{ $user->email }}</small>
                    </span>
                  </label>
                </div>
              </div>
              <!-- Custom option-->
            @endif 
          @endif                
        @endforeach

      </div>                
  </div>
</div>

<div class="email-self-item">            
  <div class="onboarding-content">
    <h4 class="onboarding-title text-body text-start">CC to me</h4>                
      <div class="row mt-3">
       
        <!-- Custom option-->
        <div class="col-sm-12 mb-3">
          <div class="form-check custom-option custom-option-basic">
            <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="self-cc-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $authUser->user_id }}">
              <input name="chk_cc[]" class="switch-input form-check-input chk_cc self" type="checkbox" data-id="{{ $authUser->user_id }}" data-vatid="{{ $vat_reg_id }}" value="{{ $authUser->email }}" id="self-cc-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $authUser->user_id }}" />
              <span class="switch-toggle-slider right-3">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="custom-option-header switch-label px-0">
                <span class="h6 mb-0">{{ $authUser->firstname . ' ' . $authUser->lastname }}</span>                            
              </span>
              <span class="custom-option-body switch-label text-start px-0 w-100">
                <small>{{ $authUser->email }}</small>
                <button type="button" class="btn-send-email-test link-primary" id="btn-send-email-test-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-d_id="{{ $i }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">Send test</button>
              </span>
            </label>
          </div>
        </div>
        <!-- Custom option-->

      </div>               
  </div>
</div>
       