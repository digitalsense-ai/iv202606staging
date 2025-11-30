<div data-repeater-item>
	<div class="row">
		<div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
			<label class="form-label" for="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_column">Column</label>
			<select id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_column" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns][{{ $row_no }}][column]" class="form-select columnname" required data-name="column">
				<option value="">-- Column --</option>
				<option value="A" {{ isset($column) ? (($column == 'A') ? 'selected' : '') : '' }}>Column A</option>
				<option value="B" {{ isset($column) ? (($column == 'B') ? 'selected' : '') : '' }}>Column B</option>
				<option value="C" {{ isset($column) ? (($column == 'C') ? 'selected' : '') : '' }}>Column C</option>
				<option value="D" {{ isset($column) ? (($column == 'D') ? 'selected' : '') : '' }}>Column D</option>
				<option value="E" {{ isset($column) ? (($column == 'E') ? 'selected' : '') : '' }}>Column E</option>
				<option value="F" {{ isset($column) ? (($column == 'F') ? 'selected' : '') : '' }}>Column F</option>
				<option value="G" {{ isset($column) ? (($column == 'G') ? 'selected' : '') : '' }}>Column G</option>
				<option value="H" {{ isset($column) ? (($column == 'H') ? 'selected' : '') : '' }}>Column H</option>
				<option value="I" {{ isset($column) ? (($column == 'I') ? 'selected' : '') : '' }}>Column I</option>
				<option value="J" {{ isset($column) ? (($column == 'J') ? 'selected' : '') : '' }}>Column J</option>
				<option value="K" {{ isset($column) ? (($column == 'K') ? 'selected' : '') : '' }}>Column K</option>
				<option value="L" {{ isset($column) ? (($column == 'L') ? 'selected' : '') : '' }}>Column L</option>
				<option value="M" {{ isset($column) ? (($column == 'M') ? 'selected' : '') : '' }}>Column M</option>
				<option value="N" {{ isset($column) ? (($column == 'N') ? 'selected' : '') : '' }}>Column N</option>
				<option value="O" {{ isset($column) ? (($column == 'O') ? 'selected' : '') : '' }}>Column O</option>
				<option value="P" {{ isset($column) ? (($column == 'P') ? 'selected' : '') : '' }}>Column P</option>
				<option value="Q" {{ isset($column) ? (($column == 'Q') ? 'selected' : '') : '' }}>Column Q</option>
				<option value="R" {{ isset($column) ? (($column == 'R') ? 'selected' : '') : '' }}>Column R</option>
				<option value="S" {{ isset($column) ? (($column == 'S') ? 'selected' : '') : '' }}>Column S</option>
				<option value="T" {{ isset($column) ? (($column == 'T') ? 'selected' : '') : '' }}>Column T</option>
				<option value="U" {{ isset($column) ? (($column == 'U') ? 'selected' : '') : '' }}>Column U</option>
				<option value="V" {{ isset($column) ? (($column == 'V') ? 'selected' : '') : '' }}>Column V</option>
				<option value="W" {{ isset($column) ? (($column == 'W') ? 'selected' : '') : '' }}>Column W</option>
				<option value="X" {{ isset($column) ? (($column == 'X') ? 'selected' : '') : '' }}>Column X</option>
				<option value="Y" {{ isset($column) ? (($column == 'Y') ? 'selected' : '') : '' }}>Column Y</option>
				<option value="Z" {{ isset($column) ? (($column == 'Z') ? 'selected' : '') : '' }}>Column Z</option>
			</select>					
		</div>
		<div class="mb-3 col-lg-6 col-xl-4 col-12 mb-0">
			<label class="form-label" for="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_columnmapping">System Column</label>
			<select id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_columnmapping" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns][{{ $row_no }}][columnmapping]" class="form-select system-excel-column" required data-name="columnmapping">
				<option value="">-- Column Mapping --</option>
				@foreach($excel_columns as $key => $excel_column)
				<option value="{{ $key }}" {{ isset($columnmapping) ? (($columnmapping == $key) ? 'selected' : '') : '' }}>{{ $excel_column }}</option>
				@endforeach                            
			</select>
		</div>
		<div class="mb-3 col-lg-6 col-xl-1 col-12 mb-0" style="{{ isset($remarks) ? (($remarks == null) ? 'display: none;' : '') : 'display: none;' }}">
			<label class="form-label" for="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_remarks">VAT %</label>
			<input type="text" id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_remarks" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns][{{ $row_no }}][remarks]" class="form-control remarks" placeholder="0" data-name="remarks" value="{{ isset($remarks) ? $remarks : '' }}" />
		</div>                        
		<div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
			<button class="btn btn-label-danger mt-4" type="button" data-repeater-delete>
				<i class="bx bx-x me-1"></i>
				<span class="align-middle">Delete</span>
			</button>
		</div>
		<div class="mb-3 col-lg-12 col-xl-3 col-12 d-flex align-items-center mb-0">
			<button class="btn btn-label-secondary mt-4 btn-special" type="button" id="btn_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special" data-file_no="{{ $file_no }}" data-sheet_tab_no="{{ $sheet_tab_no }}" data-row_no="{{ $row_no }}" style="{{ isset($special) ? (($special['prefix'] == null) ? '' : 'display: none;') : '' }}">
				<i class="bx bx-plus me-1"></i>
				<span class="align-middle">Special Row</span>
			</button>
		</div>

		<!--Special Row-->
		<div class="mb-3 col-lg-6 col-xl-12 col-12 mb-0 special-row" id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_rows" style="{{ isset($special) ? (($special['prefix'] == null) ? 'display: none;' : '') : 'display: none;' }}">
			<div class="row">
				<!-- <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
					<label class="form-label w-100" for="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][0][special_before]">+/-</label>
					<label class="switch">
						<input type="checkbox" class="switch-input special-before" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][0][special_before]" value="-" />
						<span class="switch-toggle-slider">
							<span class="switch-on"></span>
							<span class="switch-off"></span>
						</span>
						<span class="switch-label">None</span>
	                </label>
				</div> -->

				<div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
					<label class="form-label d-none" for="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_prefix">Prefix</label>
					<label class="form-label" for="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_column_1">Column 1 with Prefix</label>

					<div class="input-group">
						<select id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_prefix" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns][{{ $row_no }}][special][prefix]" class="form-select special prefix" data-name="prefix">
							<option value="">-- Prefix --</option>
							<option value="0" {{ isset($special) ? (($special['prefix'] == '0') ? 'selected' : '') : '' }}>None</option>
							<option value="1" {{ isset($special) ? (($special['prefix'] == '1') ? 'selected' : '') : '' }}>Reverse</option>
						</select>

						<select id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_column_1" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns][{{ $row_no }}][special][column_1]" class="form-select special columnname-1" data-name="column_1">
							<option value="">-- Column --</option>
							<option value="A" {{ isset($special) ? (($special['column_1'] == 'A') ? 'selected' : '') : '' }}>Column A</option>
							<option value="B" {{ isset($special) ? (($special['column_1'] == 'B') ? 'selected' : '') : '' }}>Column B</option>
							<option value="C" {{ isset($special) ? (($special['column_1'] == 'C') ? 'selected' : '') : '' }}>Column C</option>
							<option value="D" {{ isset($special) ? (($special['column_1'] == 'D') ? 'selected' : '') : '' }}>Column D</option>
							<option value="E" {{ isset($special) ? (($special['column_1'] == 'E') ? 'selected' : '') : '' }}>Column E</option>
							<option value="F" {{ isset($special) ? (($special['column_1'] == 'F') ? 'selected' : '') : '' }}>Column F</option>
							<option value="G" {{ isset($special) ? (($special['column_1'] == 'G') ? 'selected' : '') : '' }}>Column G</option>
							<option value="H" {{ isset($special) ? (($special['column_1'] == 'H') ? 'selected' : '') : '' }}>Column H</option>
							<option value="I" {{ isset($special) ? (($special['column_1'] == 'I') ? 'selected' : '') : '' }}>Column I</option>
							<option value="J" {{ isset($special) ? (($special['column_1'] == 'J') ? 'selected' : '') : '' }}>Column J</option>
							<option value="K" {{ isset($special) ? (($special['column_1'] == 'K') ? 'selected' : '') : '' }}>Column K</option>
							<option value="L" {{ isset($special) ? (($special['column_1'] == 'L') ? 'selected' : '') : '' }}>Column L</option>
							<option value="M" {{ isset($special) ? (($special['column_1'] == 'M') ? 'selected' : '') : '' }}>Column M</option>
							<option value="N" {{ isset($special) ? (($special['column_1'] == 'N') ? 'selected' : '') : '' }}>Column N</option>
							<option value="O" {{ isset($special) ? (($special['column_1'] == 'O') ? 'selected' : '') : '' }}>Column O</option>
							<option value="P" {{ isset($special) ? (($special['column_1'] == 'P') ? 'selected' : '') : '' }}>Column P</option>
							<option value="Q" {{ isset($special) ? (($special['column_1'] == 'Q') ? 'selected' : '') : '' }}>Column Q</option>
							<option value="R" {{ isset($special) ? (($special['column_1'] == 'R') ? 'selected' : '') : '' }}>Column R</option>
							<option value="S" {{ isset($special) ? (($special['column_1'] == 'S') ? 'selected' : '') : '' }}>Column S</option>
							<option value="T" {{ isset($special) ? (($special['column_1'] == 'T') ? 'selected' : '') : '' }}>Column T</option>
							<option value="U" {{ isset($special) ? (($special['column_1'] == 'U') ? 'selected' : '') : '' }}>Column U</option>
							<option value="V" {{ isset($special) ? (($special['column_1'] == 'V') ? 'selected' : '') : '' }}>Column V</option>
							<option value="W" {{ isset($special) ? (($special['column_1'] == 'W') ? 'selected' : '') : '' }}>Column W</option>
							<option value="X" {{ isset($special) ? (($special['column_1'] == 'X') ? 'selected' : '') : '' }}>Column X</option>
							<option value="Y" {{ isset($special) ? (($special['column_1'] == 'Y') ? 'selected' : '') : '' }}>Column Y</option>
							<option value="Z" {{ isset($special) ? (($special['column_1'] == 'Z') ? 'selected' : '') : '' }}>Column Z</option>
						</select>
					</div>
				</div>

				<div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
					<label class="form-label" for="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_arithmetic">Arithmetic</label>
					<select id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_arithmetic" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns][{{ $row_no }}][special][arithmetic]" class="form-select special arithmetic" data-name="arithmetic">
						<option value="">-- Arithmetic --</option>
						<option value="+" {{ isset($special) ? (($special['arithmetic'] == '+') ? 'selected' : '') : '' }}>+</option>
						<option value="-" {{ isset($special) ? (($special['arithmetic'] == '-') ? 'selected' : '') : '' }}>-</option>
						<option value="*" {{ isset($special) ? (($special['arithmetic'] == '*') ? 'selected' : '') : '' }}>*</option>
						<option value="/" {{ isset($special) ? (($special['arithmetic'] == '/') ? 'selected' : '') : '' }}>/</option>						
					</select>
				</div>

				<div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
					<label class="form-label" for="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_column_2">Column 2</label>
					<select id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_column_2" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns][{{ $row_no }}][special][column_2]" class="form-select special columnname-2" data-name="column_2">
						<option value="">-- Column --</option>
						<option value="A" {{ isset($special) ? (($special['column_2'] == 'A') ? 'selected' : '') : '' }}>Column A</option>
							<option value="B" {{ isset($special) ? (($special['column_2'] == 'B') ? 'selected' : '') : '' }}>Column B</option>
							<option value="C" {{ isset($special) ? (($special['column_2'] == 'C') ? 'selected' : '') : '' }}>Column C</option>
							<option value="D" {{ isset($special) ? (($special['column_2'] == 'D') ? 'selected' : '') : '' }}>Column D</option>
							<option value="E" {{ isset($special) ? (($special['column_2'] == 'E') ? 'selected' : '') : '' }}>Column E</option>
							<option value="F" {{ isset($special) ? (($special['column_2'] == 'F') ? 'selected' : '') : '' }}>Column F</option>
							<option value="G" {{ isset($special) ? (($special['column_2'] == 'G') ? 'selected' : '') : '' }}>Column G</option>
							<option value="H" {{ isset($special) ? (($special['column_2'] == 'H') ? 'selected' : '') : '' }}>Column H</option>
							<option value="I" {{ isset($special) ? (($special['column_2'] == 'I') ? 'selected' : '') : '' }}>Column I</option>
							<option value="J" {{ isset($special) ? (($special['column_2'] == 'J') ? 'selected' : '') : '' }}>Column J</option>
							<option value="K" {{ isset($special) ? (($special['column_2'] == 'K') ? 'selected' : '') : '' }}>Column K</option>
							<option value="L" {{ isset($special) ? (($special['column_2'] == 'L') ? 'selected' : '') : '' }}>Column L</option>
							<option value="M" {{ isset($special) ? (($special['column_2'] == 'M') ? 'selected' : '') : '' }}>Column M</option>
							<option value="N" {{ isset($special) ? (($special['column_2'] == 'N') ? 'selected' : '') : '' }}>Column N</option>
							<option value="O" {{ isset($special) ? (($special['column_2'] == 'O') ? 'selected' : '') : '' }}>Column O</option>
							<option value="P" {{ isset($special) ? (($special['column_2'] == 'P') ? 'selected' : '') : '' }}>Column P</option>
							<option value="Q" {{ isset($special) ? (($special['column_2'] == 'Q') ? 'selected' : '') : '' }}>Column Q</option>
							<option value="R" {{ isset($special) ? (($special['column_2'] == 'R') ? 'selected' : '') : '' }}>Column R</option>
							<option value="S" {{ isset($special) ? (($special['column_2'] == 'S') ? 'selected' : '') : '' }}>Column S</option>
							<option value="T" {{ isset($special) ? (($special['column_2'] == 'T') ? 'selected' : '') : '' }}>Column T</option>
							<option value="U" {{ isset($special) ? (($special['column_2'] == 'U') ? 'selected' : '') : '' }}>Column U</option>
							<option value="V" {{ isset($special) ? (($special['column_2'] == 'V') ? 'selected' : '') : '' }}>Column V</option>
							<option value="W" {{ isset($special) ? (($special['column_2'] == 'W') ? 'selected' : '') : '' }}>Column W</option>
							<option value="X" {{ isset($special) ? (($special['column_2'] == 'X') ? 'selected' : '') : '' }}>Column X</option>
							<option value="Y" {{ isset($special) ? (($special['column_2'] == 'Y') ? 'selected' : '') : '' }}>Column Y</option>
							<option value="Z" {{ isset($special) ? (($special['column_2'] == 'Z') ? 'selected' : '') : '' }}>Column Z</option>
					</select>
				</div>

				<div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
					<label class="form-label" for="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_columnmapping">System Column</label>
					<select id="template_columns_{{ $file_no }}_{{ $sheet_tab_no }}_columns_{{ $row_no }}_special_columnmapping" name="template_columns[{{ $file_no }}][{{ $sheet_tab_no }}][columns][{{ $row_no }}][special][columnmapping]" class="form-select special system-excel-column" data-name="columnmapping">
						<option value="">-- Column Mapping --</option>
						@foreach($excel_columns as $key => $excel_column)
						<option value="{{ $key }}" {{ isset($special) ? (($special['columnmapping'] == $key) ? 'selected' : '') : '' }}>{{ $excel_column }}</option>
						@endforeach                            
					</select>
				</div>

				<div class="mb-3 col-lg-6 col-xl-1 col-12 mb-0">
					<button class="btn btn-label-danger mt-4 btn-delete-special" type="button" data-file_no="{{ $file_no }}" data-sheet_tab_no="{{ $sheet_tab_no }}" data-row_no="{{ $row_no }}">
						<i class="bx bx-x me-1"></i>
						<span class="align-middle"></span>
					</button>
				</div>
			</div>
		</div>
		<!--Special Row-->
	</div>

	
	<hr>
</div>