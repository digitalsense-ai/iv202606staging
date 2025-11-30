<div class="erp-fields mb-3" id="uniconta">
  <h5>Billy API Details</h5>
  
  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="api_secret_key" name="api_secret_key" placeholder="Access Token" value="{{ (isset($api_connection) ? $api_connection->api_secret_key : '') }}" required />
    <label for="api_secret_key">Access Token</label>          
  </div> 
</div>