@if($vatreg->country == 'GB' && ($vatreg->vatregmain->uk_gateway_userid || $vatreg->vatregmain->cds_gateway_userid))
<div id="gateway-info" class="mt-3">
    <ul class="list-unstyled m-0">
      @if($vatreg->vatregmain->uk_gateway_userid)
      <li class="mb-3">
        <h5 class="text-decoration-underline">Gov. UK Profile</h5>
        <span class="fw-bold me-2">Government Gateway user ID:</span>
        <span>{{ $vatreg->vatregmain->uk_gateway_userid }}</span>
        <br>
        <span class="fw-bold me-2">Password:</span>
        <span>{{ $vatreg->vatregmain->uk_gateway_password }}</span>
      </li>
      @endif

      @if($vatreg->vatregmain->cds_gateway_userid)
      <li class="m-0">
        <h5 class="text-decoration-underline">CDS access</h5>
        <span class="fw-bold me-2">Government Gateway user ID:</span>
        <span>{{ $vatreg->vatregmain->cds_gateway_userid }}</span>
        <br>
        <span class="fw-bold me-2">Password:</span>
        <span>{{ $vatreg->vatregmain->cds_gateway_password }}</span>
      </li>
      @endif
    </ul>                        
</div>      
@endif