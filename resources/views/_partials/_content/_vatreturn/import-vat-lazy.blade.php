<!-- Import VAT --> 
<form id="formImportVat-{{ $vat_reg_id }}-{{ $import_vat_file->id }}" class="needs-validation formImportVat" novalidate data-vatid="{{ $vat_reg_id }}" data-client_id="{{ $client->client_id }}" data-import_vat_file_id="{{ $import_vat_file->id }}">
@csrf
	<div class="row g-3">
		<div class="col-12">	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>Import</strong></p>
			      <span>Statistic value</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">		    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ $import_vat_file->statistical_number }}" id="import-vat-{{ $vat_reg_id }}-{{ $import_vat_file->id }}-statistical-number" name="import_vat_statistical_number_{{ $vat_reg_id }}" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>Import</strong></p>
			      <span>Duties & taxes</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">		    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ $import_vat_file->fee_number }}" id="import-vat-{{ $vat_reg_id }}-{{ $import_vat_file->id }}-fee-number" name="import_vat_fee_number_{{ $vat_reg_id }}" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>Export & Re-export</strong></p>
			      <span>Statistic value</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">		    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ $import_vat_file->e_statistical_number }}" id="import-vat-{{ $vat_reg_id }}-{{ $import_vat_file->id }}-e-statistical-number" name="import_vat_e_statistical_number_{{ $vat_reg_id }}" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row d-none">
			    <div class="offer">
			      <p class="mb-0"><strong>Export & Re-export</strong></p>
			      <span>Duties & taxes</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">		    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ $import_vat_file->e_fee_number }}" id="import-vat-{{ $vat_reg_id }}-{{ $import_vat_file->id }}-e-fee-number" name="import_vat_e_fee_number_{{ $vat_reg_id }}" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>		  
			</ul>
		</div>	
		<div class="col-12">
			<button type="submit" class="btn btn-primary float-end importvat-save" disabled>Save</button> 
		</div>
	</div>
</form>
<!--/ Import VAT -->  