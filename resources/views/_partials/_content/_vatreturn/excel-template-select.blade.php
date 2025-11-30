<select id="excel-column-template-{{ $file_type }}-{{ $vat_reg_id }}" name="excel_column_template_{{ $file_type }}_{{ $vat_reg_id }}" class="form-select excel-column-template" required data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}">
	<optgroup label="-- Select Template --">
		<option value="0" {{ ($excel_column_templates) ? '' : 'selected=selected' }}>Default Template</option>
		<option value="any-excel" data-bs-toggle="modal" data-bs-target="#excelTemplateSelectionModal-{{ $file_type }}-{{ $vat_reg_id }}">Any Excel</option>
		@if($excelcolumntemplate)
			@foreach($excel_column_templates as $key => $excel_column_template)
				@if($excelcolumntemplate->id == $excel_column_template->id)
					<option value="{{ $excel_column_template->id }}" selected="selected">{{ $excel_column_template->name }}</option>
				@endif
			@endforeach
		@endif		
	</optgroup>         
</select>