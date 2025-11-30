@extends('layouts/layoutMaster')

@section('title', 'Task Dates - List')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<!-- <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>

<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.taskdate_datas = [];    
    //var taskdate_start = 1;
   
    var result = { 'taskdates': {!! json_encode($taskdates) !!} };    
    taskdate_datas = drawDtTable(result, 'taskdate');

    {{--
    @foreach ($taskdates as $taskdate)
      taskdate_datas.push({         
        'id' : '{{ $taskdate->id }}',
        'fake_id' : taskdate_start,
        'taskname' : '{{ $taskdate->task_name }}',                        
        'task_date' :'{{ $taskdate->task_date }}',
        'task_description' :'{{ $taskdate->task_description}}',
        'status' : parseInt('{{ $taskdate->status }}')            
      });
      taskdate_start = taskdate_start + 1;     
    @endforeach
    --}}    
});
</script>
<script src="{{asset('js/dv-taskdate.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Task Dates/</span> List</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<!-- Task Date List Table -->
<div class="card" style="display: none;" id="taskdate-card">  
  <div class="card-datatable table-responsive">
    <table class="datatables-taskdate table border-top">
      <thead>        
        <tr>            
          <th></th>  
          <th>Title</th>          
          <th>Task Date/Desription</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas to add new task date -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddTaskDate" aria-labelledby="offcanvasAddTaskDateLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasTaskNameLabel" class="offcanvas-title">Add Task Date</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
      <form class="pt-0" id="addNewTaskDateForm">        
        <input type="hidden" name="taskdate_id" id="taskdate_id" value="">  
        <div class="mb-3">
          <label class="form-label" for="task_name">Task Name</label>          
          <select id="task_name" class="form-select" name="task_name" required> 
            <option value="">Select Task Name</option>              
            <option value="Postponed Import VAT Statement">Postponed Import VAT Statement</option>
            <option value="Cash Account Statement">Cash Account Statement</option>
            <option value="Duty Deferment Account">Duty Deferment Account</option>
            <option value="Statistics Excel">Statistics Excel</option>
            <optgroup label="Rule Trigger">
              <option value="VAT Reg. Folder">VAT Reg. Folder</option>
              <option value="Client View">Client View</option>
              <option value="Api Scheduler">Api Scheduler</option>
              <option value="Exchange Rate">Exchange Rate</option>
              <option value="Reminder Scheduler">Reminder Scheduler</option>
           </optgroup>
          </select> 
        </div>                         
        <div class="mb-3">
         <label for="task_date" class="form-label" id="lbl_taskdate">Task Date</label>
                <input type="text" class="form-control" placeholder="DD" id="task_date" name="task_date" required onkeypress="return false;" data-auto-apply="true" />
        </div> 
        <div class="mb-3">
          <label class="form-label" for="task_description" id="lbl_taskdescription">Description</label>          
          <input type="text" id="task_description" class="form-control" placeholder="Description" aria-label="Description" name="task_description" required value="" />  
        </div>                          
        <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>

</div>

@endsection
