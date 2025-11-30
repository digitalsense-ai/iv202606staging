<div class="worksheet-repeater" id="worksheet-repeater-{{ $sheet_no }}-{{ $sheet_file_no }}">	
	<div class="row">
		<div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
			<label class="form-label file-name" for="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][file_name]"><b> File 1: </b></label>
			{{--
			<input type="text" id="file_name_{{ $sheet_no }}_{{ $sheet_file_no }}" name="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][file_name]" class="form-control file-name" placeholder="" required data-control_name="file_name"/>--}}
	    </div>
	</div>


	<div class="row">    
	    <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
			<label class="form-label" for="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][sheet_name]">Sheet Name</label>
			<select id="sheet_name_{{ $sheet_no }}_{{ $sheet_file_no }}" name="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][sheet_name]" class="form-select sheet-name" required data-control_name="sheet_name">
				<option value="">-- Sheet Mapping --</option>				
				<option value="Sales">Sales</option>
				<option value="Purchases">Purchases</option>
			</select>
	    </div>

	    <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
			<label class="form-label" for="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][header_row]">Header Row Index</label>
			<input type="text" id="header_row_{{ $sheet_no }}_{{ $sheet_file_no }}" name="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][header_row]" class="form-control header-row" placeholder="2" required onkeypress="return isNumber(event)" data-control_name="header_row"/>
	    </div>
	    
	    <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
			<label class="form-label" for="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][calc_type]">Calculation</label>
			<select id="calc_type_{{ $sheet_no }}_{{ $sheet_file_no }}" name="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][calc_type]" class="form-select calc-type" required data-control_name="calc_type">
				<option value="">-- Calculation --</option>	
				<option value="fixed">Fixed Calculation</option>			
				<option value="revenue">Revenue Calculation</option>	
				<option value="backward">Backward Calculation</option>			
			</select>
	    </div>
	</div>
	<hr>

	<div data-repeater-list="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][columns]">
		<div class="standard_row" data-repeater-item>
			<div class="row">
				<div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
					<label class="form-label" for="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][columns][0][column]">Column</label>
					<input type="text" id="template_columns_{{ $sheet_no }}_{{ $sheet_file_no }}_columns_0_column" name="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][columns][0][column]" class="form-control columnname" placeholder="Column A" value="" required data-name="column" />
				</div>
				<div class="mb-3 col-lg-6 col-xl-4 col-12 mb-0">
					<label class="form-label" for="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][columns][0][columnmapping]">System Column</label>
					<select id="template_columns_{{ $sheet_no }}_{{ $sheet_file_no }}_columns_0_columnmapping" name="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][columns][0][columnmapping]" class="form-select system-excel-column" required data-name="columnmapping">
						<option value="">-- Column Mapping --</option>
						@foreach($excel_columns as $key => $excel_column)
						<option value="{{ $key }}">{{ $excel_column }}</option>
						@endforeach                            
					</select>
				</div>
				<div class="mb-3 col-lg-6 col-xl-1 col-12 mb-0" style="display: none;">
					<label class="form-label" for="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][columns][0][remarks]">VAT %</label>
					<input type="text" id="template_columns_{{ $sheet_no }}_{{ $sheet_file_no }}_columns_0_remarks" name="template_columns[{{ $sheet_no }}][{{ $sheet_file_no }}][columns][0][remarks]" class="form-control remarks" placeholder="0" data-name="remarks" value="-" />
				</div>                        
				<div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
					<button class="btn btn-label-danger mt-4" type="button" data-repeater-delete>
						<i class="bx bx-x me-1"></i>
						<span class="align-middle">Delete</span>
					</button>
				</div>
			</div>
			<hr>
		</div>
	</div>
	  
	<div class="mb-0">
		<button class="btn btn-primary btn_repeater" type="button" data-column_name data-repeater-create>
			<i class="bx bx-plus me-1"></i>
			<span class="align-middle">Add</span>
		</button>
	</div>
	<hr class="border-dashed">
</div>