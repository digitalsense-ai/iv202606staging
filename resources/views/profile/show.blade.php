@extends('layouts.layoutMaster')

@php
use App\Models\DVUser;

$breadcrumbs = [['link' => 'home', 'name' => 'Home'], ['link' => 'javascript:void(0)', 'name' => 'User'], ['name' => 'Profile']];

if (Auth::check())
{
  $user_id = Auth::user()->id;
  $authUser = DVUser::where('user_id', $user_id)->first();

  $rolename = Auth::user()->roles()->first()->name;
  $authUser->rolename = $rolename;
  if($rolename == 'team-user' || $rolename == 'client-user')
    $pageConfigs = ['myLayout' => 'horizontal', 'myTheme' => 'theme-default'];      
  else
    $pageConfigs = ['myTheme' => 'theme-semi-dark'];  
}    
@endphp

@section('title', 'Profile')


@section('content')

  @if (Laravel\Fortify\Features::canUpdateProfileInformation())
   <div class="mb-4">
      @livewire('profile.update-profile-information-form')
   </div>
  @endif

  @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
    <div class="mb-4">
      @livewire('profile.update-password-form')
    </div>
  @endif

  @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
   <div class="mb-4">
      @livewire('profile.two-factor-authentication-form')
   </div>
  @endif

  <div class="mb-4">
    @livewire('profile.logout-other-browser-sessions-form')
  </div>

  @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
    @livewire('profile.delete-user-form')
  @endif

@endsection
