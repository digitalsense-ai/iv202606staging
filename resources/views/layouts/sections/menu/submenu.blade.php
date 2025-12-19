<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)

    {{-- active menu method --}}
    @php
      $activeClass = null;
      $active = $configData["layout"] === 'vertical' ? 'active open':'active';
      $currentRouteName =  Route::currentRouteName();

      if ($currentRouteName === $submenu->slug) {
          $activeClass = 'active';
      }
      elseif (isset($submenu->submenu)) {
        if (gettype($submenu->slug) === 'array') {
          foreach($submenu->slug as $slug){
            if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
                $activeClass = $active;
            }
          }
        }
        else{
          if (str_contains($currentRouteName,$submenu->slug) and strpos($currentRouteName,$submenu->slug) === 0) {
            $activeClass = $active;
          }
        }
      }
    @endphp

    @php    
      $displayClass = '';
      if (isset($submenu->roles)) 
      {
        if (in_array(Auth::user()->roles()->first()->name, $submenu->roles))
          $displayClass = 'show';
      }
    @endphp

      @if($displayClass == 'show')
        @php
          if($currentRouteName == 'uploads')
          {  
            if($submenu->name == 'All')
            {
              if($upload_file_type)
                $activeClass = null;
              else
                $activeClass = 'active';
            }
            elseif($submenu->name == 'Cash account (GB)' && $upload_file_type == 'cas')
              $activeClass = 'active';  
            elseif($submenu->name == 'Postponed import vat statements (GB)' && $upload_file_type == 'pivs')
              $activeClass = 'active';
            elseif($submenu->name == 'Duty derferment (NO)' && $upload_file_type == 'dda')
              $activeClass = 'active';            
          }
          else if($currentRouteName == 'reminders')
          {  
            if($submenu->name == 'All')
            {
              if($reminder_type)
                $activeClass = null;
              else
                $activeClass = 'active';
            }
            elseif(($submenu->name == 'AT' && $reminder_type == 'at') || 
              ($submenu->name == 'DK' && $reminder_type == 'dk') || 
              ($submenu->name == 'FR' && $reminder_type == 'fr') || 
              ($submenu->name == 'NO' && $reminder_type == 'no') || 
              ($submenu->name == 'CH' && $reminder_type == 'ch') || 
              ($submenu->name == 'GB' && $reminder_type == 'gb')
            )
              $activeClass = 'active';             
          }
        @endphp

        <li class="menu-item {{$activeClass}}">
          <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}" class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
            @if (isset($submenu->icon))
            <i class="{{ $submenu->icon }}"></i>
            @endif
            <div>{!! isset($submenu->name) ? __($submenu->name) : '' !!}</div>
          </a>

          {{-- submenu --}}
          @if (isset($submenu->submenu))
            @include('layouts.sections.menu.submenu',['menu' => $submenu->submenu, 'indent' => true])
          @endif
        </li>
      @endif  
    @endforeach
  @endif
</ul>
