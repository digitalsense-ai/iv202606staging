<div class="card-datatable table-responsive mb-3">
  <table class="datatables-contacts table border-top">
    <thead>        
      <tr>          
        <th>ID</th>              
        <th>User</th>         
        <th>Telephone</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
  </table>
</div>

<!-- Offcanvas to add new user -->
@php
  $user_contact_tab = 1;
@endphp
@include('_partials/_offcanvas/offcanvas-user-form')