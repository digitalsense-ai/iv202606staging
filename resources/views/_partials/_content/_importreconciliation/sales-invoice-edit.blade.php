@if(isset($xmlData))
<input type="hidden" name="vat_reg_id" id="vat_reg_id" value="{{ old('data.vat_reg_id', $xmlData['vat_reg_id']) }}" />
<input type="hidden" name="tab_name" id="tab_name" value="{{ old('data.tab_name', $xmlData['tab_name']) }}" />

<input type="hidden" name="sales_invoice_data_id" id="sales_invoice_data_id" value="{{ isset($xmlData['sales_invoice_data_id']) ? old('data.sales_invoice_data_id', $xmlData['sales_invoice_data_id']) : '' }}" />
<input type="hidden" name="ftp_file_id" id="ftp_file_id" value="{{ old('data.ftp_file_id', $xmlData['ftp_file_id']) }}" />
<input type="hidden" name="sales_invoice_id" id="sales_invoice_id" value="{{ old('data.sales_invoice_id', $xmlData['sales_invoice_id']) }}" />

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-purchase-tag-alt'></i> Invoice
  </div>
</div>
<div class="row">
    <!-- <label class="fs-big fw-semibold">Invoice: </label> -->

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoice_no]" placeholder="" class="form-control" value="{{ old('data.invoice_no', $xmlData['invoice_no']) }}">
            <label for="invoice_no">Invoice No.</label>
        </div> 
    </div>
    
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoice_date]" id="invoice_date" placeholder="mm/dd/yyyy" class="form-control" value="{{ old('data.invoice_date', $xmlData['invoice_date']) }}">
            <label for="invoice_date">Invoice Date</label>
        </div> 
    </div>   
    
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[order_no]" placeholder="" readonly class="form-control" value="{{ old('data.order_no', $xmlData['order_no']) }}">
            <label for="order_no">Order No.</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[currency_code]" placeholder="" readonly class="form-control" value="{{ old('data.currency_code', $xmlData['currency_code']) }}">
            <label for="currency_code">Currency Code</label>
        </div>        
    </div>

    <div class="col-md-4">    
        <div class="form-floating mb-3">            
            <textarea name="data[footer_note]" placeholder="" readonly class="form-control" style="min-height: 150px;">{{ old('data.footer_note', $xmlData['footer_note']) }}</textarea>
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
            <input type="text" name="data[sender][name]" placeholder="" readonly class="form-control" value="{{ old('data.sender.name', $xmlData['sender']['name']) }}">
            <label for="sender_name">Name</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][street]" placeholder="" readonly class="form-control" value="{{ old('data.sender.street', $xmlData['sender']['street']) }}">
            <label for="sender_street">Street</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][houseno]" placeholder="" readonly class="form-control" value="{{ old('data.sender.houseno', $xmlData['sender']['houseno']) }}">
            <label for="sender_houseno">House No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][city]" placeholder="" readonly class="form-control" value="{{ old('data.sender.city', $xmlData['sender']['city']) }}">
            <label for="sender_city">City</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][postcode]" placeholder="" readonly class="form-control" value="{{ old('data.sender.postcode', $xmlData['sender']['postcode']) }}">
            <label for="sender_postcode">Postcode</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][countrycode]" placeholder="" readonly class="form-control" value="{{ old('data.sender.countrycode', $xmlData['sender']['countrycode']) }}">
            <label for="sender_countrycode">Country Code</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][vat_no]" placeholder="" readonly class="form-control" value="{{ old('data.sender.vat_no', $xmlData['sender']['vat_no']) }}">
            <label for="sender_vat_no">VAT No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][email]" placeholder="" readonly class="form-control" value="{{ old('data.sender.email', $xmlData['sender']['email']) }}">
            <label for="sender_email">E-mail</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][website]" placeholder="" readonly class="form-control" value="{{ old('data.sender.website', $xmlData['sender']['website']) }}">
            <label for="sender_website">Website</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][endpoint]" placeholder="" readonly class="form-control" value="{{ old('data.sender.endpoint', $xmlData['sender']['endpoint']) }}">
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
            <input type="text" name="data[sender][contact][name]" placeholder="" readonly class="form-control" value="{{ old('data.sender.contact.name', $xmlData['sender']['contact']['name']) }}">
            <label for="sender_contact_name">Contact Name</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][contact][email]" placeholder="" readonly class="form-control" value="{{ old('data.sender.contact.email', $xmlData['sender']['contact']['email']) }}">
            <label for="sender_contact_email">Contact E-mail</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[sender][contact][telephone]" placeholder="" readonly class="form-control" value="{{ old('data.sender.contact.telephone', $xmlData['sender']['contact']['telephone']) }}">
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
            <input type="text" name="data[buyer][name]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.name', $xmlData['buyer']['name']) }}">
            <label for="buyer_name">Name</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][street]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.street', $xmlData['buyer']['street']) }}">
            <label for="buyer_street">Street</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][houseno]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.houseno', $xmlData['buyer']['houseno']) }}">
            <label for="buyer_houseno">House No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][city]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.city', $xmlData['buyer']['city']) }}">
            <label for="buyer_city">City</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][postcode]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.postcode', $xmlData['buyer']['postcode']) }}">
            <label for="buyer_postcode">Postcode</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][countrycode]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.countrycode', $xmlData['buyer']['countrycode']) }}">
            <label for="buyer_countrycode">Country Code</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][vat_no]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.vat_no', $xmlData['buyer']['vat_no']) }}">
            <label for="buyer_vat_no">VAT No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][email]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.email', $xmlData['buyer']['email']) }}">
            <label for="buyer_email">E-mail</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][website]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.website', $xmlData['buyer']['website']) }}">
            <label for="buyer_website">Website</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][endpoint]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.endpoint', $xmlData['buyer']['endpoint']) }}">
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
            <input type="text" name="data[buyer][contact][name]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.contact.name', $xmlData['buyer']['contact']['name']) }}">
            <label for="buyer_contact_name">Contact Name</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][contact][email]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.contact.email', $xmlData['buyer']['contact']['email']) }}">
            <label for="buyer_contact_email">Contact E-mail</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[buyer][contact][telephone]" placeholder="" readonly class="form-control" value="{{ old('data.buyer.contact.telephone', $xmlData['buyer']['contact']['telephone']) }}">
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
            <input type="text" name="data[delivery][date]" id="delivery_date" placeholder="mm/dd/yyyy" readonly class="form-control" value="{{ old('data.delivery.date', $xmlData['delivery']['date']) }}">
            <label for="delivery_date">Delivery Date</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][street]" placeholder="" class="form-control" readonly value="{{ old('data.delivery.street', $xmlData['delivery']['street']) }}">
            <label for="delivery_street">Street</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][houseno]" placeholder="" readonly class="form-control" value="{{ old('data.delivery.houseno', $xmlData['delivery']['houseno']) }}">
            <label for="delivery_houseno">House No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][city]" placeholder="" readonly class="form-control" value="{{ old('data.delivery.city', $xmlData['delivery']['city']) }}">
            <label for="delivery_city">City</label>
        </div> 
    </div>
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][postcode]" placeholder="" readonly class="form-control" value="{{ old('data.delivery.postcode', $xmlData['delivery']['postcode']) }}">
            <label for="delivery_postcode">Postcode</label>
        </div> 
    </div>   
    
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[delivery][countrycode]" placeholder="" readonly class="form-control" value="{{ old('data.delivery.countrycode', $xmlData['delivery']['countrycode']) }}">
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
            <input type="text" name="data[payment_means][id]" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.id', $xmlData['payment_means']['id']) }}">
            <label for="payment_id">ID</label>
        </div> 
    </div>
   
    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][branch_id]" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.branch_id', $xmlData['payment_means']['branch_id']) }}">
            <label for="payment_branch_id">Branch ID</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][due_date]" id="due_date" placeholder="mm/dd/yyyy" readonly class="form-control" value="{{ old('data.payment_means.due_date', $xmlData['payment_means']['due_date']) }}">
            <label for="payment_due_date">Due Date</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][institute_name]" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.institute_name', $xmlData['payment_means']['institute_name']) }}">
            <label for="payment_institute_name">Institute Name</label>
        </div> 
    </div>

   <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][type_id]" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.type_id', $xmlData['payment_means']['type_id']) }}">
            <label for="payment_type_id">Type ID</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][note]" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.note', $xmlData['payment_means']['note']) }}">
            <label for="payment_note">Note</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][discount_percent]" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.discount_percent', $xmlData['payment_means']['discount_percent']) }}">
            <label for="payment_discount_percent">Discount Percent</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][amount]" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.amount', $xmlData['payment_means']['amount']) }}">
            <label for="payment_amount">Amount</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][currencycode]" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.currencycode', $xmlData['payment_means']['currencycode']) }}">
            <label for="payment_currencycode">Currency Code</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][settlement_date]" id="settlement_date" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.settlement_date', $xmlData['payment_means']['settlement_date']) }}">
            <label for="payment_settlement_date">Settlement Date</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[payment_means][penalty_date]" id="penalty_date" placeholder="" readonly class="form-control" value="{{ old('data.payment_means.penalty_date', $xmlData['payment_means']['penalty_date']) }}">
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
            @if($xmlData['tax_total']['tax_currencycode'] == 'NOK')
                <input type="text" name="data[tax_total][amount]" placeholder="" class="form-control" value="{{ old('data.tax_total.amount', $xmlData['tax_total']['amount']) }}">
            @else
                <input type="text" name="data[tax_total][amount]" placeholder="" class="form-control" value="{{ old('data.converted_vat_amount', $xmlData['converted_vat_amount']) }}">
            @endif
            <label for="tax_total_amount">Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            @if($xmlData['tax_total']['tax_currencycode'] == 'NOK')
                <input type="text" name="data[tax_total][tax_currencycode]" placeholder="" class="form-control" value="{{ old('data.tax_total.tax_currencycode', $xmlData['tax_total']['tax_currencycode']) }}">
            @else 
                <input type="text" name="data[tax_total][tax_currencycode]" placeholder="" class="form-control" value="{{  old('data.converted_currency_code', $xmlData['converted_currency_code']) }}">
            @endif  
            <label for="tax_total_currencycode">Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            @if($xmlData['tax_total']['tax_currencycode'] == 'NOK')
                <input type="text" name="data[tax_total][net_amount]" placeholder="" class="form-control" value="{{ old('data.tax_total.net_amount', $xmlData['tax_total']['net_amount']) }}">
            @else 
                <input type="text" name="data[tax_total][net_amount]" placeholder="" class="form-control" value="{{ old('data.converted_net_amount', $xmlData['converted_net_amount']) }}">
            @endif
            <label for="tax_total_net_amount">NET Amount</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[tax_total][net_currencycode]" placeholder="" readonly class="form-control" value="{{ old('data.tax_total.net_currencycode', $xmlData['tax_total']['net_currencycode']) }}">
            <label for="tax_total_net_currencycode">NET Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[tax_total][percent]" placeholder="" readonly class="form-control" value="{{ old('data.tax_total.percent', $xmlData['tax_total']['percent']) }}">
            <label for="tax_total_percent">Percent</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[tax_total][name]" placeholder="" readonly class="form-control" value="{{ old('data.tax_total.name', $xmlData['tax_total']['name']) }}">
            <label for="tax_total_name">name</label>
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
            <input type="text" name="data[monetary_total][line_amount]" placeholder="" readonly class="form-control" value="{{ old('data.monetary_total.line_amount', $xmlData['monetary_total']['line_amount']) }}">
            <label for="monetary_total_line_amount">Line Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][line_currencycode]" placeholder="" readonly class="form-control" value="{{ old('data.monetary_total.line_currencycode', $xmlData['monetary_total']['line_currencycode']) }}">
            <label for="monetary_total_line_currencycode">Line Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][tax_excl_amount]" placeholder="" readonly class="form-control" value="{{ old('data.monetary_total.tax_excl_amount', $xmlData['monetary_total']['tax_excl_amount']) }}">
            <label for="monetary_total_tax_excl_amount">Tax Excl. Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][tax_excl_currencycode]" placeholder="" readonly class="form-control" value="{{ old('data.monetary_total.tax_excl_currencycode', $xmlData['monetary_total']['tax_excl_currencycode']) }}">
            <label for="monetary_total_tax_excl_currencycode">Tax Excl. Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][tax_incl_amount]" placeholder="" readonly class="form-control" value="{{ old('data.monetary_total.tax_incl_amount', $xmlData['monetary_total']['tax_incl_amount']) }}">
            <label for="monetary_total_tax_incl_amount">Tax Incl. Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][tax_incl_currencycode]" placeholder="" readonly class="form-control" value="{{ old('data.monetary_total.tax_incl_currencycode', $xmlData['monetary_total']['tax_incl_currencycode']) }}">
            <label for="monetary_total_tax_incl_currencycode">Tax Incl. Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][payable_amount]" placeholder="" readonly class="form-control" value="{{ old('data.monetary_total.payable_amount', $xmlData['monetary_total']['payable_amount']) }}">
            <label for="monetary_total_payable_amount">Payable Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[monetary_total][payable_currencycode]" placeholder="" readonly class="form-control" value="{{ old('data.monetary_total.payable_currencycode', $xmlData['monetary_total']['payable_currencycode']) }}">
            <label for="monetary_total_payable_currencycode">Payable Currency Code</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[allowance_charge]" placeholder="" readonly class="form-control" value="{{ old('data.allowance_charge', $xmlData['allowance_charge']) }}">
            <label for="allowance_charge">Discount Amount</label>
        </div> 
    </div>
   
    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[allowance_charge_currencycode]" placeholder="" readonly class="form-control" value="{{ old('data.allowance_charge_currencycode', $xmlData['allowance_charge_currencycode']) }}">
            <label for="allowance_charge_currencycode">Discount Currency Code</label>
        </div> 
    </div>
