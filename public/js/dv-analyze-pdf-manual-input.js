/**
 * Page OCR Invoice PDF Error List - Manual Input
 */

'use strict';
//Dropzone.autoDiscover = false;

// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';

// Datatable (jquery)
$(function () {
  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }
  
  // Variable declaration for table    
  var analyzePdfUrl = baseUrl + 'analyzepdf/';

  var statusObj = {      
      0: { title: 'Not Sync', class: 'bg-danger' },
      1: { title: 'Synced', class: 'bg-success' }      
    };

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  const endpoints = {   
    queue: analyzePdfUrl + `manual-input/queue`,
    show: analyzePdfUrl + `manual-input`,
    clientLookup: analyzePdfUrl + `manual-input/client-lookup`,
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
    if (isBusy) {
      $('#btnSaveManualInput, #btnForceSubmit, #btnDeleteItem, #btnPreviousItem, #btnNextItem')
        .prop('disabled', true);
      return;
    }

    updateNav();
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

        $('#manualInputForm').scrollTop(0);
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
    applyInvoiceTypeVisibility(item.invoice_type);
    loadPdfViewer(item);
  }

  function loadPdfViewer(item) {
    $('#manualPdfViewer').attr('src', '');

    if (!item || !item.id) {
      return;
    }

    const selectedId = item.id;

    if (item.azure_url) {
      $.get(analyzePdfUrl + item.id + '/sas-url', function (response) {
        if (!current || current.id !== selectedId) {
          return;
        }

        if (response.azure_signed_url) {
          $('#manualPdfViewer').attr(
            'src',
            response.azure_signed_url + '#page=1&zoom=page-width'
          );
        } else {
          console.log('PDF not available.');
          $('#manualPdfViewer').attr('src', '');
        }
      }).fail(function () {
        if (current && current.id === selectedId) {
          console.log('Failed to fetch PDF.');
          $('#manualPdfViewer').attr('src', '');
        }
      });

      return;
    }

    if (!item.invoice_type || !item.file_name) {
      $('#manualPdfViewer').attr('src', '');
      return;
    }

    const pdfUrl =
      '/storage/ocr/' +
      item.invoice_type +
      '/' +
      encodeURIComponent(item.file_name) +
      '#page=1&zoom=page-width';

    $('#manualPdfViewer').attr('src', pdfUrl);
  }

  function applyInvoiceTypeVisibility(invoiceType) {
    const isCommercial = invoiceType === 'com';

    const alwaysVisibleFields = [
      '#net_amount',
      '#exchange_currency',
      '#exchange_net_amount'
    ];

    const salesOnlyFields = [
      '#credit_note',
      '#vat_rate',
      '#vat_amount',
      '#total_amount',
      '#exchange_rate',
      '#exchange_vat_amount',
      '#exchange_total_amount'
    ];

    alwaysVisibleFields.concat(salesOnlyFields).forEach(function (selector) {
      $(selector)
        .prop('disabled', false)
        .closest('.mb-3')
        .removeClass('d-none');
    });

    if (isCommercial) {
      salesOnlyFields.forEach(function (selector) {
        $(selector)
          .prop('disabled', true)
          .closest('.mb-3')
          .addClass('d-none');
      });

      $('.form-salesinvoice-repeater')
        .closest('.mb-3')
        .removeClass('d-none');

      $('.sales-invoice-ref-no')
        .prop('disabled', false);
    } else {
      $('.form-salesinvoice-repeater')
        .closest('.mb-3')
        .addClass('d-none');

      $('.sales-invoice-ref-no')
        .val('')
        .prop('disabled', true);
    }
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
    const invoiceType = $('#invoice_type').val();

    data.forEach(item => {
      if (item.name && !item.name.startsWith('sales-invoice')) {
        payload[item.name] = item.value;
      }
    });

    payload.credit_note = invoiceType === 'sales' && $('#credit_note').is(':checked') ? 1 : 0;

    payload.related_sales_invoices = invoiceType === 'com'
      ? $('.sales-invoice-ref-no').map(function () {
          return ($(this).val() || '').trim();
        }).get().filter(Boolean)
      : [];

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

  function validateManualInputForm() {
    const form = $form[0];

    if (!form) {
      return true;
    }

    if (!form.checkValidity()) {
      form.reportValidity();
      return false;
    }

    return true;
  }

  function saveItem(force) {
    if (!current) return;

    if (!validateManualInputForm()) {
      return;
    }

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

  $(document).on('change', '#invoice_type', function () {  
    applyInvoiceTypeVisibility($(this).val());
  });

  $('#btnRefreshQueue').on('click', () => loadQueue(true));

  $queue.on('click', '[data-id]', function () { loadItem($(this).data('id')); });

  $(document).on('click', '#btnPreviousItem', function () {
    const idx = currentIndex();
    if (idx > 0) loadItem(queue[idx - 1].id);
  });

  $(document).on('click', '#btnNextItem', function () {
    const idx = currentIndex();
    if (idx >= 0 && idx < queue.length - 1) loadItem(queue[idx + 1].id);
  });

  $(document).on('click', '#btnDeleteItem', function () {
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

  if ($form[0]) {
    $form[0].addEventListener('invalid', function (event) {
      const invalidField = event.target;

      if (invalidField && typeof invalidField.scrollIntoView === 'function') {
        invalidField.scrollIntoView({ block: 'center', behavior: 'smooth' });
      }
    }, true);
  }

  $form.on('submit', function (event) {
    event.preventDefault();
    saveItem(false);
  });

  $(document).on('click', '#btnForceSubmit', function () {
    if (!validateManualInputForm()) {
      return;
    }

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
});
