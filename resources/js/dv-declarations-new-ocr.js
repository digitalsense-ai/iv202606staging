/**
 * Consolidated OCR declaration browser.
 * Hierarchy: declaration period -> lope/commercial invoice -> sales invoice.
 */
'use strict';

$(function () {
  const $table = $('.datatables-declarations-new-ocr');
  if (!$table.length) return;
  if ($.fn.dataTable.isDataTable($table[0])) return;

  const escapeHtml = value => $('<div>').text(value == null || value === '' ? '-' : value).html();
  const list = value => (Array.isArray(value) ? value : []);
  const declarationData = list(window.declaration_datas);
  const declarationKey = declaration => [declaration.month_year, declaration.o_declaration_date, declaration.id].join('|');
  const commercialKey = commercial => [commercial.id, commercial.group_lope_no, commercial.co_invoice_no].join('|');

  const declarationInvoiceUrl = baseUrl + 'declaration-new-invoice/';
  const $refreshButton = $('.js-refresh-declarations');
  const toastPlacementDiv = document.querySelector('.toast-placement');
  const toastPlacementHeader = toastPlacementDiv ? toastPlacementDiv.querySelector('.toast-header') : null;
  const selectedToastPlacement = String('top-0 end-0').split(' ');
  let toastPlacement = null;
  let toastHideTimer = null;
  let refreshIntervalId = null;
  let refreshXhr = null;
  let refreshFinished = false;
  let pendingBatchIds = new Set();

  let enableRefresh = null;  
  let rematchFinished = false;
 
  function toastDispose(toast) {
    if (!toast) return;

    try {
      // Only dispose if Bootstrap still has an element.
      if (toast._element) {
        toast.hide();
        toast.dispose();
      }
    } catch (error) {
      console.warn('Unable to dispose toast:', error);
    }

    toastPlacement = null;
  }

  function setRefreshToastHeader(title) {
    if (!toastPlacementHeader) return;

    $(toastPlacementHeader)
      .find('.toast-header-title')
      .text(title);
  }
 
  // function showRefreshToast(
  //   message,
  //   type = 'bg-primary',
  //   header = 'Refresh Data',
  // ) {
  //   if (!toastPlacementDiv || !toastPlacementHeader) return;

  //   toastPlacementHeader.classList.remove(
  //     'bg-primary',
  //     'bg-danger',
  //     'bg-success',
  //     'bg-warning'
  //   );

  //   toastPlacementHeader.classList.add(type);

  //   setRefreshToastHeader(header);

  //   toastPlacementDiv.classList.add(...selectedToastPlacement);

  //   $(toastPlacementDiv)
  //     .find('small')
  //     .text(moment().format('DD-MM-YYYY hh:mm:ss A'));

  //   $(toastPlacementDiv)
  //     .find('.toast-body')
  //     .text(message);

  //   // Create once and reuse.
  //   if (!toastPlacement) {
  //     toastPlacement = bootstrap.Toast.getOrCreateInstance(
  //       toastPlacementDiv,
  //       {         
  //         autohide: true,
  //         delay: 4000
  //       }
  //     );
  //   }

  //   toastPlacement.show();
  // }

//   function showRefreshToast(
//       message,
//       type = 'bg-primary',
//       header = 'Refresh Data',
//       autohide = true
//   ) {
//       if (!toastPlacementDiv || !toastPlacementHeader) return;

//       toastPlacementHeader.classList.remove(
//           'bg-primary',
//           'bg-danger',
//           'bg-success',
//           'bg-warning'
//       );

//       toastPlacementHeader.classList.add(type);

//       setRefreshToastHeader(header);

//       toastPlacementDiv.classList.add(...selectedToastPlacement);

//       $(toastPlacementDiv)
//           .find('small')
//           .text(moment().format('DD-MM-YYYY hh:mm:ss A'));

//       $(toastPlacementDiv)
//           .find('.toast-body')
//           .text(message);

//       /*
//        * Re-create toast because autohide may change
//        * between "processing" and "completed".
//        */
//       if (toastPlacement) {
//           toastPlacement.dispose();
//           toastPlacement = null;
//       }
// console.log(toastPlacement);
//       if (!toastPlacement) {console.log(autohide);
//         toastPlacement = new bootstrap.Toast(
//             toastPlacementDiv,
//             {
//                 autohide: autohide,
//                 delay: 4000
//             }
//         );
//       }

//       toastPlacement.show();
//   }

  function showRefreshToast(
      message,
      type = 'bg-primary',
      header = 'Refresh Data',
      persistent = false
  ) {
      if (!toastPlacementDiv || !toastPlacementHeader) return;

      // Cancel previous hide timer
      if (toastHideTimer) {
          clearTimeout(toastHideTimer);
          toastHideTimer = null;
      }

      toastPlacementHeader.classList.remove(
          'bg-primary',
          'bg-danger',
          'bg-success',
          'bg-warning'
      );

      toastPlacementHeader.classList.add(type);

      setRefreshToastHeader(header);

      toastPlacementDiv.classList.add(...selectedToastPlacement);

      $(toastPlacementDiv)
          .find('small')
          .text(moment().format('DD-MM-YYYY hh:mm:ss A'));

      $(toastPlacementDiv)
          .find('.toast-body')
          .text(message);

      // Create ONLY ONCE
      if (!toastPlacement) {
          toastPlacement = bootstrap.Toast.getOrCreateInstance(
              toastPlacementDiv,
              {
                  autohide: false
              }
          );
      }

      toastPlacement.show();

      // Final messages disappear after 4 seconds
      if (!persistent) {
          toastHideTimer = setTimeout(function () {

              if (toastPlacement) {
                  toastPlacement.hide();
              }

              toastHideTimer = null;

          }, 4000);
      }
  }

  function resetRefreshButton() {
    $refreshButton.prop('disabled', false).removeClass('disabled').html('<i class="bx bx-refresh me-2"></i>Refresh Data');
  }

  function stopBatchStatusChecks() {
    if (refreshIntervalId !== null) clearInterval(refreshIntervalId);
    refreshIntervalId = null;
    if (refreshXhr && refreshXhr.readyState !== 4) refreshXhr.abort();
    refreshXhr = null;
  }

  function createOcrJob() {
    const vatRegId = $('#vat_reg_id').val();

    if (!vatRegId || $refreshButton.prop('disabled')) return;

    $refreshButton
      .prop('disabled', true)
      .addClass('disabled')
      .html(
        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Refreshing…'
      );

    refreshFinished = false;

    refreshXhr = $.ajax({
      url: declarationInvoiceUrl + vatRegId + '/ocr-refresh',
      method: 'GET',

      success: function (result) {
        
        if (result.total_jobs === 0) {
          refreshFinished = true;
          
          showRefreshToast('No new data found in OCR.');
          
          //startRematching(result.clientId, refreshButton);
          if (result.clientId) {
              startRematching(result.clientId, enableRefresh);
          } else {
              finishRefreshButton();
          }

          return;
        }
      
        //showRefreshToast('Refreshing in progress…');
        showRefreshToast(
            'Refreshing in progress…',
            'bg-primary',
            'Refresh Data',
            true
        );

        refreshIntervalId = setInterval(checkOcrStatus, 2000);
        checkOcrStatus();
      },

      error: function () {
        refreshFinished = true;
        stopBatchStatusChecks();
        resetRefreshButton();

        showRefreshToast(
            'Error in fetching data from OCR.',
            'bg-danger'
        );
      }
    });
  }


  function checkOcrStatus() {
    if (refreshFinished || (refreshXhr && refreshXhr.readyState !== 4)) return;

    const vatRegId = $('#vat_reg_id').val();

    refreshXhr = $.ajax({
      url: declarationInvoiceUrl + vatRegId + '/ocr-status',
      method: 'GET',

      success: function (response) {
        refreshXhr = null;

        if (response.status === 'processing') {
          //showRefreshToast('Refreshing in progress…');
          showRefreshToast(
              'Refreshing in progress…',
              'bg-primary',
              'Refresh Data',
              true
          );
          return;
        }

        if (response.status === 'completed') {
          
          refreshFinished = true;

          // IMPORTANT: stop OCR polling
          stopBatchStatusChecks();

          // showRefreshToast(
          //     'OCR data refreshed successfully.',
          //     'bg-success'
          // );

          // if (response.clientId) {

          //     // Don't reset button here.
          //     // Rematching still has to complete.
          //     startRematching(
          //         response.clientId,
          //         enableRefresh
          //     );

          // } else {
          //     finishRefreshButton();
          // }

          if (response.clientId) {

              // Don't show a temporary OCR success toast.
              // Immediately change it to Rematching.
              startRematching(
                  response.clientId,
                  enableRefresh
              );

          } else {

              showRefreshToast(
                  'OCR data refreshed successfully.',
                  'bg-success',
                  'Refresh Data',
                  false
              );

              finishRefreshButton();
          }

          return;         
        }
      },

      error: function (_xhr, status) {
        if (status === 'abort') return;

        refreshFinished = true;
        stopBatchStatusChecks();
        //resetRefreshButton();
        finishRefreshButton();

        showRefreshToast(
          'Error in fetching data from OCR.',
          'bg-danger'
        );
      }
    });
  }

  function commentTooltip(item) {
    if (!item || !item.comment_reason) return '';
    const reason = escapeHtml(String(item.comment_reason).toUpperCase());
    const comment = escapeHtml(item.comment || '');
    return ' data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="<span>' + reason + '</span><br><span>' + comment + '</span>"';
  }

  function initializeTooltips(container) {
    $(container).find('[data-bs-toggle="tooltip"]').each(function () {
      bootstrap.Tooltip.getOrCreateInstance(this);
    });
  }

  function actionMenu(item, type) {
    if (!item || item.id === '-') return '';
    const invoiceName = type === 'declaration' ? 'declaration' : type;
    const invoiceNo = type === 'declaration' ? item.declaration_no : (type === 'com' ? item.co_invoice_no : item.invoice_no);
    const invoiceDate = item.o_declaration_date || item.o_invoice_date || item.invoice_date || '';
    const commentAction = item.comment_reason ? 'Edit' : 'Add';
    const commercialData = type === 'sales' ? ' data-cominvoice_id="' + escapeHtml(item.cominvoice_id) + '" data-cominvoice_no="' + escapeHtml(item.cominvoice_no) + '"' : (type === 'com' ? ' data-group_invoice_id="' + escapeHtml(item.group_lope_no || '') + '"' : '');
    const declarationData = typeof item.declaration_index === 'number' ? ' data-declaration_index="' + item.declaration_index + '"' : '';
    const data = ' data-invoice_name="' + invoiceName + '" data-invoice_id="' + escapeHtml(item.id) + '" data-invoice_no="' + escapeHtml(invoiceNo) + '" data-invoice_date="' + escapeHtml(invoiceDate) + '" data-tab_name="first"' + commercialData + declarationData;
    const commentMode = item.comment_reason ? 'edit' : 'add';    
    let actions = '<li><a href="javascript:;" class="dropdown-item btn-disregard-declaration-invoice"' + data +      
      ' data-disregard="0" data-insert_type="' + commentMode + '" data-comment_reason="' + escapeHtml(item.comment_reason || '') + '" data-comment="' + escapeHtml(item.comment || '') + '" data-comment_visiblity="' + escapeHtml(item.comment_visiblity || '') + '">' +
      '<i class="bx bx-comment-' + (item.comment_reason ? 'edit' : 'add') + ' me-2"></i>' + commentAction + ' Comment</a></li>';
        
    if (item.comment_reason) {
      actions += '<li><a href="javascript:;" class="dropdown-item text-danger btn-declaration-invoice-delete-comment"' + data +
        '><i class="bx bx-comment-x me-2"></i>Delete Comment</a></li>';
    }

    if (type === 'declaration') {
      //actions += '<li><button type="button" class="dropdown-item js-refresh-page"><i class="bx bx-refresh me-2"></i>Refresh Data</button></li>';
    } else {
      actions += '<li><a href="javascript:;" class="dropdown-item btn-new-declaration-refresh-invoice"' + data + '><i class="bx bx-refresh me-2"></i>Refresh Data</a></li>';
    }
    if (type === 'com') {
      const hasMatch = list(item.invoices).length > 0;
      actions += hasMatch
        ? '<li><a href="javascript:;" class="dropdown-item btn-unmatch-invoice"' + data + ' data-group_invoice_id="' + escapeHtml(item.group_lope_no || '') + '"><i class="bx bx-list-minus me-2"></i>Unmatch Com. Invoice</a></li>'
        : '<li><a href="javascript:;" class="dropdown-item btn-rematch-declaration-cominvoice" title="Rematch com. invoice"' + data + ' data-no_of_split="' + escapeHtml(item.no_of_split || '') + '"><i class="bx bx-list-plus me-2"></i>Rematch com. invoice</a></li>';

      if (item.pdf) {
        const cargoType = item.country === 'CH' ? 'swissimportreconciliationfiles' : 'cargo_mailbox';
        actions += '<li><a href="javascript:;" class="dropdown-item btn-declaration-cargo-download-pdf" title="View Cargo PDF"' + data +
          ' data-cargo_type="' + cargoType + '" data-cargo_file_id="' + escapeHtml(item.pdf) + '"><i class="bx bxs-file-pdf text-danger me-2"></i>View Cargo PDF</a></li>';
      }

      if (String(item.group_lope_no || '').includes('***')) {
        actions += '<li><a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard wrong com. invoice"' + data +
          ' data-disregard="1" data-disregard_type="ivf"><i class="bx bx-folder-minus me-2"></i>Disregard wrong com. invoice</a></li>';
      }

      actions += item.disregard_invoice
        ? '<li><a href="javascript:;" class="dropdown-item btn-retain-cominvoice" title="Retain com. invoice"' + data + ' data-retain="1"><i class="bx bx-add-to-queue me-2"></i>Retain com. invoice</a></li>'
        : '<li><a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard com. invoice"' + data + ' data-disregard="1"><i class="bx bx-folder-minus me-2"></i>Disregard com. invoice</a></li>';

      actions += item.disregard_type === 'lopeno'
        ? '<li><a href="javascript:;" class="dropdown-item btn-retain-lopeno" title="Retain lope no."' + data + ' data-retain="1" data-disregard_type="lopeno" data-lope_no="' + escapeHtml(item.disregarded_no || '') + '"><i class="bx bx-add-to-queue me-2"></i>Retain lope no.</a></li>'
        : '<li><a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard lope no."' + data + ' data-disregard="1" data-disregard_type="lopeno"><i class="bx bx-folder-minus me-2"></i>Disregard lope No.</a></li>';
    }
    if (type === 'sales') {
      // if (item.pdf) actions += '<li><a href="javascript:;" class="dropdown-item btn-declaration-invoice-download-pdf"' + data + ' data-invoice_xml_id="' + escapeHtml(item.pdf) + '"><i class="bx bxs-file-pdf text-danger me-2"></i>View PDF</a></li>';
      // actions += '<li><a href="javascript:;" class="dropdown-item btn-move-declaration-salesinvoice"' + data + '><i class="bx bx-move me-2"></i>Move sales invoice</a></li>';
      // actions += '<li><a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice"' + data + ' data-disregard="1"><i class="bx bx-list-minus me-2"></i>Disregard invoice</a></li>';

      if (item.disregard_invoice) {
        actions += '<li><a href="javascript:;" class="dropdown-item btn-enable-declaration-invoice" title="Enable invoice"' + data + ' data-enable="1"><i class="bx bx-list-check me-2"></i>Enable invoice</a></li>';
      } else {
        actions += '<li><a href="javascript:;" class="dropdown-item btn-move-declaration-salesinvoice" title="Move sales invoice"' + data + '><i class="bx bx-move me-2"></i>Move sales invoice</a></li>';
        actions += '<li><a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard invoice"' + data + ' data-disregard="1"><i class="bx bx-list-minus me-2"></i>Disregard invoice</a></li>';
      }
      if (item.pdf) {
        const pdfData = data + ' data-invoice_xml_id="' + escapeHtml(item.pdf) + '"';
        actions += '<li><a href="javascript:;" class="dropdown-item btn-declaration-invoice-download-pdf" title="View PDF"' + pdfData + '><i class="bx bxs-file-pdf text-danger me-2"></i>View PDF</a></li>';
        actions += '<li><a href="javascript:;" class="dropdown-item btn-declaration-invoice-edit" title="Edit"' + pdfData + ' data-credit_note="' + (item.credit_note ? 1 : 0) + '" data-edit_from="' + escapeHtml(item.edit_from || '') + '"><i class="bx bx-edit-alt me-2"></i>Edit</a></li>';
      }
    }
    return '<div class="d-inline-block declaration-action">' +
      '<button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" title="Actions"><i class="bx bx-dots-vertical-rounded"></i></button>' +
      '<ul class="dropdown-menu dropdown-menu-end m-0">' +
        actions +
      '</ul></div>';
  }

  function childTable(headers, rows, emptyMessage, className) {
    const amountHeaders = new Set(['Statistical value', 'Net Amount', 'Net Sum Invoice', 'Net Amount (CHF)', 'Import VAT', 'VAT Amount (CHF)', 'Duties', 'VAT on Duties', 'Adjustment', 'VAT on adjustment', 'Net amount', 'VAT amount', 'VAT amount (CHF)', 'VAT check 25%', 'VAT check 8.1%']);
    return '<div class="p-0"><div class="table-responsive"><table class="table mb-0 ' + className + '">' +
      '<thead><tr>' + headers.map(header => {
        if (header === '__checkbox__') return '<th class="align-top cw-1 declaration-th-w20 dt-chk-select-all"><input type="checkbox" class="form-check-input js-select-all-sales"></th>';
        return '<th class="align-top' + (amountHeaders.has(header) ? ' text-end' : '') + '">' + (header === '' ? '' : escapeHtml(header)) + '</th>';
      }).join('') + '</tr></thead><tbody>' +
      (rows.length ? rows.join('') : '<tr><td colspan="' + headers.length + '" class="text-center text-muted py-3">' + escapeHtml(emptyMessage) + '</td></tr>') +
      '</tbody></table></div></div>';
  }

  function salesInvoices(commercial) {
    const country = commercial.country;
    const vatPercent = country === 'CH' ? '8.1' : '25';
    const showDisregarded = $('#chk-declaration-filter-show-disregarded-invoices').prop('checked');
    const errorsOnly = $('#chk-declaration-filter-show-err-lines').prop('checked');
    const invoices = list(commercial.invoices).filter(invoice => {
      if (invoice.disregard_invoice && !showDisregarded) return false;
      if (!errorsOnly) return true;
      return invoice.is_net_amount_null || invoice.disregard_invoice || (country === 'NO' && invoice.currency !== 'NOK') || (country === 'CH' && invoice.currency !== 'CHF');
    });   
    const rows = invoices.map(invoice => '<tr class="' + (invoice.disregard_invoice ? 'disabled' : '') + '"' + commentTooltip(invoice) + '>' +
      //'<td class="cw-1 declaration-th-w20 dt-chk-cell alert-warning">' + (invoice.disregard_invoice ? '' : '<input type="checkbox" class="dt-chk form-check-input" value="' + escapeHtml(invoice.id) + '">') + '</td>' +
      '<td class="cw-1 declaration-th-w20 dt-chk-cell alert-warning">' + (invoice.disregard_invoice ? '' : '<input type="checkbox" class="dt-chk form-check-input' + (commercial.category_desc === 'Credit Notes/Missing Ref.' ? ' move-invoice-file' : '') + '" value="' + escapeHtml(invoice.id) + '" data-invoice_no="' + escapeHtml(invoice.invoice_no) + '" data-invoice_date="' + escapeHtml(invoice.o_invoice_date || invoice.invoice_date) + '">') + '</td>' +
      '<td class="text-start declaration-th-w150">' + escapeHtml(invoice.invoice_no) + '</td>' +
      '<td class="text-start declaration-th-w150">' + escapeHtml(invoice.invoice_date || invoice.o_invoice_date) + '</td>' +
      '<td class="text-end declaration-th-w150">' + escapeHtml(invoice.net_amount) + '</td>' +
      (country === 'CH' ? '<td class="text-end declaration-th-w150">' + escapeHtml(invoice.convert_net_amount) + '</td>' : '') +
      '<td class="text-end declaration-th-w150">' + escapeHtml(invoice.adjustment_amount) + '</td>' +
      '<td class="text-end declaration-th-w150">' + escapeHtml(invoice.vat_amount) + '</td>' +
      (country === 'CH' ? '<td class="text-end declaration-th-w150">' + escapeHtml(invoice.convert_vat_amount) + '</td>' : '') +
      '<td class="text-end declaration-th-w150">' + escapeHtml(invoice.vat_check_25) + '</td>' +
      '<td class="text-end declaration-th-w150">' + escapeHtml(invoice.currency) + '</td>' +
      //'<td class="text-center js-action-cell">' + actionMenu(Object.assign({}, invoice, { cominvoice_id: commercial.id, cominvoice_no: commercial.co_invoice_no, declaration_index: commercial.declaration_index }), 'sales') + '</td></tr>');
      '<td class="text-center js-action-cell">' + actionMenu(Object.assign({}, invoice, { cominvoice_id: commercial.id, cominvoice_no: commercial.co_invoice_no, credit_note: commercial.id === '-', declaration_index: commercial.declaration_index }), 'sales') + '</td></tr>');
    const headers = ['__checkbox__', 'Invoices', 'Date', 'Net amount'];
    if (country === 'CH') headers.push('Net amount (CHF)');
    headers.push('Adjustment', 'VAT amount');
    if (country === 'CH') headers.push('VAT amount (CHF)');
    headers.push('VAT check ' + vatPercent + '%', 'Currency', 'Action');
    return childTable(headers, rows, 'No sales invoices', 'datatables-declaration-invoices w-100');
  }  

  function netInvoiceSumValue(commercial) {
    return list(commercial.invoices).reduce((total, invoice) => {
      if (invoice.disregard_invoice) return total;
      return total + window.parseAmountValue(invoice.net_amount, invoice.currency) +
        window.parseAmountValue(invoice.shipping, invoice.currency) +
        window.parseAmountValue(invoice.variance, invoice.currency) -
        window.parseAmountValue(invoice.adjustment_amount, invoice.currency);
    }, 0);
  }

  function netInvoiceSum(commercial) {
    return new Intl.NumberFormat('de-DE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(netInvoiceSumValue(commercial));
  }

  function isCommercialDisregarded(commercial) {
    return Boolean(commercial && (commercial.disregard_invoice || commercial.disregard_type === 'lopeno'));
  }

  function visibleCommercialInvoices(declaration) {
    const showDisregarded = $('#chk-declaration-filter-show-disregarded-invoices').prop('checked');

    return list(declaration && declaration.co_invoices).filter(commercial => {
      const disregarded = isCommercialDisregarded(commercial);
      if (disregarded && !showDisregarded) return false;

      const lope = commercial.lope_no || commercial.group_lope_no;
      return (lope && lope !== '-') || disregarded;
    });
  }

  function commercialInvoices(declaration, declarationIndex) {
    if (!declaration) return '';

    const isSwiss = declaration.country === 'CH';
    // const commercials = list(declaration.co_invoices).filter(commercial => {
    //   const lope = commercial.lope_no || commercial.group_lope_no;
    //   //return lope && lope !== '-';
    //   return (lope && lope !== '-') || commercial.disregard_invoice || commercial.disregard_type === 'lopeno';
    // });
    const commercials = visibleCommercialInvoices(declaration);
    const rows = commercials.map((commercial, index) => {
      const invoiceSum = netInvoiceSum(commercial);    
      const netDifference = Math.abs(window.parseAmountValue(commercial.com_net_amount || commercial.net_amount, commercial.currency) - netInvoiceSumValue(commercial));
      const netAmountWarning = netDifference > 100;
      //const invoiceCount = list(commercial.invoices).length;     
      // const invoiceCount = list(commercial.invoices).filter(invoice => !invoice.disregard_invoice).length;
      // return '<tr class="accordion-button collapsed cw-1 js-commercial-row" data-commercial-index="' + index + '"' + commentTooltip(commercial) + '>' +
      const invoiceCount = list(commercial.invoices).filter(invoice => !invoice.disregard_invoice).length;
      return '<tr class="accordion-button collapsed cw-1 js-commercial-row' + (isCommercialDisregarded(commercial) ? ' disabled' : '') + '" data-commercial-index="' + index + '"' + commentTooltip(commercial) + '>' +
      '<td class="cw-1 declaration-th-w20"></td>' +      
      //'<td class="text-start declaration-th-w150">' + escapeHtml(commercial.lope_no || commercial.group_lope_no) + '</td>' +
      '<td class="text-start declaration-th-w150">' + escapeHtml(commercial.disregard_type === 'lopeno' ? commercial.disregarded_no : (commercial.lope_no || commercial.group_lope_no)) + '</td>' +
      '<td class="text-start declaration-th-w150">' + escapeHtml(commercial.category_desc) + '</td>' +
      '<td class="text-start declaration-th-w150">' + escapeHtml(commercial.o_invoice_date || commercial.invoice_date) + '</td>' +
      '<td class="text-start declaration-th-w150">' + escapeHtml(commercial.co_invoice_no) + ' <span class="' + (invoiceCount === 0 ? 'alert-danger' : 'alert-primary') + ' fs-tiny p-1 ms-1">' + invoiceCount + '</span>' +
        (commercial.orginal_co_invoice_no && commercial.co_invoice_no !== commercial.orginal_co_invoice_no ? '<br><span class="alert-warning text-end fs-tiny p-1">' + escapeHtml(commercial.orginal_co_invoice_no) + '</span>' : '') + '</td>' +    
      '<td class="text-end declaration-th-w150">' + escapeHtml(commercial.statistical_value) + '</td>' +
      '<td class="text-end declaration-th-w150' + (netAmountWarning ? ' text-danger' : '') + '"' + (netAmountWarning ? ' data-bs-toggle="tooltip" data-bs-placement="top" title="Over 100 difference between the commercial invoice and sales invoice net amount"' : '') + '>' + escapeHtml(commercial.com_net_amount || commercial.net_amount) + '</td>' +
      '<td class="text-end declaration-th-w150">' + escapeHtml(invoiceSum) + '</td>' +
      (isSwiss ? '<td class="text-end declaration-th-w150">' + escapeHtml(commercial.convert_net_amount) + '</td>' : '') +
      '<td class="text-end declaration-th-w150">' + escapeHtml(commercial.import_vat) + '</td>' +
      (isSwiss ? '<td class="text-end declaration-th-w150">' + escapeHtml(commercial.convert_vat_amount) + '</td>' : '') +
      '<td class="text-end declaration-th-w150">' + escapeHtml(commercial.duties) + '</td>' +
      '<td class="text-end declaration-th-w150">' + escapeHtml(commercial.vat_on_duties) + '</td>' +
      '<td class="text-end declaration-th-w150">' + escapeHtml(commercial.adjustment) + '</td>' +
      '<td class="text-end declaration-th-w150">' + escapeHtml(commercial.vat_on_adjustment) + '</td>' +
      '<td class="text-center js-action-cell">' + actionMenu(Object.assign({}, commercial, { declaration_index: declarationIndex }), 'com') + '</td></tr>';
    });
    const headers = ['', 'Lope No.', 'Category Desc.', 'Date', 'Commercial invoice', 'Statistical value', 'Net Amount', 'Net Sum Invoice'];
    if (isSwiss) headers.push('Net Amount (CHF)');
    headers.push('Import VAT');
    if (isSwiss) headers.push('VAT Amount (CHF)');
    headers.push('Duties', 'VAT on Duties', 'Adjustment', 'VAT on adjustment', 'Action');
    return childTable(headers, rows, 'No commercial invoices', 'datatables-declaration-co-invoices accordion');
  }
console.log(declarationData);
  const dt = $table.DataTable({
    data: declarationData,
    ordering: true,
    order: [[1, 'asc']],
    paging: true,
    pageLength: 10,
    lengthMenu: [[10, 25, 100], [10, 25, 100]],
    lengthChange: true,
    info: true,
    columns: [
      { data: null, defaultContent: '', orderable: false, searchable: false, className: 'declaration-th-w20', width: '20px' },
      { data: 'o_declaration_date', defaultContent: '-', className: 'main-row' },
      { data: 'declaration_no', defaultContent: '-', className: 'main-row' },
      { data: 'statistical_value', defaultContent: '-', className: 'text-end main-row' },
      { data: 'net_amount', defaultContent: '-', className: 'text-end main-row' },
      { data: 'import_vat', defaultContent: '-', className: 'text-end main-row' },
      { data: 'duties', defaultContent: '-', className: 'text-end main-row' },
      { data: 'vat_on_duties', defaultContent: '-', className: 'text-end main-row' },
      { data: 'adjustment', defaultContent: '-', className: 'text-end main-row' },
      { data: 'vat_on_adjustment', defaultContent: '-', className: 'text-end main-row' },
      { data: null, orderable: false, searchable: false, className: 'text-center js-action-cell', render: (_data, _type, row) => actionMenu(row, 'declaration') }
    ],
    columnDefs: [{ targets: 0, render: () => '<button type="button" class="btn btn-sm btn-icon js-declaration-toggle" aria-expanded="false" title="Show lope numbers"><i class="bx bx-chevron-right"></i></button>' }],   
    createdRow: (row, data) => {
      $(row).addClass('accordion-button collapsed');
      if (data.comment_reason) $(row).attr('data-bs-toggle', 'tooltip').attr('data-bs-offset', '0,4').attr('data-bs-placement', 'top').attr('data-bs-html', 'true').attr('title', '<span>' + escapeHtml(String(data.comment_reason).toUpperCase()) + '</span><br><span>' + escapeHtml(data.comment || '') + '</span>');
    },
    language: { searchPlaceholder: 'Search full period…', search: '', lengthMenu: '_MENU_', emptyTable: 'No declarations in this period' },
    dom: '<"row mx-0 border-bottom p-2 align-items-center"<"col-md-7 declaration-toolbar"><"col-md-5 declaration-search"f>>t<"row mx-2 align-items-center"<"col-md-6"i><"col-md-6"p>>',
    buttons: [
      {
        extend: 'collection', className: 'btn btn-outline-secondary dropdown-toggle', text: '<i class="bx bx-export me-2"></i>Export',
        buttons: [
          { extend: 'print', text: '<i class="bx bx-printer me-2"></i>Print', className: 'dropdown-item' },
          { extend: 'csv', text: '<i class="bx bx-file me-2"></i>Csv', className: 'dropdown-item' },
          { extend: 'excel', text: '<i class="bx bxs-file-export me-2"></i>Excel', className: 'dropdown-item' },
          { extend: 'pdf', text: '<i class="bx bxs-file-pdf me-2"></i>Pdf', className: 'dropdown-item' },
          { extend: 'copy', text: '<i class="bx bx-copy me-2"></i>Copy', className: 'dropdown-item' }
        ]
      },
    ],
    initComplete: function () {
      this.api().columns.adjust();
      initializeTooltips(this.api().table().body());
      const toolbar = $(this.api().table().container()).find('.declaration-toolbar');
      $(this.api().buttons().container()).addClass('d-none');
      toolbar.append('<button type="button" title="Disregard Invoice" class="btn-disregard-invoice btn btn-label-danger btn-sm" disabled data-invoice_name="sales" data-is_disregard="1" data-tab_name="first"><i class="bx bx-list-minus me-1"></i>Disregard Invoice</button>');
      toolbar.append('<button type="button" title="Move Invoice File" class="btn-move-invoice-file btn btn-label-secondary btn-sm" disabled data-invoice_name="sales" data-tab_name="first"><i class="bx bx-move me-1"></i>Move Invoice File</button>');
      $(this.api().table().container()).find('.dataTables_filter').append('<button type="button" class="btn btn-icon btn-label-secondary btn-sm ms-2 js-open-declaration-filter" title="Declaration Filter"><i class="bx bx-slider"></i></button>');
    }
  });

  window.reloadConsolidatedDeclarations = function (declarationResult) {
    const refreshedData = list(declarationResult && declarationResult.declaration_datas);
    const expandedDeclarations = new Set();
    const expandedCommercials = new Set();
    const scrollPosition = { left: window.scrollX, top: window.scrollY };

    dt.rows().every(function () {
      if (!this.child || !this.child.isShown()) return;
      expandedDeclarations.add(declarationKey(this.data()));
      this.child().find('.js-commercial-row.shown').each(function () {
        const commercial = $(this).data('commercial');
        if (commercial) expandedCommercials.add(commercialKey(commercial));
      });
    });

    window.declaration_datas = refreshedData;
    window.declaration_first_datas = refreshedData;
    window.declaration_second_datas = [];
    window.declaration_third_datas = [];

    dt.rows().every(function () {
      if (this.child && this.child.isShown()) this.child.hide();
    });
    dt.clear().rows.add(refreshedData).draw(false);
    dt.columns.adjust();

    dt.rows({ page: 'current' }).every(function () {
      if (!expandedDeclarations.has(declarationKey(this.data()))) return;
      toggleDeclaration(this.node());
      this.child().find('.js-commercial-row').each(function () {
        const commercial = $(this).data('commercial');
        if (commercial && expandedCommercials.has(commercialKey(commercial))) $(this).trigger('click');
      });
    });
    initializeTooltips($table);

    window.requestAnimationFrame(() => window.scrollTo(scrollPosition.left, scrollPosition.top));
  };

  function toggleDeclaration(rowElement) {
    const row = dt.row(rowElement);
    if (!row.any() || !row.data()) return;
    const $row = $(rowElement);
    const $button = $row.find('.js-declaration-toggle');
    if (row.child.isShown()) {
      row.child.hide();
      $row.removeClass('shown').addClass('collapsed');
      $button.attr('aria-expanded', 'false').find('i').attr('class', 'bx bx-chevron-right');
    } else {
      row.child(commercialInvoices(row.data(), row.index()), 'p-0 cw-1').show();
      // const commercials = list(row.data().co_invoices).filter(commercial => {
      //   const lope = commercial.lope_no || commercial.group_lope_no;
      //   //return lope && lope !== '-';
      //   return (lope && lope !== '-') || commercial.disregard_invoice || commercial.disregard_type === 'lopeno';
      // });
      const commercials = visibleCommercialInvoices(row.data());
      row.child().find('.js-commercial-row').each(function (index) {
        $(this).data('commercial', Object.assign({}, commercials[index], { declaration_index: row.index() }));
      });
     
      initializeTooltips(row.child());
      $row.addClass('shown').removeClass('collapsed');
      $button.attr('aria-expanded', 'true').find('i').attr('class', 'bx bx-chevron-down');
    }
  }  

  $table.children('tbody').on('click', function (event) {
    const $row = $(event.target).closest('tr');
    if (!$row.length || $row.parent()[0] !== this || $row.hasClass('child')) return;
    if ($(event.target).closest('.js-action-cell, .dropdown-menu').length) return;
    event.stopPropagation();
    toggleDeclaration($row[0]);
  });

  $table.on('click', '.js-commercial-row', function (event) {
    if ($(event.target).closest('.js-action-cell, .dropdown-menu').length) return;
    event.stopPropagation();
    const $row = $(this);
    const $existing = $row.next('.js-sales-detail');
    if ($existing.length) {
      $existing.remove();
      $row.removeClass('shown dt-hasChild').addClass('collapsed');
      return;
    }
    const commercial = $row.data('commercial') || {};
    const colspan = commercial.country === 'CH' ? 16 : 14;
    $row.after('<tr class="js-sales-detail"><td colspan="' + colspan + '">' + salesInvoices(commercial) + '</td></tr>');
    initializeTooltips($row.next('.js-sales-detail'));
    $row.addClass('shown dt-hasChild').removeClass('collapsed');
  });

  $table.on('click', '.js-view-details', function (event) {
    event.preventDefault();
    event.stopPropagation();
    const $row = $(this).closest('tr');
    if ($row.hasClass('js-commercial-row')) {
      $row.trigger('click');
    } else {
      toggleDeclaration($row[0]);
    }
  });

  $table.on('change', '.js-select-all-sales', function () {
    $(this).closest('table').find('tbody .dt-chk').prop('checked', this.checked).trigger('change');
  });

  $table.on('click', '.js-select-all-sales, .dt-chk, .dt-chk-cell', function (event) {
    event.stopPropagation();
  });

  // $table.on('change', '.dt-chk', function () {
  //   const $salesTable = $(this).closest('table');
  //   $(this).closest('tr').toggleClass('selected', this.checked);
  //   const $checkboxes = $salesTable.find('tbody .dt-chk');
  //   const checked = $checkboxes.filter(':checked').length;
  //   $salesTable.find('.js-select-all-sales').prop('checked', checked === $checkboxes.length).toggleClass('indeterminate', checked > 0 && checked < $checkboxes.length);
  //   $('.btn-disregard-invoice, .btn-move-invoice-file').prop('disabled', checked === 0).toggleClass('btn-label-primary', checked > 0).toggleClass('btn-label-secondary', checked === 0);
  // });

  $table.on('change', '.dt-chk', function () {
      const $salesTable = $(this).closest('table');

      $(this)
          .closest('tr')
          .toggleClass('selected', this.checked);

      const $checkboxes = $salesTable.find('tbody .dt-chk');
      const checked = $checkboxes.filter(':checked').length;

      const $selectAll = $salesTable.find('.js-select-all-sales');

      $selectAll
          .prop(
              'checked',
              $checkboxes.length > 0 &&
              checked === $checkboxes.length
          )
          .prop(
              'indeterminate',
              checked > 0 &&
              checked < $checkboxes.length
          );

      $('.btn-disregard-invoice, .btn-move-invoice-file')
          .prop('disabled', checked === 0)
          .toggleClass('btn-label-primary', checked > 0)
          .toggleClass('btn-label-secondary', checked === 0);
  });

  $table.on('click', '.js-sales-select', function (event) {
    event.stopPropagation();
    const $checkbox = $(this).closest('tr').find('.dt-chk');
    $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
  });

  $table.on('click', '.js-refresh-page', function () {
    window.location.reload();
  });

  $(document).on('click', '.js-refresh-declarations', function () {    
    enableRefresh = $(this);
    createOcrJob();
  });

  $(document).on('click', '.js-export-table', function () {
    const method = $(this).data('export-method');
    if (method && typeof window[method] === 'function') {
      window[method](dt, 'consolidated');
      return;
    }
    const type = $(this).data('export');
    dt.button('.buttons-' + type).trigger();
  });

  const $filter = $('#offcanvasDeclarationFilter').appendTo(document.body);
  $(document).on('click', '.js-open-declaration-filter', function () {
    if ($filter.length) bootstrap.Offcanvas.getOrCreateInstance($filter[0]).show();
  });

  $('#chk-declaration-filter-show-err-lines, #chk-declaration-filter-show-disregarded-invoices').on('change', function () {
    const showDisregardedChanged = this.id === 'chk-declaration-filter-show-disregarded-invoices';

    if (showDisregardedChanged) {
      dt.rows().every(function () {
        if (!this.child || !this.child.isShown()) return;

        const expandedCommercials = new Set();
        this.child().find('.js-commercial-row.shown').each(function () {
          const commercial = $(this).data('commercial');
          if (commercial) expandedCommercials.add(commercialKey(commercial));
        });

        const rowElement = this.node();
        toggleDeclaration(rowElement);
        toggleDeclaration(rowElement);

        this.child().find('.js-commercial-row').each(function () {
          const commercial = $(this).data('commercial');
          if (commercial && expandedCommercials.has(commercialKey(commercial))) $(this).trigger('click');
        });
      });
      return;
    }
    
    $table.find('.js-commercial-row.shown').each(function () { $(this).trigger('click').trigger('click'); });
  });

  $('.card.declarations-new-ocr .sk-bounce').hide();
  $('.card.declarations-new-ocr .card-datatable').show();
  dt.columns.adjust().draw(false);

  // Refresh specific invoice (com./sales)
  // $(document).on('click', '.btn-new-declaration-refresh-invoice', function() { 
  //   var btn_refresh_invoice = $(this);
  //   var data = btn_refresh_invoice.data();

  //   var invoice_id = data['invoice_id'];
  //   var invoice_no = data['invoice_no'];
  //   var invoice_name = data['invoice_name'];
  //   var which_tab = data['tab_name'];
    
  //   btn_refresh_invoice.attr('disabled', 'disabled');
  //   btn_refresh_invoice.html('<i class="bx bx-loader-alt me-1"></i> Refreshing...');

  //   if(btn_refresh_invoice.closest('tr.accordion-button').length > 0)
  //     btn_refresh_invoice.closest('tr.accordion-button').addClass('disabled');    
  //   else
  //     btn_refresh_invoice.closest('tr').addClass('disabled');
    
  //   refreshXhr = $.ajax({
  //       url: `${declarationInvoiceUrl}${invoice_id}/refresh`,
  //       type: 'POST',        
  //       data: {invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : $("#vat_reg_id").val(), 
  //         tab_name: which_tab},        
  //       success: function (result) {        
  //         if(result)    
  //         {                    
  //           enableRefresh = btn_refresh_invoice;
            
  //           refreshIntervalId = setInterval(checkOcrStatus, 2000);
  //           checkOcrStatus();
  //         }
  //       },
  //       error: function (xhr) {
  //         console.error('Invoice refresh failed:', xhr);

  //         enableRefresh = btn_refresh_invoice;
  //         finishRefreshButton();

  //         showRefreshToast(
  //             'Unable to refresh invoice.',
  //             'bg-danger'
  //         );
  //       }
  //     });
  // });

  $(document).on('click', '.btn-new-declaration-refresh-invoice', function () {

      const btn_refresh_invoice = $(this);
      const data = btn_refresh_invoice.data();

      const invoice_id = data['invoice_id'];
      const invoice_no = data['invoice_no'];
      const invoice_name = data['invoice_name'];
      const which_tab = data['tab_name'];

      /*
       * IMPORTANT:
       * Reset state from previous refresh/rematching.
       */
      refreshFinished = false;
      rematchFinished = false;

      stopBatchStatusChecks();
      stopRematchPolling();

      enableRefresh = btn_refresh_invoice;

      btn_refresh_invoice
          .attr('disabled', 'disabled')
          .html(
              '<span class="spinner-border spinner-border-sm me-1" ' +
              'role="status" aria-hidden="true"></span>Refreshing…'
          );

      if (btn_refresh_invoice.closest('tr.accordion-button').length > 0) {
          btn_refresh_invoice
              .closest('tr.accordion-button')
              .addClass('disabled');
      } else {
          btn_refresh_invoice
              .closest('tr')
              .addClass('disabled');
      }

      showRefreshToast(
          'Refreshing invoice…',
          'bg-primary',
          'Refresh Data',
          true
      );

      refreshXhr = $.ajax({
          url: `${declarationInvoiceUrl}${invoice_id}/refresh`,
          type: 'POST',

          data: {
              invoice_no: invoice_no,
              invoice_name: invoice_name,
              vat_reg_id: $('#vat_reg_id').val(),
              tab_name: which_tab
          },

          success: function (result) {

              if (!result) {
                  finishRefreshButton();

                  showRefreshToast(
                      'Unable to start invoice refresh.',
                      'bg-danger',
                      'Refresh Data',
                      false
                  );

                  return;
              }

              /*
               * Start checking OCR status.
               */
              refreshIntervalId = setInterval(
                  checkOcrStatus,
                  2000
              );

              checkOcrStatus();
          },

          error: function (xhr) {

              console.error(
                  'Invoice refresh failed:',
                  xhr
              );

              refreshFinished = true;

              stopBatchStatusChecks();

              finishRefreshButton();

              showRefreshToast(
                  'Unable to refresh invoice.',
                  'bg-danger',
                  'Refresh Data',
                  false
              );
          }
      });
  });
  let rematchPollTimer = null;

  function stopRematchPolling() {
      if (rematchPollTimer !== null) {
          clearTimeout(rematchPollTimer);
          rematchPollTimer = null;
      }
  }

  function startRematching(clientId, refreshButton = null) {
    rematchFinished = false;

    if (!clientId) {
      console.error('Missing clientId for OCR rematching.');
      finishRefreshButton();
      return;
    }
   
    if (refreshButton) {
        enableRefresh = refreshButton instanceof jQuery
            ? refreshButton
            : $(refreshButton);
    }

    stopRematchPolling();

    const toastheader = 'Rematching';
    
    showRefreshToast(
      'Rematching in progress…',
      'bg-primary',
      toastheader,
      true
    );

    $.ajax({
      url: '/rematch-ocr/' + clientId,
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content')
      },

      success: function (response) {

        // if (!response.success) {
        //   showRefreshToast(
        //     response.message || 'Unable to start rematching.',
        //     'bg-danger',
        //     toastheader
        //   );
        //   return;
        // }

        if (!response.success) {
            rematchFinished = true;
            stopRematchPolling();

            showRefreshToast(
                response.message || 'Unable to start rematching.',
                'bg-danger',
                toastheader
            );

            finishRefreshButton();

            return;
        }

        if (response.completed) {
          showRefreshToast(
            response.message || 'No invoices need rematching.',
            'bg-success',
            toastheader
          );

          finishRefreshButton();
          return;
        }

        if (response.waiting) {

          showRefreshToast(
            'OCR synchronization is still running. Rematching will start automatically once it finishes.',
            'bg-warning',
            toastheader,
            true
          );

          rematchPollTimer = setTimeout(function () {
            startRematching(clientId, enableRefresh);
          }, 5000);

          return;
        }

        if (response.batch_id) {

          showRefreshToast(
            'Rematching in progress…',
            'bg-primary',
            toastheader
          );

          checkRematchStatus(
            response.batch_id,
            window.vatRegId
          );

          return;
        }

        // showRefreshToast(
        //   'Invalid rematching response from server.',
        //   'bg-danger',
        //   toastheader
        // );

        rematchFinished = true;
        stopRematchPolling();

        showRefreshToast(
            'Invalid rematching response from server.',
            'bg-danger',
            toastheader
        );

        finishRefreshButton();
      },

      error: function () {

        rematchPollTimer = setTimeout(function () {
          startRematching(clientId, enableRefresh);
        }, 5000);
      }
    });
  }

  function finishRefreshButton() {
    if (!enableRefresh || !enableRefresh.length) {
        resetRefreshButton();
        enableRefresh = null;
        return;
    }

    // Main "Refresh Data" button
    if (enableRefresh.hasClass('js-refresh-declarations')) {
        resetRefreshButton();
    } else {
        // Specific invoice refresh button
        enableRefresh
            .removeAttr('disabled')
            .html('<i class="bx bx-refresh"></i> Refresh Data');

        if(enableRefresh.closest('tr.accordion-button').length > 0)
          enableRefresh
              .closest('tr.accordion-button')
              .removeClass('disabled');
        else
          enableRefresh
              .closest('tr')
              .removeClass('disabled');
    }

    enableRefresh = null;
  }

  // function checkRematchStatus(batchId, vatRegId) {

  //   if (rematchFinished) {
  //       return;
  //   }

  //   if (!batchId) {
  //     console.error('Missing batchId.');
  //     return;
  //   }

  //   $.ajax({
  //     url: '/rematch-ocr/status/' + batchId,
  //     type: 'GET',

  //     data: {
  //       vat_reg_id: vatRegId
  //     },

  //     success: function (response) {
  //       const toastheader = 'Rematching';

  //       if (!response.success) {
          
  //         showRefreshToast(
  //           response.message || 'Unable to check rematching status.',
  //           'bg-danger',
  //           toastheader
  //         );

  //         return;
  //       }
       
  //       showRefreshToast(
  //         'Rematching progress: ' + response.progress + '%',
  //         'bg-success',
  //         toastheader,
  //         true
  //       );

  //       /*
  //        * Completed
  //        */
  //       if (response.finished) {          
         
  //         rematchFinished = true;
  //         stopRematchPolling();
        
  //         /*
  //          * First update the Bootstrap refresh toast.
  //          */
  //         showRefreshToast(
  //           'Rematching completed successfully.',
  //           'bg-success',
  //           toastheader,
  //           false
  //         );

  //         /*
  //          * Clear the Toastr AFTER the Bootstrap toast
  //          * has been displayed.
  //          */         
  //         finishRefreshButton();

  //         /*
  //          * Reload declaration data.
  //          */
  //         const declarationResult =
  //           window.drawDtTable(
  //             response,
  //             'declaration-new-ocr'
  //           );

  //         window.reloadConsolidatedDeclarations(
  //           declarationResult
  //         );

  //         return;
  //       }

  //       /*
  //        * Failed
  //        */
  //       if (response.failed) {
         
  //         rematchFinished = true;
  //         stopRematchPolling();

  //         showRefreshToast(
  //           'Rematching completed with errors.',
  //           'bg-danger',
  //           toastheader,
  //           false
  //         );
          
  //         finishRefreshButton();

  //         return;
  //       }

  //       /*
  //        * Cancelled
  //        */
  //       if (response.cancelled) {
         
  //         rematchFinished = true;
  //         stopRematchPolling();

  //         showRefreshToast(
  //           'Rematching was cancelled.',
  //           'bg-danger',
  //           toastheader,
  //           false
  //         );          

  //         finishRefreshButton();

  //         return;
  //       }

  //       /*
  //        * Still processing.
  //        */
  //       showRefreshToast('Rematching in progress…',
  //         'bg-primary',
  //         toastheader
  //       );

  //       rematchPollTimer = setTimeout(function () {
  //         checkRematchStatus(batchId, vatRegId);
  //       }, 2000);
  //     },

  //     error: function () {

  //       rematchPollTimer = setTimeout(function () {
  //         checkRematchStatus(batchId, vatRegId);
  //       }, 5000);
  //     }
  //   });
  // }

  function checkRematchStatus(batchId, vatRegId) {

      if (rematchFinished) {
          return;
      }

      if (!batchId) {
          console.error('Missing batchId.');
          return;
      }

      const toastheader = 'Rematching';

      // Show immediately while waiting for server response
      showRefreshToast(
          'Rematching in progress… Please wait.',
          'bg-primary',
          toastheader,
          true // persistent
      );

      $.ajax({
          url: '/rematch-ocr/status/' + batchId,
          type: 'GET',

          data: {
              vat_reg_id: vatRegId
          },

          success: function (response) {

              if (!response.success) {
                  rematchFinished = true;
                  stopRematchPolling();

                  showRefreshToast(
                      response.message || 'Unable to check rematching status.',
                      'bg-danger',
                      toastheader,
                      false
                  );

                  finishRefreshButton();
                  return;
              }

              /*
               * Completed
               */
              if (response.finished) {

                  rematchFinished = true;
                  stopRematchPolling();

                  showRefreshToast(
                      'Rematching completed successfully.',
                      'bg-success',
                      toastheader,
                      false // auto-hide after 4 sec
                  );

                  finishRefreshButton();

                  const declarationResult = window.drawDtTable(
                      response,
                      'declaration-new-ocr'
                  );

                  window.reloadConsolidatedDeclarations(
                      declarationResult
                  );

                  return;
              }

              /*
               * Failed
               */
              if (response.failed) {

                  rematchFinished = true;
                  stopRematchPolling();

                  showRefreshToast(
                      'Rematching completed with errors.',
                      'bg-danger',
                      toastheader,
                      false
                  );

                  finishRefreshButton();
                  return;
              }

              /*
               * Cancelled
               */
              if (response.cancelled) {

                  rematchFinished = true;
                  stopRematchPolling();

                  showRefreshToast(
                      'Rematching was cancelled.',
                      'bg-danger',
                      toastheader,
                      false
                  );

                  finishRefreshButton();
                  return;
              }

              /*
               * Still processing
               */
              showRefreshToast(
                  'Rematching progress: ' + (response.progress ?? 0) + '%',
                  'bg-primary',
                  toastheader,
                  true
              );

              rematchPollTimer = setTimeout(function () {
                  checkRematchStatus(batchId, vatRegId);
              }, 2000);
          },

          error: function () {

              // Keep user informed while retrying
              showRefreshToast(
                  'Rematching is still in progress. Checking status…',
                  'bg-warning',
                  toastheader,
                  true
              );

              rematchPollTimer = setTimeout(function () {
                  checkRematchStatus(batchId, vatRegId);
              }, 5000);
          }
      });
  }

  if (window.clientId) {
    startRematching(window.clientId);
  }

});