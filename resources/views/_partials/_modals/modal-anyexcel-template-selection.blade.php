<!-- AnyExcel Template Selection Modal -->
<div class="modal fade anyexcel-template-selection-modal" id="anyexcelTemplateSelectionModal-{{ $file_type }}-{{ $vat_reg_id }}" tabindex="-1" aria-hidden="true"  data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Choose Excel Template</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body text-start">

        <div class="row">          
          <div class="col-12" id="anyexcel-template-selection">            
            @if($anyexcel_templates)
              <div class="switches-stacked">
              @foreach($anyexcel_templates as $key => $anyexcel_template)
                <label class="switch">
                  <input type="radio" class="switch-input" name="anyexcel_template_selection_{{ $file_type }}_{{ $vat_reg_id }}" value="{{ $anyexcel_template->id }}" />
                  <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                  </span>
                  <span class="switch-label">{{ $anyexcel_template->name }}</span>
                </label>                  
              @endforeach
              </div>
            @endif    
          </div>          
        </div>

        <div class="row">          
          <div class="col-12 text-end">
            <button class="btn btn-danger disabled btn-anyexcel-template-select {{ isset($file_type) ? $file_type : '' }}" data-vat_reg_id="{{ $vat_reg_id }}" data-file_type="{{ $file_type }}" disabled>             
              <span class="align-middle">Select</span>
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>
<!--/ AnyExcel Template Modal -->