</div> <!--/ row -->   

<div class="divider divider-dotted divider-dark text-start">
  <div class="divider-text fs-big fw-semibold">
    <i class='bx bx-list-ul' ></i> Invoice Items
  </div>
</div>

@foreach ($xmlData['invoices'] as $key => $item)
<div class="row ms-2">        
    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][no]" placeholder="" readonly class="form-control" value="{{ old('data.item.no', $item['no']) }}">
            <label for="item_no_{{$key}}">No.</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][order_no]" placeholder="" readonly class="form-control" value="{{ old('data.item.order_no', $item['order_no']) }}">
            <label for="item_order_no_{{$key}}">Order No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][item_name]" placeholder="" readonly class="form-control" value="{{ old('data.item.item_name', $item['item_name']) }}">
            <label for="item_name_{{$key}}">Item Name</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][item_desc]" placeholder="" readonly class="form-control" value="{{ old('data.item.item_desc', $item['item_desc']) }}">
            <label for="item_desc_{{$key}}">Item Desc.</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][base_qty]" placeholder="" readonly class="form-control" value="{{ old('data.item.base_qty', $item['base_qty']) }}">
            <label for="item_base_qty_{{$key}}">Base Qty.</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][qty]" placeholder="" readonly class="form-control" value="{{ old('data.item.qty', $item['qty']) }}">
            <label for="item_qty_{{$key}}">Qty.</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][unit_code]" placeholder="" readonly class="form-control" value="{{ old('data.item.unit_code', $item['unit_code']) }}">
            <label for="item_unit_code_{{$key}}">Unit Code</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][tax_name]" placeholder="" readonly class="form-control" value="{{ old('data.item.tax_name', $item['tax_name']) }}">
            <label for="item_tax_name_{{$key}}">TAX Name</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][line_amount]" placeholder="" readonly class="form-control" value="{{ old('data.item.line_amount', $item['line_amount']) }}">
            <label for="item_line_amount_{{$key}}">Line Amount</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][accounting_cost]" placeholder="" readonly class="form-control" value="{{ old('data.item.accounting_cost', $item['accounting_cost']) }}">
            <label for="item_accounting_cost_{{$key}}">Accounting Cost</label>
        </div> 
    </div>    

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][tax_amount]" placeholder="" readonly class="form-control" value="{{ old('data.item.tax_amount', $item['tax_amount']) }}">
            <label for="item_tax_amount_{{$key}}">Tax Amount</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][net_amount]" placeholder="" readonly class="form-control" value="{{ old('data.item.net_amount', $item['net_amount']) }}">
            <label for="item_net_amount_{{$key}}">NET Amount</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][tax_percent]" placeholder="" readonly class="form-control" value="{{ old('data.item.tax_percent', $item['tax_percent']) }}">
            <label for="item_tax_percent_{{$key}}">TAX Percent</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][price]" placeholder="" readonly class="form-control" value="{{ old('data.item.price', $item['price']) }}">
            <label for="item_price_{{$key}}">Price</label>
        </div> 
    </div> 

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][seller_item_id]" placeholder="" readonly class="form-control" value="{{ old('data.item.seller_item_id', $item['seller_item_id']) }}">
            <label for="seller_item_id_{{$key}}">Seller Item ID</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][seller_item_schema]" placeholder="" readonly class="form-control" value="{{ old('data.item.seller_item_schema', $item['seller_item_schema']) }}">
            <label for="seller_item_schema_{{$key}}">Seller Item Schema</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][std_item_id]" placeholder="" readonly class="form-control" value="{{ old('data.item.std_item_id', $item['std_item_id']) }}">
            <label for="std_item_id_{{$key}}">Std Item ID</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][std_item_schema]" placeholder="" readonly class="form-control" value="{{ old('data.item.std_item_schema', $item['std_item_schema']) }}">
            <label for="std_item_schema_{{$key}}">Std Item Schema</label>
        </div> 
    </div>

    <div class="divider divider-dotted divider-dark text-center">
      <div class="divider-text">
        <i class='bx bxl-tailwind-css'></i> <i class='bx bxl-tailwind-css'></i> <i class='bx bxl-tailwind-css'></i>
      </div>
    </div>

</div> <!--/ row -->
@endforeach
@endif    