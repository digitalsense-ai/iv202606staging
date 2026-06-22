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
  .manual-input-pdf-frame { width: 100%; height: 58vh; border: 1px solid var(--bs-border-color); border-radius: .5rem; background: #f8f9fa; }
  .manual-input-form { max-height: 58vh; overflow-y: auto; padding-right: .35rem; }
  .manual-input-note { min-height: 82px; }
  .manual-input-empty { min-height: 52vh; display: flex; align-items: center; justify-content: center; }
  .manual-input-salesinvoice-repeater { max-height: 180px; overflow-y: auto; }
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
    <p class="text-muted mb-0">Handle OCR error items without changing the existing Analyze PDF overview.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span id="manualInputCounter" class="badge bg-label-primary fs-6">0 / 0</span>
    <a href="{{ route('analyze.pdf.index') }}" class="btn btn-label-secondary">Back to Overview</a>
  </div>
</div>

<div class="row manual-input-shell g-3">
  <div class="col-12 col-xl-3">
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

  <div class="col-12 col-xl-9">
    <div class="card h-100">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 id="manualInputTitle" class="mb-0">Select an item</h5>
          <small id="manualInputSubtitle" class="text-muted">PDF and correction fields will appear here.</small>
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
          <button id="btnDeleteItem" type="button" class="btn btn-label-danger ms-xl-3" disabled>
            <i class="bx bx-trash"></i> Delete
          </button>
        </div>
      </div>

      <div class="card-body">
        <div id="manualInputEmpty" class="manual-input-empty text-muted">
          Select an error item from the queue.
        </div>

        <div id="manualInputDetail" class="row g-3 d-none">
          <div class="col-12 col-lg-8">
            <iframe id="manualPdfViewer" class="manual-input-pdf-frame"></iframe>
          </div>

          <div class="col-12 col-lg-4">
            <form id="manualInputForm" class="manual-input-form">
              @csrf
              <input type="hidden" name="id" id="manual_invoice_id">

              <div class="mb-3">
                <label class="form-label" for="invoice_type">Document Type</label>
                <select id="invoice_type" class="form-select" name="invoice_type" required>
                  <option value="">Select</option>
                  <option value="com">Commercial Invoice</option>
                  <option value="sales">Sales Invoice</option>
                </select>
                <small class="text-muted">Multiple-invoices is intentionally hidden on manual input.</small>
              </div>

              <div class="mb-3">
                <label class="form-label" for="client_no">Client No.</label>
                <input type="text" id="client_no" class="form-control" name="client_no" required>
              </div>

              <div class="mb-3">
                <label class="form-label" for="client_name">Client Name</label>
                <input type="text" id="client_name" class="form-control" name="client_name" readonly required>
                <small id="clientLookupStatus" class="text-muted">Client name is populated from the client database.</small>
              </div>

              <div class="mb-3">
                <label class="form-label" for="invoice_date">Invoice Date</label>
                <input type="text" id="invoice_date" class="form-control" name="invoice_date" placeholder="YYYY-MM-DD" required>
              </div>

              <div class="mb-3">
                <label class="form-label" for="invoice_no">Invoice No.</label>
                <input type="text" id="invoice_no" class="form-control" name="invoice_no" required>
              </div>

              <div class="form-check mb-3">
                <input type="checkbox" id="credit_note" class="form-check-input" name="credit_note" value="1">
                <label class="form-check-label" for="credit_note">Credit Note</label>
              </div>

              <div class="row">
                <div class="col-6 mb-3">
                  <label class="form-label" for="currency">Currency</label>
                  <select id="currency" class="form-select" name="currency" required>
                    <option value="">Select</option>
                    <option value="CHF">CHF</option>
                    <option value="DKK">DKK</option>
                    <option value="EUR">EUR</option>
                    <option value="NOK">NOK</option>
                    <option value="GBP">GBP</option>
                    <option value="PLN">PLN</option>
                    <option value="SEK">SEK</option>
                    <option value="USD">USD</option>
                  </select>
                </div>
                <div class="col-6 mb-3">
                  <label class="form-label" for="exchange_currency">Exchange Currency</label>
                  <select id="exchange_currency" class="form-select" name="exchange_currency">
                    <option value="">Select</option>
                    <option value="CHF">CHF</option>
                    <option value="DKK">DKK</option>
                    <option value="EUR">EUR</option>
                    <option value="NOK">NOK</option>
                    <option value="GBP">GBP</option>
                    <option value="PLN">PLN</option>
                    <option value="SEK">SEK</option>
                    <option value="USD">USD</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-3">
                  <label class="form-label" for="vat_rate">VAT %</label>
                  <input type="text" id="vat_rate" class="form-control" name="vat_rate">
                </div>
                <div class="col-6 mb-3">
                  <label class="form-label" for="exchange_rate">Exchange Rate</label>
                  <input type="text" id="exchange_rate" class="form-control" name="exchange_rate">
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-3">
                  <label class="form-label" for="net_amount">Net Amount</label>
                  <input type="text" id="net_amount" class="form-control" name="net_amount">
                </div>
                <div class="col-6 mb-3">
                  <label class="form-label" for="exchange_net_amount">Exchange Net</label>
                  <input type="text" id="exchange_net_amount" class="form-control" name="exchange_net_amount">
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-3">
                  <label class="form-label" for="vat_amount">VAT Amount</label>
                  <input type="text" id="vat_amount" class="form-control" name="vat_amount">
                </div>
                <div class="col-6 mb-3">
                  <label class="form-label" for="exchange_vat_amount">Exchange VAT</label>
                  <input type="text" id="exchange_vat_amount" class="form-control" name="exchange_vat_amount">
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-3">
                  <label class="form-label" for="total_amount">Total Amount</label>
                  <input type="text" id="total_amount" class="form-control" name="total_amount" required>
                </div>
                <div class="col-6 mb-3">
                  <label class="form-label" for="exchange_total_amount">Exchange Total</label>
                  <input type="text" id="exchange_total_amount" class="form-control" name="exchange_total_amount">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="sales_invoice_ref_no">Sales Invoice Ref. No.</label>
                <div class="form-salesinvoice-repeater manual-input-salesinvoice-repeater">
                  <button type="button" class="btn btn-label-warning mb-2" data-repeater-create>+Add</button>
                  <div data-repeater-list="sales-invoice">
                    <div data-repeater-item>
                      <div class="row">
                        <div class="mb-3 col-8 mb-0">
                          <input type="text" name="number" class="form-control sales-invoice-ref-no" placeholder="123456" />
                        </div>
                        <div class="mb-3 col-4 d-flex align-items-center mb-0">
                          <button type="button" class="btn btn-label-danger px-2" data-repeater-delete>
                            <i class="bx bx-x"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="note">Note</label>
                <textarea id="note" class="form-control manual-input-note" name="note" placeholder="Add internal correction note"></textarea>
              </div>

              <div class="d-flex justify-content-between align-items-center gap-2 pt-2">
                <button id="btnForceSubmit" type="button" class="btn btn-warning" disabled>Input</button>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-label-secondary" onclick="window.location.href='{{ route('analyze.pdf.index') }}'">Cancel</button>
                  <button id="btnSaveManualInput" type="submit" class="btn btn-primary" disabled>Save</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
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

  const $queue = $('#manualInputQueue');
  const $counter = $('#manualInputCounter');
  const $empty = $('#manualInputEmpty');
  const $detail = $('#manualInputDetail');
  const $form = $('#manualInputForm');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  initSalesInvoiceRepeater();

  function initSalesInvoiceRepeater() {
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
    $('#btnSaveManualInput, #btnForceSubmit, #btnDeleteItem, #btnPreviousItem, #btnNextItem').prop('disabled', isBusy || !current);
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
    $('#btnDeleteItem, #btnSaveManualInput, #btnForceSubmit').prop('disabled', !current);
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
        return loadItem(queue[0].id);
      }

      if (!queue.length) {
        current = null;
        $detail.addClass('d-none');
        $empty.removeClass('d-none').text('No manual input items in the queue.');
        updateNav();
      }
    });
  }

  function loadItem(id) {
    setBusy(true);

    return $.getJSON(endpoints.show + '/' + id)
      .then(response => {
        current = response.item;
        fillForm(current);
        renderQueue();
        updateCounter(response.position, response.total);
        updateNav();
        $empty.addClass('d-none');
        $detail.removeClass('d-none');
      })
      .always(() => setBusy(false));
  }

  function fillForm(item) {
    $('#manualInputTitle').text(item.file_name || ('OCR item #' + item.id));
    $('#manualInputSubtitle').text((item.error || item.validation_status || '').toString().replace(/\n/g, ' · '));
    $('#manual_invoice_id').val(item.id);
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
    $('#manualPdfViewer').attr('src', pdfUrl);
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
        renderQueue();
        updateCounter(response.position, response.total);
        updateNav();
      } else if (queue.length) {
        loadItem(queue[0].id);
      } else {
        current = null;
        $detail.addClass('d-none');
        $empty.removeClass('d-none').text('No manual input items in the queue.');
        updateCounter(null, 0);
        updateNav();
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
  $queue.on('click', '[data-id]', function () { loadItem($(this).data('id')); });
  $('#btnPreviousItem').on('click', function () {
    const idx = currentIndex();
    if (idx > 0) loadItem(queue[idx - 1].id);
  });
  $('#btnNextItem').on('click', function () {
    const idx = currentIndex();
    if (idx >= 0 && idx < queue.length - 1) loadItem(queue[idx + 1].id);
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

  $('#btnForceSubmit').on('click', function () {
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

  let lookupTimer = null;
  $('#client_no').on('input', function () {
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
