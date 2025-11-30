<li class="nav-item" id="nav-item-{{ $sheet_index }}">
  <button type="button" id="btn-anyexcel-template-preview-tab-{{ $sheet_index }}" class="btn-anyexcel-template-preview-tab nav-link {{ isset($active_sheet) ? (($active_sheet) ? 'active' : '') : 'active' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-anyexcel-template-preview-tab-{{ $sheet_index }}" aria-controls="navs-anyexcel-template-preview-tab-{{ $sheet_index }}" aria-selected="true">{{ $sheet_title }}      
  </button>
</li>