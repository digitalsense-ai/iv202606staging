@foreach ($result as $key => $vatreg)
    @php              
		$client = $vatreg->client;
		$client_id = $client->client_id;

		$vat_reg_main = $vatreg->vatregmain;
		$client_api = $vat_reg_main->clientapi;

		$vat_reg_id = $vatreg->vat_reg_id; 

		$vatreturnfiles = ($vatreg->vatreturnfiles) ? $vatreg->vatreturnfiles : [];   
      
		$file_type = 'vatreturn';
		$file_type_title = 'Excel/XML';

		$files = $vatreturnfiles;    

		$i = 1; 
    @endphp
       
	<div class="accordion-item card sort-item" data-country="{{ $vatreg->country }}" data-vat_reg_main_id="{{ $vatreg->vat_reg_main_id }}" data-index="{{ $vatreg->statusorder }}" data-range="{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('Y-m') . '***' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('Y-m')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('Y-m')) }}" {{ isset($vatreg->show) ? (($vatreg->show) ? 'data-all=true' : 'data-all=false') : '' }}>
		<h2 class="accordion-header">					
			{{--<button type="button" class="accordion-button collapsed btn-no-api-connection" data-bs-toggle="modal" data-bs-target="#onboardingSlideNoAPIConnectionModal-{{ $vat_reg_id }}" aria-expanded="false" id="btn-no-api-connection-{{ $vat_reg_id }}">--}}
			<button type="button" class="accordion-button collapsed" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" aria-expanded="false">				
				<table class="table border-0">
					<colgroup>			    					
						<col width="10%"/>
			            <col width="40%"/>
			            <col width="10%"/>
			            <col width="10%"/>
			            <col width="15%"/>
			            <col width="15%"/>
					</colgroup>
					<tbody>
						<tr>              
							<td class="border-bottom-0 p-0">										
								<img src="{{asset('assets/img/flags/'. $vatreg->country .'.png')}}" class="country-flag me-2"><span class="btn-group-vertical">{{ $vatreg->country }}</span>
							</td>
							<td class="border-bottom-0 p-0">
								{{ $client->client_name }}<br>                
								<span class="badge rounded-pill bg-label-{{ ($vat_reg_main->vat_reg_main_type == 'Basic') ? 'primary' : 'danger' }}">{{ $vat_reg_main->vat_reg_main_type }}</span>        
								<span class="badge rounded-pill bg-label-primary">VAT Return</span>
							</td>
							<td class="border-bottom-0 p-0">{{ $vatreg->general_periods }}</td> 							   
							<td class="border-bottom-0 p-0">{{ ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y')) }}</td>
							<td class="border-bottom-0 p-0 text-center">-</td>
							<td class="border-bottom-0 p-0"><span class="badge bg-label-warning">Upload Excel/XML</span></td>
							@php /*
							<td class="border-bottom-0 p-0">Upload Excel File for VAT Returns</td>
							<td class="border-bottom-0 p-0 status">
								<span class="badge {{ ($vatreg->status == 0) ? 'bg-label-dark' : '' }}{{ ($vatreg->status == 1) ? 'bg-label-secondary' : '' }}{{ ($vatreg->status == 2) ? 'bg-label-primary' : '' }}{{ ($vatreg->status == 3) ? 'bg-label-warning' : '' }}{{ ($vatreg->status == 4) ? 'bg-label-success' : '' }}{{ ($vatreg->status == 5) ? 'bg-label-info' : '' }}{{ ($vatreg->status == 6) ? 'bg-label-danger' : '' }}">{{ $vatreg->statustext }}</span>
							</td>
							*/
							@endphp		                
						</tr>
					</tbody>
	            </table>								
			</button>
		</h2>
	</div>
	{{--@include('_partials/_modals/modal-no-api-connection-lazy')--}}	
	@include('_partials/_modals/modal-file-upload-single-lazy')
@endforeach