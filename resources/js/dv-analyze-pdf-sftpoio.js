/**
 * Page sFTp/OIO File List
 */

'use strict';

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
  var analyzePdfSftpOioUrl = baseUrl + 'analyzepdf/';

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  let currentAnalyzeSftpOioClient = '';

  //window.sftpOioCommercialRawData = [];
  window.sftpOioSalesRawData = [];

  let currentPage = 1;
  let lastPage = 1;
  let isLoading = false;
  
  function loadSftpOioData(clientName) {
      if (currentAnalyzeSftpOioClient !== clientName) {
          //sftpOioCommercialRawData = [];
          sftpOioSalesRawData = [];
          
          currentAnalyzeSftpOioClient = clientName;   
          currentPage = 1;
          lastPage = 1;       
      }

      if (isLoading) return;

      isLoading = true;

      if(isLoading)
      {        
        $('#ocr-sftpoio-loading-overlay').removeClass('d-none');
      }
      
      $.ajax({
          url: `${analyzePdfSftpOioUrl}sftpdata`,
          type: "GET",
          data: {
              client_name: clientName,
              page: currentPage
          },

          success: function (result) {

              lastPage = result.last_page;

              let pageData = result.data || [];

              // $.each(pageData, function (index, item) {

              //     if (item.invoice_type === 'com') {

              //         window.sftpOioCommercialRawData.push(item);

              //     } else if (item.invoice_type === 'sales') {

              //         window.sftpOioSalesRawData.push(item);
              //     }
              // });

              window.sftpOioSalesRawData.push(...pageData);

              isLoading = false;

              reloadSftpOioData(
                  //sftpOioCommercialRawData,
                  sftpOioSalesRawData
              );

              // Continue loading next API page
              if (currentPage < lastPage) {

                  currentPage++;

                  setTimeout(function () {
                      loadSftpOioData(clientName);
                  }, 100);

              } else {

                  console.log('All Sftp/Oio data loaded');
               
                  $('#ocr-sftpoio-loading-overlay').addClass('d-none');
              }
              
              $('#ocr-sftpoio-loading-overlay').addClass('d-none');
          },

          error: function (xhr) {
              isLoading = false;
              console.error(xhr);
              
              $('#ocr-sftpoio-loading-overlay').addClass('d-none');
          }
      });
  }

  function reloadSftpOioData(
      //commercialData = [],
      salesData = []
  ) {
      var dt_analyzepdfsftpoio_tables =
          $('.datatables-analyzepdfsftpoio');

      // for (
      //     var i = 0;
      //     i < dt_analyzepdfsftpoio_tables.length;
      //     i++
      // ) {

           var analyzepdfsftpoio_name = '';

      //     if (i === 0) {
      //         analyzepdfsftpoio_name = 'commercial-invoice';
      //     }
      //     else if (i === 1) {
              analyzepdfsftpoio_name = 'sales-invoice';
          // }

          var tableSelector =
              ".datatables-" +
              analyzepdfsftpoio_name +
              "-analyzepdfsftpoio";

          if ($(tableSelector).length === 0) {
              //continue;
            return;
          }

          if ($.fn.DataTable.isDataTable(tableSelector)) {

              var dt_analyzepdfsftpoio =
                  $(tableSelector).DataTable();

              var rowsData = [];

              // if (i === 0) {
              //     rowsData = commercialData;
              // }
              // else if (i === 1) {
                  rowsData = salesData;
              //}

              dt_analyzepdfsftpoio
                  .clear()
                  .rows
                  .add(rowsData)
                  .draw();

              $("#btn-analyzepdfsftpoio-" +
                  analyzepdfsftpoio_name
              ).html(rowsData.length);
          }
      //}
  }
