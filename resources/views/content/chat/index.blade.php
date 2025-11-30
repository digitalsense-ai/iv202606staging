@extends('layouts/layoutMaster')

@section('title', 'Chat - Apps')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/app-chat.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/app-chat.js')}}"></script>
<script src="{{asset('js/dv-chat-talk.js')}}"></script>

<script>
    // var show = function(data) {
    //     alert(data.sender.name + " - '" + data.message + "'");
    // }

    var msgshow = function(data) {console.log(data);
        var attachment = '<p class="mb-0">'+ data.message +'</p>';
        if(data.is_file)
          attachment = '<a href="{{ asset("storage/") }}'+ data.message +'" class="invert-text-white text-decoration-underline" target="_blank"><i class="fa-solid fa-paperclip mx-3"></i>'+ data.message +'</a>';
        
        var html = '<li class="chat-message" id="message-'+ data.id +'">' +
          '<div class="d-flex overflow-hidden">' +
            '<div class="chat-message-wrapper flex-grow-1">' +
              '<div class="chat-message-text">' +
                //'<p class="mb-0">'+ data.message +'</p>' +
                attachment +
              '</div>' +
              '<div class="text-end text-muted mt-1">' +
                '<i class="bx bx-check-double text-success"></i>' +
                '<small>'+ data.humans_time +'</small>' +
              '</div>' +
            '</div>' +
            '<div class="user-avatar flex-shrink-0 ms-3">' +
              '<div class="avatar avatar-sm">' +
                '<img src="'+ data.sender.profile_photo_url +'" alt="'+ data.sender.name +'" class="rounded-circle">' +
              '</div>' +
            '</div>' +
          '</div>' +
        '</li>';

        $('ul.chat-history').append(html);
        
        var chatHistoryBody = document.querySelector('.chat-history-body');
        chatHistoryBody.scrollTo(0, chatHistoryBody.scrollHeight);  
    }
</script>
{!! talk_live(['user'=>["id"=>$authUser->user_id, 'callback'=>['msgshow']]]) !!}
@endsection

