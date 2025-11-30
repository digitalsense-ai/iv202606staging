<!-- Assign Client Modal -->
<div class="modal fade" id="notificationSettings" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      
      <div class="modal-body px-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-start">
          <h3 class="mb-2">Preferred receiver</h3>
          <span>We need permission from your browser to show notifications. <span class="notificationRequest"><strong>Request Permission</strong></span></span>
        </div>
      </div>
          
      
      <div class="row">
        <div class="col-md-12">
          
          <!-- <div class="card"> -->
            <!-- Notifications -->
            <!-- <h5 class="card-header">Preferred receiver</h5>
            <div class="card-body">
              <span>We need permission from your browser to show notifications. <span class="notificationRequest"><strong>Request Permission</strong></span></span>
              <div class="error"></div>
            </div> -->            
            <form method="post" class="form-notification">  
              @csrf
              <input type="hidden" id="user-id" name="user_id">
              <div class="table-responsive">
                <table class="table table-striped table-borderless">
                  <thead>
                    <tr>
                      <th class="text-nowrap">Type</th>
                      <th class="text-nowrap text-center">Email<br>
                        <input class="form-check-input fs-6" type="checkbox" id="chk-email-notification" /></th>              
                    </tr>
                  </thead>
                  <tbody>                    
                    @foreach($fileTypes as $key=>$fileType)            
                      <tr>
                        <td class="text-nowrap">{{ $fileType }}</td>
                        <td>
                          <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input chk-email-notification" type="checkbox" id="chk-email-notification-{{ $key }}" name="chk_email_notification_{{ $key }}" />
                          </div>
                        </td>                
                      </tr>
                    @endforeach            
                  </tbody>
                </table>
              </div>

              <div class="card-body border-top">
                <div class="row">         
                  <div class="col-sm-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary me-2">Save changes</button>                    
                  </div>
                </div>
              </div>
            </form>      
            <!-- /Notifications -->
          <!-- </div> -->
        </div>
      </div> 

    </div>
  </div>
</div>
<!--/ Assign Client Modal -->
