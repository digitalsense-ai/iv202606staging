<div class="row p-sm-3 p-0">
  <div class="col-6">
    <h6 class="fw-normal mb-1">{{ $client->client_name }}</h6>
    <h6 class="fw-normal mb-1">Cvr. no.{{ $client->vatno }}</h6>
    <h6 class="fw-normal mb-1">{{ \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
        \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</h6>
  </div>

  <div class="col-6">
    <div class="app-brand justify-content-end">
      <a href="{{url('/')}}" class="app-brand-link">
        @php
          $logo_width = 'w-50';
        @endphp                                          
        <span class="app-brand-text demo h3 mb-0 fw-bold text-end">@include('_partials.macros')</span>
      </a>
    </div> 
  </div>
</div>