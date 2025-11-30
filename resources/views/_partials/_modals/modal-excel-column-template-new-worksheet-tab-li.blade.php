<li class="nav-item {{ $standard ? 'standard' : '' }}" id="nav-item-{{ $file_no }}-{{ $sheet_tab_no }}">
  <button type="button" id="btn-worksheet-tab-{{ $file_no }}-{{ $sheet_tab_no }}" class="btn-worksheet-tab nav-link {{ isset($activeSheet) ? (($activeSheet) ? 'active' : '') : 'active' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-worksheet-tab-{{ $file_no }}-{{ $sheet_tab_no }}" aria-controls="navs-worksheet-tab-{{ $file_no }}-{{ $sheet_tab_no }}" aria-selected="true">{{ 'Sheet ' . ($sheet_tab_no + 1) }}  
  	@if($sheet_tab_no > 0)
  	<i class="bx bx-x ms-1 text-danger btn-delete-worksheet-tab" data-file_no="{{ $file_no }}" data-sheet_tab_no="{{ $sheet_tab_no }}"></i> 
  	@endif 	
  </button>
</li>