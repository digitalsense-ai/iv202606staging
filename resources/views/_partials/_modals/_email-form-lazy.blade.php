<div class="accordion m-0 accordion-header-primary accordion-style-email-form" id="accordionStyleEmailForm-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}">
  @php    
    $send_to_title = "Send " . $file_type_title . " email to";
    $send_to_info = "The client user who will receive " . $file_type_title . " email.";
  @endphp
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionStyleEmailForm-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}-SendTo" aria-expanded="false">
        <h3 class="mb-0">Send Email<p class="fs-6 fw-normal mb-0">{{ $send_to_info }}</p></h3>        
      </button>
    </h2>

    <div id="accordionStyleEmailForm-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}-SendTo" class="accordion-collapse collapse">
      <div class="accordion-body">
        <div class="col-sm-12" id="show-to-email-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}">
          <input type="hidden" name="file_type" id="file-type-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ $file_type }}">
          <input type="hidden" name="file_type_title" id="file-type-title-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ $file_type_title }}">
          <input type="hidden" name="vat_reg_id" id="vat-reg-id-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ $vat_reg_id }}">
          <input type="hidden" name="no_docs" id="no-docs-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="0">
          <input type="hidden" name="month_year" id="month-year-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('m-Y') }}">

          @include('_partials/_modals/_email-to-lazy')
        </div>
      </div>
    </div>
  </div>

  <div class="divider">
    <div class="divider-text p-0"></div>
  </div>

  @php    
    $send_to_title = "Send " . $file_type_title . " copy to";
    $send_to_info = "The client user who will receive " . $file_type_title . " copy.";
  @endphp 
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionStyleEmailForm-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}-SendCopy" aria-expanded="false">
        <h3 class="mb-0">Send copy<p class="fs-6 fw-normal mb-0">{{ $send_to_info }}</p></h3>
      </button>      
    </h2>
    <div id="accordionStyleEmailForm-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}-SendCopy" class="accordion-collapse collapse">
      <div class="accordion-body">
        <div class="col-sm-12" id="show-copy-email-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}">
          <input type="hidden" name="file_type" id="file-type-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ $file_type }}">
          <input type="hidden" name="file_type_title" id="file-type-title-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ $file_type_title }}">
          <input type="hidden" name="vat_reg_id" id="vat-reg-id-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ $vat_reg_id }}">
          <input type="hidden" name="no_docs" id="no-docs-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="0">
          <input type="hidden" name="month_year" id="month-year-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" value="{{ \Carbon\Carbon::parse($vatreg->service_start)->addMonth($i)->format('m-Y') }}">

          @include('_partials/_modals/_email-cc-lazy')
        </div>
      </div>
    </div>
  </div>
</div>

<div class="col-sm-12" id="show-to-email-comment-{{ $file_type }}-{{ $vat_reg_id }}-{{ $i }}" style="display: none;">
  @include('_partials/_modals/_email-comment-lazy')
</div>

<div class="divider">
  <div class="divider-text p-0"></div>
</div>

@include('_partials/_modals/_email-send-lazy')        