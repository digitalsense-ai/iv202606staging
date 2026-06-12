/**
 * Page OCR Invoice PDF File Search List
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
  var analyzePdfSearchUrl = baseUrl + 'analyzepdf/search/';

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  
  

  /*  
  var dt_analyzepdfsearch_tables = $('.datatables-analyzepdfsearch');
console.log(dt_analyzepdfsearch_tables);
  for (var i = 0; i < dt_analyzepdfsearch_tables.length; i++) {
   
    var dt_analyzepdfsearch_table = $(dt_analyzepdfsearch_tables[i]); // This is a DOM element, not a jQuery object

    if (dt_analyzepdfsearch_table) 
    {
      var analyzepdfsearch_filter_class = 'd-none';
      let analyzepdfsearch_name = '';
      var analyzepdfsearch_datas = [];
      if(i === 0)
      {
        analyzepdfsearch_filter_class = '';
        analyzepdfsearch_name = 'commercial-invoice';
        analyzepdfsearch_datas = analyzepdf_commercial_invoice_datas;
      }
      else if(i === 1)
      {
        analyzepdfsearch_name = 'sales-invoice';
        analyzepdfsearch_datas = analyzepdf_sales_invoice_datas;
      }
      else if(i === 2)
      {
        analyzepdfsearch_name = 'declaration';
        analyzepdfsearch_datas = analyzepdf_declaration_datas;
      }
console.log(i);      
console.log(analyzepdfsearch_name);
      let columns = [];      
      let invisiblecolumntargets = [];
      let columntargets = [];
      let actiontargets = 9;

      if(i === 0)
      {
        columns = [
            { data: 'fake_id', className: 'text-start w-px-100' },
            { data: 'client_no', className: 'text-start w-px-150' },
            { data: 'client_name', className: 'text-start w-px-200' },
            { data: 'invoice_no', className: 'text-start w-px-150' },
            { data: 'invoice_date', className: 'text-start w-px-150' },
            { data: 'currency', className: 'text-start w-px-100' },
            { data: 'net_amount', className: 'text-end w-px-150' },
            { data: 'related_sales_invoices', className: 'text-start w-px-300 ellipsis' },
            { data: 'created_at', className: 'text-start w-px-200' },            
            { data: 'action', className: 'w-px-50' }  
        ];

        columntargets = [0, 1, 2, 3, 4, 5, 6, 7, 8];
      }
      else if(i === 1)
      {
        columns = [
            { data: 'fake_id', className: 'text-start w-px-100' },
            { data: 'client_no', className: 'text-start w-px-150' },
            { data: 'client_name', className: 'text-start w-px-200' },
            { data: 'invoice_no', className: 'text-start w-px-150' },
            { data: 'invoice_date', className: 'text-start w-px-150' },
            { data: 'currency', className: 'text-start w-px-100' },
            { data: 'credit_note', className: 'text-start w-px-100' },
            { data: 'net_amount', className: 'text-end w-px-150' },
            { data: 'vat_rate', className: 'w-px-50' },
            { data: 'vat_amount', className: 'text-end w-px-150' },
            { data: 'variance_amount', className: 'text-end w-px-150' },
            { data: 'freight_amount', className: 'text-end w-px-150' },
            { data: 'discount_amount', className: 'text-end w-px-150' },
            { data: 'total_amount', className: 'text-end w-px-150' },
            { data: 'created_at', className: 'text-start w-px-200' },            
            { data: 'action', className: 'w-px-50' }  
        ];

        actiontargets = 15;
        columntargets = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
      }
      else if(i === 2)
      {
        columns = [
            { data: 'fake_id' },
            { data: 'client_no', className: 'text-start' },
            { data: 'client_name', className: 'text-start' },
            { data: 'declaration_no', className: 'text-start' },
            { data: 'expo_no', className: 'text-start' },
            { data: 'invoice_date', className: 'text-start' },
            { data: 'currency' },           
            { data: 'net_amount', className: 'text-end' },
            { data: 'duties', className: 'text-end' },
            { data: 'adjustment', className: 'text-end' },
            { data: 'reference_no', className: 'text-start' },            
            { data: 'created_at', className: 'text-start' },            
            { data: 'action' }  
        ];

        actiontargets = 12;
        columntargets = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
      }

      let invoiceDateIndex = columns.findIndex(c => c.data === 'invoice_date');

      var dt_analyzepdfsearch = dt_analyzepdfsearch_table.DataTable({  
          data: analyzepdfsearch_datas,              
          scrollCollapse: false,        
          scrollX: true,        
          fixedHeader: true,             
          searching: true,    
          lengthMenu: [
              [10, 25, 50, 100],
              [10, 25, 50, 100]
          ],
          pageLength: 100,     
          autoWidth: false, 
          ordering: true,                
          columns: columns,          
          columnDefs: [           
            {
              // For Uparrow Icons
              targets:  columntargets,         
              searchable: true,
              orderable: true,
              visible: true
            }, 
            {
              targets: invoiceDateIndex,
              render: function (data, type) {
                if (!data) return '';

                // FORCE consistent sort format
                let m = moment(data, ['YYYY-MM-DD', 'DD-MM-YYYY', 'YYYY/MM/DD']);

                if (type === 'sort' || type === 'type') {
                  return m.isValid() ? m.format('YYYYMMDD') : '00000000';
                }

                return data;
              }
            },                  
            {
              targets: 7,
              searchable: true,
              orderable: true,
              visible: true,
              render: function (data, type, full, meta) {

                if (full.invoice_type === 'com') {

                  let arr = full.related_sales_invoices || [];

                  // EXPORT + SEARCH → full data
                  if (type === 'display') {
                    return arr.join(', ');
                  }

                  // DISPLAY → shortened
                  if (arr.length === 1)
                    return arr[0];
                  else if (arr.length > 1)
                    return arr[0] + " ...";
                  else
                    return '';
                } 
                else {
                  return full.net_amount;
                }
              }
            },          
            {
              // For Action
              targets: actiontargets,              
              searchable: false,
              orderable: false,              
              render: function (data, type, full, meta) { 

                return `<div class="d-inline-block">
                          <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
                          <ul class="dropdown-menu dropdown-menu-end m-0">` + 
                            //((i === 0) ?                       
                            `<li>
                              <a href="javascript:;" class="dropdown-item btn-show-data" id="show-analyzepdf-data" title="Show Data" data-analyzepdf_id="`+ full['id'] +`" data-tab_name="`+ analyzepdfsearch_name +`" data-invoice_no="`+ full['invoice_no'] +`" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnalyzePdfData">
                                <span><i class="bx bx-show me-2"></i>Show Data</span>
                              </a>                                     
                            </li>` +// : '') +
                          `</ul>
                        </div>`;
              }
            }                     
          ],
          processing: true, 
          order: [[0, 'asc']],
          dom:     
            '<"row mx-0 '+ analyzepdfsearch_name +'-search-filter '+ analyzepdfsearch_filter_class +'"' +                      
            '<"col-sm-12 col-md-6 sub-btns text-start my-auto">' +
            '<"col-sm-12 col-md-6"lfB>' +            
            '>r' +
            '<"row mx-0"' +
            '<"col-sm-12 p-0"t' +                    
            '>>' +
            '<"row mx-2"' +
            '<"col-sm-12 col-md-6"i>' +
            '<"col-sm-12 col-md-6"p>' +
            '>',
          select: {            
            style: 'multi'
          },
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
          buttons: [                   
          {
            extend: 'collection',
            className: 'btn btn-outline-secondary dropdown-toggle ml-3',
            text: '<i class="bx bx-export me-2"></i>Export',
            autoClose: true,
            buttons: [
              {
                extend: 'print',
                title: 'Analyze PDF',
                text: '<i class="bx bx-printer me-2" ></i>Print',
                className: 'dropdown-item',
                exportOptions: {                
                  columns: columntargets               
                },
                customize: function (win) {
                  //customize print view for dark
                  $(win.document.body)
                    .css('color', config.colors.headingColor)
                    .css('border-color', config.colors.borderColor)
                    .css('background-color', config.colors.body);
                  $(win.document.body)
                    .find('table')
                    .addClass('compact')
                    .css('color', 'inherit')
                    .css('border-color', 'inherit')
                    .css('background-color', 'inherit');
                }
              },                
              {
                extend: 'csv',
                title: 'Analyze PDF',
                text: '<i class="bx bx-file me-2" ></i>Csv',
                className: 'dropdown-item',
                exportOptions: {                
                  columns: columntargets
                }
              },           
              {
                extend: 'excel',
                title: 'Analyze PDF',
                text: '<i class="bx bxs-file-export me-2"></i>Excel',
                className: 'dropdown-item',              
                exportOptions: {                  
                  columns: columntargets,                  
                },                            
              },
              {
                extend: 'pdf',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                title: 'Analyze PDF',
                text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
                className: 'dropdown-item',
                exportOptions: {                
                  columns: columntargets
                }
              },
              {
                extend: 'copy',
                title: 'Analyze PDF',
                text: '<i class="bx bx-copy me-2" ></i>Copy',
                className: 'dropdown-item',
                exportOptions: {               
                  columns: columntargets
                }
              }        
            ]
          }            
        ],       
        initComplete: function (settings, json) {   
          
          const api = this.api(); // ✅ DataTable instance

          const $tableWrapper = $(api.table().container()).find('.dataTables_scroll');
          const $scrollBody   = $tableWrapper.find('.dataTables_scrollBody');
          const $topScroll    = $('#top-scroll-navs-analyzepdfsearch-' + analyzepdfsearch_name);
          const $topInner     = $topScroll.find('.dt-top-scroll-inner');

          let isSyncing = false;

          // Match widths
          function syncWidth() {
              if ($scrollBody.length) {
                  $topInner.width($scrollBody.get(0).scrollWidth);
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

          $("."+ analyzepdfsearch_name +"-search-filter").appendTo('.dt-search-filter');

          var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnalyzePdfFilter" aria-controls="offcanvasAnalyzePdfFilter">' +
                                '<i class="bx bx-slider"></i>' +
                              '</label>';
          $(sliderfilter).appendTo('.'+ analyzepdfsearch_name +'-search-filter .dataTables_filter');
         
          $("."+ analyzepdfsearch_name +"-search-filter .dt-buttons.btn-group.flex-wrap").appendTo('.dt-analyzepdfsearch-export .'+ analyzepdfsearch_name +'-analyzepdfsearch-export');

          var analyzepdfsearch_total = this.api().data().length;
          $("#btn-analyzepdfsearch-"+ analyzepdfsearch_name +" span").html(analyzepdfsearch_total);

          $(".card.analyzepdfsearch .sk-bounce").hide();
          $(".card.analyzepdfsearch .card-datatable").show();          
        }
      });          
    } //if dt exist
  } //for loop dt
*/

  var dt_analyzepdfsearch_tables = $('.datatables-analyzepdfsearch');

  for (var i = 0; i < dt_analyzepdfsearch_tables.length; i++) {

    var dt_analyzepdfsearch_table = $(dt_analyzepdfsearch_tables[i]);

    if (dt_analyzepdfsearch_table) {

      var analyzepdfsearch_filter_class = 'd-none';
      let analyzepdfsearch_name = '';
      var analyzepdfsearch_datas = [];

      if (i === 0) {
        analyzepdfsearch_filter_class = '';
        analyzepdfsearch_name = 'commercial-invoice';
        analyzepdfsearch_datas = analyzepdf_commercial_invoice_datas;
      }
      else if (i === 1) {
        analyzepdfsearch_name = 'sales-invoice';
        analyzepdfsearch_datas = analyzepdf_sales_invoice_datas;
      }
      else if (i === 2) {
        analyzepdfsearch_name = 'declaration';
        analyzepdfsearch_datas = analyzepdf_declaration_datas;
      }

      let columns = [];
      let columntargets = [];
      let actiontargets = 9;

      let invoiceDateIndex = -1;
      let netAmountIndex = -1;
      let relatedInvoiceIndex = -1;

      // ===================== COMMERCIAL =====================
      if (i === 0) {        

        columns = [
          { data: 'fake_id', width: '100px' },
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
      }

      // ===================== SALES =====================
      else if (i === 1) {
       
        columns = [
          { data: 'fake_id', width: '100px' },
          { data: 'client_no', width: '150px' },
          { data: 'client_name', width: '250px' },
          { data: 'invoice_no', width: '200px' },
          { data: 'invoice_date', width: '150px' },
          { data: 'currency', width: '150px' },
          { data: 'credit_note', width: '150px' },
          { data: 'net_amount', width: '150px', className: 'text-end' },
          { data: 'vat_rate', width: '150px', className: 'text-end' },
          { data: 'vat_amount', width: '150px', className: 'text-end' },
          { data: 'variance_amount', width: '150px', className: 'text-end' },
          { data: 'freight_amount', width: '150px', className: 'text-end' },
          { data: 'discount_amount', width: '150px', className: 'text-end' },
          { data: 'total_amount', width: '150px', className: 'text-end' },
          { data: 'created_at', width: '220px' },
          { data: 'action', defaultContent: '', width: '150px' }
        ];

        columntargets = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14];

        actiontargets = 15;
        invoiceDateIndex = 4;
        netAmountIndex = 7;
      }

      // ===================== DECLARATION =====================
      else if (i === 2) {

        columns = [
          { data: 'fake_id', width: '100px' },
          { data: 'client_no', width: '150px' },
          { data: 'client_name', width: '200px' },
          { data: 'declaration_no', width: '200px' },
          { data: 'expo_no', width: '200px' },
          { data: 'invoice_date', width: '150px' },
          { data: 'currency', width: '150px' },
          { data: 'net_amount', width: '150px', className: 'text-end' },
          { data: 'duties', width: '150px', className: 'text-end' },
          { data: 'adjustment', width: '150px', className: 'text-end' },
          { data: 'reference_no', width: '200px' },
          { data: 'created_at', width: '220px' },
          { data: 'action', defaultContent: '', width: '150px' }
        ];

        columntargets = [0,1,2,3,4,5,6,7,8,9,10,11];

        actiontargets = 12;
        invoiceDateIndex = 5;
        netAmountIndex = 7;
      }

      // ===================== INIT DATATABLE =====================
      var dt_analyzepdfsearch = dt_analyzepdfsearch_table.DataTable({

        data: analyzepdfsearch_datas,

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

          // // ================= COMMERCIAL COLUMN 7 FIX =================
          // {
          //   targets: relatedInvoiceIndex,
          //   render: function (data, type, full) {

          //     let arr = full.related_sales_invoices || [];

          //     if (type === 'sort' || type === 'type') {
          //       return arr.length; // stable sorting
          //     }

          //     if (type === 'filter') {
          //       return arr.join(' ');
          //     }

          //     if (arr.length === 1) return arr[0];
          //     if (arr.length > 1) return arr[0] + " ...";
          //     return '';
          //   }
          // },

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
                                class="dropdown-item btn-show-data" 
                                id="show-analyzepdf-data"
                                title="Show Data"

                                data-analyzepdf_id="` + full['id'] + `"
                                data-tab_name="` + analyzepdfsearch_name + `"
                                data-invoice_no="` + (full['invoice_no'] || full['declaration_no'] || '') + `"

                                data-bs-toggle="offcanvas" 
                                data-bs-target="#offcanvasAnalyzePdfData">

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
          '<"row mx-0 '+ analyzepdfsearch_name +'-search-filter '+ analyzepdfsearch_filter_class +'"' +
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
                title: 'Analyze PDF',
                text: '<i class="bx bx-printer me-2"></i>Print',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets }
              },

              {
                extend: 'csv',
                title: 'Analyze PDF',
                text: '<i class="bx bx-file me-2"></i>Csv',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets }
              },

              {
                extend: 'excel',
                title: 'Analyze PDF',
                text: '<i class="bx bxs-file-export me-2"></i>Excel',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets }
              },

              {
                extend: 'pdf',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                title: 'Analyze PDF',
                text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
                className: 'dropdown-item',
                exportOptions: { columns: columntargets }
              },

              {
                extend: 'copy',
                title: 'Analyze PDF',
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
//console.log(analyzepdfsearch_name);
          //$("."+ analyzepdfsearch_name +"-search-filter").appendTo('.dt-search-filter');

          const api = this.api();

          // api
          //   .columns(2)
          //   .every(function () {
          //     var column = this;
          //     var select = $(
          //       '<select id="FilterClientName" class="form-select w-px-200 text-capitalize"><option value=""> Select Client Name </option></select>'
          //     )
          //       .appendTo('.client_name')
          //       .on('change', function () {                
          //         var val = $(this).val().replace(/-/g, " ");
          //         column.search(val ? val : '', true, false).draw();
          //       });

          //     column
          //       .data()
          //       .unique()
          //       .sort()
          //       .each(function (d, j) {
          //         var selected = (j === 0) ? 'selected' : '';
          //         if(d)
          //           select.append('<option value="' + d + '" ' + selected + '>' + d.replace(/-/g, " ") + '</option>');
          //       });

          //     // manually trigger filter
          //     select.trigger('change');  
          //   });

          // if(analyzepdfsearch_name === 'commercial-invoice')  
          //   $(".dt-dropdown-filter").prependTo('.dt-search-filter .'+ analyzepdfsearch_name +'-search-filter #DataTables_Table_0_filter');   
          // else if(analyzepdfsearch_name === 'sales-invoice')  
          //   $(".dt-dropdown-filter").prependTo('.dt-search-filter .'+ analyzepdfsearch_name +'-search-filter #DataTables_Table_1_filter');   


          // // IMPORTANT: fix hidden tab column width issue
          // setTimeout(function () {
          //   api.columns.adjust();
          //   api.tables().columns.adjust();
          //   api.responsive && api.responsive.recalc();
          // }, 150);

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
          const $topScroll    = $('#top-scroll-navs-analyzepdfsearch-' + analyzepdfsearch_name);
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

          $("." + analyzepdfsearch_name + "-search-filter")
            .appendTo('.dt-search-filter');

          var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnalyzePdfFilter" aria-controls="offcanvasAnalyzePdfFilter">' +
                                '<i class="bx bx-slider"></i>' +
                              '</label>';
          $(sliderfilter).appendTo('.'+ analyzepdfsearch_name +'-search-filter .dataTables_filter');
         
          $("."+ analyzepdfsearch_name +"-search-filter .dt-buttons.btn-group.flex-wrap").appendTo('.dt-analyzepdfsearch-export .'+ analyzepdfsearch_name +'-analyzepdfsearch-export');


          var analyzepdfsearch_total = api.data().length;

          $("#btn-analyzepdfsearch-" + analyzepdfsearch_name + " span")
            .html(analyzepdfsearch_total);

          $(".card.analyzepdfsearch .sk-bounce").hide();
          $(".card.analyzepdfsearch .card-datatable").show();
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

  $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {  
    var id = $(e.target).attr("id") // activated tab
   console.log(id);
    if(id == 'btn-analyzepdfsearch-commercial-invoice')
    {            
      $(".dt-analyzepdfsearch-export .commercial-invoice-analyzepdfsearch-export").removeClass('d-none');  
      $(".dt-analyzepdfsearch-export .sales-invoice-analyzepdfsearch-export").addClass('d-none');     
      $(".dt-analyzepdfsearch-export .declaration-analyzepdfsearch-export").addClass('d-none');

      $(".dt-search-filter .commercial-invoice-search-filter").removeClass('d-none');    
      $(".dt-search-filter .sales-invoice-search-filter").addClass('d-none');    
      $(".dt-search-filter .declaration-search-filter").addClass('d-none');          
    }
    else if(id == 'btn-analyzepdfsearch-sales-invoice')
    {            
      $(".dt-analyzepdfsearch-export .commercial-invoice-analyzepdfsearch-export").addClass('d-none');  
      $(".dt-analyzepdfsearch-export .sales-invoice-analyzepdfsearch-export").removeClass('d-none');     
      $(".dt-analyzepdfsearch-export .declaration-analyzepdfsearch-export").addClass('d-none');

      $(".dt-search-filter .commercial-invoice-search-filter").addClass('d-none');    
      $(".dt-search-filter .sales-invoice-search-filter").removeClass('d-none');    
      $(".dt-search-filter .declaration-search-filter").addClass('d-none');       
    } 
    else if(id == 'btn-analyzepdfsearch-declaration')
    {      
      $(".dt-analyzepdfsearch-export .commercial-invoice-analyzepdfsearch-export").addClass('d-none');  
      $(".dt-analyzepdfsearch-export .sales-invoice-analyzepdfsearch-export").addClass('d-none');     
      $(".dt-analyzepdfsearch-export .third-analyzepdfsearch-export").removeClass('d-none');

      $(".dt-search-filter .commercial-invoice-search-filter").addClass('d-none');    
      $(".dt-search-filter .sales-invoice-search-filter").addClass('d-none');    
      $(".dt-search-filter .declaration-search-filter").removeClass('d-none');      
    }  

    // setTimeout(function () {
    //   $('.datatables-analyzepdfsearch').each(function () {
    //     const table = $(this).DataTable();

    //     table.columns.adjust();
    //     table.draw(false);

    //     if (table.responsive) {
    //       table.responsive.recalc();
    //     }
    //   });
    // }, 150);  

  });    

  $('[data-bs-toggle="tab"]').off('shown.bs.tab.dtFix').on('shown.bs.tab.dtFix', function (e) {
    let target = $(e.target).attr('id');

    if (target === 'btn-analyzepdfsearch-commercial-invoice' || target === 'btn-analyzepdfsearch-sales-invoice') {

      setTimeout(function () {

        $('.datatables-analyzepdfsearch').each(function () {

          let table = $(this).DataTable();

          $(table.table().node()).css('width', '100%');

          table.columns.adjust().draw(false);
          table.columns.adjust();

        });

      }, 400);
    }
  });
   
});
