@php	
	$sheet_tab_no = isset($sheet_tab_no) ? $sheet_tab_no : 0;	
@endphp
<div class="card shadow-none worksheet-tab" id="worksheet-tab-{{ $file_no }}-{{ $sheet_tab_no }}" data-file_no="{{ $file_no }}" data-sheet_tab_no="{{ $sheet_tab_no }}">  
	<div class="card-header border-bottom">
	    <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
			@include('_partials/_modals/modal-excel-column-template-new-worksheet-tab-li')   
			
			<li class="nav-item add-worksheet-tab">
				<button type="button" id="btn-add-worksheet-tab-{{ $file_no }}" class="btn-add-worksheet-tab nav-link text-primary" data-file_no="{{ $file_no }}" data-sheet_tab_no="{{ $sheet_tab_no }}">+ Add Sheet</button>
			</li>
			
	    </ul>
  	</div>    
	<div class="tab-content">
		@include('_partials/_modals/modal-excel-column-template-new-worksheet-tab-content')    
	</div>
</div>