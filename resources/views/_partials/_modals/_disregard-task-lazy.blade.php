<div class="row">
  <div class="col-12 text-center" id="disregard-task-row-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}">
    <label class="form-check-label" for="disregard-task">
      There is no file to upload,       
      <a href="javascript:void(0);" class="disregard-task" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}" data-d_id="{{ $i }}" data-file_type="{{ $file_type }}" data-file_type_title="{{ $file_type_title }}" data-modal_type="{{ isset($modal_type) ? $modal_type : '' }}" data-month_year="{{ \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('m-Y') }}">disregard this task</a>
    </label>    
  </div>
</div>