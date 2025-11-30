<!-- Large Modal -->
<div class="modal fade modal-file" id="bulkUploadModal-{{ $file_type }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Bulk Upload {{ $file_type_title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
                
        <div class="row">
          <!-- Basic  -->
          <div class="col-12" id="bulk-upload">

            <div class="progress mb-4" style="display: none;">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>

            <div class="card mb-4 card-bulk-upload">              
              <div class="card-body">                
                <!-- <div class="notification"></div> -->
                <form method="post" action="{{ url('bulk-upload') }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-bulk-upload" id="dropzone-bulk-upload">                                                    
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

        <div class="row">
          <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-notification-failed" aria-controls="navs-notification-failed" aria-selected="true">Failed</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-notification-success" aria-controls="navs-success" aria-selected="false">Success</button>
              </li>              
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="navs-notification-failed" role="tabpanel">
                
              </div>
              <div class="tab-pane fade" id="navs-notification-success" role="tabpanel">
                
              </div>              
            </div>
          </div>
        </div>
       
        <div class="row">
          <div class="col-12 text-end">
            <button type="button" class="btn btn-danger disabled btn-close bottom" disabled="disabled">Close</button>
          </div>
        </div>

       {{--
        @if($file_type != 'ci')  
        <div class="divider">
          <div class="divider-text p-0"></div>
        </div>

        <div class="row">
          <form id="formEmail-{{ $file_type }}" class="needs-validation m-0 formEmail" novalidate data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">
            @csrf
            @include('_partials/_modals/_email-form-lazy')                  
          </form>    
        </div>
        @endif
        --}}
      </div>      
    </div>
  </div>
</div>