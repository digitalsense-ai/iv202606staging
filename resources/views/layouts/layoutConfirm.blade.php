@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/commonMaster' )
@php

$menuHorizontal = true;
$navbarFull = true;

/* Display elements */
$isNavbar = ($isNavbar ?? true);
$isMenu = ($isMenu ?? true);
$isFlex = ($isFlex ?? false);
$isFooter = ($isFooter ?? true);
$customizerHidden = ($customizerHidden ?? '');
$pricingModal = ($pricingModal ?? false);

/* HTML Classes */
$menuFixed = (isset($configData['menuFixed']) ? $configData['menuFixed'] : '');
$navbarFixed = (isset($configData['navbarFixed']) ? $configData['navbarFixed'] : '');
$footerFixed = (isset($configData['footerFixed']) ? $configData['footerFixed'] : '');
$menuCollapsed = (isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '');

$menuShow = (isset($configData['menuShow']) ? $configData['menuShow'] : '');
$isMenuShow = ($menuShow == 'true' ? 1 : 0);
$footerShow = (isset($configData['footerShow']) ? $configData['footerShow'] : '');
$isFooterShow = ($footerShow == 'true' ? 1 : 0);

/* Content classes */
$container = ($container ?? 'container-xxl');
$containerNav = ($containerNav ?? 'container-xxl');

@endphp

@section('layoutContent')
<div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
  <div class="layout-container">

    <!-- BEGIN: Navbar-->
    @if ($isNavbar && $isMenuShow)
    @include('layouts/sections/navbar/navbar-confirm')
    @endif
    <!-- END: Navbar-->


    <!-- Layout page -->
    <div class="layout-page">

      <!-- Content wrapper -->
      <div class="content-wrapper">
        
        @if (Auth::check())
          @php
            $rolename = Auth::user()->roles()->first()->name;            
          @endphp
          @if (isset($authUser))              
            @php
              $rolename = $authUser->rolename;
            @endphp                
          @endif          
        @endif

        @if(isset($rolename))
          @if($rolename == 'client-user')
            @php
              $containerNav = 'container-fluid';
            @endphp  
            @if ($isMenu && $isMenuShow)          
              @include('layouts/sections/menu/horizontalMenu')
            @endif
          @endif
        @endif  

        <!-- Content -->
        @if ($isFlex)
        <div class="{{$container}} d-flex align-items-stretch flex-grow-1 p-0">
          @else
          <div class="{{$container}} flex-grow-1 container-p-y">
            @endif

            @yield('content')

            <!-- pricingModal -->
            @if ($pricingModal)
            @include('_partials/_modals/modal-pricing')
            @endif
            <!--/ pricingModal -->
          </div>
          <!-- / Content -->

          <!-- Footer -->
          @if ($isFooter && $isFooterShow)
          @include('layouts/sections/footer/footer')
          @endif
          <!-- / Footer -->
          <div class="content-backdrop fade"></div>
        </div>
        <!--/ Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>
    <!-- / Layout Container -->

    
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->
  @endsection
