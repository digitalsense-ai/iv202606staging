<!-- Large Modal -->
<div class="modal fade modal-file" id="sendModal-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-d_id="{{ $i }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">{{ $modal_title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
                        
        <div class="row">
          <form id="formEmail-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" class="needs-validation m-0 formEmail" novalidate data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-d_id="{{ $i }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}">
            @csrf
            @include('_partials/_modals/_email-form-lazy')
          </form>    
        </div>
        
      </div>      
    </div>
  </div>
</div>