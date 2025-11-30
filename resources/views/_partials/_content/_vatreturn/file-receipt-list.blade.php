<div class="col-sm-4 mb-2">
	<label for="btn_download_receipt_file_{{ $vat_reg_id }}" class="fw-bold">Download: </label>
    	   
	<select class="form-select btn-download-file w-50 d-inline-block" id="btn_download_receipt_file_{{ $vat_reg_id }}" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}"  data-file_type="receipt" data-file_type_title="Receipt" data-original_file="true" style="{{ (count($vatreg->receipt) == 0) ? 'display:  none;' : '' }}">
		<option value="">Download Receipt</option>
		@if($vatreg->receipt)
			@if(count($vatreg->receipt) > 0)
				@foreach($vatreg->receipt as $key => $receipt)
		          <option value="{{ $receipt->id }}">{{ ($receipt->o_file_name) ? $receipt->o_file_name : $receipt->file_name }}</option>
		        @endforeach
		    @endif
		@endif
	</select>		
</div>	