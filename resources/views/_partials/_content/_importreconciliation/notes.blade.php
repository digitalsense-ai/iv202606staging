<!-- Timeline Advanced-->
@if($importreconciliation_notes->isEmpty())  
  No Notes.
@else  
  @php  
    $timelineClass = 'timeline-item-';  
    $color = 'secondary';    
  @endphp
  <div class="row overflow-hidden">
    <div class="col-12">
      <ul class="timeline timeline-center mt-5">
        @foreach ($importreconciliation_notes as $note_key => $note)      
          @if($note->created_at)
            @php               
              $user = $note->user;
              $role = $user->roles->first();
              $dvuser = $user->dvuser;
              $profile_photo_url = \App\Models\User::defaultProfilePhotoUrl($dvuser, true);            
            @endphp
            <li class="timeline-item timeline-item-{{ ($role->name == 'team-user') ? 'success' : $color }} mb-md-4 mb-5 {{$timelineClass .  (($note->type == 'general') ? 'right' : 'left') }}">
              <span class="timeline-indicator timeline-indicator-success border-0" data-aos="zoom-in" data-aos-delay="200">                
              </span>
              <div class="timeline-event card p-0" data-aos="fade-left">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                  <div class="d-flex flex-wrap">
                    <div class="avatar me-3">
                      <img src="{{ $profile_photo_url ? $profile_photo_url : asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div>
                      <p class="mb-0">{{ ($dvuser->firstname == "Super" && $dvuser->lastname == "Admin") ? "System" : ($dvuser->firstname . ' ' . $dvuser->lastname) }}</p>
                      <span class="text-muted">{{ \Carbon\Carbon::parse($note->created_at)->format('d-m-Y H:i') }}</span>
                    </div>  
                  </div>

                  @if($authUser->id  == $user->id)
                    <div class="float-end">
                      <button type="button" id="btn-edit-importreconciliation-note-{{ $note->id }}" class="btn btn-primary mx-1 btn-edit-importreconciliation-note" data-vat_reg_id="{{ $note->vat_reg_id }}" data-note_id="{{ $note->id }}" data-note_type="{{ $note->type }}" data-note_comment="{{ $note->notes }}" >Edit</button>

                      <button type="button" id="btn-delete-importreconciliation-note-{{ $note->id }}" class="btn btn-danger mx-1 btn-delete-importreconciliation-note" data-vat_reg_id="{{ $note->vat_reg_id }}" data-note_id="{{ $note->id }}">Delete</button>
                    </div>
                  @endif
                </div> 
                <hr class="my-0 mx-4">     
                <div class="card-body">            
                  <p class="mb-2">{!! $note->notes !!}</p>                  
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