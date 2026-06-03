@extends('layouts/layoutMaster')

@section('title', 'Company - Profile')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-on-scroll/animate-on-scroll.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-profile.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>

<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.js')}}"></script>

<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script src="{{asset('assets/vendor/libs/animate-on-scroll/animate-on-scroll.js')}}"></script>

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>

<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>

<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
@endsection

@section('page-script')
<script type="text/javascript">
    window.EchoConfig = {
        pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
        pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    };
</script>

<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    //For Vat Reg. Main Tab
    window.excise_duty_onoff = 0; 
    window.cas_dda_onoff = 0; 
    window.cas_dda_title = "Cash Account Statement / Duty Deferment Account"; 
    
    window.vat_reg_main_datas = [];   
    window.contact_datas = [];     
    //var vat_reg_main_start = 1;
    //var team_users = [];

    var result = { 
      'client': {!! json_encode($client) !!},
      'team_users': {!! json_encode($team_users) !!}
    };    
    vat_reg_main_datas = drawDtTable(result, 'vatregmain');    
    contact_datas = drawDtTable(result, 'contacts');

    window.anyexcel_template_datas = [];    
    
    var anyexcel_template_result = { 'anyexcel_templates': {!! json_encode($anyexcel_templates) !!} };    
    anyexcel_template_datas = drawDtTable(anyexcel_template_result, 'anyexcel_template'); 
});
</script>

<script src="{{asset('assets/js/intlTelInput.min.js')}}"></script>
<script src="{{asset('assets/js/extended-ui-timeline.js')}}"></script>

<script src="{{asset('js/dv-vat-registration-main-lazy.js')}}"></script>
<script src="{{asset('js/dv-submitting-fields-lazy.js')}}"></script>
<script src="{{asset('js/dv-upload.js')}}"></script>
<script src="{{asset('js/dv-vatreturn-notes.js')}}"></script>
<script src="{{asset('js/dv-importreconciliation-notes.js')}}"></script>
<!-- <script src="{{asset('js/dv-my-tasks.js')}}"></script> -->
<script src="{{asset('js/dv-all-tasks-lazy.js')}}"></script>

<script src="{{asset('js/dv-company-comment.js')}}"></script>
<!-- <script src="{{asset('js/dv-common.js')}}"></script> -->
<script src="{{asset('js/dv-comments.js')}}"></script>
<script src="{{asset('js/dv-history.js')}}"></script>
<script src="{{asset('js/dv-import-vat-lazy.js')}}"></script>
<script src="{{asset('js/dv-import-vat-files-lazy.js')}}"></script>

<script src="{{asset('js/dv-contacts-lazy.js')}}"></script>
<script src="{{asset('js/dv-users-lazy.js')}}"></script>

<script src="{{asset('js/dv-company-form-validation-lazy.js')}}"></script>

<!-- <script src="{{asset('js/dv-modal-select-vat-account-nos.js')}}"></script> -->

<script src="{{asset('js/dv-modal-assign-client-user.js')}}"></script>
<!-- <script src="{{asset('js/dv-vatreturn-files-lazy.js')}}"></script> -->

<!-- DON'T DELETE - UNTIL MULTI-FILE-MULTI-SHEET -->
<!-- <script src="{{asset('js/dv-excel-column-template.js')}}"></script> -->

<!-- <script src="{{asset('js/dv-excel-column-template-new.js')}}"></script> -->

<script src="{{asset('js/dv-anyexcel-template-others.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Company /</span> Profile
</h4>

