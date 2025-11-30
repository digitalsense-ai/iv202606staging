<!-- Onboarding slider modals -->
<div class="modal-onboarding modal fade animate__animated" id="onboardingSlideExcelModal-{{ $vat_reg_id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>      
      <div id="modalCarouselExcelControls-{{ $vat_reg_id }}" class="carousel slide pb-4 mb-2" data-bs-interval="false">        
        <div class="carousel-inner">
          <div class="carousel-item active">            
            <div class="onboarding-content"> 
              @php
                $vatreturn_file_id = 0;
                if(count($vatreturnfiles) > 0)
                  $vatreturn_file_id = $vatreturnfiles->first()->id;
              @endphp                                                     
              <h4 class="onboarding-title text-body text-start">{{ ($vatreturn_file_id == 0) ? 'Upload' : 'Overwrite' }}</h4>               
              <div class="onboarding-info text-start">{{ 'Excel/XML for ' . (($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'))) . ' ' . $vatreg->general_periods }}.</div>
                <div class="row mt-5 onboarding-upload">                    
                  <!-- Excel Sheet -->                  
                  <div class="col-sm-12" id="vatreturn-files-{{ $vat_reg_id }}">
                    <div class="mb-3">                                              
                      <form id="frm-vatreturn-file-{{ $vat_reg_id }}" class="m-0 frm-vatreturn-file" method="post" action="" enctype="multipart/form-data" data-vat_reg_id="{{ $vat_reg_id }}" data-client_id="{{ $client_id }}" data-vatreturn_file_id="{{ $vatreturn_file_id }}">
                        @csrf 
                        <div class="input-group">   
                          @if($vatreturn_file_id > 0)
                          <input type="hidden" name="vatreturn_file_id" id="vatreturn_file_id-{{ $vat_reg_id }}" value="{{ $vatreturn_file_id }}">
                          @endif                       
                          <input type="file" class="form-control" id="vatreturn-file-{{ $vat_reg_id }}" name="vatreturn_file" required>
                          <button class="btn btn-outline-primary btn-vatreturn-upload" type="submit" id="btn-vatreturn-upload-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-vatreturn_file_id="{{ $vatreturn_file_id }}">{{ ($vatreturn_file_id == 0) ? 'Upload' : 'Overwrite' }}</button>
                        </div>
                      </form>
                    </div>
                  </div> 
                  <!--/ Excel Sheet -->                
                </div>                   
            </div>
          </div>
       
        </div>        
      </div>
      
    </div>
  </div>
</div>
<!--/ Onboarding slider modals -->