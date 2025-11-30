<!-- Timeline Advanced-->
@if($client_histories->isEmpty())  
  No histories.
@else  
  @php  
    $timelineClass = 'timeline-item-';  
    $color = 'secondary';
  @endphp
  <div class="row overflow-hidden">
    <div class="col-12">
      <ul class="timeline timeline-center mt-5">
        @foreach ($client_histories as $client_history_key => $client_history)      
          @if($client_history['created_at'])            
            <li class="timeline-item timeline-item-{{ ($client_history['role'] == 'team-user') ? 'success' : $color }} mb-md-4 mb-5 {{$timelineClass .  (($client_history['role'] == 'team-user' || $client_history['role'] == 'client-user') ? 'right' : 'left') }}">
              <span class="timeline-indicator timeline-indicator-success border-0" data-aos="zoom-in" data-aos-delay="200">
                <!-- <i class="bx bx-receipt"></i> -->
              </span>
              <div class="timeline-event card p-0" data-aos="fade-left">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                  <div class="d-flex flex-wrap">
                    <div class="avatar me-3">
                      <img src="{{ $client_history['profile_photo_url'] ? $client_history['profile_photo_url'] : asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div>
                      <p class="mb-0">{{ ($client_history['firstname'] == "Super" && $client_history['lastname'] == "Admin") ? "System" : ($client_history['firstname'] . ' ' . $client_history['lastname']) }}</p>
                      <span class="text-muted">{{ \Carbon\Carbon::parse($client_history['created_at'])->format('d-m-Y H:i') }}</span>
                    </div>  
                  </div>                  
                </div> 
                <hr class="my-0 mx-4 {{ (isset($timeline['filetype'])) ? (($timeline['filetype'] == 'pivs' || $timeline['filetype'] == 'documents' || $timeline['filetype'] == 'comments') ? 'border-bottom' : '') : '' }}">     
                <div class="card-body">            
                  <p class="mb-2">{!! $client_history['message'] !!}</p>                  
                </div>    
                
                <div class="timeline-event-time"></div>
              </div>
            </li>          
          @endif
        @endforeach
      </ul>
    </div>
  </div>
@endif  
<!-- /Timeline Advanced-->