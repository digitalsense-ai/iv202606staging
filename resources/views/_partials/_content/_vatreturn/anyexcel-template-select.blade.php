<select id="anyexcel-template-{{ $file_type }}-{{ $vat_reg_id }}" name="anyexcel_template_{{ $file_type }}_{{ $vat_reg_id }}" class="form-select anyexcel-template" required data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}" {{ ($vatreg->status == 6 || !$vatregmain_status || $vatreg->is_disregard) ? 'disabled=disabled' : '' }}>
	<optgroup label="-- Select Template --">
		<option value="0" {{ ($anyexcel_templates) ? '' : 'selected=selected' }}>Default Template</option>
		<option value="any-excel">Any Excel</option>
		@if($anyexceltemplate)
			@foreach($anyexcel_templates as $key => $anyexcel_template)
				@if($anyexceltemplate->id == $anyexcel_template->id)
					<option value="{{ $anyexcel_template->id }}" selected="selected">{{ $anyexcel_template->name }}</option>
				@endif
			@endforeach
		@endif		
	</optgroup> 	
	<optgroup label="-- Create New Template --">  
		<option value="">-- New Template --</option>
	</optgroup>      
</select>