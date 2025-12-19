<div class="email-to-item">
  <div class="onboarding-content">    
    <div class="row">   

      <!-- Client users--> 
      @if($client_users)
        <div class="col-sm-12" id="reminder_client_users">
          <label class="form-label" for="send-to-reminder">Client users</label>
          <span class="alert-primary text-end fs-tiny p-1 mx-2 client-user-count" style="display: none;"></span>
        
          @php                                                         
            $unique_client_user_ids = [];                    
          @endphp

          @foreach($client_users as $key=>$client_user)
            @php          
              $user = $client_user->user;            
              $dvuser = $user->dvuser;
              $checked = ''; 
            @endphp
           
            @if(!$dvuser->is_deleted)
              @if(!in_array($user->id, $unique_client_user_ids, true)) 
                @php            
                  array_push($unique_client_user_ids, $user->id);                          
                @endphp
                
                <!-- Custom option-->
                <div class="col-sm-12 mb-3">
                  <div class="form-check custom-option custom-option-basic">
                  
                    <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="send-to-reminder-{{ $dvuser->user_id }}">
                      @if($reminderusers)                        
                        @foreach($reminderusers as $reminderuser)
                          @php                              
                            if($user->id == $reminderuser['user_id'])
                              $checked = "checked='checked'";                                    
                          @endphp                      
                        @endforeach
                      @endif  
                      <input name="send_to[]" class="form-check-input switch-input send-to-reminder" type="checkbox" data-id="{{ $dvuser->user_id }}" value="{{ $user->email }}" id="send-to-reminder-{{ $dvuser->user_id }}" {{ $checked }} />
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

          @if(count($unique_client_user_ids) == 0)
            <div class="col-sm-12 mb-3 text-danger">
                No client users
            </div>        
          @endif
        </div>
      {{--@else
        <div class="col-sm-12 mb-3 text-danger">
            No client users
        </div>  --}}
      @endif  
      <!--/ Client users--> 

      <!-- Team users--> 
      @if($team_users)
        <div class="col-sm-12" id="reminder_team_users">
          <label class="form-label" for="send-to-reminder">Team users</label>
          <span class="alert-primary text-end fs-tiny p-1 mx-2 team-user-count" style="display: none;"></span>
      
          @php                                                         
            $unique_team_user_ids = [];                    
          @endphp
          @foreach($team_users as $key=>$team_user)
            @php                
              $uservatregs = $team_user->uservatreg;
              $checked = ''; 
            @endphp
            
            @foreach($uservatregs as $uservatreg)
              @php  
                $user = $uservatreg->user;            
                $dvuser = $user->dvuser;              
              @endphp
              
              @if(!$dvuser->is_deleted)
                @if(!in_array($user->id, $unique_team_user_ids, true)) 
                  @php            
                    array_push($unique_team_user_ids, $user->id);                          
                  @endphp  
                  
                  <!-- Custom option-->
                  <div class="col-sm-12 mb-3">
                    <div class="form-check custom-option custom-option-basic">
                    
                      <label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="send-to-reminder-{{ $dvuser->user_id }}">
                        @if($reminderusers)                          
                          @foreach($reminderusers as $reminderuser)
                            @php                                 
                              if($user->id == $reminderuser['user_id'])
                                $checked = "checked='checked'";                                    
                            @endphp                      
                          @endforeach
                        @endif  
                        <input name="send_to[]" class="form-check-input switch-input send-to-reminder" type="checkbox" data-id="{{ $dvuser->user_id }}" value="{{ $user->email }}" id="send-to-reminder-{{ $dvuser->user_id }}" {{ $checked }} />
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
          @endforeach

          @if(count($unique_team_user_ids) == 0)
            <div class="col-sm-12 mb-3 text-danger">
                No team users
            </div>        
          @endif
        </div>
      {{--@else
        <div class="col-sm-12 mb-3 text-danger">
            No team users
        </div>--}}  
      @endif  
      <!--/ Team users--> 

      <!-- Reminder users-->          
      @if($reminder_users)
        <div class="col-sm-12" id="reminder_reminder_users">

          <div class="row">
            <div class="col-sm-3">
              <label class="form-label" for="send-to-reminder">Reminder users</label>
              <span class="alert-primary text-end fs-tiny p-1 mx-2 reminder-user-count" style="display: none;"></span>
            </div>

            <div class="col-sm-9">
              <div class="form-check custom-option custom-option-basic border-0 float-end">
                <div class="d-inline-block align-center px-3 mx-3 fw-normal">Select All</div>

                <div class="d-inline-block">
                  <label class="switch form-check-label custom-option-content text-end px-3 fs-big select-all" for="send-to-reminder-all">
                   <input class="form-check-input switch-input send-to-reminder-all" type="checkbox" id="send-to-reminder-all" />
                    <span class="switch-toggle-slider right-3">
                      <span class="switch-on"></span>
                      <span class="switch-off"></span>
                    </span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          @php                                                         
            $unique_reminder_user_ids = [];       
          @endphp

          @foreach($reminder_users as $key=>$reminder_user)
            @php          
              $client_name = $reminder_user->client->client_name;
              $user = $reminder_user->user;   
              $dvuser = $user->dvuser;         
              $notificationsettings = $user->notificationsettings;
              $checked = '';               
            @endphp
            @if(!in_array($user->id, $unique_reminder_user_ids, true)) 
              @php            
                array_push($unique_reminder_user_ids, $user->id);                          
              @endphp
               @if($notificationsettings)
                  @foreach($notificationsettings as $notificationsetting)
                    @if($notificationsetting->file_type == 'reminders' && $notificationsetting->email_notification == 1)                
                      <!-- Custom option-->
                      <div class="col-sm-12 mb-3">
                        <div class="form-check custom-option custom-option-basic">
                        
                          <label class="switch form-check-label custom-option-content text-end px-3 fs-big reminder-user-main" for="send-to-reminder-{{ $dvuser->user_id }}">
                            @if($reminderusers)                          
                              @foreach($reminderusers as $reminderuser)
                                @php                                 
                                  if($user->id == $reminderuser['user_id'])
                                    $checked = "checked='checked'";                                    
                                @endphp                      
                              @endforeach
                            @endif 

                           <input name="send_to[]" class="form-check-input switch-input send-to-reminder" type="checkbox" data-id="{{ $dvuser->user_id }}" value="{{ $user->email }}" id="send-to-reminder-{{ $dvuser->user_id }}" {{ $checked }} />
                            <span class="switch-toggle-slider right-3">
                              <span class="switch-on"></span>
                              <span class="switch-off"></span>
                            </span>

                            <div class="d-block w-100 text-start">
                              <div class="d-inline-block w-40">
                                <span class="custom-option-header switch-label px-0">
                                  <span class="h6 mb-0">{{ $dvuser->firstname . ' ' . $dvuser->lastname }}</span>
                                </span>
                                <span class="custom-option-body switch-label text-start px-0 w-100">
                                  <small>{{ $user->email }}</small>
                                </span>
                              </div>

                              <div class="d-inline-block w-40 align-top">
                                {{--<span class="">{{ ($client_id == '0') ? 'All' : $client_name }}--}}
                                @if($client_id == '0')
                                  @foreach($reminder_users as $reminder_user_client)
                                    @if($reminder_user->user_id == $reminder_user_client->user_id)
                                      @php                                                
                                        $client_name_final = $reminder_user_client->client->client_name; 
                                        $client_checked = '';                                     
                                      @endphp
                                      
                                      <label class="switch form-check-label custom-option-content text-start fw-light fs-normal p-2 reminder-user-sub m-0 w-auto" for="send-to-reminder-client-{{ $reminder_user->user_id . '-' . $reminder_user_client->client_id }}">

                                        @if($reminderusers) 
                                          @foreach($reminderusers as $reminderuser)
                                            @if($reminder_user_client->user_id == $reminderuser['user_id'])
                                              @if(isset($reminderuser['reminderuserclient']))
                                                @foreach($reminderuser['reminderuserclient'] as $reminderuserclient)
                                                  @php                                 
                                                    if($reminder_user_client->client_id == $reminderuserclient['client_id'])
                                                      $client_checked = "checked='checked'";                                    
                                                  @endphp
                                                @endforeach
                                              @endif    
                                            @endif                      
                                          @endforeach
                                        @endif

                                        <input name="send_to_client[{{$reminder_user->user_id}}][]" class="form-check-input switch-input send-to-reminder-client" type="checkbox" value="{{ $reminder_user_client->client_id }}" id="send-to-reminder-client-{{ $reminder_user->user_id . '-' . $reminder_user_client->client_id }}" {{ $client_checked }} />
                                        <span class="switch-toggle-slider">
                                          <span class="switch-on"></span>
                                          <span class="switch-off"></span>
                                        </span>
                                        <div class="ms-5">{{ $client_name_final }}</div>
                                      </label><br>
                                    @endif
                                  @endforeach
                                @else
                                  {{ $client_name }} 
                                @endif
                                {{--</span>--}}
                              </div>
                            </div>

                          </label>

                        </div>
                      </div>
                      <!-- Custom option--> 
                    @endif  
                  @endforeach   
                @endif  
            @else
              @php
                $client_name .= '<br>' . $client_name;
              @endphp     
            @endif                                  
          @endforeach        

          @if(count($unique_reminder_user_ids) == 0)
            <div class="col-sm-12 mb-3 text-danger">
                No reminder users
            </div>        
          @endif
        </div>        
      @endif  
      <!--/ Reminder users--> 

    </div>                
  </div>
</div>      