@extends('layouts/layoutMaster')

@section('title', 'Users - List')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}" />
@endsection

@section('vendor-script')
<!-- <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.user_datas = [];
    //var vat_countries = "";
    //var user_start = 1;

    var result = { 'users': {!! json_encode($users) !!} };    
    user_datas = drawDtTable(result, 'user');         
});
</script>

<script src="{{asset('assets/js/intlTelInput.min.js')}}"></script>

<script src="{{asset('js/dv-users-lazy.js')}}"></script>
<script src="{{asset('js/dv-modal-assign-vat-reg.js')}}"></script>
<script src="{{asset('js/dv-modal-assign-team-user.js')}}"></script>
<script src="{{asset('js/dv-modal-assign-client.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Users/</span> List</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center user-page">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<div class="row g-4 mb-4" style="display: none;" id="header-card">
  @php
    //Get Users              
    $total_users = $users->count();
    $active_users = $users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? $user->dvuser->status : false) : false; 
    })->count();

    //Get Admin Users 
    $admin_users = $users->filter(function ($user, $key) {                   
      //return ($user->roles->first()->name == 'super-admin'); 
      return $user->roles->contains('name', 'super-admin');
    });
    $total_admin_users = $admin_users->count();
    $active_admin_users = $admin_users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? $user->dvuser->status : false) : false; 
    })->count();
    $inactive_admin_users = $admin_users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? !$user->dvuser->status : false) : true; 
    })->count();

    //Get Company Admin Users 
    $company_admin_users = $users->filter(function ($user, $key) {                   
      //return ($user->roles->first()->name == 'company-admin'); 
      return $user->roles->contains('name', 'company-admin');
    });
    $total_company_admin_users = $company_admin_users->count();
    $active_company_admin_users = $company_admin_users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? $user->dvuser->status : false) : false; 
    })->count();
    $inactive_company_admin_users = $company_admin_users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? !$user->dvuser->status : false) : true; 
    })->count();

    //Get Team Users 
    $team_users = $users->filter(function ($user, $key) {                   
      //return ($user->roles->first()->name == 'team-user'); 
      return $user->roles->contains('name', 'team-user');
    });
    $total_team_users = $team_users->count();
    $active_team_users = $team_users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? $user->dvuser->status : false) : false; 
    })->count();
    $inactive_team_users = $team_users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? !$user->dvuser->status : false) : true; 
    })->count();

    //Get Client Users 
    $client_users = $users->filter(function ($user, $key) {                   
      //return ($user->roles->first()->name == 'client-user'); 
      return $user->roles->contains('name', 'client-user');
    });
    $total_client_users = $client_users->count();
    $active_client_users = $client_users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? $user->dvuser->status : false) : false; 
    })->count();
    $inactive_client_users = $client_users->filter(function ($user, $key) {                   
        return ($user->dvuser) ? (($user->dvuser->is_deleted == 0) ? !$user->dvuser->status : false) : true; 
    })->count();
  @endphp      
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Super Admin</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{ $total_admin_users }}</h4>
            </div>
            <small>Total Users</small><br/>
            <small class="text-success me-5">Active - {{ $active_admin_users }}</small>
            <small class="text-danger">InActive - {{ $inactive_admin_users }}</small>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            <i class="bx bx-cog bx-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Company Admin</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{ $total_company_admin_users }}</h4>
            </div>
            <small>Total Users</small><br/>
            <small class="text-success me-5">Active - {{ $active_company_admin_users }}</small>
            <small class="text-danger">InActive - {{ $inactive_company_admin_users }}</small>
          </div>
          <span class="badge bg-label-danger rounded p-2">
            <i class="bx bx-mobile-alt bx-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Team Users</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{ $total_team_users }}</h4>
            </div>
            <small>Total Users</small><br/>
            <small class="text-success me-5">Active - {{ $active_team_users }}</small>
            <small class="text-danger">InActive - {{ $inactive_team_users }}</small>
          </div>
          <span class="badge bg-label-success rounded p-2">
            <i class="bx bx-group bx-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Client Users</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{ $total_client_users }}</h4>
            </div>
            <small>Total Users</small><br/>
            <small class="text-success me-5">Active - {{ $active_client_users }}</small>
            <small class="text-danger">InActive - {{ $inactive_client_users }}</small>
          </div>
          <span class="badge bg-label-warning rounded p-2">
            <i class="bx bx-user bx-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Users List Table -->
<div class="card" style="display: none;" id="table-card">
  <div class="card-header border-bottom">
    <h5 class="card-title">Search Filter</h5>
    <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
      <!-- <div class="col-md-4 user_role"></div>
      <div class="col-md-4 user_plan"></div>
      <div class="col-md-4 user_status"></div> -->
      <div class="col-md-6"></div>
      <div class="col-md-2 user_role"></div>
      <div class="col-md-2 user_lang"></div>
      <div class="col-md-2 user_status"></div>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <!-- <tr>
          <th></th>
          <th>User</th>
          <th>Role</th>
          <th>Plan</th>
          <th>Billing</th>
          <th>Status</th>
          <th>Actions</th>
        </tr> -->
        <tr>            
          <th>id</th>         
          <th>User</th>  
          <th>Name</th>           
          <th>Email</th>
          <th>Company</th>             
          <th>Role</th>          
          <th>Telephone</th>
          <th>Language</th>
          <!-- <th>Assigned Company</th>
          <th>Assigned VAT Reg.</th>   -->        
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas to add new user -->
  @php
    $user_contact_tab = 0;
  @endphp
  @include('_partials/_offcanvas/offcanvas-user-form')
</div>

@include('_partials/_modals/modal-assign-vat-reg-lazy')
@include('_partials/_modals/modal-assign-team-user-lazy')
@include('_partials/_modals/modal-assign-client-lazy')
@include('_partials/_modals/modal-notification-settings')

@endsection
