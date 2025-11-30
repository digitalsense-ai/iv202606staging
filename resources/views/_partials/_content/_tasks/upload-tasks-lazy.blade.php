@if(count($result) > 0)
	<label class="float-end cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTaskFilter" aria-controls="offcanvasTaskFilter">
		<i class="bx bx-slider"></i>
	</label>

	<!-- NEW pending PIVS Tasks for GB - LAST of every month -->
	@include('_partials/_content/_tasks/upload-tasks-pivs')
	<!--/ NEW pending PIVS Tasks for GB - LAST of every month -->	

	<!-- NEW pending Cash Account Statement Tasks for GB - 1st of every month -->	
	@include('_partials/_content/_tasks/upload-tasks-cas')
	<!--/ NEW pending Cash Account Statement Tasks for GB - 1st of every month -->

	<!-- NEW pending Duty Deferment Account Tasks for NO - 1st of every month -->	
	@include('_partials/_content/_tasks/upload-tasks-dda')
	<!--/ NEW pending Duty Deferment Account Tasks for NO - 1st of every month -->
	
	{{--
	@php
		$filtered_result = $result->filter(function ($vatreg, $key) {
		    return $vatreg->country == 'GB';
		});
		$filetasks = $filtered_result;
	@endphp

	@php	
		$pivs_taskdate="";	
		$pivs_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
			return strtolower($taskdate->task_name) == 'postponed import vat statement';
		});
        if(count($pivs_taskdates) > 0) 
        	$pivs_taskdate = $pivs_taskdates->first()->task_date;       
	@endphp

	<!-- NEW pending PIVS Tasks for GB - 10th of every month -->	
	@php
		$file_type = 'pivs';
		$file_type_title = 'Postponed import VAT statement';
		
		$tasks_type = $file_type_title;	
	@endphp	

	@if(count($filetasks) == 0)
		@include('_partials/_content/_tasks/no-tasks-lazy')
	@else	
		@include('_partials/_content/_vatreturn/file-single-tasks-lazy')
	@endif		
	<!--/ NEW pending PIVS Tasks for GB - 10th of every month -->	

	@php	
		$cas_taskdate="";	
		$cas_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
			return strtolower($taskdate->task_name) == 'cash account statement';
		});  	        
		if(count($cas_taskdates) > 0) 
			$cas_taskdate = $cas_taskdates->first()->task_date;
	@endphp

	<!-- NEW pending Cash Account Statement Tasks for GB - 1st of every month -->	
	@php
		$filtered_result = $filtered_result->filter(function ($vatreg, $key) {
		    return $vatreg->vatregmain->cash_acc_stmt == 1;
		});
		$filetasks = $filtered_result;
	@endphp
	@php
		$file_type = 'cas';
		$file_type_title = 'Cash Account Statement';

		$tasks_type = $file_type_title;
	@endphp	

	@if(count($filetasks) == 0)
		@include('_partials/_content/_tasks/no-tasks-lazy')
	@else
		@include('_partials/_content/_vatreturn/file-single-tasks-lazy')	
	@endif	
	<!--/ NEW pending Cash Account Statement Tasks for GB - 1st of every month -->

	@php	
		$dda_taskdate="";	
		$dda_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
			return strtolower($taskdate->task_name) == 'duty deferment account';
		});  
		if(count($dda_taskdates) > 0) 
			$dda_taskdate = $dda_taskdates->first()->task_date;
	@endphp

	<!-- NEW pending Duty Deferment Account Tasks for NO - 1st of every month -->	
	@php
		$filtered_result = $result->filter(function ($vatreg, $key) {
		    return $vatreg->country == 'NO';
		});

		$filtered_result = $filtered_result->filter(function ($vatreg, $key) {
		    return $vatreg->vatregmain->duty_defer_acc == 1;
		});
		$filetasks = $filtered_result;
	@endphp
	@php
		$file_type = 'dda';
		$file_type_title = 'Duty Deferment Account';
		
		$tasks_type = $file_type_title;
	@endphp	

	@if(count($filetasks) == 0)
		@include('_partials/_content/_tasks/no-tasks-lazy')
	@else
		@include('_partials/_content/_vatreturn/file-single-tasks-lazy')
	@endif		
	<!--/ NEW pending Duty Deferment Account Tasks for NO - 1st of every month -->
	--}}
@endif

<!-- No API Connection - Excel Upload -->          
<div class="col-md">                 
  <h4 class="py-3 breadcrumb-wrapper mb-4" style="display: none;">
    <span class="text-muted fw-light">Upload VAT Return</span>
  </h4>
  
  <div class="accordion mt-0 accordion-header-primary" id="no_connection" style="display: none;">
    <!-- <div id="load-no-api-connection-list"></div> -->  
    @include('_partials/_content/_vatreturn/no-api-connection-list-lazy')              
  </div>
</div>          
<!--/ No API Connection - Excel Upload -->   	

@include('_partials/_offcanvas/offcanvas-task-filter')