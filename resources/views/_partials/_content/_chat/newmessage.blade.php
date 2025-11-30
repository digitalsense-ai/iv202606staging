<li class="chat-message chat-message-right" id="message-{{$message->id}}">
  <div class="d-flex overflow-hidden">
    <div class="chat-message-wrapper flex-grow-1">
      <div class="chat-message-text">
        @if($message->is_file)
          <a href="{{ asset('storage/'. $message->message) }}" class="invert-text-white text-decoration-underline" target="_blank"><i class="fa-solid fa-paperclip mx-3"></i>{{$message->message}}</a>
        @else
          <p class="mb-0">{{$message->message}}</p>
        @endif  
      </div>
      <div class="text-end text-muted mt-1">
        <i class='bx bx-check-double text-success'></i>
        <small>{{$message->humans_time}}</small>
      </div>
    </div>
    <div class="user-avatar flex-shrink-0 ms-3">
      <div class="avatar avatar-sm">                    
        <img src="{{ $authUser ? $authUser->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt="{{ $authUser->firstname . ' ' . $authUser->lastname }}" class="rounded-circle">
      </div>
    </div>
  </div>
</li>