<!-- Large Modal -->
<div class="modal fade modal-file" id="UploadModal-{{ $file_type }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Upload {{ $file_type_title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <div class="row">
          <!-- Basic  -->
          <div class="col-12" id="compliance-upload">
            <div class="progress custom mb-4" style="display: none;">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>

            <div class="card mb-4 card-compliance-upload">              
              <div class="card-body">                
                <div class="notification"></div>  
                <form method="post" action="{{ url('compliance') }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-compliance" id="dropzone-compliance">
                  @csrf                  
                  <div class="dz-message needsclick">
                    Drop files here or click to upload
                    <span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
                  </div>
                </form>                
              </div>
            </div>
          </div>
          <!-- /Basic  -->
        </div>
              
      </div>      
    </div>
  </div>
</div>