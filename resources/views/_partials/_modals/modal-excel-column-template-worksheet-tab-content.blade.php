@if(isset($sheetName))
<!-- {{ $sheetName }} -->
<div class="tab-pane fade {{ isset($activeSheet) ? (($activeSheet) ? 'show active' : '') : '' }}" id="navs-worksheet-tab-{{ ($add_tabs) ? 0 : $vat_reg_id }}-{{ $sheet_no }}" role="tabpanel">
	<div class="row">
		<div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
			<label class="form-label" for="no_of_files_{{ $sheet_no }}">No. of files to be uploaded <span class="text-danger">(Grouped as single file)</span></label>
			<input type="text" id="no_of_files_{{ $sheet_no }}" name="no_of_files_{{ $sheet_no }}" class="form-control no-of-files w-px-50" placeholder="1" value="1" maxlength="2" required onkeypress="return isNumber(event)"/>
	    </div>
	</div>
	<hr class="border-dashed">
	<div class="file-repeater">
  		@include('_partials/_modals/modal-excel-column-template-repeater')
  	</div>
</div>
<!--/ {{ $sheetName }} -->
@endif