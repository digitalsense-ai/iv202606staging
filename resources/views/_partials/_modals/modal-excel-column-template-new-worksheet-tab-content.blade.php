<!-- {{ 'Sheet ' . ($sheet_tab_no +1) }} -->
<div class="tab-pane fade {{ isset($activeSheet) ? (($activeSheet) ? 'show active' : '') : 'show active' }}" id="navs-worksheet-tab-{{ $file_no }}-{{ $sheet_tab_no }}" role="tabpanel">	
	<div class="row-repeater">
  		@include('_partials/_modals/modal-excel-column-template-new-worksheet-tab-content-header')
  	</div>
</div>
<!--/ {{ 'Sheet ' . ($sheet_tab_no +1) }} -->