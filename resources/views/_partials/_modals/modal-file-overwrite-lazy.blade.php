<!-- Large Modal -->
<div class="modal fade modal-file" id="overwriteModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Overwrite {{ $file_type_title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <div class="row">
          <!-- Basic  -->
          <div class="col-12">
            <div class="card mb-4">             
              <div class="card-body">                
                <form method="post" action="{{ url('file/' . $vat_reg_id) }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-file" id="dropzone-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}-overwrite" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">       
                  <input type="hidden" name="file_type" id="file-type-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" value="{{ $file_type }}">
                  <input type="hidden" name="file_type_title" id="file-type-title-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" value="{{ $file_type_title }}">
                  <input type="hidden" name="month_year" id="month-year-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" value="{{ \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('m-Y') }}">
                  <div class="dz-message needsclick">
                    Drop file here or click to overwrite
                    <span class="note needsclick">(The overwritten file is stored in <strong>One-Drive</strong>.)</span>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- /Basic  -->
        </div>
       
        @if($file_type != 'ci')
        <div class="divider">
          <div class="divider-text p-0"></div>
        </div>

        <form id="formEmail-{{ $file_type }}-{{ $vat_reg_id }}-{{ $file->id }}" class="needs-validation m-0 formEmail" novalidate data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_id="{{ $file->id }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">
            @csrf
            @if($file_type == 'cas')  
              @include('_partials/_modals/_table-to-excel-form')
            @endif
            @include('_partials/_modals/_email-form-lazy')
        </form>
        @endif
      </div>      
    </div>
  </div>
</div>