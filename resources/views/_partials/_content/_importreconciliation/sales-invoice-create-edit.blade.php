<input type="hidden" name="vat_reg_id" id="vat_reg_id" value="{{ old('data.vat_reg_id', (isset($xmlData) ? $xmlData['vat_reg_id'] : $vat_reg_id)) }}" />
<input type="hidden" name="tab_name" id="tab_name" value="{{ old('data.tab_name', (isset($xmlData) ? $xmlData['tab_name'] : $tab_name)) }}" />

<input type="hidden" name="sales_invoice_data_id" id="sales_invoice_data_id" value="{{ isset($xmlData) ? (isset($xmlData['sales_invoice_data_id']) ? old('data.sales_invoice_data_id', $xmlData['sales_invoice_data_id']) : '') : '' }}" />
<input type="hidden" name="ftp_file_id" id="ftp_file_id" value="{{ old('data.ftp_file_id', (isset($xmlData) ? $xmlData['ftp_file_id'] : '')) }}" />
<input type="hidden" name="sales_invoice_id" id="sales_invoice_id" value="{{ old('data.sales_invoice_id', (isset($xmlData) ? $xmlData['sales_invoice_id'] : $sales_invoice_id)) }}" />

<input type="hidden" class="form-control" id="month_year" name="month_year" value="{{ old('data.month_year', (isset($month_year) ? $month_year : '')) }}">

{{--
@if(!isset($xmlData))
    <div class="divider divider-dotted divider-dark text-start">
      <div class="divider-text fs-big fw-semibold">
        <i class='bx bx-upload'></i> Upload PDF
      </div>
    </div>

    <div class="row">
        <div class="col-md-6">                        
            <div class="input-group">
                <input type="hidden" class="form-control" id="file_type" name="file_type" value="import_reconciliation">
                <input type="hidden" class="form-control" id="file_type_title" name="file_type_title" value="Import Reconciliation">
                <input type="hidden" class="form-control" id="month_year" name="month_year" value="">
                <input type="file" class="form-control" id="file" name="file" aria-describedby="btnSalesInvoiceFile" aria-label="Upload">
                <button class="btn btn-outline-primary" type="button" id="btnSalesInvoiceFile">Upload</button>
            </div>
        </div>
    </div> <!--/ row -->
@endif
--}}

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-purchase-tag-alt'></i> Invoice
  </div>
</div>

<div class="row">    
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoice_no]" placeholder="" class="form-control" value="{{ old('data.invoice_no', (isset($xmlData) ? $xmlData['invoice_no'] : $invoice_no)) }}" required>
            <label for="invoice_no">Invoice No. <em>*</em></label>
        </div> 
    </div>
    
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoice_date]" id="invoice_date" placeholder="mm/dd/yyyy" class="form-control" value="{{ old('data.invoice_date', (isset($xmlData) ? $xmlData['invoice_date'] : $invoice_date)) }}" required>
            <label for="invoice_date">Invoice Date <em>*</em></label>
        </div> 
    </div>   
    
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[order_no]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.order_no', (isset($xmlData) ? $xmlData['order_no'] : '')) }}">
            <label for="order_no">Order No.</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[currency_code]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.currency_code', (isset($xmlData) ? $xmlData['currency_code'] : 'NOK')) }}" required>
            <label for="currency_code">Currency Code</label>
        </div>        
    </div>

    <div class="col-md-4">    
        <div class="form-floating mb-3">            
            <textarea name="data[footer_note]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" style="min-height: 150px;">{{ old('data.footer_note', (isset($xmlData) ? $xmlData['footer_note'] : '')) }}</textarea>
            <label for="footer_note">Note</label>
        </div>
    </div>    
</div> <!--/ row -->

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bxs-contact'></i> Sender
  </div>
</div>

<div class="row">    
    <!-- <label class="fs-big fw-semibold">Sender:</label> -->

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][name]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.name', (isset($xmlData) ? $xmlData['sender']['name'] : '')) }}">
            <label for="sender_name">Name</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][street]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.street', (isset($xmlData) ? $xmlData['sender']['street'] : '')) }}">
            <label for="sender_street">Street</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][houseno]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.houseno', (isset($xmlData) ? $xmlData['sender']['houseno'] : '')) }}">
            <label for="sender_houseno">House No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][city]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.city', (isset($xmlData) ? $xmlData['sender']['city'] : '')) }}">
            <label for="sender_city">City</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][postcode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.postcode', (isset($xmlData) ? $xmlData['sender']['postcode'] : '')) }}">
            <label for="sender_postcode">Postcode</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][countrycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.countrycode', (isset($xmlData) ? $xmlData['sender']['countrycode'] : '')) }}">
            <label for="sender_countrycode">Country Code</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][vat_no]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.vat_no', (isset($xmlData) ? $xmlData['sender']['vat_no'] : '')) }}">
            <label for="sender_vat_no">VAT No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][email]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.email', (isset($xmlData) ? $xmlData['sender']['email'] : '')) }}">
            <label for="sender_email">E-mail</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][website]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.website', (isset($xmlData) ? $xmlData['sender']['website'] : '')) }}">
            <label for="sender_website">Website</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][endpoint]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.endpoint', (isset($xmlData) ? $xmlData['sender']['endpoint'] : '')) }}">
            <label for="sender_endpoint">Endpoint</label>
        </div> 
    </div>
