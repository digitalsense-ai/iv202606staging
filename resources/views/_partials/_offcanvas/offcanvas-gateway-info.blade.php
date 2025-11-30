<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasGatewayInfo" aria-labelledby="offcanvasGatewayInfoLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasGatewayInfoLabel" class="offcanvas-title">Gateway Info</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <div id="gatewayInfo">
    	@foreach($client->vatregmain as $vatregmain)
    		@if($vatregmain->country == 'GB')
    			<ul class="list-unstyled">
		            <li class="mb-3">
		            	<h5 class="text-decoration-underline">Gov. UK Profile</h5>
						<span class="fw-bold me-2">Government Gateway user ID:</span>
						<span>{{ $vatregmain->uk_gateway_userid }}</span>
		            	<br>
						<span class="fw-bold me-2">Password:</span>
						<span>{{ $vatregmain->uk_gateway_password }}</span>
						<hr>
		            </li>

		            <li class="mb-3">
		            	<h5 class="text-decoration-underline">CDS access</h5>
						<span class="fw-bold me-2">Government Gateway user ID:</span>
						<span>{{ $vatregmain->cds_gateway_userid }}</span>
		            	<br>
						<span class="fw-bold me-2">Password:</span>
						<span>{{ $vatregmain->cds_gateway_password }}</span>
		            </li>
		        </ul>
    		@endif
    	@endforeach
    </div>    
  </div>
</div>