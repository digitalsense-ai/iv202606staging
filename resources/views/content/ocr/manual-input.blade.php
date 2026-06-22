@extends('layouts/layoutMaster')

@section('title', 'OCR Manual Input')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}">
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
<style>
  .manual-input-shell { min-height: calc(100vh - 11rem); }
  .manual-input-queue { max-height: calc(100vh - 15rem); overflow-y: auto; }
  .manual-input-queue .list-group-item { cursor: pointer; }
  .manual-input-queue .list-group-item.active .text-muted,
  .manual-input-queue .list-group-item.active .text-danger { color: rgba(255,255,255,.85) !important; }
  .manual-input-empty { min-height: 52vh; display: flex; align-items: center; justify-content: center; }
  #offcanvasAnalyzePdfData .offcanvas-body { height: auto !important; }
  #offcanvasAnalyzePdfData .offcanvas-body > .row { height: auto !important; min-height: 58vh; }
  #offcanvasAnalyzePdfData #docViewer { height: 58vh !important; border: 1px solid var(--bs-border-color); border-radius: .5rem; background: #f8f9fa; }
  #offcanvasAnalyzePdfData .form-salesinvoice-repeater { max-height: 180px; }
  .manual-input-note { min-height: 82px; }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <h4 class="mb-1">Manual Input</h4>
    <p class="text-muted mb-0">Handle OCR error items using the same correction offcanvas as Analyze PDF.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span id="manualInputCounter" class="badge bg-label-primary fs-6">0 / 0</span>
    <a href="{{ route('analyze.pdf.index') }}" class="btn btn-label-secondary">Back to Overview</a>
  </div>
</div>

<div class="row manual-input-shell g-3">
  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">Error Queue</h5>
          <small class="text-muted">Manual correction workload</small>
        </div>
        <button id="btnRefreshQueue" type="button" class="btn btn-sm btn-label-primary">
          <i class="bx bx-refresh"></i>
        </button>
      </div>
      <div class="list-group list-group-flush manual-input-queue" id="manualInputQueue"></div>
    </div>
  </div>

  <div class="col-12 col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 id="manualInputTitle" class="mb-0">Select an item</h5>
          <small id="manualInputSubtitle" class="text-muted">Open an item to correct it in the manual input offcanvas.</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <div class="btn-group" role="group" aria-label="Queue navigation">
            <button id="btnPreviousItem" type="button" class="btn btn-label-primary" disabled>
              <i class="bx bx-chevron-left"></i> Previous
            </button>
            <button id="btnNextItem" type="button" class="btn btn-label-primary" disabled>
              Next <i class="bx bx-chevron-right"></i>
            </button>
          </div>
          <button id="btnOpenCorrection" type="button" class="btn btn-primary" disabled>
            <i class="bx bx-edit"></i> Open Manual Input
          </button>
          <button id="btnDeleteItem" type="button" class="btn btn-label-danger ms-xl-3" disabled>
            <i class="bx bx-trash"></i> Delete
          </button>
        </div>
      </div>

      <div class="card-body">
        <div id="manualInputEmpty" class="manual-input-empty text-muted">
          Select an error item from the queue.
        </div>

        <div id="manualInputSummary" class="d-none">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="text-muted small">File</div>
                <div id="summaryFileName" class="fw-semibold text-break">-</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="border rounded p-3 h-100">
                <div class="text-muted small">Invoice No.</div>
                <div id="summaryInvoiceNo" class="fw-semibold">-</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="border rounded p-3 h-100">
                <div class="text-muted small">Client No.</div>
                <div id="summaryClientNo" class="fw-semibold">-</div>
              </div>
            </div>
            <div class="col-12">
              <div class="alert alert-danger mb-0 d-none" id="summaryError"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('_partials/_offcanvas/offcanvas-analyzepdf-form')
@endsection

