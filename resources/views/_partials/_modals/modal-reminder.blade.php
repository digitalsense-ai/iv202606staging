<!-- Large Modal -->
<div class="modal fade modal-reminder" id="reminderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Create Reminder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <div class="row">
          <!-- Basic  -->
          <div class="col-12">
            <form method="post" action="{{ url('reminder') }}" enctype="multipart/form-data" id="frm-reminder" class="formReminder">
              @csrf
              <input type="hidden" name="reminder_id" id="reminder_id" value="">
              <input type="hidden" name="send_test_reminder" id="send_test_reminder" value="">
              <input type="hidden" name="sel_action_name" id="sel_action_name" value="">
              <input type="hidden" name="reminder_send_to" id="reminder_send_to" value="">

              @php                       
                //$clients = $vat_reg_mains->pluck('client')->pluck('client_name')->unique();                    
                $countries = $vat_reg_mains->pluck('country')->unique()->toArray();
              @endphp

              <div class="row">
                <div class="col-6">
                  <div class="mb-3">
                    <label class="form-label" for="user_role">Role</label>          
                    <select id="user_role" class="form-select" name="user_role" required>    
                      <option value="">Select Role</option>                 
                      <option value="team-user">Team User</option>
                      <option value="client-user">Client User</option>
                      <option value="reminder">Reminder</option>
                    </select> 
                  </div>
                </div>
                
                <div class="col-6">  
                  <div class="mb-3">
                    <label class="form-label" for="country">Country</label>
                    <select id="country" class="form-select" data-allow-clear="true" name="country" required disabled="disabled">
                      <option value="">Select</option>
                      <optgroup label="Europe">
                        <option value="AT" class="{{ isset($countries) ? ((in_array('AT', $countries)) ? '' : 'd-none') : 'd-none' }}">Austria</option>
                        <option value="BE" class="{{ isset($countries) ? ((in_array('BE', $countries)) ? '' : 'd-none') : 'd-none' }}">Belgium</option>
                        <option value="BG" class="{{ isset($countries) ? ((in_array('BG', $countries)) ? '' : 'd-none') : 'd-none' }}">Bulgaria</option>
                        <option value="HR" class="{{ isset($countries) ? ((in_array('HR', $countries)) ? '' : 'd-none') : 'd-none' }}">Croatia</option>
                        <option value="CY" class="{{ isset($countries) ? ((in_array('CY', $countries)) ? '' : 'd-none') : 'd-none' }}">Cyprus</option>
                        <option value="CZ" class="{{ isset($countries) ? ((in_array('CZ', $countries)) ? '' : 'd-none') : 'd-none' }}">Czech Republic</option>
                        <option value="DK" class="{{ isset($countries) ? ((in_array('DK', $countries)) ? '' : 'd-none') : 'd-none' }}">Denmark</option>
                        <option value="EE" class="{{ isset($countries) ? ((in_array('EE', $countries)) ? '' : 'd-none') : 'd-none' }}">Estonia</option>
                        <option value="FI" class="{{ isset($countries) ? ((in_array('FI', $countries)) ? '' : 'd-none') : 'd-none' }}">Finland</option>
                        <option value="FR" class="{{ isset($countries) ? ((in_array('FR', $countries)) ? '' : 'd-none') : 'd-none' }}">France</option>
                        <option value="DE" class="{{ isset($countries) ? ((in_array('DE', $countries)) ? '' : 'd-none') : 'd-none' }}">Germany</option>
                        <option value="GR" class="{{ isset($countries) ? ((in_array('GR', $countries)) ? '' : 'd-none') : 'd-none' }}">Greece</option>
                        <option value="HU" class="{{ isset($countries) ? ((in_array('HU', $countries)) ? '' : 'd-none') : 'd-none' }}">Hungary</option>
                        <option value="IE" class="{{ isset($countries) ? ((in_array('IE', $countries)) ? '' : 'd-none') : 'd-none' }}">Ireland, Republic of (EIRE)</option>
                        <option value="IT" class="{{ isset($countries) ? ((in_array('IT', $countries)) ? '' : 'd-none') : 'd-none' }}">Italy</option>
                        <option value="LV" class="{{ isset($countries) ? ((in_array('LV', $countries)) ? '' : 'd-none') : 'd-none' }}">Latvia</option>
                        <option value="LT" class="{{ isset($countries) ? ((in_array('LT', $countries)) ? '' : 'd-none') : 'd-none' }}">Lithuania</option>
                        <option value="LU" class="{{ isset($countries) ? ((in_array('LU', $countries)) ? '' : 'd-none') : 'd-none' }}">Luxembourg</option>
                        <option value="MT" class="{{ isset($countries) ? ((in_array('MT', $countries)) ? '' : 'd-none') : 'd-none' }}">Malta</option>
                        <option value="NL" class="{{ isset($countries) ? ((in_array('NL', $countries)) ? '' : 'd-none') : 'd-none' }}">Netherlands</option>
                        <option value="NO" class="{{ isset($countries) ? ((in_array('NO', $countries)) ? '' : 'd-none') : 'd-none' }}">Norway</option>             
                        <option value="PL" class="{{ isset($countries) ? ((in_array('PL', $countries)) ? '' : 'd-none') : 'd-none' }}">Poland</option>
                        <option value="PT" class="{{ isset($countries) ? ((in_array('PT', $countries)) ? '' : 'd-none') : 'd-none' }}">Portugal</option>
                        <option value="RO" class="{{ isset($countries) ? ((in_array('RO', $countries)) ? '' : 'd-none') : 'd-none' }}">Romania</option>
                        <option value="SK" class="{{ isset($countries) ? ((in_array('SK', $countries)) ? '' : 'd-none') : 'd-none' }}">Slovakia</option>
                        <option value="SI" class="{{ isset($countries) ? ((in_array('SI', $countries)) ? '' : 'd-none') : 'd-none' }}">Slovenia</option>
                        <option value="ES" class="{{ isset($countries) ? ((in_array('ES', $countries)) ? '' : 'd-none') : 'd-none' }}">Spain</option>
                        <option value="SE" class="{{ isset($countries) ? ((in_array('SE', $countries)) ? '' : 'd-none') : 'd-none' }}">Sweden</option>
                        <option value="CH" class="{{ isset($countries) ? ((in_array('CH', $countries)) ? '' : 'd-none') : 'd-none' }}">Switzerland</option>
                        <option value="GB" class="{{ isset($countries) ? ((in_array('GB', $countries)) ? '' : 'd-none') : 'd-none' }}">United Kingdom</option>
                      </optgroup>
                      <optgroup label="Rest of the world">
                        <option value="US" class="{{ isset($countries) ? ((in_array('US', $countries)) ? '' : 'd-none') : 'd-none' }}">United States of America</option>
                        <option value="HK" class="{{ isset($countries) ? ((in_array('HK', $countries)) ? '' : 'd-none') : 'd-none' }}">Hong Kong</option>
                      </optgroup>
                   </select>     
                  </div>
                </div>
              </div>

              <div class="row">   
                <div class="col-6">
                  <div class="mb-3">
                    <label class="form-label" for="user">Action</label>          
                    <select id="reminder_action" class="form-select" name="reminder_action" required disabled="disabled">
                      <option value="">Select Action</option>  
                      {{--                
                      @foreach($reminder_actions as $reminder_action)                    
                        <option value="{{ $reminder_action->id }}">{{ $reminder_action->action_name }}</option>
                      @endforeach
                      --}}
                    </select> 
                  </div>
                </div> 
                             
                <div class="col-6">
                  <div class="mb-3">
                    {{--
                    <label class="form-label" for="user">Vat Reg.</label>          
                    <select id="vat_reg_main" class="form-select" name="vat_reg_main" required>
                      <option value="">Select Vat Reg.</option>
                      
                      @foreach($clients as $client_name)                           
                        <optgroup label="{{ $client_name }}">     
                          @foreach($vat_reg_mains as $vat_reg_main)
                            @php
                              $client = $vat_reg_main->client;
                            @endphp
                            @if($client_name == $client->client_name)
                              <option value="{{ $vat_reg_main->id }}" data-client_id="{{ $client->id }}">{{ $vat_reg_main->country }}</option>
                            @endif  
                          @endforeach
                        </optgroup>                          
                      @endforeach  
                    </select>
                    --}}

                    <label class="form-label" for="company">Company</label>          
                    <select id="company" class="form-select" name="company" required disabled="disabled">
                      <option value="">No company found</option>
                    </select>                    
                  </div>
                </div>                

                
              </div>               
           
              {{--            
              <div class="row">
                <div class="col-6">
                  <div class="mb-3">
                    <label class="form-label" for="user-role">Role</label>          
                    <select id="user-role" class="form-select" name="role" required>    
                      <option value="">Select Role</option>                 
                      <option value="team-user">Team User</option>
                      <option value="client-user">Client User</option>
                    </select> 
                  </div>
                </div>
                
                <div class="col-6">  
                  <div class="mb-3">
                    <label class="form-label" for="user">Email</label>          
                    <select id="user" class="form-select" name="user" required>
                      <option value="">Select User</option>
                      <optgroup label="Team User" id="team-user" style="display: none;">
                        @foreach($team_users as $team_user)
                          <option value="{{ $team_user->id }}">{{ $team_user->email }}</option>
                        @endforeach
                      </optgroup>
                      <optgroup label="Client User" id="client-user" style="display: none;">
                        @foreach($client_users as $client_user)
                          <option value="{{ $client_user->id }}">{{ $client_user->email }}</option>
                        @endforeach
                      </optgroup>
                    </select> 
                  </div>
                </div>
              </div>
              --}}

              <div class="row">
                <div class="col-6">
                  <div class="mb-3">
                    <label for="reminder_datetime" class="form-label">Date Time</label>
                    <input type="text" class="form-control" placeholder="DD-MM-YYYY HH:MM" id="reminder_datetime" name="reminder_datetime" required onkeypress="return false;" data-auto-apply="true" disabled="disabled" />
                  </div>
                </div>
                <div class="col-6">  
                  <div class="mb-3">
                    <label class="form-label" for="schedule">Schedule</label>          
                    <select id="schedule" class="form-select" name="schedule" required disabled="disabled">    
                      <option value="">Select Schedule</option>
                      <option value="Does not repeat">Does not repeat</option>
                      <option value="Every second week">Every second week</option>
                      <option value="Each month">Each month</option>
                      <option value="Every second month">Every second month</option>
                      <option value="Every quarterly">Every quarterly</option>
                      <option value="Every year">Every year</option>
                    </select> 
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-2">
                  <div class="mb-3">
                    <label class="form-label" for="year">Year</label>          
                    <select id="year" class="form-select" name="year" required disabled="disabled">
                      <option value="" selected="selected">Select</option>       
                      @for ($year = 2023; $year <= date('Y'); $year++)
                          <option value="{{ $year }}">{{ $year }}</option>
                      @endfor
                    </select> 
                  </div>
                </div>

                <div class="col-2">
                  <div class="mb-3">
                    <label class="form-label" for="period">Period</label>          
                    <select id="period" class="form-select" name="period" required disabled="disabled">
                      <option value="" selected="selected">Select</option>
                      <optgroup label="NO">
                        <option value="no_1">Jan - Feb</option>
                        <option value="no_2">Mar - Apr</option>
                        <option value="no_3">May - Jun</option>
                        <option value="no_4">Jul - Aug</option>
                        <option value="no_5">Sep - Oct</option>
                        <option value="no_6">Nov - Dec</option> 
                      </optgroup> 
                      <optgroup label="UK">
                        <option value="uk_1">Jan - Mar</option>
                        <option value="uk_2">Feb - Apr</option>
                        <option value="uk_3">Mar - May</option>
                        <option value="uk_4">Apr - Jun</option>
                        <option value="uk_5">May - Jul</option>
                        <option value="uk_6">Jun - Aug</option>  
                        <option value="uk_7">Jul - Sep</option>
                        <option value="uk_8">Aug - Oct</option>
                        <option value="uk_9">Sep - Nov</option>
                        <option value="uk_10">Oct - Dec</option>
                        <option value="uk_11">Nov - Jan</option>
                        <option value="uk_12">Dec - Feb</option>
                      </optgroup>                    
                    </select> 
                  </div>
                </div>
                <div class="col-2">
                  <div class="mb-3">
                    <label class="form-label" for="language">Language</label>          
                    <select id="language" class="form-select" name="language" required disabled="disabled">
                      <option value="en" selected="selected">English</option>
                      <option value="dk">Danish</option>                      
                    </select> 
                  </div>
                </div>

                <div class="col-3">
                  <div class="mb-3">
                    <label class="form-label" for="reminder_template">Reminder Template</label>          
                    <select id="reminder_template" class="form-select" name="reminder_template" required disabled="disabled">
                      <option value="" selected="selected">Select</option>
                      <option value="reminder_1">1. Reminder</option>
                      <option value="reminder_2">2. Reminder</option>
                      <option value="reminder_2">3. Reminder</option>
                      <option value="reminder_2">4. Reminder</option>                      
                    </select> 
                  </div>
                </div>

                <div class="col-3">
                  <label class="form-label" for="shortcodes">Shortcodes</label>
                    <br>[client_name] <b>&nbsp;</b> [period]
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <div id="email-template" class="mb-3">
                    <label class="form-label" for="title">Title <em class="ms-4 text-danger text-none">NOTE: Enter the title in both languages by switching the language dropdown.</em></label>  
                    <span class="float-end email-template-name">Email Template: General Reminder</span>    
                    <input type="text" class="form-control" id="title" name="title" required disabled="disabled" />
                    <input type="text" class="form-control" id="dk_title" name="dk_title" required disabled="disabled" style="display: none;" />
                  </div>
                </div>
              </div>

              <div class="mb-3" id="reminder_content">
                <label class="form-label" for="content">Content <em class="ms-4 text-danger text-none">NOTE: Enter the content in both languages by switching the language dropdown.</em></label>                     
                <!-- HTML Editor-->
                <textarea name="reminder_content_quill" style="display: none;" id="reminder-content-quill" readonly="true"></textarea>
                <div class="email-compose-message">                  
                  <div class="reminder-editor"></div>
                </div> 
                <!--/ HTML Editor-->     
              </div>

              <div class="mb-3" id="dk_reminder_content" style="display: none;">
                <label class="form-label" for="content">Content <em class="ms-4 text-danger text-none">NOTE: Enter the content in both languages by switching the language dropdown.</em></label>                     
                <!-- HTML Editor-->
                <textarea name="dk_reminder_content_quill" style="display: none;" id="dk-reminder-content-quill" readonly="true"></textarea>
                <div class="email-compose-message">                  
                  <div class="dk-reminder-editor"></div>
                </div> 
                <!--/ HTML Editor-->     
              </div>

              <div class="row">
                <div class="col-12" id="reminder_to_users">
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-sm-4 text-start">
                  <button type="button" class="btn btn-danger disabled btn-send-test-reminder" id="btn-send-test-reminder">Send Test</button>
                </div>
                <div class="col-sm-4 text-center">
                  <button type="button" class="btn btn-danger disabled btn-send-reminder" id="btn-send-reminder">Send Reminder (0)</button>
                </div>
                <div class="col-sm-4 text-end">          
                  <button type="button" class="btn btn-danger disabled btn-create-reminder" id="btn-create-reminder">Save</button>
                </div>                                                      
              </div>
            </form>
          </div>
          <!-- /Basic  -->
        </div>          
        
      </div>      
    </div>
  </div>
</div>