@extends('layouts/layoutMaster')

@section('title', 'CRM - Quotes')

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('page-script')
<script src="{{asset('js/dv-crm.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ url('crm/quotes') }}">{{ __('Quotes') }}</a>/</span> {{ __('Create') }}  
</h4>

<h4><a href="{{ route('leads.index') }}" class="fw-light">{{ isset($lead) ? $lead->company_name : $quote->lead->company_name }}</a></h4>

<form id="formCrmQuotes" method="post" action="{{ route('quotes.store') }}" class="card-body needs-validation form-crm-quotes">
  @csrf

  <input type="hidden" name="lead_id" value="{{ isset($lead) ? $lead->id : $quote->lead_id }}">
  <input type="hidden" name="quote_id" value="{{ isset($quote) ? $quote->id : '' }}">

  <div class="row">
    <div class="col-md-3 d-flex">
      <div class="card mb-4" style="flex: 1;">
        <h5 class="card-header">Package Pricing</h5>
        <div class="card-body">          
          
          <div class="input-group mb-3">  
            <div class="form-floating">        
              <select name="package" id="package" class="form-control" required>
                <option value="">Select</option>
                @foreach($packages as $key => $price)                  
                  <option value="{{ $key }}" data-price="{{ $price }}" {{ isset($quote) && $quote->package == $key ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_',' ',$key)) }}
                  </option>
                @endforeach
              </select>
              <label for="package">Package</label> 
            </div>
          </div>

          <div class="input-group mb-3">  
            <div class="form-floating">        
              <input type="number" name="base_price" id="base_price" class="form-control" placeholder="0,00" step="0.5" min="0" value="{{ isset($quote) ? $quote->base_price : '' }}" required>
              <label for="package">Base Price</label> 
            </div>
          </div>

          <div class="input-group mb-3">  
            <div class="form-floating">        
              <input type="number" name="registration_price" id="registration_price" class="form-control" placeholder="0,00" step="0.5" min="0" value="{{ isset($quote) ? $quote->registration_price : '' }}" required>
              <label for="package">Registration & Setup Price</label> 
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card mb-4">
        <h5 class="card-header">Optional Add-ons</h5>
        <div class="card-body">          
                  
          @foreach($addons as $addon)
            @php
                $quoteAddon = isset($quote)
                    ? $quote->addons->firstWhere('addon_name', $addon->name)
                    : null;
            @endphp

            <div class="row mb-2">
              <div class="col-md-8 py-2">
                <input type="checkbox" name="addons[{{$addon->name}}][enabled]" {{ $quoteAddon ? 'checked' : '' }}>
                {{$addon->name}}
                <span class="text-muted">{{$addon->frequency}}</span>
              </div>

              <div class="col-md-4">
                <!-- <div class="input-group mb-3">  
                  <div class="form-floating"> -->              
                    <input type="number" name="addons[{{$addon->name}}][price]" id="addons[{{$addon->name}}][price]" class="form-control addon-price" placeholder="0,00" step="0.5" min="0"value="{{ $quoteAddon ? $quoteAddon->price : $addon->price }}">
                    <!-- <label for="addons[{{$addon->name}}][price]">Price</label>
                  </div>
                </div> -->
              </div>
            </div>
          @endforeach

        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card mb-4">
        <h5 class="card-header">Summary</h5>
        <div class="card-body">          
                  
          <p>Registration: <span id="registration_total">0</span></p>
          <p>Monthly: <span id="monthly_total">0</span></p>          
          <hr>
          <p><strong>Yearly: <span id="yearly_total">0</span></strong></p>

          <button id="save-quote" class="btn btn-primary w-100">Save Quote</button>

        </div>
      </div>
    </div>

  </div>
</form>

@endsection