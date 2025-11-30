<div class="col-sm-4 mb-2">
	<label for="btn_download_swiss_file_{{ $vat_reg_id }}" class="fw-bold">Download: </label>
    		        				
	<select class="form-select btn-download-file w-50 d-inline-block" id="btn_download_swiss_file_{{ $vat_reg_id }}" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="swissimportreconciliationfiles" data-file_type_title="Swiss Import Reconciliation" data-original_file="true" style="{{ (count($vatreg->importreconciliationswissfiles) == 0) ? 'display:  none;' : '' }}">
		<option value="">Download Swiss Files</option>
		@if($vatreg->importreconciliationswissfiles)
			@if(count($vatreg->importreconciliationswissfiles) > 0)
				@foreach($vatreg->importreconciliationswissfiles as $key => $importreconciliationswissfile)
		          <option value="{{ $importreconciliationswissfile->id }}">{{ $importreconciliationswissfile->o_file_name }}</option>
		        @endforeach		
			@endif
		@endif        							
	</select>
</div>	