<div class="email-to-item">            
  <div class="onboarding-content">    
      <div class="row mt-3">
        @if(count($client_users) == 0)
          <div class="col-sm-12 mb-3 text-danger">
              No client users
          </div>
        @endif

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
                    
                      <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="send-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $user->user_id }}">
                        <input name="send_to" class="form-check-input switch-input send-to" type="radio" data-d_id="{{ $i }}" data-id="{{ $user->user_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}" value="{{ $user->email }}" id="send-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $user->user_id }}" />
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
                
                  <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="send-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $user->user_id }}">
                    <input name="send_to" class="form-check-input switch-input send-to" type="radio" data-d_id="{{ $i }}" data-id="{{ $user->user_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}" value="{{ $user->email }}" id="send-to-{{ $file_type }}-{{ $vat_reg_id }}-{{$i}}-{{ $user->user_id }}" />
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