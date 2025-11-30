<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasInvoiceColumnSetting" aria-labelledby="offcanvasInvoiceColumnSettingLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasInvoiceColumnSettingLabel" class="offcanvas-title">Column Settings</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body my-auto mx-0 flex-grow-0 py-0">
    <form method="post" class="form-invoice-column-settings">  
      @csrf
      {{--<input type="hidden" id="user-id" name="user_id" value="{{ $authUser->user_id }}">--}}
      <input type="hidden" id="client_id" name="client_id" value="{{ $vatreg->client_id }}">
      <input type="hidden" id="vat_reg_main_id" name="vat_reg_main_id" value="{{ $vatreg->vat_reg_main_id }}">
      <div class="row">
        <div class="col-md-12">
          
          <div class="form-check mt-3 mb-2">
            <input class="form-check-input" type="checkbox" value="" id="chk_invoice_column_check_all" name="chk_invoice_column_check_all" data-column="-1" />
            <label class="form-check-label" for="chk_invoice_column_check_all">
               Check All
            </label>
          </div>

          @foreach($invoice_column_names as $key=>$invoice_column_name)
            @php
              $filtered_invoice_column_setting = $invoice_column_settings->filter(function ($invoice_column_setting) use ($key) {
                  return ($invoice_column_setting->column_name == $key);
              })->first();             
            @endphp

            @if($key == 'taxcode')              
              <small class="text-light fw-medium">Invoice Info</small>
            @elseif($key == 'localcurrencycode')                
              <small class="text-light fw-medium">Exchange Info</small>
            @elseif($key == 'n')              
              <small class="text-light fw-medium">Optional Info</small>
            @elseif($key == 'cname')              
              <small class="text-light fw-medium">Client Info</small>
            @elseif($key == 'pdf')              
              <small class="text-light fw-medium">Download Info</small>
            @endif
            {{--<div class="form-check {{ ($invoice_column_name['index'] == 3 || $invoice_column_name['index'] == 11 || $invoice_column_name['index'] == 16 || $invoice_column_name['index'] == 20 || $invoice_column_name['index'] == 27) ? 'mt-3 mb-2' : 'my-2' }} {{ ($invoice_column_name['index'] == 10 || $invoice_column_name['index'] == 15 || $invoice_column_name['index'] == 19 || $invoice_column_name['index'] == 26) ? 'mt-2 mb-3' : '' }}">--}}
            <div class="form-check {{ ($invoice_column_name['index'] == 3 || $invoice_column_name['index'] == 12 || $invoice_column_name['index'] == 17 || $invoice_column_name['index'] == 21 || $invoice_column_name['index'] == 28) ? 'mt-3 mb-2' : 'my-2' }} {{ ($invoice_column_name['index'] == 11 || $invoice_column_name['index'] == 16 || $invoice_column_name['index'] == 20 || $invoice_column_name['index'] == 27) ? 'mt-2 mb-3' : '' }}">  
              <input class="form-check-input" type="checkbox" value="" id="chk_invoice_column_{{ $key }}" name="chk_invoice_column_{{ $key }}" {{ ($filtered_invoice_column_setting) ? (($filtered_invoice_column_setting->status) ? 'checked="checked"' : '') : 'checked="checked"' }} data-column="{{ $invoice_column_name['index'] }}" />
              <label class="form-check-label" for="chk_invoice_column_{{ $key }}">
                 {{ $invoice_column_name['name'] }}
              </label>
            </div>
          @endforeach
         
        </div><!--/ col -->
      </div>
    
      <button type="submit" class="btn btn-primary mb-2 w-100 btn-save-invoice-column-setting">Save</button>
      <button type="button" class="btn btn-label-secondary d-grid w-100" data-bs-dismiss="offcanvas">Cancel</button>
    </form>
  </div>
</div>