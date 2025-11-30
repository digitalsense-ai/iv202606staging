@if(isset($sheetName))
<li class="nav-item" id="nav-item-{{ ($add_tabs) ? 0 : $vat_reg_id }}-{{ $sheet_no }}">
  <button type="button" id="btn-worksheet-tab-{{ ($add_tabs) ? 0 : $vat_reg_id }}-{{ $sheet_no }}" class="btn-worksheet-tab nav-link {{ isset($activeSheet) ? (($activeSheet) ? 'active' : '') : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-worksheet-tab-{{ ($add_tabs) ? 0 : $vat_reg_id }}-{{ $sheet_no }}" aria-controls="navs-worksheet-tab-{{ ($add_tabs) ? 0 : $vat_reg_id }}-{{ $sheet_no }}" aria-selected="true">{{ $sheetName }}
  	<!-- DON'T DELETE -->
  	{{--	
  	@if($add_tabs)
  		<i class="bx bx-x ms-1 text-danger btn-tab-delete" data-sheet_no="{{ $sheet_no }}"></i>
  	@endif	
  	--}}
  </button>
</li>
@endif