@php
$configData = Helper::appClasses();
@endphp
<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal  menu bg-menu-theme flex-grow-0">
  <div class="{{$containerNav}} d-flex h-100">
    <ul class="menu-inner">
      @foreach ($menuData[1]->menu as $menu)

        {{-- active menu method --}}
        @php
          $activeClass = null;
          $currentRouteName =  Route::currentRouteName();

          //if ($currentRouteName === $menu->slug) {
          if (in_array($currentRouteName, $menu->slug)) {
              $activeClass = 'active';
          }
          elseif (isset($menu->submenu)) {
            if (gettype($menu->slug) === 'array') {
              foreach($menu->slug as $slug){
                if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
                  $activeClass = 'active';
                }
              }
            }
            else{
              if (str_contains($currentRouteName,$menu->slug) and strpos($currentRouteName,$menu->slug) === 0) {
                $activeClass = 'active';
              }
            }

          }
        @endphp

        @php    
          $displayClass = '';
          if (isset($menu->roles)) 
          {
            if (in_array(Auth::user()->roles()->first()->name, $menu->roles))
              $displayClass = 'show';
          }

          $notificationClass = '';
          if (isset($menu->notification)) 
          {
            if ($menu->notification)
            {
              if (isset($result)) 
              {
                foreach ($result as $vatreg)  
                {              
                  //if ( ($item->api_ledger[0]->status == 2) || ($item->api_ledger[0]->status == 4))
                  if ( ($vatreg->status == 2) || ($vatreg->status == 4))
                  {
                    $notificationClass = 'notification';
                    break;
                  }
                }
              }
            }
          }
        @endphp

        @if($displayClass == 'show')
          {{-- main menu --}}
          <li class="menu-item {{$activeClass}} {{$notificationClass}}">
            <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
              @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
              @endisset
              <div>{!! isset($menu->name) ? __($menu->name) : '' !!}</div>
            </a>

            {{-- submenu --}}
            @isset($menu->submenu)
              @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
            @endisset
          </li>
        @endif  
      @endforeach
    </ul>
  </div>
</aside>
<!--/ Horizontal Menu -->
