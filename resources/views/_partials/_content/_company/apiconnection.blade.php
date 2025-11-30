<div class="row">
    <input type="hidden" name="api_connection" id="api_connection" value="{{ ($api_connection) ? 1 : 0 }}">
    @if($api_connection)
      <!-- API Connection -->                      
      <div class="col-xl-6 col-lg-5 col-md-5">
        <div class="card mb-4">                             
          <div class="card-header header-elements">
            <span class=" me-2">{{ (($api_connection) ? $api_connection->api_name : '-') }}</span>   

            {{-- //DON"T DELETE
            @if(!in_array($api_connection->api_name, ['Shopify', 'FTP']))                  
            <div class="card-header-elements ms-auto">
              <button type="button" class="btn btn-xs btn-primary select-vat-account-nos" data-id="{{ (($api_connection) ? $api_connection->client_id : '') }}" data-bs-toggle="modal" data-bs-target="#selectVATAccountNos"><i class="menu-icon tf-icons bx bx-cog"></i> Account No.'s</button>                
            </div>
            @endif--}}                            
          </div>
          <div class="card-body">            
            <!-- <small class="text-muted text-uppercase">{{ (($api_connection) ? $api_connection->api_name : '-') }}</small> -->
            <ul class="list-unstyled mb-4 mt-3">
              <li class="d-flex align-items-center mb-3"><i class='bx {{ (($api_connection) ? (($api_connection->status) ? "bxs-battery" : "bx-battery") : "") }}'></i><span class="fw-semibold mx-2">Status:</span> <span class="bg-label-success text-end p-2 m-0">{{ (($api_connection) ? (($api_connection->status) ? 'Active' : 'Inactive') : '') }}</span></li>                 
              <li class="d-flex align-items-center mb-3"><i class='bx bx-signal-5'></i><span class="fw-semibold mx-2">Connection Name:</span> <span>{{ (($api_connection) ? $api_connection->api_name : '-') }}</span></li> 
              {{-- //DON"T DELETE
              <li class="d-flex align-items-center mb-3"><i class='bx bx-cloud-lightning'></i><span class="fw-semibold mx-2">Environment:</span> <span>{{ (($api_connection) ? $api_connection->api_env : '-') }}</span></li>  
              <li class="d-flex align-items-center mb-3"><i class='bx bx-purchase-tag-alt'></i><span class="fw-semibold mx-2">G/L Sales Account No.:</span> <span>{{ (($api_vat_acc_no) ? $api_vat_acc_no->sales_vat_ac_no : '-') }}</span></li>  
              <li class="d-flex align-items-center mb-3"><i class='bx bxs-purchase-tag-alt'></i><span class="fw-semibold mx-2">G/L Purchase Account No.:</span> <span>{{ (($api_vat_acc_no) ? $api_vat_acc_no->purchase_vat_ac_no : '-') }}</span></li>
              --}}
            </ul>            
          </div>
        </div>
      </div>
      <!--/ API Connection -->      
    @else
      <!-- No API Connection -->      
      <!-- Accordion Header Color -->
      <div class="col-md">
        <div class="accordion mt-0 accordion-header-primary" id="no_connection">
          <!-- <div id="load-no-api-connection-list"></div> -->      
          @include('_partials/_content/_vatreturn/no-api-connection-list-lazy') 
        </div>
      </div>          
      <!--/ No API Connection -->      
    @endif

    {{-- //DON"T DELETE
    @if($authUser->role == "team-user" || $authUser->role == "client-user")
    <!-- Grant Authority - HMRC -->                      
    <div class="col-xl-6 col-lg-5 col-md-5">
      <div class="card mb-4">                             
        <div class="card-header header-elements">
          Grant authority to interact with HMRC                        
        </div>
        <div class="card-body">            
          <button type="button" class="btn btn-primary grant-authority" id="grant_authority">Grant authority</button>
        </div>
      </div>
    </div>
    <!--/ Grant Authority - HMRC --> 
    @endif
    --}}          

  </div>