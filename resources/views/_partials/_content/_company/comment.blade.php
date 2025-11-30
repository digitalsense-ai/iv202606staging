<!-- Timeline Advanced-->
@if($client_comments->isEmpty())  
  No comments.
@else  
  @php  
    $timelineClass = 'timeline-item-';  
    $color = 'secondary';
  @endphp
  <div class="row overflow-hidden">
    <div class="col-12">
      <ul class="timeline timeline-center mt-5">
        @foreach ($client_comments as $client_comment_key => $client_comment)      
          @if($client_comment->created_at)
            @php              
              $user = $client_comment->user;
              $role = $user->roles->first();
              $dvuser = $user->dvuser;
              $profile_photo_url = \App\Models\User::defaultProfilePhotoUrl($dvuser, true);            
            @endphp
            <li class="timeline-item timeline-item-{{ ($role->name == 'team-user') ? 'success' : $color }} mb-md-4 mb-5 {{$timelineClass .  (($role->name == 'team-user' || $role->name == 'client-user') ? 'right' : 'left') }}">
              <span class="timeline-indicator timeline-indicator-success border-0" data-aos="zoom-in" data-aos-delay="200">
                <!-- <i class="bx bx-receipt"></i> -->
              </span>
              <div class="timeline-event card p-0" data-aos="fade-left">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                  <div class="d-flex flex-wrap">
                    <div class="avatar me-3">
                      <img src="{{ $profile_photo_url ? $profile_photo_url : asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div>
                      <p class="mb-0">{{ ($dvuser->firstname == "Super" && $dvuser->lastname == "Admin") ? "System" : ($dvuser->firstname . ' ' . $dvuser->lastname) }}</p>
                      <span class="text-muted">{{ \Carbon\Carbon::parse($client_comment->created_at)->format('d-m-Y H:i') }}</span>
                    </div>  
                  </div>

                  @if($authUser->id  == $user->id)
                    <button type="button" id="btn-delete-client-comment-{{ $client_comment->client_id }}" class="btn btn-danger float-end mx-2 btn-delete-client-comment my-n1" data-client_id="{{ $client_comment->client_id }}" data-comment_id="{{ $client_comment->id }}">Delete Comment</button>
                  @endif
                </div> 
                <hr class="my-0 mx-4 {{ (isset($timeline['filetype'])) ? (($timeline['filetype'] == 'pivs' || $timeline['filetype'] == 'documents' || $timeline['filetype'] == 'comments') ? 'border-bottom' : '') : '' }}">     
                <div class="card-body">            
                  <p class="mb-2">{!! $client_comment->comment !!}</p>                  
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