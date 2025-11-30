<label for="sl_no_{{$key}}" class="mx-n2">{{ ($key+1) . '.' }}</label>  
<div class="row ms-2 my-n2">        
    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][no]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.no', (isset($item) ? $item['no'] : '')) }}">
            <label for="item_no_{{$key}}">No.</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][order_no]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.order_no', (isset($item) ? $item['order_no'] : '')) }}">
            <label for="item_order_no_{{$key}}">Order No.</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][item_name]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.item_name', (isset($item) ? $item['item_name'] : '')) }}">
            <label for="item_name_{{$key}}">Item Name</label>
        </div> 
    </div>

    <div class="col-md-3">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][item_desc]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.item_desc', (isset($item) ? $item['item_desc'] : '')) }}">
            <label for="item_desc_{{$key}}">Item Desc.</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][base_qty]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.base_qty', (isset($item) ? $item['base_qty'] : '')) }}">
            <label for="item_base_qty_{{$key}}">Base Qty.</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][qty]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.qty', (isset($item) ? $item['qty'] : '')) }}">
            <label for="item_qty_{{$key}}">Qty.</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][unit_code]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.unit_code', (isset($item) ? $item['unit_code'] : '')) }}">
            <label for="item_unit_code_{{$key}}">Unit Code</label>
        </div> 
    </div>

    <div class="col-md-1">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][tax_name]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.tax_name', (isset($item) ? $item['tax_name'] : '')) }}">
            <label for="item_tax_name_{{$key}}">TAX Name</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][line_amount]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.line_amount', (isset($item) ? $item['line_amount'] : '')) }}">
            <label for="item_line_amount_{{$key}}">Line Amount</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][accounting_cost]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.accounting_cost', (isset($item) ? $item['accounting_cost'] : '')) }}">
            <label for="item_accounting_cost_{{$key}}">Accounting Cost</label>
        </div> 
    </div>    

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][tax_amount]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.tax_amount', (isset($item) ? $item['tax_amount'] : '')) }}">
            <label for="item_tax_amount_{{$key}}">Tax Amount</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][net_amount]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.net_amount', (isset($item) ? $item['net_amount'] : '')) }}">
            <label for="item_net_amount_{{$key}}">NET Amount</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][tax_percent]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.tax_percent', (isset($item) ? $item['tax_percent'] : '')) }}">
            <label for="item_tax_percent_{{$key}}">TAX Percent</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][price]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.price', (isset($item) ? $item['price'] : '')) }}">
            <label for="item_price_{{$key}}">Price</label>
        </div> 
    </div> 

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][seller_item_id]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.seller_item_id', (isset($item) ? $item['seller_item_id'] : '')) }}">
            <label for="seller_item_id_{{$key}}">Seller Item ID</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][seller_item_schema]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.seller_item_schema', (isset($item) ? $item['seller_item_schema'] : '')) }}">
            <label for="seller_item_schema_{{$key}}">Seller Item Schema</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][std_item_id]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.std_item_id', (isset($item) ? $item['std_item_id'] : '')) }}">
            <label for="std_item_id_{{$key}}">Std Item ID</label>
        </div> 
    </div>

    <div class="col-md-2">
        <div class="form-floating mb-3">
            <input type="text" name="data[invoices][{{$key}}][std_item_schema]" placeholder="" {{ isset($xmlData) ? 'readonly' : '' }} class="form-control" value="{{ old('data.item.std_item_schema', (isset($item) ? $item['std_item_schema'] : '')) }}">
            <label for="std_item_schema_{{$key}}">Std Item Schema</label>
        </div> 
    </div>

    <div class="divider divider-dotted divider-dark text-center">
      <div class="divider-text">
        <i class='bx bxl-tailwind-css'></i> <i class='bx bxl-tailwind-css'></i> <i class='bx bxl-tailwind-css'></i>
      </div>
    </div>

</div> <!--/ row -->