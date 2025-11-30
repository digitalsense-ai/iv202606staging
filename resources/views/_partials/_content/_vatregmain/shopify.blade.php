<div class="erp-fields mb-3" id="shopify">
  <h5>Shopify API Details</h5>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="api_base_url" name="api_base_url" placeholder="Store Name" value="{{ (isset($api_connection) ? $api_connection->api_base_url : 'quickstart-ad1f592d') }}" />
    <label for="api_base_url">Store Name</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="api_client_id" name="api_client_id" placeholder="API Version" value="{{ (isset($api_connection) ? $api_connection->api_client_id : '2024-07') }}" required />
    <label for="api_client_id">API Version</label>
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="api_secret_key" name="api_secret_key" placeholder="Shopify Access Token" value="{{ (isset($api_connection) ? $api_connection->api_secret_key : '') }}" required />
    <label for="api_secret_key">Shopify Access Token</label>          
  </div>

  {{-- //DON'T DELETE
  <div class="form-floating mb-3" style="display: none;">
    <select id="api_environment" class="form-select" data-allow-clear="true" name="api_environment" placeholder="Environment">
      <option value="">Select</option>
      <option value="Sandbox" {{ (isset($api_connection) ? (($api_connection->api_env == 'Sandbox') ? 'selected="selected"' : '') : '') }}>Sandbox</option>
      <option value="Production" {{ (isset($api_connection) ? (($api_connection->api_env == 'Production') ? 'selected="selected"' : '') : '') }}>Production</option>
    </select>
    <label for="api_environment">Environment</label>          
  </div>

  <div class="form-floating mb-3" style="display: none;">
    <input type="text" class="form-control" id="api_base_url" name="api_base_url" placeholder="Base Url" value="{{ (isset($api_connection) ? $api_connection->api_base_url : 'https://quickstart-ad1f592d.myshopify.com') }}" />
    <label for="api_base_url">Base Url</label>          
  </div>
  --}}

  {{--
  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="api_tenant_id" name="api_tenant_id" placeholder="API Version" value="{{ (isset($api_connection) ? $api_connection->api_tenant_id : '') }}" required />
    <label for="api_tenant_id">API Version</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="api_client_id" name="api_client_id" placeholder="API Key" value="{{ (isset($api_connection) ? $api_connection->api_client_id : '') }}" required />
    <label for="api_client_id">API Key</label>          
  </div> 

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="api_secret_key" name="api_secret_key" placeholder="Access Token" value="{{ (isset($api_connection) ? $api_connection->api_secret_key : '') }}" required />
    <label for="api_secret_key">API Password</label>          
  </div>
  --}}

  {{-- //DON'T DELETE
  <div class="form-floating mb-3" style="display: none;">
    <select id="currency_code" class="form-select" data-allow-clear="true" name="currency_code" placeholder="Currency Code">
      <option value="">Select</option>
      <option value="DKK" {{ (isset($api_connection) ? (($api_connection->currency_code == 'DKK') ? 'selected="selected"' : '') : '') }}>DKK</option>
      <option value="GBP" {{ (isset($api_connection) ? (($api_connection->currency_code == 'GBP') ? 'selected="selected"' : '') : '') }}>GBP</option><option value="INR" {{ (isset($api_connection) ? (($api_connection->currency_code == 'INR') ? 'selected="selected"' : '') : '') }}>INR</option>
      <option value="USD" {{ (isset($api_connection) ? (($api_connection->currency_code == 'USD') ? 'selected="selected"' : '') : '') }}>USD</option>
    </select>
    <label for="currency_code">Currency Code</label>          
  </div>

  @include('_partials/_content/_vatregmain/vat-acc-fields')
  --}}
</div>