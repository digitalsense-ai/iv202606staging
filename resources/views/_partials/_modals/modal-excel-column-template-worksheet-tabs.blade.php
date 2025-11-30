<div class="card shadow-none worksheet-tabs" id="worksheet-tabs-{{ ($add_tabs) ? 0 : $vat_reg_id }}">  
	<div class="card-header border-bottom">
	    <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
			@include('_partials/_modals/modal-excel-column-template-worksheet-tab-li')   

			<!-- DON'T DELETE -->
			{{--
			@if($add_tabs)
			<li class="nav-item add-new-tab">
				<button type="button" id="btn-worksheet-add-tab" class="btn-worksheet-tab nav-link active text-primary">+ Add Sheet</button>
			</li>
			@endif 
			--}}  
	    </ul>
  	</div>    
	<div class="tab-content">
		@include('_partials/_modals/modal-excel-column-template-worksheet-tab-content')    
	</div>
</div>