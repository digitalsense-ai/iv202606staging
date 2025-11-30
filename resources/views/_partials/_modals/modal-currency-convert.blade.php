<!-- Large Modal -->
<div class="modal fade modal-file" id="currencyConvertModal-{{ $vat_reg_id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        {{--<h5 class="modal-title" id="modalLabel">Convert currency</h5>--}}
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">            

        <form id="formCurrencyConvert-{{ $vat_reg_id }}" class="needs-validation m-0 formCurrencyConvert" novalidate data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" >
          @csrf
          <input type="hidden" name="from_currencies" id="from_currencies" value="{{ $from_currencies }}">
          <input type="hidden" name="to_currency" id="to_currency" value="{{ $currency_code }}">
          @include('_partials/_modals/_currency-convert-list')
          
          <div class="row mt-3">
            <div class="col-sm-12 text-end">          
              <button type="button" class="btn btn-danger disabled btn-convert" data-vat_reg_id="{{ $vat_reg_id }}">Convert</button>
            </div>                                                      
          </div> 
        </form>  
        
      </div>      
    </div>
  </div>
</div>