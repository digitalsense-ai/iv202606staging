@if($user)
  <div class="sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap px-4 pt-5">
    <div class="avatar avatar-xl avatar-online">
      <img src="{{ $user->profile_photo_url }}" alt="{{ $user->firstname . ' ' . $user->lastname }}" class="rounded-circle">
    </div>
    <h6 class="mt-2 mb-0">{{ $user->firstname . ' ' . $user->lastname }}</h6>
    <span>{{ ucfirst(str_replace('-', ' ', $user->role)) }}</span>
    <i class="bx bx-x bx-sm cursor-pointer close-sidebar d-block" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-right"></i>
  </div>
  <div class="sidebar-body px-4 pb-4">
    <!-- <div class="my-4">
      <p class="text-muted text-uppercase">About</p>
      <p class="mb-0 mt-3">A Next. js developer is a software developer who uses the Next. js framework alongside ReactJS to build web applications.</p>
    </div> -->
    <div class="my-4">
      <p class="text-muted text-uppercase">Personal Information</p>
      <ul class="list-unstyled d-grid gap-2 mt-3">
        <li class="d-flex align-items-center">
          <i class='bx bx-envelope'></i>
          <span class="align-middle ms-2">{{ $user->email }}</span>
        </li>
        <li class="d-flex align-items-center">
          <i class='bx bx-phone-call'></i>
          <span class="align-middle ms-2">{{ $user->telephone }}</span>
        </li>            
      </ul>
    </div>
    <!-- <div class="mt-4">
      <p class="text-muted text-uppercase">Options</p>
      <ul class="list-unstyled d-grid gap-2 mt-3">
        <li class="cursor-pointer d-flex align-items-center">
          <i class='bx bx-tag'></i>
          <span class="align-middle ms-2">Add Tag</span>
        </li>
        <li class="cursor-pointer d-flex align-items-center">
          <i class='bx bx-star'></i>
          <span class="align-middle ms-2">Important Contact</span>
        </li>
        <li class="cursor-pointer d-flex align-items-center">
          <i class='bx bx-image'></i>
          <span class="align-middle ms-2">Shared Media</span>
        </li>
        <li class="cursor-pointer d-flex align-items-center">
          <i class='bx bx-trash'></i>
          <span class="align-middle ms-2">Delete Contact</span>
        </li>
        <li class="cursor-pointer d-flex align-items-center">
          <i class='bx bx-block'></i>
          <span class="align-middle ms-2">Block Contact</span>
        </li>
      </ul>
    </div> -->
  </div>
@endif