<div class="row my-4">
  <div class="card mb-4" style="flex: 1;">
    <h5 class="card-header">File Upload</h5>                   

    <div class="card-body card-company-file-upload"> 
      <!-- <div class="progress mb-4" style="display: none;">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="100" aria-valuemax="100">File uploads occur only on submit</div>
      </div> -->
      
      <!-- Multi  --> 
      <form method="post" action="{{ url('register/files/0') }}" enctype="multipart/form-data" class="dropzone needsclick" id="dropzone-multi-register-0" data-clientid="0">                   
        <div class="dz-message needsclick">
          Drop files here or click to upload
          <span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
        </div>
      </form> 
      <!--/ Multi  -->
    </div>
  </div>
</div>       