/*
  function applySftpOioFilter(selectedClientName = '') {

      const clientNo =
          $('#filter_client_no').val().trim().toLowerCase();
      
      const clientName =
          selectedClientName?.trim().toLowerCase();

      const invoiceDate =
          $('#filter_invoice_date').val();

      const invoiceNo =
          $('#filter_invoice_no').val().trim().toLowerCase();

      const currency =
          $('#filter_currency').val();

      const creditNote =
          $('#filter_credit_note').is(':checked');

      const netAmount =
          $('#filter_net_amount').val().trim();

      const vatAmount =
          $('#filter_vat_amount').val().trim();

      const totalAmount =
          $('#filter_total_amount').val().trim();


      function filterData(data, isSales = false) {

          return data.filter(function (item) {

              if (
                  clientNo &&
                  !String(item.client_no || '')
                      .toLowerCase()
                      .includes(clientNo)
              ) {
                  return false;
              }

              if (
                  clientName &&
                  !String(item.client_name || '')
                      .toLowerCase()
                      .includes(clientName)
              ) {
                  return false;
              }

              if (invoiceDate) {

                  const itemDate =
                      String(item.invoice_date || '')
                          .substring(0, 10);

                  if (itemDate !== invoiceDate) {
                      return false;
                  }
              }

              if (
                  invoiceNo &&
                  !String(item.invoice_no || '')
                      .toLowerCase()
                      .includes(invoiceNo)
              ) {
                  return false;
              }

              if (
                  currency &&
                  String(item.currency || '').toUpperCase() !==
                  currency.toUpperCase()
              ) {
                  return false;
              }

              if (isSales && creditNote) {

                  const isCreditNote =
                      item.credit_note === true ||
                      item.credit_note === 1 ||
                      item.credit_note === '1';

                  if (!isCreditNote) {
                      return false;
                  }
              }

              if (netAmount) {

                  const itemValue = parseFloat(
                      String(item.net_amount || '')
                          .replace(/,/g, '')
                  );

                  const filterValue = parseFloat(
                      netAmount.replace(/,/g, '')
                  );

                  if (itemValue !== filterValue) {
                      return false;
                  }
              }

              if (vatAmount) {

                  const itemValue = parseFloat(
                      String(item.vat_amount || '')
                          .replace(/,/g, '')
                  );

                  const filterValue = parseFloat(
                      vatAmount.replace(/,/g, '')
                  );

                  if (itemValue !== filterValue) {
                      return false;
                  }
              }

              if (totalAmount) {

                  const itemValue = parseFloat(
                      String(item.total_amount || '')
                          .replace(/,/g, '')
                  );

                  const filterValue = parseFloat(
                      totalAmount.replace(/,/g, '')
                  );

                  if (itemValue !== filterValue) {
                      return false;
                  }
              }

              return true;
          });
      }


      // Filter the ORIGINAL stored data
      // const filteredCommercial =
      //     filterData(
      //         window.sftpOioCommercialRawData || [],
      //         false
      //     );

      const filteredSales =
          filterData(
              window.sftpOioSalesRawData || [],
              true
          );


      // Directly load filtered data into DataTables
      reloadSftpOioData(
          //filteredCommercial,
          filteredSales
      );
  }

  $('.btn-analyzepdf-filter').on('click', function () { 
    const selectedClient =
        $('.btn-syncdb-client').attr('data-selected-client') || '';

    applySftpOioFilter(selectedClient);   
  });
  */

  $(document).on('dblclick', '.datatables-analyzepdfsftpoio tbody tr', function () {
      const irFileId = $(this).attr('id').replace('invoice_', '');

      $("#offcanvasAnalyzePdfData").offcanvas('show');
      loadSFtpOioItem(irFileId);
  });
  
  const $clientSelectSftpOio = $('#select2OcrSftpOioClient');

  if ($clientSelectSftpOio.length) {

      $clientSelectSftpOio.on('change', function () {
          const clientName = $(this).val() || '';          

          if (clientName) {
              loadSftpOioData(clientName);
          }
      });

      const clientName = $clientSelectSftpOio.val() || '';

      if (clientName) {
          loadSftpOioData(clientName);
      }
  }
