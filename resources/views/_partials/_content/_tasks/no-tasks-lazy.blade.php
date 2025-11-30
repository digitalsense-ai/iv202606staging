<!-- No Tasks -->
<div class="container-xxl container-p-y">
<div class="misc-wrapper text-center">    
  <div class="mt-5">
    <img src="{{asset('assets/img/illustrations/girl-doing-yoga-light.png')}}" alt="page-misc-not-authorized-light" width="450" class="img-fluid" data-app-light-img="illustrations/girl-doing-yoga-light.png" data-app-dark-img="illustrations/girl-doing-yoga-dark.png">
  </div>
  <h1 class="mb-2 mx-2">Hooray!!</h1>
  <p class="mb-4 mx-2">You don´t have any {{ isset($tasks_type) ? $tasks_type : 'pending' }} tasks</p>        
</div>
</div>
<!-- No Tasks -->