@section('content')
<div class="app-chat card overflow-hidden">
  <div class="row g-0">
    <!-- Sidebar Left -->
    <div class="col app-chat-sidebar-left app-sidebar overflow-hidden" id="app-chat-sidebar-left">
      <div class="chat-sidebar-left-user sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap px-4 pt-5">
        <div class="avatar avatar-xl avatar-online">
          <img src="{{$authUser->profile_photo_url}}" alt="{{ $authUser->firstname . ' ' . $authUser->lastname }}" class="rounded-circle">
        </div>
        <h5 class="mt-2 mb-0">{{ $authUser->firstname . ' ' . $authUser->lastname }}</h5>
        <small>{{ ucfirst(str_replace('-', ' ', $authUser->role)) }}</small>
        <i class="bx bx-x bx-sm cursor-pointer close-sidebar" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-left"></i>
      </div>
      <div class="sidebar-body px-4 pb-4">
        <!-- <div class="my-4">
          <p class="text-muted text-uppercase">About</p>
          <textarea id="chat-sidebar-left-user-about" class="form-control chat-sidebar-left-user-about mt-3" rows="4" maxlength="120">Dessert chocolate cake lemon drops jujubes. Biscuit cupcake ice cream bear claw brownie brownie marshmallow.</textarea>
        </div> -->
        <div class="my-4">
          <p class="text-muted text-uppercase">Status</p>
          <div class="d-grid gap-1">
            <div class="form-check form-check-success">
              <input name="chat-user-status" class="form-check-input" type="radio" value="active" id="user-active" checked>
              <label class="form-check-label" for="user-active">Active</label>
            </div>
            <div class="form-check form-check-danger">
              <input name="chat-user-status" class="form-check-input" type="radio" value="busy" id="user-busy">
              <label class="form-check-label" for="user-busy">Busy</label>
            </div>
            <div class="form-check form-check-warning">
              <input name="chat-user-status" class="form-check-input" type="radio" value="away" id="user-away">
              <label class="form-check-label" for="user-away">Away</label>
            </div>
            <div class="form-check form-check-secondary">
              <input name="chat-user-status" class="form-check-input" type="radio" value="offline" id="user-offline">
              <label class="form-check-label" for="user-offline">Offline</label>
            </div>
          </div>
        </div>
        <!-- <div class="my-4">
          <p class="text-muted text-uppercase">Settings</p>
          <ul class="list-unstyled d-grid gap-2 me-3">
            <li class="d-flex justify-content-between align-items-center">
              <div>
                <i class='bx bx-message-square-detail me-1'></i>
                <span class="align-middle">Two-step Verification</span>
              </div>
              <label class="switch switch-primary me-4">
                <input type="checkbox" class="switch-input" checked="" />
                <span class="switch-toggle-slider">
                  <span class="switch-on"></span>
                  <span class="switch-off"></span>
                </span>
              </label>
            </li>
            <li class="d-flex justify-content-between align-items-center">
              <div>
                <i class='bx bx-bell me-1'></i>
                <span class="align-middle">Notification</span>
              </div>
              <label class="switch switch-primary me-4">
                <input type="checkbox" class="switch-input" />
                <span class="switch-toggle-slider">
                  <span class="switch-on"></span>
                  <span class="switch-off"></span>
                </span>
              </label>
            </li>
            <li>
              <i class="bx bx-user me-1"></i>
              <span class="align-middle">Invite Friends</span>
            </li>
            <li>
              <i class="bx bx-trash me-1"></i>
              <span class="align-middle">Delete Account</span>
            </li>
          </ul>
        </div>
        <div class="d-flex mt-4">
          <button class="btn btn-primary" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-left">Logout</button>
        </div> -->
      </div>
    </div>
    <!-- /Sidebar Left-->

    <!-- Chat & Contacts -->
    <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
      <div class="sidebar-header py-3 px-4 border-bottom">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0 avatar avatar-online me-3" data-bs-toggle="sidebar" data-overlay="app-overlay-ex" data-target="#app-chat-sidebar-left">
            <img class="user-avatar rounded-circle cursor-pointer" src="{{$authUser->profile_photo_url}}" alt="{{ $authUser->firstname . ' ' . $authUser->lastname }}">
          </div>
          <div class="flex-grow-1 input-group input-group-merge rounded-pill">
            <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search fs-4"></i></span>
            <input type="text" class="form-control chat-search-input" placeholder="Search..." aria-label="Search..." aria-describedby="basic-addon-search31">
          </div>
        </div>
        <i class="bx bx-x cursor-pointer position-absolute top-0 end-0 mt-2 me-1 fs-4 d-lg-none d-block" data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
      </div>
      <div class="sidebar-body">
        <!-- Chats -->
        <ul class="list-unstyled chat-contact-list" id="chat-list">          
          <li class="chat-contact-list-item chat-contact-list-item-title">
            <h5 class="text-primary mb-0">Chats</h5>
          </li>          
          <li class="chat-contact-list-item chat-list-item-0 d-none">
            <h6 class="text-muted mb-0">No Chats Found</h6>
          </li>
          
          @foreach($threads as $inbox)
            @if(!is_null($inbox->thread))
              <li class="chat-contact-list-item chat-list-item" data-user_id="{{$inbox->withUser->id}}">
                <a class="d-flex align-items-center">
                  <div class="flex-shrink-0 avatar avatar-online">
                    <img src="{{$inbox->withUser->profile_photo_url}}" alt="{{ $inbox->withUser->name }}" class="rounded-circle">
                  </div>
                  <div class="chat-contact-info flex-grow-1 ms-3">
                    <h6 class="chat-contact-name text-truncate m-0">{{$inbox->withUser->name}}</h6>
                    <p class="chat-contact-status text-truncate mb-0 text-muted">{{ ucfirst(str_replace('-', ' ', $inbox->withUser->role)) }}</p>
                  </div>
                  <small class="text-muted mb-auto">{{$inbox->thread->humans_time}} ago</small>
                </a>
              </li>
            @endif
          @endforeach  
        </ul>
        <!-- Contacts -->
        <ul class="list-unstyled chat-contact-list mb-0" id="contact-list">          
          <li class="chat-contact-list-item chat-contact-list-item-title">
            <h5 class="text-primary mb-0">Contacts</h5>
          </li>         
          <li class="chat-contact-list-item contact-list-item-0 d-none">
            <h6 class="text-muted mb-0">No Contacts Found</h6>
          </li>          
          @foreach($allUsers as $allUser)
          <li class="chat-contact-list-item chat-list-item" data-user_id="{{$allUser->user_id}}">
            <a class="d-flex align-items-center">
              <div class="flex-shrink-0 avatar avatar-offline">
                <img src="{{$allUser->profile_photo_url}}" alt="{{ $allUser->firstname . ' ' . $allUser->lastname }}" class="rounded-circle">
              </div>
              <div class="chat-contact-info flex-grow-1 ms-3">
                <h6 class="chat-contact-name text-truncate m-0">{{ $allUser->firstname . ' ' . $allUser->lastname }}</h6>
                <p class="chat-contact-status text-truncate mb-0 text-muted">{{ ucfirst(str_replace('-', ' ', $allUser->role)) }}</p>
              </div>
            </a>
          </li>
          @endforeach            
        </ul>
      </div>
    </div>
    <!-- /Chat contacts -->

    <!-- Chat History -->
    <div class="col app-chat-history bg-body">
      <div class="chat-history-wrapper">
        @include('_partials/_content/_chat/chatheader') 

        <div class="chat-history-body bg-body">
          <ul class="list-unstyled chat-history mb-0">
            @include('_partials/_content/_chat/chatbody') 
          </ul>
        </div>

        @if($user)
          @include('_partials/_content/_chat/sendmessage') 
        @endif
      </div>
    </div>
    <!-- /Chat History -->

    <!-- Sidebar Right -->
    <div class="col app-chat-sidebar-right app-sidebar overflow-hidden" id="app-chat-sidebar-right">      
      @include('_partials/_content/_chat/rightsidebar') 
    </div>
    <!-- /Sidebar Right -->

    <div class="app-overlay"></div>
  </div>
</div>
@endsection