/*
  clearFilter();
  // =========================
  // CLEAR FILTER
  // =========================
  $('.btn-analyzepdf-clear-filter').on('click', function () {
    clearFilter();      
  });

  function clearFilter()
  {
    // Clear all filter inputs
    $('.form-analyzepdf-filter')[0].reset();

    // Explicitly clear fields if needed
    $('#filter_client_no').val('');
    $('#filter_client_name').val('');
    $('#filter_invoice_date').val('');
    $('#filter_invoice_no').val('');
    $('#filter_currency').val('');
    $('#filter_credit_note').prop('checked', false);
    $('#filter_net_amount').val('');
    $('#filter_vat_amount').val('');
    $('#filter_total_amount').val('');
   
    $clientSelectSftpOio.trigger('change');

    //const filteredCommercial = [];

    const filteredSales = [];

    reloadSftpOioData(
        //filteredCommercial,
        filteredSales
    );

    // Close filter panel
    $('#offcanvasAnalyzePdfFilter').offcanvas('hide');
  }

  // =========================
  // CANCEL
  // =========================
  $('.form-analyzepdf-filter').on(
      'click',
      '[data-bs-dismiss="offcanvas"]',
      function () {

          // Do NOT change DataTable data.
          // Just close the offcanvas.

          $('#offcanvasAnalyzePdfFilter').offcanvas('hide');
      }
  );
*/
  var dt_analyzepdfsftpoio_tables = $('.datatables-analyzepdfsftpoio');

  for (var i = 0; i < dt_analyzepdfsftpoio_tables.length; i++) {

    var dt_analyzepdfsftpoio_table = $(dt_analyzepdfsftpoio_tables[i]);

    if (dt_analyzepdfsftpoio_table) {

      var analyzepdfsftpoio_filter_class = '';//'d-none';
      let analyzepdfsftpoio_name = '';
      var analyzepdfsftpoio_datas = [];

      // if (i === 0) {
      //   analyzepdfsftpoio_filter_class = '';
      //   analyzepdfsftpoio_name = 'commercial-invoice';
      //   analyzepdfsftpoio_datas = window.sFtpOioCommercialRawData;
      // }
      // else if (i === 1) {
        analyzepdfsftpoio_name = 'sales-invoice';
        analyzepdfsftpoio_datas = window.sFtpOioSalesRawData;
      //}
    
      let columns = [];
      let columntargets = [];
      let actiontargets = 9;

      let invoiceDateIndex = -1;     
      let euroIndexes = [];
      let relatedInvoiceIndex = -1;
      let fetchDateIndex = -1;

      // // ===================== COMMERCIAL =====================
      // if (i === 0) {        

      //   columns = [
      //     {
      //         data: 'id',
      //         width: '100px',
      //         orderable: false,
      //         searchable: false,
      //         render: function (data, type, row, meta) {
      //             return meta.row + meta.settings._iDisplayStart + 1;
      //         }
      //     },
      //     { data: 'client_no', width: '150px' },
      //     { data: 'client_name', width: '250px' },
      //     { data: 'invoice_no', width: '200px' },
      //     { data: 'invoice_date', width: '150px' },
      //     { data: 'currency', width: '150px' },
      //     { data: 'net_amount', width: '150px', className: 'text-end' },
      //     { data: 'related_sales_invoices', width: '220px' },
      //     { data: 'created_at', width: '220px' },
      //     { data: 'action', defaultContent: '', width: '150px' }
      //   ];

      //   columntargets = [0,1,2,3,4,5,6,7,8];

      //   actiontargets = 9;
      //   invoiceDateIndex = 4;
      //   //netAmountIndex = 6;
      //   euroIndexes = [6];
      //   relatedInvoiceIndex = 7;
      //   fetchDateIndex = 8;
      // }

      // ===================== SALES =====================
      //else if (i === 1) {
       
        columns = [
          {
              data: 'id',
              width: '100px',
              orderable: false,
              searchable: false,
              render: function (data, type, row, meta) {
                  return meta.row + meta.settings._iDisplayStart + 1;
              }
          },
          { data: 'client_no', width: '150px' },
          { data: 'client_name', width: '250px' },
          { data: 'invoice_no', width: '200px' },
          { data: 'invoice_date', width: '150px' },
          { data: 'currency', width: '150px' },
          {
              data: 'credit_note',
              width: '150px',
              render: function (data, type) {
                  if (type === 'sort' || type === 'type') {
                      return data ? 1 : 0;
                  }

                  return data == 1 ? 'True' : 'False';
              }
          },
          { data: 'calc_net_amount', width: '150px', className: 'text-end', render: amountRender },
          { data: 'net_amount', width: '150px', className: 'text-end', render: amountRender },
          { data: 'vat_rate', width: '150px', className: 'text-end' },
          { data: 'vat_amount', width: '150px', className: 'text-end', render: amountRender },
          { data: 'variance', width: '150px', className: 'text-end', render: amountRender },
          { data: 'additional_amount', width: '150px', className: 'text-end', render: amountRender },
          { data: 'adjustment_amount', width: '150px', className: 'text-end', render: amountRender },
          { data: 'total_amount', width: '150px', className: 'text-end', render: amountRender },

          { data: 'exchange_currency', width: '150px' },
          { data: 'exchange_rate', width: '150px', className: 'text-end', render: exchangeRateRender },
          { data: 'exchange_net_amount', width: '150px', className: 'text-end', render: amountRender },
          { data: 'exchange_vat_amount', width: '150px', className: 'text-end', render: amountRender },
          { data: 'exchange_total_amount', width: '150px', className: 'text-end', render: amountRender },

          { data: 'created_at', width: '220px' },
          { data: 'action', defaultContent: '', width: '150px' }
        ];

        columntargets = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20];

        actiontargets = 21;
        invoiceDateIndex = 4;
        //netAmountIndex = 7;
        euroIndexes = [7, 8, 10, 11, 12, 13, 14];
        fetchDateIndex = 20;
      //}
      
      // ===================== INIT DATATABLE =====================
      var dt_analyzepdfsftpoio = dt_analyzepdfsftpoio_table.DataTable({

        data: analyzepdfsftpoio_datas,
        rowId: function (data) {
            return 'invoice_' + data.id;
        },
        scrollCollapse: true,
        scrollX: true,
        ordering: true,
        autoWidth: false,
        responsive: false,
        pageLength: 100,

        columns: columns,       
        columnDefs: [

          // ================= DATE SORT FIX =================
          {
            targets: invoiceDateIndex,
            render: function (data, type) {
              if (!data) return '';

              let m = moment(data, [
                'YYYY-MM-DD',
                'DD-MM-YYYY',
                'YYYY/MM/DD',
                'DD/MM/YYYY'
              ], true);

              if (type === 'sort' || type === 'type') {
                return m.isValid() ? m.format('YYYYMMDD') : '00000000';
              }

              return data;
            }
          },
        
          {
            targets: euroIndexes,
            className: 'text-end',
            render: function (data, type) {             
              const numericValue = parseEuropeanNumber(data) || 0;

              // Sorting / type detection
              if (type === 'sort' || type === 'type') {
                  return numericValue;
              }

              // Display
              return numericValue.toLocaleString('de-DE', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
              });
            }
          },
          // ================= FETCH DATE SORT FIX =================
          {
            targets: fetchDateIndex,
            render: function (data, type) {

                if (!data) return '';

                let m = moment(data, [
                    'YYYY-MM-DDTHH:mm:ss.SSSSSSZ',
                    'YYYY-MM-DD',
                    'DD-MM-YYYY',
                    'YYYY/MM/DD',
                    'DD/MM/YYYY',
                    'DD-MM-YYYY hh:mm A'
                ], true);

                if (!m.isValid()) {
                    return data;
                }

                // Sort only by date
                if (type === 'sort' || type === 'type') {
                    return m.format('YYYYMMDD');
                }

                // Display date + time
                return m.format('DD-MM-YYYY hh:mm A');
            }
        },
         
          // ...(relatedInvoiceIndex >= 0 ? [{
          //   targets: relatedInvoiceIndex,
          //   render: function (data, type, full) {

          //     let arr = full.related_sales_invoices || [];
             
          //     if (type === 'sort' || type === 'type') {
          //       return arr.length;
          //     }

          //     if (type === 'filter') {
          //       return arr.join(' ');
          //     }

          //     if (arr.length === 1) return arr[0];
          //     if (arr.length > 1) return arr[0] + " ...";

          //     return '';
          //   }
          // }] : []),
          // ================= ACTION FIX =================
          {
            targets: actiontargets,
            orderable: false,
            searchable: false,
            render: function (data, type, full, meta) { 
                return `<div class="d-inline-block">
                          <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                          </a>
                          <ul class="dropdown-menu dropdown-menu-end m-0">

                            <li>
                              <a href="javascript:;" 
                                class="dropdown-item btn-show-pdf" 
                                id="show-analyzepdf"
                                title="Show Data"

                                data-ir_file_id="` + full['ir_file_id'] + `"
                                data-tab_name="` + analyzepdfsftpoio_name + `"
                                data-invoice_no="` + (full['invoice_no'] || full['declaration_no'] || '') + `"

                                >

                                <span>
                                  <i class="bx bx-show me-2"></i>Show Data
                                </span>
                              </a>
                            </li>

                          </ul>
                        </div>`;
            }                
          },

          // ================= GENERAL COLUMNS =================
          {
            targets: columntargets,
            searchable: true,
            orderable: true
          }
        ],

        order: [[invoiceDateIndex, 'desc']],

        dom:
          '<"row mx-0 '+ analyzepdfsftpoio_name +'-sftpoio-filter '+ analyzepdfsftpoio_filter_class +'"' +
          '<"col-sm-12 col-md-6 sub-btns text-start my-auto">' +
          '<"col-sm-12 col-md-6"plfB>' +          
          '>r' +
          '<"row mx-0"<"col-sm-12 p-0"t>>' +
          '<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

        buttons: [

          // ================= EXPORT (UNCHANGED) =================
          {
            extend: 'collection',
            className: 'btn btn-outline-secondary dropdown-toggle ml-3',
            text: '<i class="bx bx-export me-2"></i>Export',
            autoClose: true,
            buttons: [

              {
                extend: 'print',
                title: 'OCR - Print',
                text: '<i class="bx bx-printer me-2"></i>Print',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets }
              },

              {
                extend: 'csv',
                title: 'OCR - CSV',
                text: '<i class="bx bx-file me-2"></i>Csv',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets }
              },

              {
                extend: 'excel',
                title: 'OCR - Excel',
                text: '<i class="bx bxs-file-export me-2"></i>Excel',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets },                          
                action: function (e, dt, node, config) {
                    exportToExcel(dt, analyzepdfsftpoio_name); 
                }
              },

              {
                extend: 'pdf',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                title: 'OCR - PDF',
                text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets }
              },

              {
                extend: 'copy',
                title: 'OCR - Copy',
                text: '<i class="bx bx-copy me-2"></i>Copy',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets }
              }

            ]
          }
        ],
        
        language: {
          processing: '<div class="sk-bounce sk-primary sk-center">' +
                        '<div class="sk-bounce-dot"></div>' +
                        '<div class="sk-bounce-dot"></div>' +
                      '</div>',
          sLengthMenu: '_MENU_',
          search: '',
          searchPlaceholder: 'Search..',
          infoEmpty: 'No entries to show',
          info : '_START_ to _END_ of _TOTAL_',          
          infoFiltered: ' - filtered from _MAX_ records'
        },        

        initComplete: function () {
          const api = this.api();
         
          function fixLayout() {
            api.columns.adjust();
            api.columns.adjust();

            // IMPORTANT: force header/body sync in scrollX mode
            $(api.table().node())
              .css('width', '100%');

            $(api.table().container())
              .find('table')
              .css('width', '100%');
          }

          requestAnimationFrame(fixLayout);

          setTimeout(fixLayout, 50);
          setTimeout(fixLayout, 150);
          setTimeout(fixLayout, 400);

          const $tableWrapper = $(api.table().container()).find('.dataTables_scroll');
          const $scrollBody   = $tableWrapper.find('.dataTables_scrollBody');
          const $topScroll    = $('#top-scroll-navs-analyzepdfsftpoio-' + analyzepdfsftpoio_name);
          const $topInner     = $topScroll.find('.dt-top-scroll-inner');

          let isSyncing = false;

          function syncWidth() {
              if ($scrollBody.length) {

                  let scrollBodyEl = $scrollBody.get(0);

                  // FORCE DataTables layout recalculation first
                  api.columns.adjust();

                  setTimeout(function () {
                      $topInner.width(scrollBodyEl.scrollWidth);
                  }, 50);
              }
          }

          // Remove previous handlers to avoid duplicates
          $scrollBody.off('scroll.dtTop');
          $topScroll.off('scroll.dtTop');

          // Sync scrolling
          $scrollBody.on('scroll.dtTop', function () {
              if (isSyncing) return;
              isSyncing = true;
              $topScroll.scrollLeft(this.scrollLeft);
              isSyncing = false;
          });

          $topScroll.on('scroll.dtTop', function () {
              if (isSyncing) return;
              isSyncing = true;
              $scrollBody.scrollLeft(this.scrollLeft);
              isSyncing = false;
          });

          // Initial sync
          syncWidth();

          // Re-sync on redraw
          api.on('draw.dtTop', syncWidth);

          // Re-sync on resize
          $(window).off('resize.dtTop').on('resize.dtTop', syncWidth);

          // Re-sync on tab show
          // $('a[data-bs-toggle="tab"]').off('shown.bs.tab.dtTop')
          //     .on('shown.bs.tab.dtTop', syncWidth);

          $("." + analyzepdfsftpoio_name + "-sftpoio-filter")
            .appendTo('.dt-sftpoio-filter');

          // var sliderfilter =  '<label class="mx-3 cursor-pointer analyzepdf-filter-disabled" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnalyzePdfFilter" aria-controls="offcanvasAnalyzePdfFilter">' +
          //                       '<i class="bx bx-slider"></i>' +
          //                     '</label>';
          // $(sliderfilter).appendTo('.'+ analyzepdfsftpoio_name +'-sftpoio-filter .dataTables_filter');
         
          // $("."+ analyzepdfsftpoio_name +"-sftpoio-filter .dt-buttons.btn-group.flex-wrap").appendTo('.dt-analyzepdfsftpoio-export .'+ analyzepdfsftpoio_name +'-analyzepdfsftpoio-export');


          var analyzepdfsftpoio_total = api.data().length;

          $("#btn-analyzepdfsftpoio-" + analyzepdfsftpoio_name)
            .html(analyzepdfsftpoio_total);         
        }
      });
    }
  }

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);   

  function amountRender(data, type, row) {
      if (data == null || data === '') {
          return '';
      }

      let value = parseEuropeanNumber(data);

      // Credit note → make amount negative
      if (row.credit_note == 1 || row.credit_note === true) {
          value = -Math.abs(value);
      }

      // Keep sorting numeric
      if (type === 'sort' || type === 'type') {
          return value;
      }

      // Display European format
      return value.toLocaleString('de-DE', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
      });
  }

  function exchangeRateRender(data, type) {

      if (data == null || data === '') {
          return '';
      }

      let value = parseEuropeanNumber(data);

      // Keep sorting numeric
      if (type === 'sort' || type === 'type') {
          return value;
      }

      // European format: 1.234,5678
      return value.toLocaleString('de-DE', {
          minimumFractionDigits: 4,
          maximumFractionDigits: 4
      });
  }

  // show pdf
  $(document).on('click', '#show-analyzepdf', function () {
    $("#offcanvasAnalyzePdfData").offcanvas('show');    
    loadSFtpOioItem($(this).data('ir_file_id')); 
  });

  // $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {  
  //   var id = $(e.target).attr("id") // activated tab
   
  //   if(id == 'btn-analyzepdfsynced-commercial-invoice')
  //   {            
  //     $(".dt-analyzepdfsynced-export .commercial-invoice-analyzepdfsynced-export").removeClass('d-none');  
  //     $(".dt-analyzepdfsynced-export .sales-invoice-analyzepdfsynced-export").addClass('d-none');     
  //     $(".dt-analyzepdfsynced-export .declaration-analyzepdfsynced-export").addClass('d-none');

  //     $(".dt-synced-filter .commercial-invoice-synced-filter").removeClass('d-none');    
  //     $(".dt-synced-filter .sales-invoice-synced-filter").addClass('d-none');    
  //     $(".dt-synced-filter .declaration-synced-filter").addClass('d-none');          
  //   }
  //   else if(id == 'btn-analyzepdfsynced-sales-invoice')
  //   {            
  //     $(".dt-analyzepdfsynced-export .commercial-invoice-analyzepdfsynced-export").addClass('d-none');  
  //     $(".dt-analyzepdfsynced-export .sales-invoice-analyzepdfsynced-export").removeClass('d-none');     
  //     $(".dt-analyzepdfsynced-export .declaration-analyzepdfsynced-export").addClass('d-none');

  //     $(".dt-synced-filter .commercial-invoice-synced-filter").addClass('d-none');    
  //     $(".dt-synced-filter .sales-invoice-synced-filter").removeClass('d-none');    
  //     $(".dt-synced-filter .declaration-synced-filter").addClass('d-none');       
  //   } 
  //   else if(id == 'btn-analyzepdfsynced-declaration')
  //   {      
  //     $(".dt-analyzepdfsynced-export .commercial-invoice-analyzepdfsynced-export").addClass('d-none');  
  //     $(".dt-analyzepdfsynced-export .sales-invoice-analyzepdfsynced-export").addClass('d-none');     
  //     $(".dt-analyzepdfsynced-export .third-analyzepdfsynced-export").removeClass('d-none');

  //     $(".dt-synced-filter .commercial-invoice-synced-filter").addClass('d-none');    
  //     $(".dt-synced-filter .sales-invoice-synced-filter").addClass('d-none');    
  //     $(".dt-synced-filter .declaration-synced-filter").removeClass('d-none');      
  //   }  
  // });    

  // $('[data-bs-toggle="tab"]').off('shown.bs.tab.dtFix').on('shown.bs.tab.dtFix', function (e) {
  //   let target = $(e.target).attr('id');

  //   if (target === 'btn-analyzepdfsynced-commercial-invoice' || target === 'btn-analyzepdfsynced-sales-invoice') {

  //     setTimeout(function () {

  //       $('.datatables-analyzepdfsynced').each(function () {

  //         let table = $(this).DataTable();

  //         $(table.table().node()).css('width', '100%');

  //         table.columns.adjust().draw(false);
  //         table.columns.adjust();

  //       });

  //     }, 400);
  //   }
  // });  
  
});