</div> <!--/ row -->    

<div class="my-1 text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bxs-contact'></i> Contact
  </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][contact][name]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.contact.name', (isset($xmlData) ? $xmlData['sender']['contact']['name'] : '')) }}">
            <label for="sender_contact_name">Contact Name</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][contact][email]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.contact.email', (isset($xmlData) ? $xmlData['sender']['contact']['email'] : '')) }}">
            <label for="sender_contact_email">Contact E-mail</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][contact][telephone]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.sender.contact.telephone', (isset($xmlData) ? $xmlData['sender']['contact']['telephone'] : '')) }}">
            <label for="sender_contact_telephone">Contact Telephone</label>
        </div> 
    </div>
</div> <!--/ row -->

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-cart'></i> Buyer
  </div>
</div>

<div class="row">    
    <!-- <label class="fs-big fw-semibold">Buyer:</label> -->

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][name]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.name', (isset($xmlData) ? $xmlData['buyer']['name'] : 'X')) }}" required>
            <label for="buyer_name">Name</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][street]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.street', (isset($xmlData) ? $xmlData['buyer']['street'] : '')) }}">
            <label for="buyer_street">Street</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][houseno]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.houseno', (isset($xmlData) ? $xmlData['buyer']['houseno'] : '')) }}">
            <label for="buyer_houseno">House No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][city]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.city', (isset($xmlData) ? $xmlData['buyer']['city'] : '')) }}">
            <label for="buyer_city">City</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][postcode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.postcode', (isset($xmlData) ? $xmlData['buyer']['postcode'] : '')) }}">
            <label for="buyer_postcode">Postcode</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][countrycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.countrycode', (isset($xmlData) ? $xmlData['buyer']['countrycode'] : '')) }}">
            <label for="buyer_countrycode">Country Code</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][vat_no]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.vat_no', (isset($xmlData) ? $xmlData['buyer']['vat_no'] : '')) }}">
            <label for="buyer_vat_no">VAT No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][email]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.email', (isset($xmlData) ? $xmlData['buyer']['email'] : '')) }}">
            <label for="buyer_email">E-mail</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][website]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.website', (isset($xmlData) ? $xmlData['buyer']['website'] : '')) }}">
            <label for="buyer_website">Website</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][endpoint]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.endpoint', (isset($xmlData) ? $xmlData['buyer']['endpoint'] : '')) }}">
            <label for="buyer_endpoint">Endpoint</label>
        </div> 
    </div>
</div> <!--/ row -->    

<div class="my-1 text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bxs-contact'></i> Contact
  </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][contact][name]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.contact.name', (isset($xmlData) ? $xmlData['buyer']['contact']['name'] : '')) }}">
            <label for="buyer_contact_name">Contact Name</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][contact][email]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.contact.email', (isset($xmlData) ? $xmlData['buyer']['contact']['email'] : '')) }}">
            <label for="buyer_contact_email">Contact E-mail</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][contact][telephone]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.buyer.contact.telephone', (isset($xmlData) ? $xmlData['buyer']['contact']['telephone'] : '')) }}">
            <label for="buyer_contact_telephone">Contact Telephone</label>
        </div> 
    </div>
</div> <!--/ row -->

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-package'></i> Delivery
  </div>
</div>

