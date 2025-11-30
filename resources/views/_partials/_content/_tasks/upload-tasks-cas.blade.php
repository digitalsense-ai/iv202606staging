@if(count($result) > 0)
	@php
		$filtered_result = $result->filter(function ($vatreg, $key) {
		    return $vatreg->country == 'GB';
		});	
	@endphp

	@php	
		$cas_taskdate="";	
		$cas_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
			return strtolower($taskdate->task_name) == 'cash account statement';
		});  	        
		if(count($cas_taskdates) > 0) 
			$cas_taskdate = $cas_taskdates->first()->task_date;
	@endphp

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
		{{--@include('_partials/_content/_tasks/no-tasks-lazy')--}}
		@if(!isset($morepage))
			@include('_partials/_content/_vatreturn/file-single-tasks-lazy')
		@endif
	@else
		@include('_partials/_content/_vatreturn/file-single-tasks-lazy')	
	@endif
@endif		