@php
  $vat_reg_mains = $client->vatregmain;
  $overall_team_users = [];
  $overall_team_users_flag = [];
  foreach ($vat_reg_mains as $vat_reg_main) 
  {     
    foreach ($team_users as $team_user)
    {
      if($vat_reg_main->id == $team_user->id)
      {        
        foreach ($team_user->vatreg as $vatreg)
        {          
          foreach ($vatreg->uservatreg as $uservatreg)
          {            
            $value = $uservatreg->user->dvuser->firstname . ' ' . $uservatreg->user->dvuser->lastname;

            if(!in_array($value, $overall_team_users, true))            
              array_push($overall_team_users, $value);              
            
            if(isset($overall_team_users_flag[$value]))
            {
              if(!in_array($vatreg->country, $overall_team_users_flag[$value], true))              
                $overall_team_users_flag[$value][] = $vatreg->country;              
            }
            else           
              $overall_team_users_flag[$value][] = $vatreg->country;           
          }//for          
        }//for
      }//if
    }//for
  }//for

  /*-- No VAT reg.--*/
  if(count($overall_team_users_flag) == 0)
  {
    foreach ($vat_reg_mains as $vat_reg_main) 
    {
      foreach ($vat_reg_main->uservatregmain as $uservatregmain) 
      {
        $value = $uservatregmain->user->dvuser->firstname . ' ' . $uservatregmain->user->dvuser->lastname;

        if(!in_array($value, $overall_team_users, true))            
          array_push($overall_team_users, $value);  

        if(isset($overall_team_users_flag[$value]))
        {
          if(!in_array($vat_reg_main->country, $overall_team_users_flag[$value], true))              
            $overall_team_users_flag[$value][] = $vat_reg_main->country;              
        }
        else           
          $overall_team_users_flag[$value][] = $vat_reg_main->country;     
      } //for
    } //for
  } //if
  /*--end No VAT reg.--*/

  $team_contacts = '';
  foreach ($overall_team_users as $overall_team_user)
  {
    $team_user_flag = '';
    foreach ($overall_team_users_flag[$overall_team_user] as $overall_team_user_flag)
    {
      $team_user_flag .= '<img src="' . asset('assets/img/flags/'. $overall_team_user_flag .'.png') . '" data-flag="'.$overall_team_user_flag.'" class="country-flag me-2">';
    }

    //if($team_contacts == '')
    //  $team_contacts = $overall_team_user;
    //else
      //$team_contacts .=  ', ' . $overall_team_user;  
      $team_contacts .=  $team_user_flag . '<span class="btn-group-vertical me-2">' . $overall_team_user . '</span>';  
  }
  if($team_contacts == '')
    $team_contacts = '-';

  $client_id = $client->id;
  $vat_reg_main = $client->vatregmain->first();
  $api_connection = ($vat_reg_main) ? $vat_reg_main->clientapi : null;
  $client_comments = $client->clientcomment;
  $assiged_client_users = $client->userclient;
  //$client_users = $clientusers->dvuser;
  
  $clientfiles = $client->clientfiles;
  $cover_pic = $clientfiles->filter(function ($clientfile, $key) {                   
    return ($clientfile->file_for == 'cover' && ((strpos($clientfile->file_name, ".png") !== false) || (strpos($clientfile->file_name, ".jpg") !== false)) );
  })->first();

  $profile_pic = $clientfiles->filter(function ($clientfile, $key) {                   
    return ($clientfile->file_for == 'profile' && ((strpos($clientfile->file_name, ".png") !== false) || (strpos($clientfile->file_name, ".jpg") !== false)) );
  })->first();

  $clientqas = $client->clientqa;

  $clientextrafields = $client->clientextrafield;

  $clientlegalreps = $client->clientlegalrep;
@endphp 

@php
  $full_result = $result;
@endphp

<!-- Header -->
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      @php
        $client_logo = ($profile_pic) ? $profile_pic->downloadurl : 'assets/img/avatars/1.png';
        $client_cover = ($cover_pic) ? $cover_pic->downloadurl : 'assets/img/pages/profile-banner.png';     
        $style = '';     
        
        if($api_connection)
        {
          if($api_connection->api_name == "Dynamics 365")
            $client_logo = 'assets/img/logo/logo-dynamics-crm-365.png';
          else if($api_connection->api_name == "E-conomic")
            $client_logo = 'assets/img/logo/logo-e-conomic.jpg';  
          else if($api_connection->api_name == "Uniconta")
            $client_logo = 'assets/img/logo/logo-uniconta.png'; 
          else if($api_connection->api_name == "Shopify")
            $client_logo = 'assets/img/logo/logo-shopify.webp';     
          else if($api_connection->api_name == "Billy")
            $client_logo = 'assets/img/logo/billy-dark-logo.svg';
        }   
       
        /*
        if($client)
        {
          if(strpos($client->client_name, "Nordic") !== false)
          {
            $client_logo = 'assets/img/logo/logo-nordic.webp';   
            $client_cover = 'assets/img/pages/profile-banner-nordic.jpg';    
            //$style = "width: auto !important; height: 120px !important;";     
          }
        }
        */      
      @endphp
      <div class="user-profile-header-banner">
        <img src="{{asset($client_cover)}}" alt="Banner image" class="rounded-top">
      </div>
      <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">  
          <div class="user-profile-img-div ms-0 rounded-3 ms-sm-4">         
          <img src="{{asset($client_logo)}}" alt="user image" class="d-block user-profile-img"> 
          </div>         
        </div>
        <div class="flex-grow-1 mt-3 mt-sm-5">
          <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
            <div class="user-profile-info">
              <input type="hidden" name="client_id" id="client_id" value="{{ $client_id }}">
              <input type="hidden" name="client_name" id="client_name" value="{{ ($client) ? $client->client_name : ''}}">
              <h4>{{ ($client) ? $client->client_name : ''}}</h4>
              <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                <li class="list-inline-item fw-semibold">
                  <i class='bx bx-pen'></i> {{ ($client) ? ucfirst($client->lrep_position) : ''}}
                </li>
                <li class="list-inline-item fw-semibold">
                  <i class='bx bx-map'></i> {{ ($client) ? ucfirst($client->off_city) : ''}}
                </li>
                <li class="list-inline-item fw-semibold">
                  <i class='bx bx-calendar-alt'></i> Joined {{ ($client) ? \Carbon\Carbon::parse($client->created_at)->format('F Y') : ''}}
                </li>
                <li class="list-inline-item"><i class='bx bxl-microsoft-teams'></i><span class="fw-semibold mx-2">Team contact:</span> <span>{!! html_entity_decode($team_contacts) !!}</span>
                </li>                  
              </ul>
            </div>

            <div class="d-flex">
              <span class="p-2 mx-2 bg-label-success ">{{ (($client) ? (($client->status) ? 'Active' : 'Inactive') : '') }}</span>

              @php
                $has_gateway = false;
              @endphp
              @foreach($client->vatregmain as $vatregmain)
                @if($vatregmain->country == 'GB' && ($vatregmain->uk_gateway_userid || $vatregmain->cds_gateway_userid))
                  @php
                    $has_gateway = true;
                  @endphp
                @endif
              @endforeach
             
              @if($has_gateway)
                <a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasGatewayInfo"><img src="{{asset('assets/img/icons/favicon-hmrc.ico')}}" alt="Gateway Info" class="rounded w-75"></a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Header -->

