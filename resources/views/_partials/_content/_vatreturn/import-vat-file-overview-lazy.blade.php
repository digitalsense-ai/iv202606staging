<div class="card table-responsive" style="box-shadow: none;">  
  @php  
    $compare_year_month = \Carbon\Carbon::parse('01-'. $import_vat_file->month_year)->format('Ym');
    $hasExpiryDate = collect($import_vat_file->xml)->contains(function ($item) use ($compare_year_month, $vat_reg_id) {
      $faktura_year_month = \Carbon\Carbon::parse($item['Ekspedisjon']['EkspDato'])->format('Ym');

      $allow = false;
      if($compare_year_month != $faktura_year_month)      
        $allow = true;

      return isset($item['Ekspedisjon']['EkspType']['EkspTypeNr']) && ($item['Ekspedisjon']['EkspType']['EkspTypeNr'] === "5" && $allow);
    });
  @endphp
    <table class="table m-0">
      <thead>
        <tr>          
          <th class="text-end">Fee</th>
          <th class="text-end">Statistical Value</th>
          <th class="text-end">Adjustment Fee</th>
          <th class="text-end">Invoice Total</th>          
          @if($hasExpiryDate)
          <th class="text-start">Note</th>
          @endif
          <th class="text-center">Action</th>
        </tr>
      </thead>
      @if($import_vat_file->xml)
        @foreach ($import_vat_file->xml as $key=>$declaration)  
          @php
            $import_vat_line_no = $key + 1;
          @endphp
          <tbody>
          	<tr>  
              <td class="text-end">{{ $declaration['Avgift'] }}</td>
              <td class="text-end">{{ $declaration['StatistiskVerdi'] }}</td>
              <td class="text-end">{{ $declaration['Justering'] }}</td>
              <td class="text-end">{{ $declaration['FakturaValutaBelop']['Fakturasum'] }}</td>
              @if($hasExpiryDate)
                <td class="text-start text-danger">
                  @if($declaration['Ekspedisjon']['EkspType']['EkspTypeNr'] == 5)
                  
                    @php                        
                      $faktura_year_month = \Carbon\Carbon::parse($declaration['Ekspedisjon']['EkspDato'])->format('Ym');
                    @endphp

                    @if($compare_year_month == $faktura_year_month)                        
                      {{--<span class="text-info">{{ __('Lope No.') }}: {{ $declaration['Ekspedisjon']['EkspedisjonsId']['LopeNr'] }}</span>
                      <span class="text-warning">{{ __('EkspDato') }}: {{ $declaration['Ekspedisjon']['EkspDato'] }}<br></span>
                      <span class="text-warning">Same month</span>--}}
                    @else
                      {{ __('Lope No.') }}: {{ $declaration['Ekspedisjon']['EkspedisjonsId']['LopeNr'] }}<br>  
                      {{--<span class="text-info">{{ __('Expiry Date') }}: {{ $declaration['GjenutFrist'] }}<br> </span>--}}    
                      {{ __('EkspDato') }}: {{ $declaration['Ekspedisjon']['EkspDato'] }}<br>
                      {{ $declaration['Ekspedisjon']['EkspType']['EkspTypeNavn'] }}<br>                
                      {{ $declaration['Tollrepresentant']['TollrepresentantNavn'] }} 
                    @endif 

                  {{--@else
                    <span class="text-info">{{ __('EkspTypeNr') }}: {{ $declaration['Ekspedisjon']['EkspType']['EkspTypeNr'] }}<br></span>--}}
                  @endif
                </td>
              @endif  
              <td class="text-center">
              	@if($declaration['comment'] == NULL)
      	        	<button class="btn btn-sm btn-icon btn-add-import-vat-comment" data-import_vat_file_id="{{ $import_vat_file->id }}" data-import_vat_line_no="{{ $import_vat_line_no }}" data-bs-toggle="modal" data-bs-target="#onboardingSlideImportVatCommentModal-{{ $vat_reg_id }}-{{ $import_vat_file->id }}-{{ $import_vat_line_no }}" title="Add Comment">
    	          		<i class='bx bx-comment-add'></i>
    	          	</button>
                  @include('_partials/_modals/modal-import-vat-comment-lazy')  
                @endif
              </td>
            </tr>
            @if($declaration['comment'] != NULL)
            <tr>          
              <td class="text-start import-vat-comment" colspan="5">
                {!! $declaration['comment']['comment'] !!}
              </td>              
            </tr>
            @endif          
          </tbody>
        @endforeach
      @endif  
  </table>
</div>