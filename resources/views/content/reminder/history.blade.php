@extends('layouts/layoutMaster')

@section('title', 'Reminder - History')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<!-- <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" /> -->
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>

<!-- <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script> -->
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {      
    window.reminder_history_datas = [];    
    //var reminder_history_start = 1;

    var result = { 'reminders': {!! json_encode($reminders) !!}, 'authUser': {!! json_encode($authUser) !!} };    
    reminder_history_datas = drawDtTable(result, 'reminderhistory');
    
    {{--
    @foreach ($reminders as $reminder)  
      var reminder_users = [];
      var reminder_histories = [];
      @php  
        $reminderhistories = $reminder->reminderhistory; 
        $reminderusers = $reminder->reminderuser;
        $reminderactionoption = $reminder->reminderactionoption;
        $vatregmain = $reminder->vatregmain; 
        $client = $vatregmain->client;         
      @endphp  

      @foreach ($reminderhistories as $reminderhistory)         
        reminder_histories.push({
          'sent_at' : '{{ \Carbon\Carbon::parse($reminderhistory->sent_at)->format("d-m-Y") }}',                   
        });
      @endforeach 

      @foreach ($reminderusers as $reminderuser) 
        @php            
          $user = $reminderuser->user;             
          $dvuser = $user->dvuser;
          $role = $user->roles->first();                
        @endphp

        reminder_users.push({      
          'name' : '{{ $dvuser->firstname . " " . $dvuser->lastname }}',  
          'firstname' : '{{ $dvuser->firstname }}',
          'lastname' : '{{ $dvuser->lastname }}',
          'email' : '{{ $user->email }}',
          'role' : '{{ $role->name }}',
        });
      @endforeach 

      reminder_history_datas.push({         
            'id' : '{{ $reminder->id }}',
            'fake_id' : reminder_history_start,
            'title' : '{{ $reminder->title }}',     
            'users' : reminder_users,            
            'client' : '{{ $client->client_name }}',
            'vatregmain' : '{{ $vatregmain->country . " " . \Carbon\Carbon::parse($vatregmain->service_start)->format("M Y") . " " . $vatregmain->general_periods }}',
            'reminder_action' : '{{ $reminderactionoption->action_name }}',
            'schedule' : '{{ $reminder->schedule }}',
            'start_at' : '{{ \Carbon\Carbon::parse($reminder->start_at)->format("d-m-Y g:i A") }}',
            'status' : parseInt('{{ $reminder->status }}'),
            'histories' : reminder_histories, 
          });
      reminder_history_start = reminder_history_start + 1;     
    @endforeach 
    --}}   
});
</script>

<!-- <script src="{{asset('assets/js/intlTelInput.min.js')}}"></script> -->

<script src="{{asset('js/dv-reminder.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Reminder/</span> History</h4>

<!-- Bounce -->
<div class="sk-bounce sk-primary sk-center">
  <div class="sk-bounce-dot"></div>
  <div class="sk-bounce-dot"></div>
</div>

<!-- Reminder Task List Table -->
<div class="card" style="display: none;" id="reminder-history-card">  
  <div class="card-datatable table-responsive">
    <table class="datatables-reminder-history table border-top">
      <thead>        
        <tr>            
          <th></th>  
          <th>Country</th>
          <th>Title</th>
          <!-- <th>Action</th>
          <th>Schedule</th> -->
          <th>Period</th>
          <th>Start Date</th>
          <th>User</th>  
          <th>Vat Reg.</th>
          <th>Sent</th>
        </tr>
      </thead>
    </table>
  </div>

</div>

@endsection
