<div class="chat-history-header border-bottom">
  <div class="d-flex justify-content-between align-items-center">
    <div class="d-flex overflow-hidden align-items-center">
      <i class="bx bx-menu bx-sm cursor-pointer d-lg-none d-block me-2" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-contacts"></i>      
      <div class="flex-shrink-0 avatar">
        @if($user)
          <img src="{{ $user->profile_photo_url }}" alt="{{ $user->firstname . ' ' . $user->lastname }}" class="rounded-circle rightsidebar" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-right">
        @endif
      </div>
      <div class="chat-contact-info flex-grow-1 ms-3">
        @if($user)
          <h6 class="m-0">{{ $user->firstname . ' ' . $user->lastname }}</h6>
          <small class="user-status text-muted">{{ ucfirst(str_replace('-', ' ', $user->role)) }}</small>
        @endif
      </div>      
    </div>
    <div class="d-flex align-items-center">
      @if($user)
      <i class="bx bx-phone-call cursor-pointer d-sm-block d-none me-3 fs-4"></i>
      <i class="bx bx-video cursor-pointer d-sm-block d-none me-3 fs-4"></i>
      <i class="bx bx-search cursor-pointer d-sm-block d-none me-3 fs-4"></i>
      <div class="dropdown">
        <i class="bx bx-dots-vertical-rounded cursor-pointer fs-4" id="chat-header-actions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        </i>
        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="chat-header-actions">
          <a class="dropdown-item" href="javascript:void(0);">View Contact</a>
          <a class="dropdown-item" href="javascript:void(0);">Mute Notifications</a>
          <a class="dropdown-item" href="javascript:void(0);">Block Contact</a>
          <a class="dropdown-item" href="javascript:void(0);">Clear Chat</a>
          <a class="dropdown-item" href="javascript:void(0);">Report</a>
        </div>
      </div>
      @endif
    </div>
  </div>
</div> 