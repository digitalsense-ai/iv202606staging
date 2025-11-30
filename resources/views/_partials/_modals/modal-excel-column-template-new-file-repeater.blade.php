@php
	$file_no = isset($file_no) ? $file_no : 0;	
	$standard = isset($standard) ? $standard : true;
@endphp
<div class="file-repeater {{ $standard ? 'standard' : '' }}" id="file-repeater-{{ $file_no }}">	
	<div class="row">
		<div class="mb-0 col-lg-1 col-xl-1 col-1 py-3">
			<label class="form-label file-name"><b> File {{ ($file_no + 1) }}: </b></label>			
	    </div>

	    <div class="mb-0 col-lg-11 col-xl-11 col-11">
	    	@include('_partials/_modals/modal-excel-column-template-new-worksheet-tab')   	
	    </div>
	</div>

	
	<hr class="border-dashed">
</div>