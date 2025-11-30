@if(count($result) > 0)
	@php	
		$dda_taskdate="";	
		$dda_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
			return strtolower($taskdate->task_name) == 'duty deferment account';
		});  
		if(count($dda_taskdates) > 0) 
			$dda_taskdate = $dda_taskdates->first()->task_date;
	@endphp

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
		{{--@include('_partials/_content/_tasks/no-tasks-lazy')--}}
		@if(!isset($morepage))
			@include('_partials/_content/_vatreturn/file-single-tasks-lazy')
		@endif
	@else
		@include('_partials/_content/_vatreturn/file-single-tasks-lazy')
	@endif
@endif			