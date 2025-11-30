@extends('layouts/layoutMaster')

@section('title', ' Email Template - List')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/typography.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>

<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-email-template-editors.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Email Template/</span> List</h4>

@if(count($emailTemplates) == 0)
  <!-- No Email Template -->
  <div class="container-xxl container-p-y">
    <div class="misc-wrapper text-center">    
      <div class="mt-5">
        <img src="{{asset('assets/img/illustrations/girl-doing-yoga-light.png')}}" alt="page-misc-not-authorized-light" width="450" class="img-fluid" data-app-light-img="illustrations/girl-doing-yoga-light.png" data-app-dark-img="illustrations/girl-doing-yoga-dark.png">
      </div>
      <h1 class="mb-2 mx-2">Hooray!!</h1>
      <p class="mb-4 mx-2">You don´t have any email templates</p>        
    </div>
  </div>
  <!-- / No Email Template -->
@else

  <!-- Email Template -->                      
  <!-- Accordion Header Color -->
  <div class="col-md">          
    
    <table class="table border-0 m-0 tbl-header" id="tbl-header">
      <colgroup>                    
        <col width="100%"/>       
      </colgroup>
      <thead>
        <tr>              
          <th class="border-bottom-0 p-0">Template Name</th>                                
        </tr>
      </thead>              
    </table>       

    <div class="accordion mt-0 accordion-header-primary" id="accordionStyleEmailTemplate">
      @foreach ($emailTemplates as $key => $emailTemplate)
          <div class="accordion-item card sort-item">
            <h2 class="accordion-header">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionStyle-EmailTemplate-{{ $key }}" aria-expanded="false" id="btn-accordion-{{ $key }}" data-email_type="{{ $key }}">
                <table class="table border-0">
                  <colgroup>          
                    <!-- <col width="10%"/>  
                    <col width="90%"/> -->    
                    <col class="col-lg-1 col-md-2 col-sm-3 col-xs-4" />  
                    <col class="col-lg-11 col-md-10 col-sm-9 col-xs-8"/>             
                  </colgroup>
                  <tbody>
                    <tr>              
                      <td class="border-bottom-0 p-0">                                               
                        @if(isset($emailTemplate['template_lang']))
                          <img src="{{asset('assets/img/flags/'. $emailTemplate['template_lang'] .'.png')}}" class="country-flag me-2"><span class="btn-group-vertical">{{ $emailTemplate['template_lang'] }}</span>
                        @else
                          <img src="{{asset('assets/img/flags/EN.png')}}" class="country-flag me-2"><span class="btn-group-vertical">{{ 'EN' }}</span> 
                        @endif
                      </td>  
                      <td class="border-bottom-0 p-0">                                               
                        <span class="btn-group-vertical">{{ $emailTemplate['template_name'] }}</span>
                      </td>            
                    </tr>
                  </tbody>
                </table>                
              </button>
            </h2>

            <div id="accordionStyle-EmailTemplate-{{ $key }}" class="accordion-collapse collapse" data-bs-parent="#accordionStyleEmailTemplate">
              <div class="accordion-body">
                                
                  @include('_partials/_content/_emailtemplate/preview')
                
              </div>
            </div>
          </div>
      @endforeach
    </div>

  </div>
  <!--/ Accordion Header Color --> 
  <!--/ Email Template -->                       
@endif  
@endsection
