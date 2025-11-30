@php	
	$row_no = isset($row_no) ? $row_no : 0;	
@endphp
<div class="worksheet-row-repeater {{ $standard ? 'standard' : '' }}" id="worksheet-row-repeater-{{ $file_no }}-{{ $sheet_tab_no }}">	
	
	<div class="row">    
	    <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
			<label class="form-label" for="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][sheet_name]">Sheet Name</label>
			<select id="sheet_name_{{ $file_no }}_{{ $sheet_tab_no }}" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][sheet_name]" class="form-select sheet-name common-header" required data-name="sheet_name">
				<option value="">-- Sheet Mapping --</option>				
				<option value="Sales" {{ isset($sheet_name) ? (($sheet_name == 'Sales') ? 'selected' : '') : '' }}>Sales</option>
				<option value="Purchases" {{ isset($sheet_name) ? (($sheet_name == 'Purchases') ? 'selected' : '') : '' }}>Purchases</option>
			</select>
	    </div>

	    <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
			<label class="form-label" for="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][header_row]">Header Row Index</label>
			<input type="text" id="header_row_{{ $file_no }}_{{ $sheet_tab_no }}" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][header_row]" class="form-control header-row common-header" placeholder="2" required onkeypress="return isNumber(event)" data-name="header_row" value="{{ isset($header_row) ? $header_row : '' }}" />
	    </div>
	    
	    <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
			<label class="form-label" for="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][calc_type]">Calculation</label>
			<select id="calc_type_{{ $file_no }}_{{ $sheet_tab_no }}" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][calc_type]" class="form-select calc-type common-header" required data-name="calc_type" data-file_no="{{ $file_no }}">
				<option value="">-- Calculation --</option>	
				<option value="fixed" {{ isset($calc_type) ? (($calc_type == 'fixed') ? 'selected' : '') : '' }} data-content="<p>VAT Percentage = (VAT Amount * 100) / Net Amount<br><br>
Gross Amount = VAT Amount + Net Amount<br><br><button id='skipButton' class='btn btn-link'>Skip</button></p>">Fixed Calculation</option>			
				<option value="revenue" {{ isset($calc_type) ? (($calc_type == 'revenue') ? 'selected' : '') : '' }} data-content="<p>Excl. VAT Amount = Total Revenue Amount - Total VAT Amount<br><br>
Percentage = (VAT Amount * 100)/Total VAT Amount<br><br>
Net Amount = Excl. VAT Amount * Percentage)/100</p> <div class='d-flex justify-content-between'><button type='button' class='btn btn-sm btn-label-secondary btn-skip'>Skip</button></div>">Revenue Calculation</option>
				<option value="backward" {{ isset($calc_type) ? (($calc_type == 'backward') ? 'selected' : '') : '' }} data-content="<p>Net Amount = (VAT Amount / VAT Percentage) * 100<br><br>
Gross Amount = VAT Amount + Net Amount</p> <div class='d-flex justify-content-between'><button type='button' class='btn btn-sm btn-label-secondary btn-skip'>Skip</button></div>">Backward Calculation</option>			
			</select>
		</div>

		<div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0 position-relative">	
			<button type="button" class="btn btn-primary text-nowrap" id="btn-popover-calculation-{{ $file_no }}" data-bs-toggle="popover"  data-bs-offset="0,14" data-bs-placement="right" data-bs-html="true" data-bs-content="fgdggdgdfg dfgdfgfd gfdg" style="visibility: hidden; width: 1px; position: absolute; top: 25px;">
	          Calculation
	        </button>

			<button type="button" class="btn btn-primary text-nowrap" id="btn-popover-fixed-calculation-{{ $file_no }}" data-bs-toggle="popover"  data-bs-offset="0,14" data-bs-placement="right" data-bs-html="true" data-bs-content="<p>VAT Percentage = (VAT Amount * 100) / Net Amount<br><br>
Gross Amount = VAT Amount + Net Amount<br><br><button id='skipButton' class='btn btn-link'>Skip</button></p>" title="Fixed Calculation" style="visibility: hidden; width: 1px; position: absolute; top: 25px;">
	          Fixed Calculation
	        </button>

	        <button type="button" class="btn btn-primary text-nowrap" id="btn-popover-revenue-calculation-{{ $file_no }}" data-bs-toggle="popover"  data-bs-offset="0,14" data-bs-placement="right" data-bs-html="true" data-bs-content="<p>Excl. VAT Amount = Total Revenue Amount - Total VAT Amount<br><br>
Percentage = (VAT Amount * 100)/Total VAT Amount<br><br>
Net Amount = Excl. VAT Amount * Percentage)/100</p> <div class='d-flex justify-content-between'><button type='button' class='btn btn-sm btn-label-secondary btn-skip'>Skip</button></div>" title="Revenue Calculation" style="visibility: hidden; width: 1px; position: absolute; top: 25px;">
	          Revenue Calculation
	        </button>

	        <button type="button" class="btn btn-primary text-nowrap" id="btn-popover-backward-calculation-{{ $file_no }}" data-bs-toggle="popover"  data-bs-offset="0,14" data-bs-placement="right" data-bs-html="true" data-bs-content="<p>Net Amount = (VAT Amount / VAT Percentage) * 100<br><br>
Gross Amount = VAT Amount + Net Amount</p> <div class='d-flex justify-content-between'><button type='button' class='btn btn-sm btn-label-secondary btn-skip'>Skip</button></div>" title="Backward Calculation" style="visibility: hidden; width: 1px; position: absolute; top: 25px;">
	          Backward Calculation
	        </button>
	    </div>
	    
	</div>
	<hr>

	<div class=" {{ $standard ? 'standard' : '' }}" data-repeater-list="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns]">
		@include('_partials/_modals/modal-excel-column-template-new-row-repeater')	
	</div>
	  
	<div class="mb-0 row-add">
		<button class="btn btn-primary btn_repeater" type="button" data-column_name data-repeater-create>
			<i class="bx bx-plus me-1"></i>
			<span class="align-middle">Add</span>
		</button>
	</div>	
</div>