@if($product_type == $check_product_type || ($product_type == 3 || $product_type == 5) )

  @php
    $show_accordion = false;

    $reload = '';

    $statusorder = '';
    $statustext = '';

    $disabled = '';
    if($check_product_type == 1 || $check_product_type == 4)
    {
      $show_accordion = true;

      $statusorder = $vatreg->statusorder; 
      $statustext = $vatreg->statustext; 
      if(count($vatreturns) == 0 && $vatreg->status <= 2)
        $reload = 'data-reload=true';

      $disabled = ($vatreg->is_disregard) ? 'disabled' : '';  
    }
    else if($check_product_type == 2)
    {
      $statusorder = $vatreg->statusorder_importre; 
      $statustext = $vatreg->statustext_importre; 
      
      if(isset($import_vat_files))
      {
        $show_accordion = true;

        //if((count($import_vat_files) == 0 || count($importreconciliations) == 0) && $vatreg->status_import_re <= 2)
        if((count($import_vat_files) == 0 || count($importreconciliationsalesinvoices) == 0) && $vatreg->status_import_re <= 2)
          $reload = 'data-reload=true';
      }   

      $disabled = ($vatreg->is_disregard_import_re) ? 'disabled' : '';   
    }

    if($authUser->role == 'team-user') 
    { 
      if($show_accordion)          
      {
        $show_vatreg = false;

        if(isset($otherClient))
        {
          if($otherClient)
            $show_vatreg = true; 
          else
          {
            $uservatregs = $vatreg->uservatreg;

            $filtered_uservatregs_result = $uservatregs->filter(function ($uservatreg, $key) use($authUser) {         
              return ($uservatreg->user_id == $authUser->user_id); 
            }); 
            
            if(count($filtered_uservatregs_result) > 0)
              $show_vatreg = true;     
          }              
        }
        else
        {
          $uservatregs = $vatreg->uservatreg;

          $filtered_uservatregs_result = $uservatregs->filter(function ($uservatreg, $key) use($authUser) {         
            return ($uservatreg->user_id == $authUser->user_id); 
          }); 
          
          if(count($filtered_uservatregs_result) > 0)
            $show_vatreg = true;     
        }   
      }
    }
  @endphp

  @if($show_accordion)
    <div class="accordion-item card sort-item" data-country="{{ $vatreg->country }}" data-vat_reg_main_id="{{ $vatreg->vat_reg_main_id }}" data-index="{{ (!$vatregmain_status) ? -1 : $statusorder }}" data-range="{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('Y-m') . '***' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('Y-m')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('Y-m')) }}" {{ isset($show_vatreg) ? (($show_vatreg) ? 'data-all=true' : 'data-all=false') : '' }} style="{{ isset($show_vatreg) ? (($show_vatreg) ? '' : 'display: none;') : '' }}" {{ $reload }} data-product_type="{{ $check_product_type }}" >
        <h2 class="accordion-header table-responsive text-nowrap">
          <button type="button" class="accordion-button collapsed {{ $disabled }} {{ ($vatregmain_status) ? '' : 'inactive' }}" data-bs-toggle="collapse" data-bs-target="#accordionStyle{{ $accordion_name }}Tasks-{{ $vat_reg_id }}" aria-expanded="false" id="btn-accordion-{{ $vat_reg_id }}">
            <table class="table border-0">
              <colgroup>          
                <col width="10%"/>
              </colgroup>
              <colgroup>
                <col width="40%"/>
              </colgroup>
              <colgroup>
                <col width="10%"/>
              </colgroup>
              <colgroup>
                <col width="10%"/>
              </colgroup>
              <colgroup>
                <col width="15%"/>
              </colgroup>
              <colgroup>
                <col width="15%"/>        
              </colgroup>
              <tbody>
                <tr>              
                  <td class="border-bottom-0 p-0">                
                    <img src="{{asset('assets/img/flags/'. $vatreg->country .'.png')}}" class="country-flag me-2"><span class="btn-group-vertical">{{ $vatreg->country }}</span>
                  </td>
                  <td class="border-bottom-0 p-0">
                    {{ $client->client_name }}<br>
                    {{--<span class="badge rounded-pill bg-label-{{ ($vatreg->vatregmain->vat_reg_type == 'Basic') ? 'primary' : 'danger' }}">{{ $vatreg->vatregmain->vat_reg_type }}</span>--}}        
                    <span class="badge rounded-pill bg-label-{{ ($check_product_type == 3 || $check_product_type == 5) ? 'danger' : 'primary' }}">{{ $product_type_name }}</span>
                  </td>
                  <td class="border-bottom-0 p-0">{{ $vatreg->general_periods }}</td>              
                  <td class="border-bottom-0 p-0">{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y')) }}</td>
                  <td class="border-bottom-0 p-0 pe-3 text-end" id="total-tax-top-{{ $vat_reg_id }}">{{ ($check_product_type == 1 || $check_product_type == 4) ? ($currencyFormatter->format($totalvat) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode)) : '' }}</td>
                  {{--
                  <td class="border-bottom-0 p-0 pe-3 text-end" id="total-tax-top-{{ $vat_reg_id }}">{{ $currencyFormatter->format($totalvat) . ' ' . $currencycode }}</td>
                  --}}
                  <td class="border-bottom-0 p-0 status">
                    @if($check_product_type == 1 || $check_product_type == 4)
                      <span class="badge {{ ($vatreg->status == 0) ? 'bg-label-dark' : '' }}{{ ($vatreg->status == 1) ? ' bg-label-secondary' : '' }}{{ ($vatreg->status == 2) ? ' bg-label-primary' : '' }}{{ ($vatreg->status == 3) ? ' bg-label-warning' : '' }}{{ ($vatreg->status == 4) ? ' bg-label-success' : '' }}{{ ($vatreg->status == 5) ? ' bg-label-info' : '' }}{{ ($vatreg->status == 6) ? ' bg-label-danger' : '' }}">{{ $statustext }}</span>
                    @elseif($check_product_type == 2)
                      <span class="badge {{ ($vatreg->status_import_re == 0) ? 'bg-label-dark' : '' }}{{ ($vatreg->status_import_re == 1) ? ' bg-label-secondary' : '' }}{{ ($vatreg->status_import_re == 2) ? ' bg-label-primary' : '' }}{{ ($vatreg->status_import_re == 3) ? ' bg-label-danger' : '' }}">{{ $statustext }}</span>
                    @endif
                  </td>                          
                </tr>
              </tbody>
            </table>                
          </button>
        </h2>

        <div id="accordionStyle{{ $accordion_name }}Tasks-{{ $vat_reg_id }}" class="accordion-collapse collapse {{ $disabled }}" data-bs-parent="#accordionStyle{{ $accordion_name }}Tasks">
          <div class="accordion-body">
            
            <div class="bs-stepper wizard-modern wizard-modern-example">
                <div class="bs-stepper-header">
                  <div class="step {{ ($check_product_type == 1 || $check_product_type == 4) ? (($vatreg->status == 1) ? 'active' : '') : '' }} {{ ($check_product_type == 2) ? (($vatreg->status_import_re == 1) ? 'active' : '') : '' }} {{ ($check_product_type == 1 || $check_product_type == 4) ? (($vatreg->status > 1) ? 'crossed' : '') : '' }} {{ ($check_product_type == 2) ? (($vatreg->status_import_re > 1) ? 'crossed' : '') : '' }}" id="step-draft-created">                        
                    <span class="step-trigger"> 
                      <span class="bs-stepper-circle ms-0">1</span>
                      <span class="bs-stepper-label">Created</span>
                    </span>
                  </div>
                  <div class="line"></div>
                  <div class="step {{ ($check_product_type == 1 || $check_product_type == 4) ? (($vatreg->status == 2) ? 'active' : '') : '' }} {{ ($check_product_type == 2) ? (($vatreg->status_import_re == 2) ? 'active' : '') : '' }} {{ ($check_product_type == 1 || $check_product_type == 4) ? (($vatreg->status > 2 ) ? 'crossed' : '') : '' }} {{ ($check_product_type == 2) ? (($vatreg->status_import_re > 2 ) ? 'crossed' : '') : '' }}" id="step-draft">
                    <span class="step-trigger">
                      <span class="bs-stepper-circle">2</span>
                      <span class="bs-stepper-label">Draft</span>
                    </span>
                  </div>
                  <div class="line"></div>
                  @if($check_product_type == 1 || $check_product_type == 4)
                  <div class="step {{ ($vatreg->status == 3) ? 'active' : '' }}  {{ ($vatreg->status > 3) ? 'crossed' : '' }}" id="step-pending-review">
                    <span class="step-trigger">
                      <span class="bs-stepper-circle">3</span>
                      <span class="bs-stepper-label">Pending review</span>
                    </span>
                  </div>
                  <div class="line"></div>
                  <div class="step {{ ($vatreg->status == 4) ? 'active' : '' }}  {{ ($vatreg->status > 4) ? 'crossed' : '' }}" id="step-ready-to-submit">
                    <span class="step-trigger">
                      <span class="bs-stepper-circle">4</span>
                      <span class="bs-stepper-label">Ready to submit</span>
                    </span>
                  </div>
                  <div class="line"></div>
                  <div class="step {{ ($vatreg->status == 5) ? 'active' : '' }}  {{ ($vatreg->status > 5) ? 'crossed' : '' }}" id="step-submitted">
                    <span class="step-trigger">
                      <span class="bs-stepper-circle">5</span>
                      <span class="bs-stepper-label">Submitted</span>
                    </span>
                  </div>
                  @elseif($check_product_type == 2)
                  <div class="step {{ ($vatreg->status_import_re == 3) ? 'active' : '' }}  {{ ($vatreg->status_import_re > 3) ? 'crossed' : '' }}" id="step-completed">
                    <span class="step-trigger">
                      <span class="bs-stepper-circle">3</span>
                      <span class="bs-stepper-label">Completed</span>
                    </span>
                  </div>
                  @endif
                </div>
              </div>
              @include('_partials/_content/_vatreturn/vatreturns-tabs-lazy')
            
          </div>
        </div>
    </div> 
  @endif   
@endif