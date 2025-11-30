@if(count($result) > 0)
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

	@php
		$file_type = 'pivs';
		$file_type_title = 'Postponed import VAT statement';
		
		$tasks_type = $file_type_title;	
	@endphp	

	@if(count($filetasks) == 0)
		{{--@include('_partials/_content/_tasks/no-tasks-lazy')	--}}
		@if(!isset($morepage))
			@include('_partials/_content/_vatreturn/file-single-tasks-lazy')
		@endif
	@else
		@include('_partials/_content/_vatreturn/file-single-tasks-lazy')
	@endif
@endif		