@section('page-script')
<script>
(function () {
  const endpoints = {
    queue: "{{ route('analyze.pdf.manual-input.queue') }}",
    show: "{{ url('analyzepdf/manual-input') }}",
    clientLookup: "{{ route('analyze.pdf.manual-input.client-lookup') }}"
  };

  let queue = [];
  let current = null;
  let lookupTimer = null;

  const $queue = $('#manualInputQueue');
  const $counter = $('#manualInputCounter');
  const $empty = $('#manualInputEmpty');
  const $summary = $('#manualInputSummary');
  const $form = $('#addAnalyzePdfForm');
  const correctionCanvas = document.getElementById('offcanvasAnalyzePdfData');
  const correctionOffcanvas = correctionCanvas ? new bootstrap.Offcanvas(correctionCanvas) : null;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  prepareOffcanvasForm();

  function prepareOffcanvasForm() {
    $('#offcanvasAnalyzePdfDataLabel').text('Manual Input');
    $('#invoice_type option[value="multi-invoices"]').remove();
    $('#client_name').prop('readonly', true);

    if (!$('#clientLookupStatus').length) {
      $('#client_name').after('<small id="clientLookupStatus" class="text-muted">Client name is populated from the client database.</small>');
    }

    if (!$('#note').length) {
      const noteField = '<div class="mb-3 manual-input-note-wrap">' +
        '<label class="form-label" for="note">Note</label>' +
        '<textarea id="note" class="form-control manual-input-note" name="note" placeholder="Add internal correction note"></textarea>' +
      '</div>';
      $('.form-salesinvoice-repeater').closest('.mb-3').after(noteField);
    }

    if (!$('#btnForceSubmit').length) {
      const actions = '<div class="d-flex justify-content-between align-items-center gap-2 pt-2 manual-input-actions">' +
        '<button id="btnForceSubmit" type="button" class="btn btn-warning" disabled>Input</button>' +
        '<div class="d-flex gap-2">' +
          '<button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>' +
          '<button id="btnSaveManualInput" type="submit" class="btn btn-primary" disabled>Save</button>' +
        '</div>' +
      '</div>';
      $('#addAnalyzePdfForm > button[type="reset"]').remove();
      $('#addAnalyzePdfForm').append(actions);
    }

    const $repeater = $('.form-salesinvoice-repeater');
    if ($repeater.length && !$repeater.data('repeater-initialized')) {
      $repeater.repeater({
        show: function () { $(this).slideDown(); },
        hide: function (deleteElement) { $(this).slideUp(deleteElement); }
      });
      $repeater.data('repeater-initialized', true);
    }
  }

  function setBusy(isBusy) {
    $('#btnSaveManualInput, #btnForceSubmit, #btnDeleteItem, #btnPreviousItem, #btnNextItem, #btnOpenCorrection').prop('disabled', isBusy || !current);
  }

  function currentIndex() {
    return queue.findIndex(item => current && item.id === current.id);
  }

  function updateCounter(position, total) {
    if (!current || !total) {
      $counter.text('0 / 0');
      return;
    }
    $counter.text((position || (currentIndex() + 1)) + ' / ' + total);
  }

  function updateNav() {
    const idx = currentIndex();
    $('#btnPreviousItem').prop('disabled', !current || idx <= 0);
    $('#btnNextItem').prop('disabled', !current || idx < 0 || idx >= queue.length - 1);
    $('#btnDeleteItem, #btnOpenCorrection, #btnSaveManualInput, #btnForceSubmit').prop('disabled', !current);
  }

  function renderQueue() {
    $queue.empty();

    if (!queue.length) {
      $queue.append('<div class="p-3 text-muted">No manual input items.</div>');
      updateCounter(null, 0);
      return;
    }

    queue.forEach(item => {
      const active = current && current.id === item.id ? 'active' : '';
      const error = item.error ? '<div class="small text-danger text-truncate">' + escapeHtml(item.error) + '</div>' : '';
      $queue.append(
        '<button type="button" class="list-group-item list-group-item-action ' + active + '" data-id="' + item.id + '">' +
          '<div class="d-flex justify-content-between gap-2">' +
            '<span class="fw-semibold text-truncate">' + escapeHtml(item.file_name || ('#' + item.id)) + '</span>' +
            '<span class="badge bg-label-secondary">#' + item.id + '</span>' +
          '</div>' +
          '<div class="small text-muted">' + escapeHtml(item.invoice_type_name || '') + ' · ' + escapeHtml(item.invoice_no || '-') + '</div>' +
          error +
        '</button>'
      );
    });
  }

  function loadQueue(selectFirst = true) {
    return $.getJSON(endpoints.queue).then(response => {
      queue = response.items || [];
      renderQueue();

      if (selectFirst && queue.length) {
        return loadItem(queue[0].id, false);
      }

      if (!queue.length) {
        current = null;
        $summary.addClass('d-none');
        $empty.removeClass('d-none').text('No manual input items in the queue.');
        updateNav();
      }
    });
  }

  function loadItem(id, openCanvas = true) {
    setBusy(true);

    return $.getJSON(endpoints.show + '/' + id)
      .then(response => {
        current = response.item;
        fillForm(current);
        renderQueue();
        renderSummary(current);
        updateCounter(response.position, response.total);
        updateNav();
        $empty.addClass('d-none');
        $summary.removeClass('d-none');

        if (openCanvas && correctionOffcanvas) {
          correctionOffcanvas.show();
        }
      })
      .always(() => setBusy(false));
  }

  function renderSummary(item) {
    $('#manualInputTitle').text(item.file_name || ('OCR item #' + item.id));
    $('#manualInputSubtitle').text((item.error || item.validation_status || '').toString().replace(/\n/g, ' · '));
    $('#summaryFileName').text(item.file_name || '-');
    $('#summaryInvoiceNo').text(item.invoice_no || '-');
    $('#summaryClientNo').text(item.client_no || '-');

    if (item.error) {
      $('#summaryError').removeClass('d-none').html(escapeHtml(item.error).replace(/\n/g, '<br>'));
    } else {
      $('#summaryError').addClass('d-none').empty();
    }
  }

  function fillForm(item) {
    $('#analyzepdf_id').val(item.id);
    $('#analyzepdf_status').val(item.status || 'failed');
    $('#invoice_type').val(item.invoice_type || '');
    $('#client_no').val(item.client_no || '');
    $('#client_name').val(item.client_name || '');
    $('#invoice_date').val(item.invoice_date || '');
    $('#invoice_no').val(item.invoice_no || '');
    $('#credit_note').prop('checked', !!item.credit_note);
    $('#currency').val(item.currency || '');
    $('#exchange_currency').val(item.exchange_currency || '');
    $('#vat_rate').val(item.vat_rate || '');
    $('#exchange_rate').val(item.exchange_rate || '');
    $('#net_amount').val(item.net_amount || '');
    $('#exchange_net_amount').val(item.exchange_net_amount || '');
    $('#vat_amount').val(item.vat_amount || '');
    $('#exchange_vat_amount').val(item.exchange_vat_amount || '');
    $('#total_amount').val(item.total_amount || '');
    $('#exchange_total_amount').val(item.exchange_total_amount || '');
    $('#note').val(item.note || '');
    setSalesInvoiceRefs(item.related_sales_invoices || []);

    const pdfUrl = item.sas_url ? item.sas_url + '#zoom=page-width' : '';
    $('#docViewer').attr('src', pdfUrl);
  }

  function setSalesInvoiceRefs(values) {
    const $list = $('[data-repeater-list="sales-invoice"]');
    const $create = $('[data-repeater-create]');

    $list.find('[data-repeater-item]').not(':first').remove();
    $list.find('[data-repeater-item]:first .sales-invoice-ref-no').val(values[0] || '');

    values.slice(1).forEach(value => {
      $create.trigger('click');
      $list.find('[data-repeater-item]:last .sales-invoice-ref-no').val(value);
    });
  }

  function serializeForm() {
    const data = $form.serializeArray();
    const payload = {};

    data.forEach(item => {
      if (item.name && !item.name.startsWith('sales-invoice')) {
        payload[item.name] = item.value;
      }
    });

    payload.credit_note = $('#credit_note').is(':checked') ? 1 : 0;
    payload.related_sales_invoices = $('.sales-invoice-ref-no').map(function () {
      return ($(this).val() || '').trim();
    }).get().filter(Boolean);

    return payload;
  }

  function handleNextResponse(response) {
    return loadQueue(false).then(() => {
      if (response.next) {
        current = response.next;
        fillForm(current);
        renderSummary(current);
        renderQueue();
        updateCounter(response.position, response.total);
        updateNav();
      } else if (queue.length) {
        loadItem(queue[0].id, false);
      } else {
        current = null;
        $summary.addClass('d-none');
        $empty.removeClass('d-none').text('No manual input items in the queue.');
        updateCounter(null, 0);
        updateNav();
        if (correctionOffcanvas) correctionOffcanvas.hide();
      }
    });
  }

  function saveItem(force) {
    if (!current) return;

    setBusy(true);
    const url = endpoints.show + '/' + current.id + (force ? '/force-submit' : '/save');

    $.post(url, serializeForm())
      .then(response => handleNextResponse(response))
      .fail(xhr => {
        Swal.fire('Save failed', xhr.responseJSON?.message || 'Unable to save manual input.', 'error');
      })
      .always(() => setBusy(false));
  }

  function escapeHtml(value) {
    return $('<div>').text(value || '').html();
  }

  $('#btnRefreshQueue').on('click', () => loadQueue(true));
  $('#btnOpenCorrection').on('click', () => correctionOffcanvas && correctionOffcanvas.show());
  $queue.on('click', '[data-id]', function () { loadItem($(this).data('id'), true); });
  $('#btnPreviousItem').on('click', function () {
    const idx = currentIndex();
    if (idx > 0) loadItem(queue[idx - 1].id, true);
  });
  $('#btnNextItem').on('click', function () {
    const idx = currentIndex();
    if (idx >= 0 && idx < queue.length - 1) loadItem(queue[idx + 1].id, true);
  });
  $('#btnDeleteItem').on('click', function () {
    if (!current) return;

    Swal.fire({
      title: 'Delete this item?',
      text: 'The delete button is separated from navigation to avoid accidental clicks.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
      customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (!result.isConfirmed) return;

      setBusy(true);
      $.ajax({ url: endpoints.show + '/' + current.id, type: 'DELETE' })
        .then(response => handleNextResponse(response))
        .always(() => setBusy(false));
    });
  });

  $form.on('submit', function (event) {
    event.preventDefault();
    saveItem(false);
  });

  $(document).on('click', '#btnForceSubmit', function () {
    Swal.fire({
      title: 'Force input?',
      text: 'This submits the item even if validation requirements are not fully met.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Input anyway',
      customClass: { confirmButton: 'btn btn-warning me-2', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) saveItem(true);
    });
  });

  $(document).on('input', '#client_no', function () {
    clearTimeout(lookupTimer);
    const clientNo = $(this).val();
    $('#clientLookupStatus').text('Looking up client...');

    lookupTimer = setTimeout(() => {
      $.getJSON(endpoints.clientLookup, { client_no: clientNo })
        .then(response => {
          if (response.client) {
            $('#client_name').val(response.client.name || '');
            $('#clientLookupStatus').text('Client found in database.');
          } else {
            $('#client_name').val('');
            $('#clientLookupStatus').text('Client not found. Force Input may be used if onboarding is pending.');
          }
        });
    }, 350);
  });

  loadQueue(true);
})();
</script>
@endsection