<!-- Navbar pills -->
<div class="row">
  <div class="col-md-12">
    <ul class="nav nav-pills flex-column flex-sm-row mb-4">      
      <li class="nav-item">
        <button type="button" id="btn-vatreturns" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-vatreturns" aria-controls="navs-pills-top-vatreturns" aria-selected="false"><i class='bx bx-user me-1'></i> VAT Returns</button>
      </li>
      <li class="nav-item">
        <button type="button" id="btn-import-reconciliation" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-import-reconciliation" aria-controls="navs-pills-top-import-reconciliation" aria-selected="false"><i class='bx bx-import me-1'></i> Import Reconciliation</button>
      </li>
      <li class="nav-item">
        <button type="button" id="btn-vatregistrations" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-vatregistrations" aria-controls="navs-pills-top-vatregistrations" aria-selected="true"><i class='bx bx-group me-1'></i> VAT Registrations</button>
      </li>
      <li class="nav-item">
        <button type="button" id="btn-client" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-client" aria-controls="navs-pills-top-client" aria-selected="false"><i class='bx bx-grid-alt me-1'></i> Company</button>
      </li>
      <li class="nav-item">
        <button type="button" id="btn-client-comment" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-client-comment" aria-controls="navs-pills-top-client-comment" aria-selected="false"><i class='bx bx-chat me-1'></i> Comments</button>
      </li>
      <li class="nav-item">
        <button type="button" id="btn-api-connection" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-api-connection" aria-controls="navs-pills-top-api-connection" aria-selected="false"><i class='bx bx-signal-5 me-1'></i> {{ (($api_connection) ? (($api_connection->api_name == 'FTP') ? 'FTP Connection' : 'API Connection') : 'Upload Data\'s') }}</button>
      </li>
      <li class="nav-item">
        <button type="button" id="btn-contacts" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-contacts" aria-controls="navs-pills-top-contacts" aria-selected="false"><i class='bx bxs-contact me-1'></i> Contacts</button>
      </li>
      <li class="nav-item">
        <button type="button" id="btn-qa" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-qa" aria-controls="navs-pills-top-qa" aria-selected="false"><i class='bx bx-objects-horizontal-left me-1'></i> Q & A</button>
      </li>
      <li class="nav-item">
        <button type="button" id="btn-client-history" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-client-history" aria-controls="navs-pills-top-client-history" aria-selected="false"><i class='bx bx-history me-1'></i> History</button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content px-0">
      <!-- VAT Returns -->               
      <div class="tab-pane fade show active" id="navs-pills-top-vatreturns" role="tabpanel">
        <div class="accordion mt-0 accordion-header-primary" id="accordionStyleAllTasks">          
          @php
            $check_product_type = 1;
            $accordion_name = 'All';
            
            $filtered_result = $full_result->filter(function ($vatreg, $key) {                   
              return ($vatreg->vatregmain->product_type != 4);
            });
            $result = $filtered_result;
          @endphp
          @include('_partials/_content/_vatreturn/vatreturns-all-tasks-lazy')    

          @php
            $check_product_type = 4;
            
            $filtered_result = $full_result->filter(function ($vatreg, $key) {                   
              return ($vatreg->vatregmain->product_type == 4);
            });
            $result = $filtered_result;
          @endphp
          @include('_partials/_content/_vatreturn/vatreturns-all-tasks-lazy') 

          @if(count($full_result) == 0)  
            @include('_partials/_content/_tasks/no-tasks-lazy')
          @endif         
        </div>
      </div>      
      <!--/ VAT Returns --> 

      <!-- Import Reconciliation -->               
      <div class="tab-pane fade" id="navs-pills-top-import-reconciliation" role="tabpanel">
        <div class="accordion mt-0 accordion-header-primary" id="accordionStyleImportReconciliationTasks">          
          @php
            $check_product_type = 2;
            $accordion_name = 'ImportReconciliation';

            $result = $full_result;
          @endphp
          @include('_partials/_content/_vatreturn/vatreturns-all-tasks-lazy')         
        </div>
      </div>      
      <!--/ Import Reconciliation -->     

      <!-- VAT Registrations Main -->      
      <div class="tab-pane fade" id="navs-pills-top-vatregistrations" role="tabpanel"> 
        <!-- Bounce -->
        <!-- <div class="sk-bounce sk-primary sk-center">
          <div class="sk-bounce-dot"></div>
          <div class="sk-bounce-dot"></div>
        </div> -->      
        <div class="card mb-4" style="display: none;" id="vat-reg-lists">                  
          <div class="mb-3">            
            <table class="table datatable-vat-registration-main border-top">  
              <thead>
                <tr>              
                  <th></th>
                  <!-- <th>Client ID</th>
                  <th>Client Name</th> -->
                  <th>Country</th>     
                  <th>Team User</th>           
                  <th>Service Start</th>
                  <!-- <th>Turnover Date</th> -->
                  <th>Periods</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>OSS</th>
                  <th>Excise Duty</th>
                  <th>Cash Account Statement / Duty Deferment Account</th>
                  <th>Actions</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

        <!-- VAT Reg. Info -->
        <div id="vat-reg-info"></div>        
        <!--/ VAT Reg. Info --> 
      </div>
      <!--/ VAT Registrations Main -->  

      <!-- Client/Company -->      
      <div class="tab-pane fade" id="navs-pills-top-client" role="tabpanel">
        @include('_partials/_content/_company/edit')
      </div>
      <!--/ Client/Company --> 

      <!-- Client Comments -->               
      <div class="tab-pane fade" id="navs-pills-top-client-comment" role="tabpanel">
        <div class="card mb-4">
          <div class="card-body"> 
            <button type="button" id="btn-open-client-comment-{{ $client_id }}" class="btn btn-dark float-end mx-2 btn-open-comment my-n1" data-client_id="{{ $client_id }}" data-bs-toggle="modal" data-bs-target="#onboardingSlideClientCommentModal-{{ $client_id }}">Add Comment</button>

            @include('_partials/_modals/modal-client-comment')

            <div id="load-client-comment">
              @include('_partials/_content/_company/comment')
            </div>
          </div>
        </div>
      </div>      
      <!--/ Client Comments -->  

      <!-- API Connection/No API Connection -->          
      <div class="tab-pane fade" id="navs-pills-top-api-connection" role="tabpanel">
        @include('_partials/_content/_company/apiconnection')
      </div>      
      <!--/ API Connection/No API Connection -->

      <!-- Contacts -->      
      <div class="tab-pane fade" id="navs-pills-top-contacts" role="tabpanel">             
        <div class="card mb-4">      
          @include('_partials/_content/_company/contacts') 
          @include('_partials/_modals/modal-assign-client-user-lazy')                        
        </div>
      </div>
      <!--/ Contacts -->

      <!-- Q&A -->      
      <div class="tab-pane fade" id="navs-pills-top-qa" role="tabpanel">             
        @include('_partials/_content/_company/q-and-a')
      </div>
      <!--/ Q&A -->

      <!-- Client History -->               
      <div class="tab-pane fade" id="navs-pills-top-client-history" role="tabpanel">
        <div class="card mb-4">
          <div class="card-body">             
            <div id="load-client-history">
              @include('_partials/_content/_company/history')
            </div>
          </div>
        </div>
      </div>      
      <!--/ Client History -->  

    </div>
    <!--/ Tab Content -->
  </div>
</div>
<!--/ Navbar pills -->
@include('_partials/_modals/modal-select-vat-account-nos')

@include('_partials/_offcanvas/offcanvas-document-view')
@include('_partials/_offcanvas/offcanvas-gateway-info')

@endsection