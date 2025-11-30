<div class="erp-fields mb-3" id="dynamics_365">
  <h5>Dynamics 365 via SMART API Details</h5>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="sales_invoice_url" name="sales_invoice_url" placeholder="Sales Invoice Url" value="{{ (isset($api_connection) ? $api_connection->sales_invoice_url : '') }}" required />
    <label for="sales_invoice_url">Sales Invoice Url</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="purchase_invoice_url" name="purchase_invoice_url" placeholder="Purchase Invoice Url" value="{{ (isset($api_connection) ? $api_connection->purchase_invoice_url : '') }}" />
    <label for="purchase_invoice_url">Purchase Invoice Url</label>          
  </div>
</div>