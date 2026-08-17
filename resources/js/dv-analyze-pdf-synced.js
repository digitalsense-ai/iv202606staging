/**
 * Page OCR Invoice PDF File Synced List
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
  var analyzePdfSyncedUrl = baseUrl + 'analyzepdf/';

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  window.syncedCommercialRawData = [];
  window.syncedSalesRawData = [];

  let currentPage = 1;
  let lastPage = 1;
  let isLoading = false;
  function loadSyncedData() {
      if (isLoading) return;

      isLoading = true;

      $(".card.analyzepdfsynced .sk-bounce").show();
      
      $.ajax({
          url: `${analyzePdfSyncedUrl}synceddbdata`,
          type: "GET",
          data: {
              page: currentPage
          },

          success: function (result) {

              lastPage = result.last_page;

              let pageData = result.data || [];

              $.each(pageData, function (index, item) {

                  if (item.invoice_type === 'com') {

                      window.syncedCommercialRawData.push(item);

                  } else if (item.invoice_type === 'sales') {

                      window.syncedSalesRawData.push(item);
                  }
              });

              isLoading = false;

              console.log(
                  'Loaded page:',
                  currentPage,
                  'Commercial:',
                  window.syncedCommercialRawData.length,
                  'Sales:',
                  window.syncedSalesRawData.length
              );

              // Continue loading next API page
              if (currentPage < lastPage) {

                  currentPage++;

                  setTimeout(function () {
                      loadSyncedData();
                  }, 100);

                  //return;
              }

              // // ALL API DATA IS NOW STORED
              // console.log('Finished loading synced data');

              // console.log(
              //     'Total Commercial:',
              //     window.syncedCommercialRawData.length
              // );

              // console.log(
              //     'Total Sales:',
              //     window.syncedSalesRawData.length
              // );

              $(".card.analyzepdfsynced .sk-bounce").hide();
              $(".card.analyzepdfsynced .card-header").show();

              //$('.analyzepdf-filter-disabled').removeClass('disabled').addClass('cursor-pointer');
          },

          error: function (xhr) {
              isLoading = false;
              console.error(xhr);

              $(".card.analyzepdfsynced .sk-bounce").hide();
              $(".card.analyzepdfsynced .card-header").show();
          }
      });
  }

  loadSyncedData();

  function reloadSyncedDbData(
      commercialData = [],
      salesData = []
  ) {
      var dt_analyzepdfsynced_tables =
          $('.datatables-analyzepdfsynced');

      for (
          var i = 0;
          i < dt_analyzepdfsynced_tables.length;
          i++
      ) {

          var analyzepdfsynced_name = '';

          if (i === 0) {
              analyzepdfsynced_name = 'commercial-invoice';
          }
          else if (i === 1) {
              analyzepdfsynced_name = 'sales-invoice';
          }

          var tableSelector =
              ".datatables-" +
              analyzepdfsynced_name +
              "-analyzepdfsynced";

          if ($(tableSelector).length === 0) {
              continue;
          }

          if ($.fn.DataTable.isDataTable(tableSelector)) {

              var dt_analyzepdfsynced =
                  $(tableSelector).DataTable();

              var rowsData = [];

              if (i === 0) {
                  rowsData = commercialData;
              }
              else if (i === 1) {
                  rowsData = salesData;
              }

              dt_analyzepdfsynced
                  .clear()
                  .rows
                  .add(rowsData)
                  .draw();

              $("#btn-analyzepdfsynced-" +
                  analyzepdfsynced_name +
                  " span"
              ).html(rowsData.length);
          }
      }
  }

  $('.btn-analyzepdf-filter').on('click', function () {

      const clientNo =
          $('#filter_client_no').val().trim().toLowerCase();

      const clientName =
          $('#filter_client_name').val().trim().toLowerCase();

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
      const filteredCommercial =
          filterData(
              window.syncedCommercialRawData || [],
              false
          );

      const filteredSales =
          filterData(
              window.syncedSalesRawData || [],
              true
          );


      // Directly load filtered data into DataTables
      reloadSyncedDbData(
          filteredCommercial,
          filteredSales
      );
  });

  // =========================
  // CLEAR FILTER
  // =========================
  $('.btn-analyzepdf-clear-filter').on('click', function () {

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

      // const filteredCommercial =
      //     window.syncedCommercialRawData || [];

      // const filteredSales =
      //     window.syncedSalesRawData || [];

      const filteredCommercial = [];

      const filteredSales = [];

      reloadSyncedDbData(
          filteredCommercial,
          filteredSales
      );

      // Close filter panel
      $('#offcanvasAnalyzePdfFilter').offcanvas('hide');
  });


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

  var dt_analyzepdfsynced_tables = $('.datatables-analyzepdfsynced');

  for (var i = 0; i < dt_analyzepdfsynced_tables.length; i++) {

    var dt_analyzepdfsynced_table = $(dt_analyzepdfsynced_tables[i]);

    if (dt_analyzepdfsynced_table) {

      var analyzepdfsynced_filter_class = 'd-none';
      let analyzepdfsynced_name = '';
      var analyzepdfsynced_datas = [];

      if (i === 0) {
        analyzepdfsynced_filter_class = '';
        analyzepdfsynced_name = 'commercial-invoice';
        analyzepdfsynced_datas = window.syncedCommercialRawData;
      }
      else if (i === 1) {
        analyzepdfsynced_name = 'sales-invoice';
        analyzepdfsynced_datas = window.syncedSalesRawData;
      }
      // else if (i === 2) {
      //   analyzepdfsynced_name = 'declaration';
      //   analyzepdfsynced_datas = analyzepdf_synced_declaration_datas;
      // }

      let columns = [];
      let columntargets = [];
      let actiontargets = 9;

      let invoiceDateIndex = -1;
      let netAmountIndex = -1;
      let relatedInvoiceIndex = -1;
      let fetchDateIndex = -1;

      // ===================== COMMERCIAL =====================
      if (i === 0) {        

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
          { data: 'net_amount', width: '150px', className: 'text-end' },
          { data: 'related_sales_invoices', width: '220px' },
          { data: 'created_at', width: '220px' },
          { data: 'action', defaultContent: '', width: '150px' }
        ];

        columntargets = [0,1,2,3,4,5,6,7,8];

        actiontargets = 9;
        invoiceDateIndex = 4;
        netAmountIndex = 6;
        relatedInvoiceIndex = 7;
        fetchDateIndex = 8;
      }

      // ===================== SALES =====================
      else if (i === 1) {
       
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
        netAmountIndex = 7;
        fetchDateIndex = 20;
      }

      // ===================== DECLARATION =====================
      // else if (i === 2) {

      //   columns = [
      //     { data: 'fake_id', width: '100px' },
      //     { data: 'client_no', width: '150px' },
      //     { data: 'client_name', width: '200px' },
      //     { data: 'declaration_no', width: '200px' },
      //     { data: 'expo_no', width: '200px' },
      //     { data: 'invoice_date', width: '150px' },
      //     { data: 'currency', width: '150px' },
      //     { data: 'net_amount', width: '150px', className: 'text-end' },
      //     { data: 'duties', width: '150px', className: 'text-end' },
      //     { data: 'adjustment', width: '150px', className: 'text-end' },
      //     { data: 'reference_no', width: '200px' },
      //     { data: 'created_at', width: '220px' },
      //     //{ data: 'action', defaultContent: '', width: '150px' }
      //   ];

      //   columntargets = [0,1,2,3,4,5,6,7,8,9,10,11];

      //   //actiontargets = 12;
      //   invoiceDateIndex = 5;
      //   netAmountIndex = 7;
      //   fetchDateIndex = 11;
      // }

      // ===================== INIT DATATABLE =====================
      var dt_analyzepdfsynced = dt_analyzepdfsynced_table.DataTable({

        data: analyzepdfsynced_datas,
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

          // ================= NUMBER SORT FIX =================
          {
            targets: netAmountIndex,
            className: 'text-end',
            render: function (data, type) {
              if (type === 'sort' || type === 'type') {
                return parseFloat(String(data).replace(/,/g, '')) || 0;
              }
              return data;
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
         
          ...(relatedInvoiceIndex >= 0 ? [{
            targets: relatedInvoiceIndex,
            render: function (data, type, full) {

              let arr = full.related_sales_invoices || [];
             
              if (type === 'sort' || type === 'type') {
                return arr.length;
              }

              if (type === 'filter') {
                return arr.join(' ');
              }

              if (arr.length === 1) return arr[0];
              if (arr.length > 1) return arr[0] + " ...";

              return '';
            }
          }] : []),
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

                                data-analyzepdf_id="` + full['ocr_pdf_id'] + `"
                                data-tab_name="` + analyzepdfsynced_name + `"
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
          '<"row mx-0 '+ analyzepdfsynced_name +'-synced-filter '+ analyzepdfsynced_filter_class +'"' +
          '<"col-sm-12 col-md-6 sub-btns text-start my-auto">' +
          '<"col-sm-12 col-md-6"lfB>' +
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
                    exportToExcel(dt, analyzepdfsynced_name); 
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
          const $topScroll    = $('#top-scroll-navs-analyzepdfsynced-' + analyzepdfsynced_name);
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
          $('a[data-bs-toggle="tab"]').off('shown.bs.tab.dtTop')
              .on('shown.bs.tab.dtTop', syncWidth);

          $("." + analyzepdfsynced_name + "-synced-filter")
            .appendTo('.dt-synced-filter');

          var sliderfilter =  '<label class="mx-3 cursor-pointer analyzepdf-filter-disabled" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnalyzePdfFilter" aria-controls="offcanvasAnalyzePdfFilter">' +
                                '<i class="bx bx-slider"></i>' +
                              '</label>';
          $(sliderfilter).appendTo('.'+ analyzepdfsynced_name +'-synced-filter .dataTables_filter');
         
          $("."+ analyzepdfsynced_name +"-synced-filter .dt-buttons.btn-group.flex-wrap").appendTo('.dt-analyzepdfsynced-export .'+ analyzepdfsynced_name +'-analyzepdfsynced-export');


          var analyzepdfsynced_total = api.data().length;

          $("#btn-analyzepdfsynced-" + analyzepdfsynced_name + " span")
            .html(analyzepdfsynced_total);

          ///$(".card.analyzepdfsynced .sk-bounce").hide();
          //$(".card.analyzepdfsynced .card-datatable").show();
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
    loadItem($(this).data('analyzepdf_id')); 
  });

  $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {  
    var id = $(e.target).attr("id") // activated tab
   
    if(id == 'btn-analyzepdfsynced-commercial-invoice')
    {            
      $(".dt-analyzepdfsynced-export .commercial-invoice-analyzepdfsynced-export").removeClass('d-none');  
      $(".dt-analyzepdfsynced-export .sales-invoice-analyzepdfsynced-export").addClass('d-none');     
      $(".dt-analyzepdfsynced-export .declaration-analyzepdfsynced-export").addClass('d-none');

      $(".dt-synced-filter .commercial-invoice-synced-filter").removeClass('d-none');    
      $(".dt-synced-filter .sales-invoice-synced-filter").addClass('d-none');    
      $(".dt-synced-filter .declaration-synced-filter").addClass('d-none');          
    }
    else if(id == 'btn-analyzepdfsynced-sales-invoice')
    {            
      $(".dt-analyzepdfsynced-export .commercial-invoice-analyzepdfsynced-export").addClass('d-none');  
      $(".dt-analyzepdfsynced-export .sales-invoice-analyzepdfsynced-export").removeClass('d-none');     
      $(".dt-analyzepdfsynced-export .declaration-analyzepdfsynced-export").addClass('d-none');

      $(".dt-synced-filter .commercial-invoice-synced-filter").addClass('d-none');    
      $(".dt-synced-filter .sales-invoice-synced-filter").removeClass('d-none');    
      $(".dt-synced-filter .declaration-synced-filter").addClass('d-none');       
    } 
    else if(id == 'btn-analyzepdfsynced-declaration')
    {      
      $(".dt-analyzepdfsynced-export .commercial-invoice-analyzepdfsynced-export").addClass('d-none');  
      $(".dt-analyzepdfsynced-export .sales-invoice-analyzepdfsynced-export").addClass('d-none');     
      $(".dt-analyzepdfsynced-export .third-analyzepdfsynced-export").removeClass('d-none');

      $(".dt-synced-filter .commercial-invoice-synced-filter").addClass('d-none');    
      $(".dt-synced-filter .sales-invoice-synced-filter").addClass('d-none');    
      $(".dt-synced-filter .declaration-synced-filter").removeClass('d-none');      
    }  
  });    

  $('[data-bs-toggle="tab"]').off('shown.bs.tab.dtFix').on('shown.bs.tab.dtFix', function (e) {
    let target = $(e.target).attr('id');

    if (target === 'btn-analyzepdfsynced-commercial-invoice' || target === 'btn-analyzepdfsynced-sales-invoice') {

      setTimeout(function () {

        $('.datatables-analyzepdfsynced').each(function () {

          let table = $(this).DataTable();

          $(table.table().node()).css('width', '100%');

          table.columns.adjust().draw(false);
          table.columns.adjust();

        });

      }, 400);
    }
  });  

/*
  function exportToExcel(dt, which_tab) 
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
      dt.rows().every(function(rowIdx) {
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
      XLSX.writeFile(workbook, 'OCR-synced-'+ which_tab +'.xlsx');
  }
  */

  // Sync Data
  $(document).on('click', '.btn-sync-data', function () {
    var btn_sync_data = $(this);   

    if (!btn_sync_data.is(':disabled')) 
    {
      var client_id = $(this).data('client_id');   
      var country = $(this).data('country'); 
      var text = $(this).text();   
     
      btn_sync_data.attr('disabled', 'disabled');
      btn_sync_data.addClass('disabled-opacity');    
      btn_sync_data.html('<span><i class="bx bx-refresh me-2"></i>Syncing...' + text + '</span>');

      $.ajax({      
        url: `${baseUrl}analyzepdf-sync`,
        type: 'GET',           
        data: 'client_id=' + client_id + '&country=' + country,  
        success: function (response) { 

          btn_sync_data.removeAttr('disabled');
          btn_sync_data.removeClass(
              'disabled-opacity'
          );
          btn_sync_data.html(text);

          setTimeout(() => {
            Swal.fire({
              title: 'Sync completed',
              text: 'Page will be reloaded now :)',
              icon: 'info',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            }).then(function (result) { 
              if (result.isConfirmed)
                window.location.reload();
            });
          }, 5000); // Show the alert after 5 seconds    

          return;        
        },
        error: function (error) {
          console.log(error);
        }
      });
    }

  });
  
  // Sync DB
  $(document).on('click', '.btn-sync-db', function () {
    var btn_sync_db = $(this);   

    // if (!btn_sync_db.is(':disabled')) 
    // {
    //   var client_id = $(this).data('client_id');   
    //   var country = $(this).data('country'); 
    var text = $(this).text();   
     
      btn_sync_db.attr('disabled', 'disabled');
      btn_sync_db.addClass('disabled-opacity');    
      //btn_sync_db.html('<span><i class="bx bx-refresh me-2"></i>Syncing...' + text + '</span>');
      btn_sync_db.html('<span><i class="bx bx-refresh me-2"></i>Syncing...</span>');

      $.ajax({      
        url: `${analyzePdfSyncedUrl}syncdb`,
        type: 'GET',           
        //data: 'client_id=' + client_id + '&country=' + country,  
        success: function (response) { 
          let totalSync = response.totalSync;          
          const syncMessage = totalSync === 0
            ? 'No records synced.'
            : `${totalSync} records synced successfully.`;
    
          setTimeout(() => {
            Swal.fire({
              title: 'Sync completed',
              html: `${syncMessage}<br>Page will be reloaded now :)`,
              icon: 'info',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            }).then(function (result) { 
              if (result.isConfirmed)
              {
                btn_sync_db.removeAttr('disabled');
                btn_sync_db.removeClass(
                    'disabled-opacity'
                );
                btn_sync_db.html(text);
                window.location.reload();
              }
            });
          }, 5000); // Show the alert after 5 seconds    

          return;        
        },
        error: function (error) {
          console.log(error);
        }
      });
    //}

  });
 
});
