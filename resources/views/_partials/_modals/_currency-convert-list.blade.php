<div class="accordion m-0 accordion-header-primary accordion-style-email-form" id="accordionStyleCurrencyConvert-{{ $vat_reg_id }}">
  @php
    $arr_from_currencies = explode(',', $from_currencies);
    //dd($arr_from_currencies);
  @endphp

  @if(count($arr_from_currencies) == 0)

  @else
    @foreach ($arr_from_currencies as $from_currency)
      {{--@if($currency_code !=  $from_currency->currency_code) --}}

        <div class="accordion-item {{ (request()->has('currency')) ? ((request()->get('currency') == $from_currency) ? '' : 'd-none') : '' }}" id="{{ $from_currency }}">
          <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionStyleCurrencyConvert-{{ $vat_reg_id }}-{{ $from_currency }}" aria-expanded="false">
              <h3 class="mb-0">Convert From {{ $from_currency }} To {{ $currency_code }} @if($currency_code != 'CHF') <span class="alert-primary text-end fs-tiny p-1 mx-2 selectedInvoiceCount"></span>@endif</h3>        
            </button>
          </h2>

          <div id="accordionStyleCurrencyConvert-{{ $vat_reg_id }}-{{ $from_currency }}" class="accordion-collapse collapse">
            <div class="accordion-body">
              @include('_partials/_modals/_currency-convert-dates')
            </div>
          </div>
        </div>

        <div class="divider {{ (request()->has('currency')) ? ((request()->get('currency') == $from_currency) ? '' : 'd-none') : '' }}">
          <div class="divider-text p-0"></div>
        </div>
     {{-- @endif--}}
    @endforeach
  @endif  
</div>