/**
 * Page OCR Invoice PDF Error List - Manual Input
 */

'use strict';
//Dropzone.autoDiscover = false;

// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';

// Datatable (jquery)
$(function () {
  // window.Pusher = Pusher;
  // window.Echo = new Echo({
  //     broadcaster: 'pusher',      
  //     key: window.EchoConfig.pusherKey,
  //     cluster: window.EchoConfig.pusherCluster,      
  //     forceTLS: true
  // });

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
  
  // window.Echo.channel('ocr-sync-invoices-channel').listen('.OcrInvoicesSyncEvent', (event) => {
  //   //console.log(event);
  //   console.log('OCR Sync Invoices Event:', event);
  //    // console.log(event.message);
  //    // console.log(event.client_id);
  //   // Handle the event
  //   var client_id = event.client_id;
  //   //console.log(client_id);
    
  //   $.ajax({              
  //     url: `${analyzePdfUrl}progress`,     
  //     type: 'GET',
  //     success: function (result) {   console.log(result);
  //       //const progressData = result.json();

  //       var analyzepdf_datas = drawDtTable(result, 'analyzepdf');            
  //       reloadAnalyzedPdf(analyzepdf_datas);
  //     },
  //     error: function (err) {
  //       console.log(err);        
  //     }
  //   });
  // });


  const endpoints = {   
    queue: analyzePdfUrl + `manual-input/queue`,
    show: analyzePdfUrl + `manual-input`,
    clientLookup: analyzePdfUrl + `manual-input/client-lookup`,
  };
  let queue = [];
  let filteredQueue = [];
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
      $('#btnSaveManualInput, #btnSearchSave, #btnForceSubmit, #btnDeleteItem, #btnPreviousItem, #btnNextItem')
        .prop('disabled', true);
      return;
    }

    updateNav();
  }

  // function getVisibleQueue() {
  //     return filteredQueue.length || $('#manualQueueSearch').val().trim()
  //         ? filteredQueue
  //         : queue;
  // }

  function getVisibleQueue() {
      const hasSearch = $('#manualQueueSearch').length &&
                        ($('#manualQueueSearch').val() || '').trim();

      return filteredQueue.length || hasSearch
          ? filteredQueue
          : queue;
  }

  function filterQueue(keyword) {

      keyword = keyword.toLowerCase();

      if (!keyword) {
          filteredQueue = [];
          renderQueue();
          updateNav();
          return;
      }

      filteredQueue = queue.filter(item => {
          return (
              String(item.file_name || '').toLowerCase().includes(keyword) ||
              String(item.id || '').toLowerCase().includes(keyword) ||
              String(item.invoice_type || '').toLowerCase().includes(keyword) ||
              String(item.invoice_no || '').toLowerCase().includes(keyword) ||
              String(item.client_no || '').toLowerCase().includes(keyword) ||
              String(item.client_name || '').toLowerCase().includes(keyword) ||
              String(item.error || '').toLowerCase().includes(keyword)
          );
      });

      renderQueue();
      updateNav();
  }

  function currentIndex() {
      return getVisibleQueue().findIndex(item => current && item.id === current.id);
  }

  // function currentIndex() {
  //   return queue.findIndex(item => current && item.id === current.id);
  // }

  function updateCounter(position, total) {
    if (!current || !total) {
      $counter.text('0 / 0');
      return;
    }
    $counter.text((position || (currentIndex() + 1)) + ' / ' + total);
  }

  function updateNav() {
    const items = getVisibleQueue();
    const idx = currentIndex();
    $('#btnPreviousItem').prop('disabled', !current || idx <= 0);
    //$('#btnNextItem').prop('disabled', !current || idx < 0 || idx >= queue.length - 1);
    $('#btnNextItem').prop('disabled', !current || idx < 0 || idx >= items.length - 1);
    $('#btnDeleteItem, #btnSaveManualInput, #btnSearchSave, #btnForceSubmit').prop('disabled', !current);
  }

  function renderQueue() {
    $queue.empty();

    const items = getVisibleQueue();

    //if (!queue.length) {    
    if (!items.length) {    
      $queue.append('<div class="p-3 text-muted">No manual input items.</div>');
      updateCounter(null, 0);
      return;
    }
    //queue.forEach(item => {
    items.forEach(item => {
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
    var searchSave = ($('#searchSave').length > 0) ? true :  false;

    return $.getJSON(endpoints.queue, {searchSave: searchSave}).then(response => {
      queue = response.items || [];
      
      filteredQueue = [];

      if($('#manualQueueSearch').length > 0)
      {
        if ($('#manualQueueSearch').val().trim()) {
            filterQueue($('#manualQueueSearch').val());
        } else {
            renderQueue();
        }
      }

      if (selectFirst && queue.length) {
        return loadItem(queue[0].id);
      }
      if (!queue.length) {
        current = null;
        $detail.addClass('d-none');
        var emptytext = 'No manual input items in the queue.';
        if($('#searchSave').length > 0)
          emptytext = 'No items in the queue.';
        $empty.removeClass('d-none').text(emptytext);
        updateNav();
      }
    });
  }

  //function loadItem(id) {
  window.loadItem = function loadItem(id) { 
    setBusy(true);

    var syncedPage = ($('#syncedPage').length > 0) ? true :  false;

    return $.getJSON(endpoints.show + '/' + id, {syncedPage: syncedPage})
      .then(response => { console.log(response);
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
    const localCurrencyMap = {
        no: 'NOK',
        gb: 'GBP',
        uk: 'GBP',
        ch: 'CHF',
        dk: 'DKK',
        se: 'SEK',
        pl: 'PLN'
    };
    let client_name = item.client_name || '';

    let invoice_no = item.invoice_no || '';
    if (client_name && client_name.toLowerCase().indexOf('horn bord') > -1)
    {
      if(!item.credit_note)
        invoice_no = (item.order_number) ? item.order_number : invoice_no;
    }
    else if (client_name && client_name.toLowerCase().indexOf('rainwear') > -1
      || client_name.toLowerCase().indexOf('engel') > -1
      || client_name.toLowerCase().indexOf('berendsohn') > -1
    )
    {
      invoice_no = (item.no_invoice_number) ? item.no_invoice_number : invoice_no;
    }

    let credit_note = item.credit_note;

    const countryCode = (item.country_code || '').toLowerCase();
    const localCurrency = localCurrencyMap[countryCode];

    let currency = item.currency;
    let exchange_currency = item.exchange_currency;

    let net_amount = item.net_amount;

    let og_net_amount = item.net_amount; //console.log("og_net_amount: " + og_net_amount);
    let og_variance = item.variance; //console.log("og_variance: " + og_variance);  
    let og_additional_charges = item.additional_charges; //console.log("og_additional_charges: " + og_additional_charges);    
    let og_discount_amount = item.discount_amount; //console.log("og_discount_amount: " + og_discount_amount);
    let og_vat_amount = item.vat_amount;
    let og_total_amount = item.total_amount;

    if (client_name && client_name.toLowerCase().indexOf('dfi-geisler') > -1)
    {
      og_additional_charges = '';
    }

    if (client_name && client_name.toLowerCase().indexOf('rieker') > -1
      || client_name.toLowerCase().indexOf('woden') > -1
      || client_name.toLowerCase().indexOf('pier one') > -1
    )
    {
      og_discount_amount = '';
    }

    let parse_net_amount = 0;//parseAmountValue(og_net_amount);   
    let parse_variance_amount = 0;//parseAmountValue(og_variance);
    let parse_freight_amount = 0;//parseAmountValue(og_additional_charges);
    let parse_discount_amount = 0;//parseAmountValue(og_discount_amount);
    let parse_vat_amount = 0;
    let parse_total_amount = 0;

    if(/,(\d{1,2})$/.test(og_net_amount))
    {
      parse_net_amount = parseAmountValue(og_net_amount, 'NOK');    
      //console.log("parse_net_amount: " + parse_net_amount);
    } 
    else
    {
      parse_net_amount = parseAmountValue(og_net_amount); 

      //console.log("else parse_net_amount: " + parse_net_amount); 
    }

    if(/,(\d{1,2})$/.test(og_variance))
    {
      parse_variance_amount = parseAmountValue(og_variance, 'NOK');
      //console.log("parse_variance_amount: " + parse_variance_amount);
    } 
    else
    {
      parse_variance_amount = parseAmountValue(og_variance); 

      //console.log("else parse_variance_amount: " + parse_variance_amount); 
    }

    if(/,(\d{1,2})$/.test(og_additional_charges))
    {
      parse_freight_amount = parseAmountValue(og_additional_charges, 'NOK');
      //console.log("parse_freight_amount: " + parse_freight_amount);
    } 
    else
    {
      parse_freight_amount = parseAmountValue(og_additional_charges); 

      //console.log("else parse_freight_amount: " + parse_freight_amount); 
    }

// same format - - but discount on some of the files (don't include)
// aid 
// committee
// lost boys
// pier one
// qnuz
// sea ranch
// sindico
// sports
// woden

//different format - same discount issue
// dfi-geisler
// rieker    

    if(/,(\d{1,2})$/.test(og_discount_amount))
    {
      if (client_name && client_name.toLowerCase().indexOf('dfi-geisler') > -1
        || client_name.toLowerCase().indexOf('rieker') > -1
        || client_name.toLowerCase().indexOf('woden') > -1
        || client_name.toLowerCase().indexOf('pier one') > -1
      )
      {

      } 
      else
        parse_discount_amount = parseAmountValue(og_discount_amount, 'NOK');
      //console.log("parse_discount_amount: " + parse_discount_amount);
    } 
    else
    {
      if (client_name && client_name.toLowerCase().indexOf('dfi-geisler') > -1
        || client_name.toLowerCase().indexOf('rieker') > -1
        || client_name.toLowerCase().indexOf('woden') > -1
        || client_name.toLowerCase().indexOf('pier one') > -1
      )
      {

      } 
      else      
        parse_discount_amount = parseAmountValue(og_discount_amount); 

      //console.log("else parse_discount_amount: " + parse_discount_amount); 
    }

    if(/,(\d{1,2})$/.test(og_vat_amount))
    {
      parse_vat_amount = parseAmountValue(og_vat_amount, 'NOK');
    } 
    else
    {
      parse_vat_amount = parseAmountValue(og_vat_amount);
    }

    if(/,(\d{1,2})$/.test(og_total_amount))
    {
      parse_total_amount = parseAmountValue(og_total_amount, 'NOK');
    } 
    else
    {
      parse_total_amount = parseAmountValue(og_total_amount);
    }

    let calNetAmount = (Math.abs(parse_net_amount) + Math.abs(parse_freight_amount) + Math.abs(parse_variance_amount)) - Math.abs(parse_discount_amount);    
    if (client_name && (client_name.toLowerCase().indexOf('sgi wholesale') > -1
        || client_name.toLowerCase().indexOf('sand cph') > -1
      )
    )
    {
      calNetAmount = (Math.abs(parse_net_amount) + Math.abs(parse_freight_amount)) - (Math.abs(parse_variance_amount) + Math.abs(parse_discount_amount));
    }

    //console.log("calNetAmount: " + calNetAmount);
    let formatted_net_amount = parseDenmarkFormat(calNetAmount);  
    //console.log("formatted_net_amount: " + formatted_net_amount);
    net_amount = formatted_net_amount;
    
    let exchange_net_amount = item.exchange_net_amount;

    let vat_amount = item.vat_amount;
    let exchange_vat_amount = item.exchange_vat_amount;

    let total_amount = item.total_amount;
    let exchange_total_amount = item.exchange_total_amount;

    if(!total_amount)
    {              
      parse_total_amount = calNetAmount + Math.abs(parse_vat_amount);
      
      let formatted_total_amount = parseDenmarkFormat(parse_total_amount.toString());        
      total_amount = formatted_total_amount;
    }

    const shouldConvert = ($('#searchSave').length > 0)
        ? (
            localCurrency &&
            exchange_currency === localCurrency &&
            currency !== localCurrency
        )
        : (
            localCurrency &&
            (exchange_currency === null || exchange_currency === localCurrency) &&
            currency !== localCurrency
        );

    // if (
    //     localCurrency &&
    //     (exchange_currency === null || exchange_currency === localCurrency) &&
    //     currency !== localCurrency
    // ) {
    if (shouldConvert) {
        [currency, exchange_currency] = [exchange_currency, currency];
        [net_amount, exchange_net_amount] = [exchange_net_amount, net_amount];
        [vat_amount, exchange_vat_amount] = [exchange_vat_amount, vat_amount];
        [total_amount, exchange_total_amount] = [exchange_total_amount, total_amount];

        if (
            !item.exchange_rate &&
            !exchange_net_amount &&
            !exchange_vat_amount &&
            !exchange_total_amount
        ) {
            exchange_currency = '';
        }
    }    

    let formatted_original_net_amount = null;
    let formatted_discount_amount = null;
    let formatted_additional_amount = null;
    let formatted_variance_amount = null;
    if(parse_net_amount > 0)
    {
      formatted_original_net_amount = parseDenmarkFormat(parse_net_amount);      
    }

    if(parse_discount_amount > 0)
    {   
      formatted_discount_amount = parseDenmarkFormat(parse_discount_amount);
    }

    if(parse_freight_amount > 0) 
    {
      formatted_additional_amount = parseDenmarkFormat(parse_freight_amount);
    }

    if(parse_variance_amount > 0)
    {
      formatted_variance_amount = parseDenmarkFormat(parse_variance_amount);
    }

    if (credit_note === true && net_amount && !net_amount.startsWith('-'))
      net_amount = '-' + net_amount.trim();

    if (credit_note === true && vat_amount && !vat_amount.startsWith('-'))
      vat_amount = '-' + vat_amount.trim();

    if (credit_note === true && total_amount && !total_amount.startsWith('-'))
      total_amount = '-' + total_amount.trim();    

    if (credit_note === true && exchange_net_amount && !exchange_net_amount.startsWith('-'))
      exchange_net_amount = '-' + exchange_net_amount.trim();

    if (credit_note === true && exchange_vat_amount && !exchange_vat_amount.startsWith('-'))
      exchange_vat_amount = '-' + exchange_vat_amount.trim();

    if (credit_note === true && exchange_total_amount && !exchange_total_amount.startsWith('-'))
      exchange_total_amount = '-' + exchange_total_amount.trim();    

    $('#manualInputTitle').text(item.file_name || ('OCR item #' + item.id));
    $('#manualInputSubtitle').text((item.error || item.validation_status || '').toString().replace(/\n/g, ' · '));
    $('#manual_invoice_id').val(item.id);
    $('#invoice_type').val(item.invoice_type || '');
    $('#client_no').val(item.client_no || '');
    $('#client_name').val(client_name);
    $('#invoice_date').val(item.invoice_date || '');
    $('#invoice_no').val(invoice_no);
    $('#credit_note').prop('checked', !!credit_note);
    $('#currency').val(currency || '');
    $('#exchange_currency').val(exchange_currency || '');
    $('#vat_rate').val(item.vat_rate || '');
    $('#exchange_rate').val(item.exchange_rate || '');
    $('#net_amount').val(net_amount || '');
    $('#exchange_net_amount').val(exchange_net_amount || '');
    $('#vat_amount').val(vat_amount || '');
    $('#exchange_vat_amount').val(exchange_vat_amount || '');
    $('#total_amount').val(total_amount || '');
    $('#exchange_total_amount').val(exchange_total_amount || '');
    
    $('#original_net_amount').val(formatted_original_net_amount || '');
    $('#discount_amount').val(formatted_discount_amount || '');
    $('#variance_amount').val(formatted_variance_amount || '');
    $('#additional_amount').val(formatted_additional_amount || '');
      
    $('#note').val(item.note || '');
    
    //setSalesInvoiceRefs(item.related_sales_invoices || []);
    const relatedSalesInvoices = expandSalesInvoiceRefs(item.related_sales_invoices || []);    
    setSalesInvoiceRefs(relatedSalesInvoices);

    //const pdfUrl = item.sas_url ? item.sas_url + '#zoom=page-width' : '';
    //$('#manualPdfViewer').attr('src', pdfUrl);
    
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
      '#exchange_total_amount',      
      '#original_net_amount',
      '#discount_amount',
      '#additional_amount',
      '#variance_amount'
    ];

    alwaysVisibleFields.concat(salesOnlyFields).forEach(function (selector) {
      $(selector)
        .prop('disabled', false)
        .closest('.mb-2')
        .removeClass('d-none');
    });

    if (isCommercial) {
      salesOnlyFields.forEach(function (selector) {
        $(selector)
          .prop('disabled', true)
          .closest('.mb-2')
          .addClass('d-none');
      });

      $('.form-salesinvoice-repeater')
        .closest('.mb-2')
        .removeClass('d-none');

      $('.sales-invoice-ref-no')
        .prop('disabled', false);
    } else {
      $('.form-salesinvoice-repeater')
        .closest('.mb-2')
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

      // Reapply search filter after queue refresh
      const keyword = ($('#manualQueueSearch').length > 0) ? $('#manualQueueSearch').val().trim() : '';

      if (keyword) {
          filterQueue(keyword);
      }

      if (response.next) {
        current = response.next;
        fillForm(current);
        renderQueue();
        updateCounter(response.position, response.total);
        updateNav();
      // } else if (queue.length) {
      //   loadItem(queue[0].id);
      } else if (getVisibleQueue().length) {
          loadItem(getVisibleQueue()[0].id);      
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
      //.then(response => handleNextResponse(response))
      .then(function (response) { 

        if($('#searchSave').length > 0)
        {
          var invoice = response;
          var id = invoice.invoice_id;
          var invoice_type = invoice.invoice_type;

          Swal.fire({
              icon: 'success',
              title: 'Success',
              text: 'Data saved successfully.'
          }).then(function (result) { 
            if (result.isConfirmed)
            {
              $("#offcanvasAnalyzePdfData").offcanvas('hide');

              //reload specific row
              let table_invoice_type = 'sales';
              if(invoice_type == 'com')
                table_invoice_type = 'commercial';

              loadSearchSave(table_invoice_type, id);
            }
          });
        }
        else
          handleNextResponse(response);
      })
      .fail(xhr => { console.log(xhr);
        Swal.fire('Save failed', xhr.responseJSON?.message || 'Unable to save manual input.', 'error');
      })
      .always(() => setBusy(false));
  }

  function loadSearchSave(type, id) {    
    const formData = Object.fromEntries(
        $("#manualInputForm").serializeArray().map(item => [item.name, item.value])
    );

    const table = $('.datatables-analyzepdfsearch.datatables-'+ type +'-invoice-analyzepdfsearch').DataTable();
    const row = table.row('#invoice_' + id);

    // Get existing DataTables row data
    let rowData = row.data();

    let euroIndexes = [7, 8, 10, 11, 12, 13, 14];
    if(type === 'commercial')
      euroIndexes = [7];

    $(row.node()).find('td').each(function(index) {

      if(type === 'commercial')
      {
        if (index >= 3 && index <= 6) {
          const field = table.column(index).header().dataset.field;
          let value = formData[field];

          if (value !== undefined) {
            // IMPORTANT:
            // Update DataTables data with the RAW value
            rowData[field] = value;

            // Update displayed value
            let displayValue = value;

            if (euroIndexes.includes(index)) {
                // value = Number(value).toLocaleString('de-DE', {
                //     minimumFractionDigits: 2,
                //     maximumFractionDigits: 2
                // });
                displayValue = Number(value).toLocaleString('de-DE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            //$(this).text(value);
            $(this).text(displayValue);
          }
        }
      }
      else
      {
        if (index >= 3 && index <= 14) {

            const field = table.column(index).header().dataset.field;
            let value = formData[field];

            if (index === 7) {
                const field8  = table.column(8).header().dataset.field;
                const field11 = table.column(11).header().dataset.field;
                const field12 = table.column(12).header().dataset.field;
                const field13 = table.column(13).header().dataset.field;

                const col8  = parseEuropeanNumber(formData[field8]);
                const col11 = parseEuropeanNumber(formData[field11]);
                const col12 = parseEuropeanNumber(formData[field12]);
                const col13 = parseEuropeanNumber(formData[field13]);

                value = (col8 + col11 + col12) - col13;

                // Update calculated field in DataTables
                rowData[field] = value;
                console.log("index777 == " + value);
            }

            if (value !== undefined) {

                // For normal fields
                if (index !== 7) {
                    rowData[field] = value;
                }

                // Display formatting only
                let displayValue = value;

                if (euroIndexes.includes(index)) {
                  // value = parseEuropeanNumber(value).toLocaleString('de-DE', {
                  //     minimumFractionDigits: 2,
                  //     maximumFractionDigits: 2
                  // });

                  displayValue = parseEuropeanNumber(value).toLocaleString('de-DE', {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2
                  });
                }
                
                //$(this).text(value === "0,00" ? "" : value);
                $(this).text(
                    displayValue === "0,00" ? "" : displayValue
                );
            }
        }
      }
    });

    // IMPORTANT:
    // Update DataTables' internal row data
    row.data(rowData).invalidate();

    // Keep current pagination/page
    table.draw(false); 
  }

  //function parseEuropeanNumber(value) {
  window.parseEuropeanNumber = function parseEuropeanNumber(value) { 
      if (value === undefined || value === null || value === '') {
          return 0;
      }

      value = value.toString().trim();

      // European format: 19.372,82
      if (value.includes(',') && value.includes('.')) {
          value = value.replace(/\./g, '').replace(',', '.');
      }
      // European decimal only: 100,50
      else if (value.includes(',')) {
          value = value.replace(',', '.');
      }
      // Normal decimal: 18404.18
      // keep as it is

      return Number(value) || 0;
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
    const items = getVisibleQueue();
    const idx = currentIndex();
    //if (idx > 0) loadItem(queue[idx - 1].id);
    if (idx > 0) loadItem(items[idx - 1].id);
  });
  
  $(document).on('click', '#btnNextItem', function () {
    const items = getVisibleQueue();
    const idx = currentIndex();
    //if (idx >= 0 && idx < queue.length - 1) loadItem(queue[idx + 1].id);
    if (idx >= 0 && idx < items.length - 1) loadItem(items[idx + 1].id);
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

  $(document).on('input', '.only-date', function () {
    this.value = this.value
        .replace(/[^0-9-]/g, '')
        .substring(0, 10);
  });

  $(document).on('input', '.only-amount', function () {
      this.value = this.value.replace(/[^0-9.,]/g, '');
  });

  $form.on('submit', function (event) {
    event.preventDefault();
    saveItem(false);
  });
  
  // cancel button
  $(document).on('click', '.btn-cancel-analyzepdf-form', function () {
    $("#offcanvasAnalyzePdfData").offcanvas('hide');
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

  $(document).on('input', '#manualQueueSearch', function () {
      filterQueue($(this).val());
  });

  //function exportToExcel(dt, which_tab)
  window.exportToExcel = function exportToExcel(dt, which_tab)
  {  
      let workbook = XLSX.utils.book_new();
      let sheetData = [];

      // Define headers     
      let headers = [];
      let visibleColumnIndexes = [];              

      dt.columns().every(function(index) {        
          if (this.visible()) {             
              let columnData = this.dataSrc(); // Get the data property name       
              if(columnData != 'id')
              {
                if(columnData == 'fake_id')
                  headers.push('No.'); // Get header text       
                else
                  headers.push(this.header().innerText); // Get header text       

                visibleColumnIndexes.push(columnData); // Store the data property name
              }
          }
      });

      // Loop through the main DataTable
      let allData = [];
      //dt.rows().every(function(rowIdx) {
      dt.rows({ search: 'applied' }).every(function(rowIdx) {
          var rowData = this.data();
         
          let rowInfo = [];
          var currency_code = '';
          const MAX_CELL_LENGTH = 32767;

          visibleColumnIndexes.forEach(function(colName) {                        
              if(colName == 'pdf')
                rowInfo.push('-'); // Push the value into the row array
              else
              {    
                currency_code = 'NOK';            
                // if(colName == 'currency_code')
                // {
                //   currency_code = rowData[colName];
                //   rowInfo.push(currency_code); // Push the value into the row array
                // }
                // else 
                if(colName == 'original_net_amount' || colName == 'net_amount' || colName == 'vat_amount' || colName == 'variance_amount'
                   || colName == 'freight_amount' || colName == 'discount_amount' || colName == 'total_amount')
                {
                  let value = rowData[colName];
                  if (typeof value === "number") 
                    rowInfo.push(value); // Directly push the number
                  else {
                    
                    let parsed_value =  parseAmountValue(value, currency_code);
                    rowInfo.push(parsed_value); // Push the number or an empty string
                  }                  
                }
                //else
                //  rowInfo.push(rowData[colName]); // Push the value into the row array  
                else
                {
                    let value = rowData[colName];

                    if (String(value).length > MAX_CELL_LENGTH) {
                        console.error(
                            `Row ${rowIdx + 1}, Field "${colName}" exceeds limit: ${String(value).length} characters`
                        );
                        value = String(value).substring(0, MAX_CELL_LENGTH);
                    }
                    // Handle arrays
                    if (Array.isArray(value)) {
                        rowInfo.push(value.join(', '));
                    }

                    // Handle null/undefined
                    else if (value == null) {
                        rowInfo.push('');
                    }

                    // Handle objects if needed
                    else if (typeof value === 'object') {
                        rowInfo.push(JSON.stringify(value));
                    }

                    // Normal values
                    else {
                        rowInfo.push(value);
                    }
                }
              }
          });
          
          // Push main row data         
          allData.push(rowInfo); // Push the rowInfo object to the array   
      });
      
      // Include the headers      
      allData.unshift(headers); // Add headers as the first row

      // Create the worksheet      
      let worksheet = XLSX.utils.aoa_to_sheet(allData);

      XLSX.utils.book_append_sheet(workbook, worksheet, "OCR");

      // Export the workbook
      XLSX.writeFile(workbook, 'OCR-'+ which_tab +'.xlsx');
  }

  let lookupTimer = null; 
  $(document).on('input', '#client_no', function () {
    clearTimeout(lookupTimer);
    const clientNo = $(this).val();
    const invoiceId = $('#manual_invoice_id').val();
    $('#clientLookupStatus').text('Looking up client...');
    lookupTimer = setTimeout(() => {
      $.getJSON(endpoints.clientLookup, { client_no: clientNo, invoice_id: invoiceId })
        .then(response => {
          if (response.client) {
            if(response.client.name)
            {
              $('#client_name').val(response.client.name || '');
              $('#clientLookupStatus').text('Client found in database.');
            }
            else
            {
              if(response.client.extracted_name)
              {
                $('#client_name').val(response.client.extracted_name || '');
                $('#clientLookupStatus').text('Client not found in database. Force Input from PDF.');
              }
              else
              {
                $('#client_name').val('');
                $('#clientLookupStatus').text('Client not found. Force Input may be used if onboarding is pending.');
              }
            }            
          } else {
            $('#client_name').val('');
            $('#clientLookupStatus').text('Client not found. Force Input may be used if onboarding is pending.');
          }
        });
    }, 350);
  });
  
  if($('#searchSave').length == 0)
    loadQueue(true);  

});