<div class="row">        
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][date]" id="delivery_date" placeholder="mm/dd/yyyy" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.delivery.date', (isset($xmlData) ? $xmlData['delivery']['date'] : '')) }}">
            <label for="delivery_date">Delivery Date</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][street]" placeholder="" class="form-control" {{ isset($xmlData) ? 'readonly' : '' }} value="{{ old('data.delivery.street', (isset($xmlData) ? $xmlData['delivery']['street'] : '')) }}">
            <label for="delivery_street">Street</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][houseno]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.delivery.houseno', (isset($xmlData) ? $xmlData['delivery']['houseno'] : '')) }}">
            <label for="delivery_houseno">House No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][city]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.delivery.city', (isset($xmlData) ? $xmlData['delivery']['city'] : '')) }}">
            <label for="delivery_city">City</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][postcode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.delivery.postcode', (isset($xmlData) ? $xmlData['delivery']['postcode'] : '')) }}">
            <label for="delivery_postcode">Postcode</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][countrycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.delivery.countrycode', (isset($xmlData) ? $xmlData['delivery']['countrycode'] : '')) }}">
            <label for="delivery_countrycode">Country Code</label>
        </div> 
    </div>
</div> <!--/ row -->    

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-credit-card'></i> Payment
  </div>
</div>

<div class="row">        
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][id]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.id', (isset($xmlData) ? $xmlData['payment_means']['id'] : '')) }}">
            <label for="payment_id">ID</label>
        </div> 
    </div>
   
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][branch_id]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.branch_id', (isset($xmlData) ? $xmlData['payment_means']['branch_id'] : '')) }}">
            <label for="payment_branch_id">Branch ID</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][due_date]" id="due_date" placeholder="mm/dd/yyyy" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.due_date', (isset($xmlData) ? $xmlData['payment_means']['due_date'] : '')) }}">
            <label for="payment_due_date">Due Date</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][institute_name]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.institute_name', (isset($xmlData) ? $xmlData['payment_means']['institute_name'] : '')) }}">
            <label for="payment_institute_name">Institute Name</label>
        </div> 
    </div>

   <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][type_id]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.type_id', (isset($xmlData) ? $xmlData['payment_means']['type_id'] : '')) }}">
            <label for="payment_type_id">Type ID</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][note]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.note', (isset($xmlData) ? $xmlData['payment_means']['note'] : '')) }}">
            <label for="payment_note">Note</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][discount_percent]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.discount_percent', (isset($xmlData) ? $xmlData['payment_means']['discount_percent'] : '')) }}">
            <label for="payment_discount_percent">Discount Percent</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][amount]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.amount', (isset($xmlData) ? $xmlData['payment_means']['amount'] : '')) }}">
            <label for="payment_amount">Amount</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][currencycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.currencycode', (isset($xmlData) ? $xmlData['payment_means']['currencycode'] : '')) }}">
            <label for="payment_currencycode">Currency Code</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][settlement_date]" id="settlement_date" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.settlement_date', (isset($xmlData) ? $xmlData['payment_means']['settlement_date'] : '')) }}">
            <label for="payment_settlement_date">Settlement Date</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][penalty_date]" id="penalty_date" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.payment_means.penalty_date', (isset($xmlData) ? $xmlData['payment_means']['penalty_date'] : '')) }}">
            <label for="payment_penalty_date">Penalty Date</label>
        </div> 
    </div> 
</div> <!--/ row -->   

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-money'></i> Tax Total
  </div>
</div>

