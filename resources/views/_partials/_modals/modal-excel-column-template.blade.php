<!-- Excel Column Template Modal -->
<div class="modal fade excel-column-template-modal" id="excelColumnTemplateModal-{{ $file_type }}-{{ $vat_reg_id }}" tabindex="-1" aria-hidden="true"  data-bs-backdrop="static" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Excel Column Template</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body text-start">

        <div class="row">          
          <div class="col-12" id="excel_column_template">            
            <div class="card mb-4 card-excel-column-template">              
              <div class="card-body">                                
                <form method="post" action="{{ url('excel-column-mapping-template/' . $vat_reg_id) }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-excel-column-template" id="dropzone-excel-column-template-{{ $file_type }}-{{ $vat_reg_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}">
                  <input type="hidden" id="original-file-{{ $file_type }}-{{ $vat_reg_id }}" name="original_file" value="1" />
                  <input type="hidden" id="file-type-{{ $file_type }}-{{ $vat_reg_id }}" name="file_type" value="{{ $file_type }}" />
                  <div class="dz-message needsclick">                    
                    Drop files here or click to upload
                    <span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
                  </div>
                </form>                
              </div>
            </div>
          </div>          
        </div>

        <div class="row" id="column_mapping"> 
          <!-- Bounce -->
          <div class="sk-bounce sk-primary sk-center" style="display: none;">
            <div class="sk-bounce-dot"></div>
            <div class="sk-bounce-dot"></div>
          </div>     

          <!-- Form Repeater -->
          <div class="col-12 card-column-mapping" style="display: none;">
            <div class="card">
              <h5 class="card-header">Column Mapping</h5>
              <div class="card-body">
                <form class="form-repeater" id="formRepeater-{{ $file_type }}-{{ $vat_reg_id }}" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}">
                  @csrf
                  @php
                    $add_tabs = false;
                  @endphp
                  <div class="row">
                      <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
                        <label class="form-label" for="template_name">Template Name</label>
                        <input type="text" id="template_name" name="template_name" class="form-control" placeholder="Template 1" required/>                        
                      </div>                      
                  </div>
                  <hr>

                  @include('_partials/_modals/modal-excel-column-template-worksheet-tabs')                  
                  
                  <div class="text-end">
                    <button type="submit" class="btn btn-danger disabled btn-save-template" id="btn-save-template-{{ $file_type }}-{{ $vat_reg_id }}" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}">Save Template</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- /Form Repeater -->          
        </div>

      </div>

    </div>
  </div>
</div>
<!--/ Excel Column Template Modal -->