<div class="row">        
    <div class="col-md-2">
        <div class="form-floating mb-3">
            @if(isset($xmlData))
                @if($xmlData['tax_total']['tax_currencycode'] == 'NOK')
                    <input type="text" name="data[tax_total][amount]" placeholder="" class="form-control" value="{{ old('data.tax_total.amount', (isset($xmlData) ? $xmlData['tax_total']['amount'] : '')) }}" required>
                @else
                    @if(isset($xmlData['converted_vat_amount']))
                        <input type="text" name="data[tax_total][amount]" placeholder="" class="form-control" value="{{ old('data.converted_vat_amount', (isset($xmlData) ? $xmlData['converted_vat_amount'] : '')) }}" required>
                    @else
                        <input type="text" name="data[tax_total][amount]" placeholder="" class="form-control" value="{{ old('data.tax_total.amount', (isset($xmlData) ? $xmlData['tax_total']['amount'] : '')) }}" required>
                    @endif    
                @endif
            @else
                <input type="text" name="data[tax_total][amount]" placeholder="" class="form-control" value="{{ old('data.tax_total.amount', (isset($xmlData) ? $xmlData['tax_total']['amount'] : '')) }}" required>    
            @endif
            <label for="tax_total_amount">Amount<em>*</em></label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            @if(isset($xmlData))
                @if($xmlData['tax_total']['tax_currencycode'] == 'NOK')
                    <input type="text" name="data[tax_total][tax_currencycode]" placeholder="" class="form-control" value="{{ old('data.tax_total.tax_currencycode', (isset($xmlData) ? $xmlData['tax_total']['tax_currencycode'] : '')) }}" required>
                @else 
                    @if(isset($xmlData['converted_currency_code']))
                        <input type="text" name="data[tax_total][tax_currencycode]" placeholder="" class="form-control" value="{{  old('data.converted_currency_code', (isset($xmlData) ? $xmlData['converted_currency_code'] : '')) }}" required>
                    @else
                        <input type="text" name="data[tax_total][tax_currencycode]" placeholder="" class="form-control" value="{{ old('data.tax_total.tax_currencycode', 'NOK') }}" required>
                    @endif
                @endif 
            @else
                <input type="text" name="data[tax_total][tax_currencycode]" placeholder="" class="form-control" value="{{ old('data.tax_total.tax_currencycode', (isset($xmlData) ? $xmlData['tax_total']['tax_currencycode'] : '')) }}" required>    
            @endif 
            <label for="tax_total_currencycode">Currency Code<em>*</em></label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            @if(isset($xmlData))
                @if($xmlData['tax_total']['tax_currencycode'] == 'NOK')
                    <input type="text" name="data[tax_total][net_amount]" placeholder="" class="form-control" value="{{ old('data.tax_total.net_amount', (isset($xmlData) ? $xmlData['tax_total']['net_amount'] : '')) }}" required>
                @else 
                    @if(isset($xmlData['converted_net_amount']))
                        <input type="text" name="data[tax_total][net_amount]" placeholder="" class="form-control" value="{{ old('data.converted_net_amount', (isset($xmlData) ? $xmlData['converted_net_amount'] : '')) }}" required>
                    @else
                        <input type="text" name="data[tax_total][net_amount]" placeholder="" class="form-control" value="{{ old('data.tax_total.net_amount', (isset($xmlData) ? $xmlData['tax_total']['net_amount'] : '')) }}" required>
                    @endif    
                @endif
            @else
                <input type="text" name="data[tax_total][net_amount]" placeholder="" class="form-control" value="{{ old('data.tax_total.net_amount', (isset($xmlData) ? $xmlData['tax_total']['net_amount'] : '')) }}" required>    
            @endif
            <label for="tax_total_net_amount">NET Amount<em>*</em></label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[tax_total][net_currencycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.tax_total.net_currencycode', (isset($xmlData) ? $xmlData['tax_total']['net_currencycode'] : '')) }}">
            <label for="tax_total_net_currencycode">NET Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[tax_total][percent]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.tax_total.percent', (isset($xmlData) ? $xmlData['tax_total']['percent'] : '25')) }}">
            <label for="tax_total_percent">Percent</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[tax_total][name]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.tax_total.name', (isset($xmlData) ? $xmlData['tax_total']['name'] : '')) }}">
            <label for="tax_total_name">Tax Name</label>
        </div> 
    </div>
</div> <!--/ row -->   

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-money'></i> Total
  </div>
</div>

<div class="row">        
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][line_amount]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.monetary_total.line_amount', (isset($xmlData) ? $xmlData['monetary_total']['line_amount'] : '')) }}">
            <label for="monetary_total_line_amount">Line Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][line_currencycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.monetary_total.line_currencycode', (isset($xmlData) ? $xmlData['monetary_total']['line_currencycode'] : '')) }}">
            <label for="monetary_total_line_currencycode">Line Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][tax_excl_amount]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.monetary_total.tax_excl_amount', (isset($xmlData) ? $xmlData['monetary_total']['tax_excl_amount'] : '')) }}">
            <label for="monetary_total_tax_excl_amount">Tax Excl. Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][tax_excl_currencycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.monetary_total.tax_excl_currencycode', (isset($xmlData) ? $xmlData['monetary_total']['tax_excl_currencycode'] : '')) }}">
            <label for="monetary_total_tax_excl_currencycode">Tax Excl. Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][tax_incl_amount]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.monetary_total.tax_incl_amount', (isset($xmlData) ? $xmlData['monetary_total']['tax_incl_amount'] : '')) }}">
            <label for="monetary_total_tax_incl_amount">Tax Incl. Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][tax_incl_currencycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.monetary_total.tax_incl_currencycode', (isset($xmlData) ? $xmlData['monetary_total']['tax_incl_currencycode'] : '')) }}">
            <label for="monetary_total_tax_incl_currencycode">Tax Incl. Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][payable_amount]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.monetary_total.payable_amount', (isset($xmlData) ? $xmlData['monetary_total']['payable_amount'] : '')) }}">
            <label for="monetary_total_payable_amount">Payable Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][payable_currencycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.monetary_total.payable_currencycode', (isset($xmlData) ? $xmlData['monetary_total']['payable_currencycode'] : '')) }}">
            <label for="monetary_total_payable_currencycode">Payable Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[allowance_charge]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.allowance_charge', (isset($xmlData) ? $xmlData['allowance_charge'] : '')) }}">
            <label for="allowance_charge">Discount Amount</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[allowance_charge_currencycode]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.allowance_charge_currencycode', (isset($xmlData) ? $xmlData['allowance_charge_currencycode'] : '')) }}">
            <label for="allowance_charge_currencycode">Discount Currency Code</label>
        </div> 
    </div>
</div> <!--/ row -->   

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-list-ul' ></i> Invoice Items
  </div>
</div>

@if(isset($xmlData))
    @foreach ($xmlData['invoices'] as $key => $item)
        @include('_partials/_content/_importreconciliation/sales-invoice-item-create-edit')     
    @endforeach   
@else
    @php
        $key = 0;
    @endphp
    @include('_partials/_content/_importreconciliation/sales-invoice-item-create-edit')
@endif