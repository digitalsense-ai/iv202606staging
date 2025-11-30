/**
 * Page Declarations List
 */

'use strict';

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
  var fileUrl = baseUrl + 'file/';
  var userView = baseUrl + 'dv-user/';
  var declarationUrl = baseUrl + 'declaration/';
  var declarationInvoiceUrl = baseUrl + 'declaration-invoice/';  

  // window.Pusher = Pusher;
  // window.Echo = new Echo({
  // //const echo = new Echo({    
  //     broadcaster: 'pusher',
  //     key: 'bc3c5712d049ef05fcb5',
  //     cluster: 'eu',
  //     //encrypted: true,
  //     //debug: true  // Enable debugging
  //     forceTLS: true
  // });

  let totalJobs = 0;
  let completedJobs = 0; 
  let finshedRefresh = false;

  let selectedToastType = "bg-primary";
  let errorToastType = "bg-danger";
  let selectedToastPlacement = String("top-0 end-0").split(' ');
  let toastPlacement;
  const toastPlacementDiv = document.querySelector('.toast-placement'),
        toastPlacementHeader = document.querySelector('.toast-placement .toast-header');

  let intervalId = null; // Store the interval ID for checking the status
  let xhr = null; // Store the AJAX request object
        
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var dt_declarations_tables = $('.datatables-declarations');

  for (var i = 0; i < dt_declarations_tables.length; i++) {
   
    var dt_declarations_table = $(dt_declarations_tables[i]); // This is a DOM element, not a jQuery object

    if (dt_declarations_table) 
    {
      var declaration_filter_class = 'd-none';
      let declaration_name = '';
      var declaration_datas = [];
      if(i === 0)
      {
        declaration_filter_class = '';
        declaration_name = 'first';
        declaration_datas = declaration_first_datas;
      }
      else if(i === 1)
      {
        declaration_name = 'second';
        declaration_datas = declaration_second_datas;
      }
      else if(i === 2)
      {
        declaration_name = 'third';
        declaration_datas = declaration_third_datas;
      }
console.log(declaration_datas);

      let columns = [
          { data: 'id', className: "declaration-th-w20 main-row" },                    
          { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" }, 
          { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" },                    
          { data: 'statistical_value', className: "text-end declaration-th-w150 main-row" },          
          { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" },  
          { data: 'net_amount', className: "text-end declaration-th-w150 main-row" },      
          { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" },        
          { data: 'import_vat', className: "text-end declaration-th-w150 main-row" },      
          { data: 'duties', className: "text-end declaration-th-w150 main-row" },
          { data: 'vat_on_duties', className: "text-end declaration-th-w150 main-row" },                      
          { data: 'adjustment', className: "text-end declaration-th-w150 main-row" },
          { data: 'vat_on_adjustment', className: "text-end declaration-th-w150 main-row" }, 
          { data: 'action', className: "text-center" }  
      ];
      let columntargets = [0, 2, 4, 6];
      if(declaration_datas[0]['country'] == 'CH')
      {
        columns = [
            { data: 'id', className: "declaration-th-w20 main-row" },                    
            { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" }, 
            { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" },   
            { data: 'statistical_value', className: "text-end declaration-th-w150 main-row" },        
            { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" }, 
            { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" }, 
            { data: 'net_amount', className: "text-end declaration-th-w150 main-row" }, 
            { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" }, 
            { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" }, 
            { data: 'import_vat', className: "text-end declaration-th-w150 main-row" }, 
            { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" },      
            { data: 'duties', className: "text-end declaration-th-w150 main-row" },
            { data: 'vat_on_duties', className: "text-end declaration-th-w150 main-row" },                      
            { data: 'adjustment', className: "text-end declaration-th-w150 main-row" },
            { data: 'vat_on_adjustment', className: "text-end declaration-th-w150 main-row" }, 
            { data: 'action', className: "text-center" }  
        ];
        columntargets = [0, 2, 4, 5, 7, 8, 10];
        //columns.push({ data: 'declaration_no', className: "text-start declaration-th-w150 main-row" });
        //columns.push({ data: 'declaration_no', className: "text-start declaration-th-w150 main-row" });
      }

      var dt_declarations = dt_declarations_table.DataTable({  
          data: declaration_datas,              
          scrollCollapse: false,              
          searching: true,    
          lengthMenu: [
              [10, 25, 50, 100],
              [10, 25, 50, 100]
          ],
          pageLength: 100,     
          autoWidth: false, 
          ordering: false,                
          columns: columns,
          //[           
          //   { data: 'id', className: "declaration-th-w20 main-row" },                    
          //   { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" }, 
          //   { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" }, 
          //   { data: 'declaration_no', className: "text-start declaration-th-w150 main-row" },             
          //   { data: 'statistical_value', className: "text-end declaration-th-w150 main-row" }, 
          //   { data: 'net_amount', className: "text-end declaration-th-w150 main-row" },      
          //   { data: 'import_vat', className: "text-end declaration-th-w150 main-row" },      
          //   { data: 'duties', className: "text-end declaration-th-w150 main-row" },
          //   { data: 'vat_on_duties', className: "text-end declaration-th-w150 main-row" },                      
          //   { data: 'adjustment', className: "text-end declaration-th-w150 main-row" },
          //   { data: 'vat_on_adjustment', className: "text-end declaration-th-w150 main-row" }, 
          //   { data: 'action', className: "text-center" }         
          // ],         
          columnDefs: [
            {
              // For Uparrow Icons
              targets:  columntargets,         
              searchable: false,
              orderable: false,              
              render: function (data, type, full, meta) {                
                return '';
              }
            },
            // {
            //   // For Space
            //   targets: 2,              
            //   searchable: false,
            //   orderable: false,              
            //   render: function (data, type, full, meta) {                
            //     return '';
            //   }
            // },
            // {
            //   // For Space
            //   targets: 4,              
            //   searchable: false,
            //   orderable: false,              
            //   render: function (data, type, full, meta) {                
            //     return '';
            //   }
            // },
            {
              // For Action
              targets: (declaration_datas[0]['country'] == 'CH') ? 15 : 12,              
              searchable: false,
              orderable: false,              
              render: function (data, type, full, meta) { 
                var btn_icn = (full['comment_reason']) ? 'edit' : 'add';  
                var btn_title = (full['comment_reason']) ? 'Edit' : 'Add'; 

                var btn_delete = '';
                if(full['comment_reason'])                
                  btn_delete = '<li>' +
                                  '<a href="javascript:;" class="dropdown-item text-danger btn-declaration-invoice-delete-comment" title="Delete Comment" data-invoice_name="declaration" data-tab_name="first" data-invoice_id="'+ full['id'] +'" data-invoice_no="'+ full['declaration_no'] +'">' +
                                    '<span class="text-danger"><i class="bx bx-comment-x"></i> Delete Comment</span>' +
                                  '</a>' + 
                                '</li>';             

                return '<div class="d-inline-block">' +
                    '<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                    '<ul class="dropdown-menu dropdown-menu-end m-0">' +
                      '<li>' +
                        '<a href="javascript:;" class="dropdown-item btn-disregard-declaration-invoice" data-insert_type="'+ btn_icn +' Comment" data-comment_reason="'+ full['comment_reason'] +'" data-comment="'+ full['comment'] +'" data-comment_visiblity="'+ full['comment_visiblity'] +'" title="'+btn_title+' Comment" data-invoice_name="declaration" data-invoice_id="'+ full['id'] +'" data-invoice_no="'+ full['declaration_no'] +'" data-tab_name="first" data-disregard="0" data-invoice_date="'+ full['o_declaration_date'] +'">' +
                          '<span><i class="bx bx-comment-'+ btn_icn +'"></i> '+ btn_title +' Comment</span>' +
                        '</a>' +                                          
                      '</li>' +                                   
                      btn_delete +                     
                    '</ul>' +
                  '</div>';
              }
            } 
          ],
          processing: true, 
          order: [[1, 'desc']],
          dom:     
            '<"row mx-0 '+ declaration_name +'-search-filter '+ declaration_filter_class +'"' +                      
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
                title: 'Declarations',
                text: '<i class="bx bx-printer me-2" ></i>Print',
                className: 'dropdown-item',
                exportOptions: {                
                  columns: ':visible',                
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
                title: 'Declarations',
                text: '<i class="bx bx-file me-2" ></i>Csv',
                className: 'dropdown-item',
                exportOptions: {                
                  columns: ':visible'
                }
              },           
              {
                extend: 'excel',
                title: 'Declarations',
                text: '<i class="bx bxs-file-export me-2"></i>Excel',
                className: 'dropdown-item',              
                exportOptions: {
                  columns: ':visible'
                },             
                action: function (e, dt, node, config) {                  
                    exportToExcelPeriodOverviewNew(dt, declaration_name);
                }
              },
              {
                extend: 'pdf',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                title: 'Declarations',
                text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
                className: 'dropdown-item',
                exportOptions: {                
                  columns: ':visible'
                }
              },
              {
                extend: 'copy',
                title: 'Declarations',
                text: '<i class="bx bx-copy me-2" ></i>Copy',
                className: 'dropdown-item',
                exportOptions: {               
                  columns: ':visible'
                }
              },
              {
                extend: 'excel',
                title: 'Declarations - Missing files',
                text: '<i class="bx bxs-file-export me-2"></i>Missing Files',
                className: 'dropdown-item',              
                exportOptions: {
                  columns: ':visible'
                },          
                action: function (e, dt, node, config) {
                    exportToExcelMissingFilesNew(dt, declaration_name);
                }
              },
              {
                extend: 'excel',
                title: 'Declarations',
                text: '<i class="bx bxs-file-export me-2"></i>Declarations',
                className: 'dropdown-item',              
                exportOptions: {
                  columns: ':visible'
                },            
                action: function (e, dt, node, config) {
                    exportToExcelOnlyDeclarationsNew(dt, declaration_name); 
                }
              },
              {
                extend: 'excel',
                title: 'Com. Invoices',
                text: '<i class="bx bxs-file-export me-2"></i>Com. Invoices',
                className: 'dropdown-item',              
                exportOptions: {
                  columns: ':visible'
                },            
                action: function (e, dt, node, config) {
                    exportToExcelOnlyComInvoicesNew(dt, declaration_name);
                }
              },
              {
                extend: 'excel',
                title: 'Invoices',
                text: '<i class="bx bxs-file-export me-2"></i>Invoices',
                className: 'dropdown-item',              
                exportOptions: {
                  columns: ':visible'
                },           
                action: function (e, dt, node, config) {
                    exportToExcelOnlySalesInvoicesNew(dt, declaration_name);
                }
              }             
            ]
          }            
        ],
        initComplete: function (settings, json) {   
        
          $('#navs-declaration-'+ declaration_name +' .dataTables_scrollBody').css('overflow', 'auto');
          
          $('#navs-declaration-'+ declaration_name +' .dataTables_scrollHead').css('overflow', 'auto');
                
          $('#navs-declaration-'+ declaration_name +' .dataTables_scrollBody').on('scroll', function () {          
            $('#navs-declaration-'+ declaration_name +' .dataTables_scrollHead').scrollLeft($(this).scrollLeft());          
          });      
          
          $('#navs-declaration-'+ declaration_name +' .dataTables_scrollHead').on('scroll', function () {
            $('#navs-declaration-'+ declaration_name +' .dataTables_scrollBody').scrollLeft($(this).scrollLeft());          
          });        
                  
          $("."+ declaration_name +"-search-filter").appendTo('.dt-search-filter');

          var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDeclarationFilter" aria-controls="offcanvasDeclarationFilter">' +
                                '<i class="bx bx-slider"></i>' +
                              '</label>';
          $(sliderfilter).appendTo('.'+ declaration_name +'-search-filter .dataTables_filter');

          var btn_disregard_invoice =  '<button type="button" id="btn_'+ declaration_name +'_disregard_invoice" title="Disregard Invoice" class="btn-disregard-invoice badge rounded-pill bg-label-secondary border-0 text-capitalize" disabled="disabled" data-invoice_name="sales" data-is_disregard="1" data-tab_name="'+ declaration_name +'" >' +                                     
                                      '<span><i class="bx bx-list-minus"></i> Disregard invoice</span>' +
                                    '</button>';
          $(btn_disregard_invoice).appendTo('.'+ declaration_name +'-search-filter .sub-btns');
      
          $("."+ declaration_name +"-search-filter .dt-buttons.btn-group.flex-wrap").appendTo('.dt-declaration-export .'+ declaration_name +'-declaration-export');

          var declaration_total = this.api().data().length;
          $("#btn-declaration-"+ declaration_name +" span").html(declaration_total);

          $(".card.declarations .sk-bounce").hide();
          $(".card.declarations .card-datatable").show();          
        }
      });    

      // Add class to all data rows
      $('.datatables-'+ declaration_name +'-declarations tbody tr').addClass('accordion-button');
     
      clickAccordionButton(dt_declarations, declaration_name, $('.datatables-'+ declaration_name +'-declarations tbody tr.accordion-button:first-child'));

      addRedRemarks(declaration_name);     
    }// DATATABLE
  } //loop 

  function clickAccordionButton(dt_declarations, declaration_name, tr)
  {      
    //addTooltip(tr);
    reInitializeTooltips();

    var row = dt_declarations.row(tr);

    // Toggle row details
    if (row.child.isShown()) {
        // This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
    } else { 
      var declaration_datas = [];
      if(declaration_name === 'first')      
        declaration_datas = declaration_first_datas;      
      else if(declaration_name === 'second')      
        declaration_datas = declaration_second_datas;      
      else if(declaration_name === 'third')      
        declaration_datas = declaration_third_datas;
      
        row.child(format(declaration_datas, declaration_name, 'co-invoice', row.index()), 'p-0 cw-1').show();
        tr.addClass('shown');
       
        // Add click handler for the first level nested table
        setupNestedTable(declaration_datas, declaration_name, tr, row.index());

        filterErrorOnly(tr);          
    }
  }

  /*
  // Add event listener for opening and closing details
  $('.datatables-declarations tbody').on('click', 'tr.accordion-button', function(e) {console.log("tbody tr click");
  //$(document).on('click', '.datatables-declarations tbody tr.accordion-button', function (e) {    
    var tr = $(this);

    var declaration_name = $(this).closest('table.datatables-declarations').data('declaration_name');
   
    if ($.fn.DataTable.isDataTable('.datatables-'+ declaration_name +'-declarations'))
      var dt_declarations = $('.datatables-'+ declaration_name +'-declarations').DataTable(); // safely get it
   
    if(e.target.classList.contains('main-row') || e.target.classList.contains('bx-dots-vertical-rounded') || $(e.target).is(':last-child'))
    {      
      if(e.target.tagName.toLowerCase() == 'td' || e.target.tagName.toLowerCase() == 'i') 
        e.stopPropagation();
    } 
    else
      clickAccordionButton(dt_declarations, declaration_name, tr);      
  });
  */

  function setupNestedTable(declaration_datas, declaration_name, parentTr, parent_row_no = null) {    
    // Step 1: Find the nested table (it must be in the DOM at this point)
    var nestedTable = parentTr.next('tr').find('.datatables-'+ declaration_name +'-declaration-co-invoices');

    // Step 2: Exit if not found
    if (!nestedTable.length) {
        console.log('Nested table not found in DOM yet.');
        //return;
    }

    // Step 3: Only initialize if not already done
    if (!$.fn.DataTable.isDataTable(nestedTable[0])) 
    {
      var theadCols = nestedTable.find('thead th').length;

      var tbodyFirstRow = nestedTable.children('tbody').children('tr').first();
      var tbodyFirstRowCols = tbodyFirstRow.children('td').length;

      // var tbodyFirstRow = nestedTable.children('tbody').children('tr').first();
      // var tbodyFirstRowCols = 0;
      // tbodyFirstRow.children('td').each(function () {
      //   var colspan = parseInt($(this).attr('colspan')) || 1;
      //   tbodyFirstRowCols += colspan;
      // });

      if (theadCols === tbodyFirstRowCols) {
//         tbodyFirstRowCols = 0;
//         tbodyFirstRow.children('td').each(function () {  
//         console.log($(this).attr('colspan'))       ;
//           var colspan = parseInt($(this).attr('colspan')) || 1;
//           tbodyFirstRowCols += colspan;
//         });
// console.log(tbodyFirstRowCols);

//         if (theadCols !== tbodyFirstRowCols)
//           console.log('Skipping table with mismatched columns:', nestedTable.attr('id'));
//         //return;
//       }

        // Step 4: Define and apply initial order
        var savedOrder = nestedTable.data('stored-order') || [[(declaration_datas[0]['country'] == 'CH') ? 4 : 3, 'asc']];
     
        //if(theadCols !== tbodyFirstRow.children('td').length)
        //  savedOrder = [[0, 'asc']];
          //savedOrder = [];       
        nestedTable.data('stored-order', savedOrder);

        let columntargets = [0, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        if(declaration_datas[0]['country'] == 'CH')
          columntargets = [0, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];

        // Step 5: Initialize DataTable
        var nesteddt = nestedTable.DataTable({
              paging: false,
              searching: false,
              info: false,           
              ordering: true,
              order: savedOrder,
              columnDefs: [
                { orderable: false, targets: columntargets },
                {
                    targets: 1,
                    render: function (data, type, row, meta) {                          
                      if (type === 'sort')
                      {
                        const cleanedData = data.replace(/<span.*?>.*?<\/span>/g, '');
                        return cleanedData.trim().toLowerCase();
                      }

                      return data;
                    }
                },
                {
                    targets: (declaration_datas[0]['country'] == 'CH') ? 4 : 3,                        
                    render: function (data, type, row, meta) {                          
                      if (type === 'sort')
                      {
                        const cleanedData = data.replace(/<span.*?>.*?<\/span>/g, '');
                        return cleanedData.trim().toLowerCase() === '-' ? 'ZZZZZZZZ' : cleanedData.trim().toLowerCase();
                      }

                      return data;
                    }
                }
            ]     
        }); 

        // Step 6: Store updated sort order on user sort
        nestedTable.on('order.dt', function () {
            nestedTable.data('stored-order', nesteddt.order());
        });

        // Step 7: Prevent accidental sorting from certain UI actions
        $(nesteddt.table().container()).on('click', function (e) {
            if (
                $(e.target).closest('thead').length ||
                $(e.target).closest('.dropdown-menu, .dropdown-toggle, .action-menu, .menu-button, .btn, .accordion-button, .dt-chk-cell').length
            ) return;

            e.stopPropagation();
        }); 
        
        // Step 8: Setup nested row expand/collapse inside this table
        nestedTable.on('click', 'tr.accordion-button', function (e) {console.log("tr click");         
          if(e.target.classList.contains('main-row') || e.target.classList.contains('bx-dots-vertical-rounded') || 
            ($(e.target).is(':last-child') && !$(e.target).is('span')))
          {   
            if(e.target.tagName.toLowerCase() == 'td' || e.target.tagName.toLowerCase() == 'i') 
              e.stopPropagation();
          } 
          else
          { 
            var nestedTr = $(this);
            var nestedRow = nesteddt.row(nestedTr);

            if (nestedRow.child.isShown()) {
                nestedRow.child.hide();
                nestedTr.removeClass('shown');
            } else {             
                nestedRow.child(
                    format(declaration_datas, declaration_name, 'invoice', nestedRow.index(), parent_row_no),
                    'p-0 cw-1'
                ).show();
                nestedTr.addClass('shown');
            }
          }
        });
      }//if both head and body counts
    }          

    /*
    // Handle the click event for the nested table's detail control        
    nestedTable.on('click', 'tr.accordion-button', function() {                    
        var nestedTr = $(this);
        if ($.fn.dataTable.isDataTable(nestedTable[0]))
          var nestedRow = nestedTable.DataTable().row(nestedTr); // Use DataTable to get the row object
        else
        {
          // Check if order is stored
          var savedOrder = nestedTable.data('stored-order') || [[3, 'asc']];

          //var nestedRow = nestedTable.DataTable({
            var nesteddt = nestedTable.DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                order: savedOrder,
                columnDefs: [
                    { orderable: false, targets: [0, 2, 4, 5, 6, 7, 8, 9, 10, 11] },
                    {
                        targets: 1,
                        render: function (data, type, row, meta) {                          
                          if (type === 'sort')
                          {
                            const cleanedData = data.replace(/<span.*?>.*?<\/span>/g, '');
                            return cleanedData.trim().toLowerCase();
                          }

                          return data;
                        }
                    },
                    {
                        targets: 3,                        
                        render: function (data, type, row, meta) {                          
                          if (type === 'sort')
                          {
                            const cleanedData = data.replace(/<span.*?>.*?<\/span>/g, '');
                            return cleanedData.trim().toLowerCase();
                          }

                          return data;
                        }
                    }
                ]
            //}).row(nestedTr); // Use DataTable to get the row object
          });          

          // Lock order unless manually sorted
          nesteddt.on('order.dt', function () {console.log(nesteddt.order());
              nestedTable.data('stored-order', nesteddt.order());
          });

          var nestedRow = nesteddt.row(nestedTr);

          // Block unintended sorting
          $(nesteddt.table().container()).on('click', function (e) {           

              // If click is on thead, allow
              if ($(e.target).closest('thead').length) return;

              // If click is on an action menu/button, allow
              //if ($(e.target).closest('ul.dropdown-menu').length) return;
              if ($(e.target).closest('.dropdown-menu, .dropdown-toggle, .action-menu, .menu-button, .btn, .accordion-button').length) return;

              // Otherwise, stop propagation to prevent sorting
              e.stopPropagation();
          });
        }
      
        if (nestedRow.child.isShown()) {
            nestedRow.child.hide();
            nestedTr.removeClass('shown');
        } else {
            
            // Open the next level nested table               
            nestedRow.child(format(declaration_datas, declaration_name, 'invoice', nestedRow.index(), parent_row_no), 'p-0 cw-1').show();
            nestedTr.addClass('shown');
        }
    });
    */
  }

  function format ( d, which_tab, row_type, row_no, parent_row_no) { 
    
      // `d` is the original data object for the row
      if(row_type == 'co-invoice') 
      {
        var co_invoice_html = '<table class="datatables-declaration-co-invoices datatables-'+ which_tab +'-declaration-co-invoices table accordion">' +
                                '<thead class="">' +
                                  '<tr class="accordion-button">' +
                                    '<th class="cw-1 declaration-th-w20"></th>' + //sorting_disabled
                                    '<th class="text-start declaration-th-w150 align-top">Lope No.</th>' +  //sorting
                                    '<th class="text-start declaration-th-w150 align-top">Category Desc.</th>' +  //sorting_disabled
                                    ((d[row_no].country == 'CH') ? '<th class="text-start declaration-th-w150 align-top">Date</th>' : '') + 
                                    '<th class="text-start declaration-th-w150 align-top">Commercial invoice</th>' +  // sorting sorting_asc
                                    '<th class="text-end declaration-th-w150 align-top">Statistical value</th>' + //sorting_disabled                          
                                    '<th class="text-end declaration-th-w150 align-top">Net Amount</th>' + //sorting_disabled
                                    '<th class="text-end declaration-th-w150 align-top">Net Sum Invoice</th>' + 

                                    ((d[row_no].country == 'CH') ? '<th class="text-end declaration-th-w150 align-top">Net Amount (CHF)</th>' : '') + 

                                    '<th class="text-end declaration-th-w150 align-top">Import VAT</th>' + //sorting_disabled

                                    ((d[row_no].country == 'CH') ? '<th class="text-end declaration-th-w150 align-top">VAT Amount (CHF)</th>' : '') + 

                                    //'<th class="text-end"></th>' +   
                                    '<th class="text-end declaration-th-w150 align-top">Duties</th>' + //sorting_disabled
                                    // '<th class="text-end declaration-th-w150">Currency</th>' +                                    
                                    '<th class="text-end declaration-th-w150 align-top">VAT on Duties</th>' +  //sorting_disabled
                                    '<th class="text-end declaration-th-w150 align-top">Adjustment</th>' + //sorting_disabled
                                    '<th class="text-end declaration-th-w150 align-top">VAT on adjustment</th>' + //sorting_disabled
                                    '<th class="text-center align-top">Action</th>' + //sorting_disabled
                                  '</tr>' +
                                '</thead>' +
                                '<tbody>'
        ;        

        if (typeof row_no !== 'undefined')
        {
          if(d[row_no].co_invoices == null || d[row_no].co_invoices.length == 0)
            co_invoice_html += '<tr><td colspan="'+ ((d[row_no].country == 'CH') ? 16 : 13) +'" style="text-align: center;">No commercial invoices</td></tr>';         
          else {
            for (let i in d[row_no].co_invoices) {  

              /*-- Rules --*/
              var net_amount_class = '';  

              var tooltip_prefix = 'data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="';
              var tooltip_suffix = '"';

              var net_amount_tooltip = '';  

              let cominvoice_net_amount = d[row_no].co_invoices[i].com_net_amount;
              let parsed_cominvoice_net_amount =  parseAmountValue(cominvoice_net_amount, d[row_no].co_invoices[i].currency);
              
              let parsed_salesinvoice_net_amount_gross = 0;
              for (let j in d[row_no].co_invoices[i].invoices) 
              {
                let salesinvoice_net_amount = d[row_no].co_invoices[i].invoices[j].net_amount;
                let parsed_salesinvoice_net_amount = parseAmountValue(salesinvoice_net_amount, d[row_no].co_invoices[i].invoices[j].currency);

                let sales_invoice_shipping_amount = d[row_no].co_invoices[i].invoices[j].shipping;
                let parsed_salesinvoice_shipping_amount = parseAmountValue(sales_invoice_shipping_amount, d[row_no].co_invoices[i].invoices[j].currency);

                let sales_invoice_variance_amount = d[row_no].co_invoices[i].invoices[j].variance;
                let parsed_salesinvoice_variance_amount = parseAmountValue(sales_invoice_variance_amount, d[row_no].co_invoices[i].invoices[j].currency);

                parsed_salesinvoice_net_amount_gross += (parsed_salesinvoice_net_amount + parsed_salesinvoice_shipping_amount + parsed_salesinvoice_variance_amount);                            
              }

              var currency_locale = 'da-DK';
              var currency_style = 'NOK';
              if(d[row_no].co_invoices[i].country == 'CH')
              {
                currency_locale = 'fr-FR';
                currency_style = 'CHF';
              }
              let parsed_salesinvoice_net_amount_gross_format = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parsed_salesinvoice_net_amount_gross);

              //RULE 1 - TOTAL com. invoice net amount - TOTAL sales invoice net amount
              if(d[row_no].co_invoices[i].country == 'CH')
              {
                if(d[row_no].co_invoices[i].invoices.length == 0)
                {
                  net_amount_class = 'text-danger'; 

                  net_amount_tooltip = 'Sales invoice missing';
                }
              }
              else
              {
                let diff_cominvoice_vs_salesinvoice_net_amount = parsed_cominvoice_net_amount - parsed_salesinvoice_net_amount_gross;              
                if (Math.abs(diff_cominvoice_vs_salesinvoice_net_amount) > 100)
                {
                  if(d[row_no].co_invoices[i].invoices.length > 0)
                  {
                    net_amount_class = 'text-danger'; 

                    net_amount_tooltip = 'Over 100 difference between the commercial invoice and sales invoice net amount';
                  }
                }
              }//other than CH

              //RULE 2 - TOTAL com. invoice net amount - TOTAL ivf/xml net amount     
              if(d[row_no].co_invoices[i].country != 'CH')
              {         
                let statistical_value = d[row_no].co_invoices[i].statistical_value;
                let parsed_statistical_value =  parseAmountValue(statistical_value, d[row_no].co_invoices[i].currency);
                
                let diff_cominvoice_vs_xml_net_amount = parsed_statistical_value - parsed_cominvoice_net_amount;              
                if (Math.abs(diff_cominvoice_vs_xml_net_amount) > 100)
                {
                  net_amount_class = 'text-danger'; 
                 
                  if(net_amount_tooltip == '')                
                    net_amount_tooltip = 'Over 100 difference between the statistical value and commercial invoice net amount'; 
                  else                
                    net_amount_tooltip = 'Over 100 difference between the commercial invoice and sales invoice / statistical value net amount'; 
                }                
              }
              var final_net_amount_tooltip = tooltip_prefix + net_amount_tooltip + tooltip_suffix;
              /*--end Rules --*/

              /*-- Menus --*/ 
              var btn_icn = (d[row_no].co_invoices[i].comment_reason) ? 'edit' : 'add';   
              var btn_title = (d[row_no].co_invoices[i].comment_reason) ? 'Edit' : 'Add';

              var divider = '<div class="dropdown-divider"></div>';

              var btn_delete_comment = '';
              if(d[row_no].co_invoices[i].comment_reason)                
                btn_delete_comment = divider + '<li>' +
                              '<a href="javascript:;" class="dropdown-item text-danger btn-declaration-invoice-delete-comment" title="Delete Comment" data-invoice_name="com" data-tab_name="'+ which_tab +'" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'">' +
                                '<span class="text-danger"><i class="bx bx-comment-x"></i> Delete Comment</span>' +
                              '</a>' +                                          
                            '</li>';

              var tr_tooltip = '';      
              var tr_tooltip_message = "";//"<i class='bx bx-bell bx-xs'></i> <span>File Missing</span>";
                        
              var tr_class_name = '';          
              if(d[row_no].co_invoices[i].doc_status.toLowerCase() != 'validated' || d[row_no].co_invoices[i].lope_no == '-'
                 || d[row_no].co_invoices[i].co_invoice_no == '-' || d[row_no].co_invoices[i].comment_reason != null)  
              {   
                if(d[row_no].co_invoices[i].lope_no == '-')
                {
                  if(d[row_no].co_invoices[i].disregard_type == 'lopeno') 
                    tr_tooltip_message = "<span>Disregarded Lope No.: "+ d[row_no].co_invoices[i].disregarded_no +"<br>"+ d[row_no].co_invoices[i].disregard_reason.toUpperCase() +
                                          d[row_no].co_invoices[i].disregard_comment.replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;") +"</span>";       
                  else
                    tr_tooltip_message = "<i class='bx bx-bell bx-xs'></i> <span>Lope No. Missing</span>";       
                }
                else if(d[row_no].co_invoices[i].co_invoice_no == '-')
                {
                  if(d[row_no].co_invoices[i].disregard_invoice) 
                  {                    
                    tr_tooltip_message = "<span>Disregarded Com. invoice "+ d[row_no].co_invoices[i].disregarded_no +"<br>"+ d[row_no].co_invoices[i].disregard_reason.toUpperCase() +
                                          d[row_no].co_invoices[i].disregard_comment.replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;") +"</span>";  

                  }
                  else
                    tr_tooltip_message = "<i class='bx bx-bell bx-xs'></i> <span>Com. Invoice Missing</span>"; 
                }
                else if(d[row_no].co_invoices[i].comment_reason != null)
                {
                  tr_tooltip_message = "<span>"+ d[row_no].co_invoices[i].comment_reason.toUpperCase() +
                                          d[row_no].co_invoices[i].comment.replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;") +"</span>";  
                }

                tr_class_name = 'alert-danger';
                tr_tooltip = 'data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="'+ tr_tooltip_message +'"';         
              } 

              var invoice_count_class = 'alert-primary';
              if(d[row_no].co_invoices[i].invoices.length == 0)  
                invoice_count_class = 'alert-danger';                                                 

              var filter_disregarded_invoices = d[row_no].co_invoices[i].invoices.filter(function(obj) {                
                  return (obj.disregard_invoice);
              });
             
              var orginal_co_invoice_no = '';            
              var btn_rematch_cominvoice_menu = '';
              if(d[row_no].co_invoices[i].co_invoice_no != d[row_no].co_invoices[i].orginal_co_invoice_no)
              {        
                if(!d[row_no].co_invoices[i].disregard_invoice)     
                {   
                  if(d[row_no].co_invoices[i].orginal_co_invoice_no != '')                    
                    orginal_co_invoice_no = '<br><span class="alert-warning text-end fs-tiny p-1">' + d[row_no].co_invoices[i].orginal_co_invoice_no + '</span>';
                 
                  btn_rematch_cominvoice_menu = divider + 
                                          '<li>' +
                                            '<a href="javascript:;" class="dropdown-item text-danger btn-remove-rematch-declaration-cominvoice" title="Remove Rematch com. invoice" data-invoice_name="com" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-group_lope_no="'+ d[row_no].co_invoices[i].group_lope_no +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-disregard="1" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                              '<span><i class="bx bx-folder-minus"></i> Remove Rematch com. invoice</span>' +
                                            '</a>' +
                                          '</li>';
                }                                          
              }
             
              if(btn_rematch_cominvoice_menu == '')
              {
                if(d[row_no].co_invoices[i].invoices.length == 0 || d[row_no].co_invoices[i].co_invoice_no == '-')
                {                
                  btn_rematch_cominvoice_menu = divider + 
                                          '<li>' +
                                            '<a href="javascript:;" class="dropdown-item btn-rematch-declaration-cominvoice" title="Rematch com. invoice" data-invoice_name="com" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-disregard="1" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'" data-no_of_split="'+ d[row_no].co_invoices[i].no_of_split +'">' +
                                              '<span><i class="bx bx-list-plus"></i> Rematch com. invoice</span>' +
                                            '</a>' +
                                          '</li>';
                }
              }

              var same_cominvoice_class = '';
              var filter_same_cominvoice = d[row_no].co_invoices.filter(function(obj) {                
                  return (obj.co_invoice_no === d[row_no].co_invoices[i].co_invoice_no);
              });
              if(filter_same_cominvoice.length > 1)
                same_cominvoice_class = 'text-danger'; 
                
              var btn_cargo_pdf = '';
              if(d[row_no].co_invoices[i].pdf)
              {
                if(d[row_no].co_invoices[i].country == 'CH')
                  btn_cargo_pdf = divider + 
                                '<li>' +
                                  '<a href="javascript:;" class="dropdown-item btn-declaration-cargo-download-pdf" title="View Cargo PDF" data-invoice_name="com" data-cargo_type="swissimportreconciliationfiles" data-tab_name="'+ which_tab +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-cargo_file_id="'+ d[row_no].co_invoices[i].pdf +'">' +
                                    '<span><i class="bx bxs-file-pdf text-danger"></i> View Cargo PDF</span>' +
                                  '</a>' +                            
                                '</li>';   
                else
                  btn_cargo_pdf = divider + 
                                  '<li>' +
                                    '<a href="javascript:;" class="dropdown-item btn-declaration-cargo-download-pdf" title="View Cargo PDF" data-invoice_name="com" data-cargo_type="cargo_mailbox" data-tab_name="'+ which_tab +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-cargo_file_id="'+ d[row_no].co_invoices[i].pdf +'">' +
                                      '<span><i class="bx bxs-file-pdf text-danger"></i> View Cargo PDF</span>' +
                                    '</a>' +                            
                                  '</li>';                            
              }
              
              // var btn_delete_cominvoice = divider + 
              //                             '<li>' +
              //                               '<a href="javascript:;" class="dropdown-item text-danger btn-delete-declaration-invoice" title="Delete com. invoice" data-invoice_name="com" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
              //                                 '<span><i class="bx bx-folder-minus"></i> Delete com. invoice</span>' +
              //                               '</a>' +
              //                             '</li>';

              //var btn_disregard_cominvoice = '';
              var has_retain_cominvoice = 0;
              var has_disregard_cominvoice = 0;
              var btn_disregard_cominvoice_lopeno = '';
              if(d[row_no].co_invoices[i].co_invoice_no == '-')
              {                  
                if(d[row_no].co_invoices[i].disregard_invoice)  
                {
                  has_retain_cominvoice = 1;
                  btn_disregard_cominvoice_lopeno = divider + 
                                              '<li>' +
                                                '<a href="javascript:;" class="dropdown-item btn-retain-cominvoice" title="Retain com. invoice" data-invoice_name="com" data-retain="1" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                                  '<span><i class="bx bx-add-to-queue"></i> Retain com. invoice</span>' +
                                                '</a>' +
                                              '</li>';
                }
              }
              else
              {
                has_disregard_cominvoice = 1;
                btn_disregard_cominvoice_lopeno = divider + 
                                            '<li>' +
                                              '<a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard com. invoice" data-invoice_name="com" data-disregard="1" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                                '<span><i class="bx bx-folder-minus"></i> Disregard com. invoice</span>' +
                                              '</a>' +
                                            '</li>';
              }

              //var btn_disregard_lopeno = '';
              if(d[row_no].co_invoices[i].lope_no == '-') 
              {
                if(d[row_no].co_invoices[i].disregard_type == 'lopeno') 
                  btn_disregard_cominvoice_lopeno = divider + 
                                              '<li>' +
                                                '<a href="javascript:;" class="dropdown-item btn-retain-lopeno" title="Retain lope no." data-invoice_name="com" data-retain="1" data-disregard_type="lopeno" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-lope_no="'+ d[row_no].co_invoices[i].disregarded_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                                  '<span><i class="bx bx-add-to-queue"></i> Retain lope no.</span>' +
                                                '</a>' +
                                              '</li>';
              }
              else
              {
                if(has_disregard_cominvoice)
                  btn_disregard_cominvoice_lopeno += divider + 
                                            '<li>' +
                                              '<a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard lope no." data-invoice_name="com" data-disregard="1" data-disregard_type="lopeno" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                                '<span><i class="bx bx-folder-minus"></i> Disregard lope no.</span>' +
                                              '</a>' +
                                            '</li>';
                else  
                {  
                  if(!has_retain_cominvoice) 
                  //   btn_disregard_cominvoice_lopeno += divider + 
                  //                             '<li>' +
                  //                               '<a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard lope no." data-invoice_name="com" data-disregard="1" data-disregard_type="lopeno" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                  //                                 '<span><i class="bx bx-folder-minus"></i> Disregard lope no.</span>' +
                  //                               '</a>' +
                  //                             '</li>';
                  // else
                    btn_disregard_cominvoice_lopeno = divider + 
                                                '<li>' +
                                                  '<a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard lope no." data-invoice_name="com" data-disregard="1" data-disregard_type="lopeno" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                                    '<span><i class="bx bx-folder-minus"></i> Disregard lope no.</span>' +
                                                  '</a>' +
                                                '</li>';
                }
              }
              
              var btn_disregard_wrong_cominvoice = '';
              if(String(d[row_no].co_invoices[i].group_lope_no).indexOf('***') != -1)  
              {
                btn_disregard_wrong_cominvoice = divider + 
                                          '<li>' +
                                            '<a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Disregard wrong com. invoice" data-invoice_name="com" data-disregard="1" data-disregard_type="ivf" data-group_invoice_id="'+ d[row_no].co_invoices[i].group_lope_no +'" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                              '<span><i class="bx bx-folder-minus"></i> Disregard wrong com. invoice</span>' +
                                            '</a>' +
                                          '</li>';
              }       

              //var btn_delete_cominvoice = '';
              // if(d[row_no].co_invoices[i].co_invoice_no != '-')  
              //   btn_delete_cominvoice = divider + 
              //                               '<li>' +
              //                                 '<a href="javascript:;" class="dropdown-item text-danger btn-disregard-declaration-invoice" title="Delete com. invoice" data-invoice_name="com" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
              //                                   '<span><i class="bx bx-folder-minus"></i> Delete com. invoice</span>' +
              //                                 '</a>' +
              //                               '</li>'; 

              var btn_refresh_cominvoice = '';
              //if(d[row_no].co_invoices[i].invoices.length == 0)             
                btn_refresh_cominvoice  = divider + 
                                          '<li>' +
                                            '<a href="javascript:;" class="dropdown-item text-secondary btn-refresh-invoice" title="Refresh Data" data-invoice_name="com" data-group_invoice_id="'+ d[row_no].co_invoices[i].group_lope_no +'" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                              '<span><i class="bx bx-refresh"></i> Refresh Data</span>' +
                                            '</a>' +
                                          '</li>';

              var btn_unmatch_cominvoice = '';   
              // if(d[row_no].co_invoices[i].co_invoice_no == d[row_no].co_invoices[i].orginal_co_invoice_no)
              // {
              //   //Remove rematch for the same com. invoice no. (Unmatch com. invoice)
              //   if(btn_rematch_cominvoice_menu == '' && d[row_no].co_invoices[i].lope_no != '-')
              //     btn_unmatch_cominvoice = divider + 
              //                               '<li>' +
              //                                 '<a href="javascript:;" class="dropdown-item text-secondary btn-remove-rematch-declaration-cominvoice" title="Unmatch com. invoice" data-invoice_name="com" data-unmatch="1" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-disregard="1" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
              //                                   '<span><i class="bx bx-list-minus"></i> Unmatch com. invoice</span>' +
              //                                 '</a>' +
              //                               '</li>'; 
              // }
              // else
              // {         
                if(btn_rematch_cominvoice_menu == '' && d[row_no].co_invoices[i].lope_no != '-' && d[row_no].co_invoices[i].doc_id)
                  btn_unmatch_cominvoice  = divider + 
                                            '<li>' +
                                              '<a href="javascript:;" class="dropdown-item text-secondary btn-unmatch-invoice" title="Unmatch com. invoice" data-invoice_name="com" data-group_invoice_id="'+ d[row_no].co_invoices[i].group_lope_no +'" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                                '<span><i class="bx bx-list-minus"></i> Unmatch com. invoice</span>' +
                                              '</a>' +
                                            '</li>';
                else
                {
                  if(d[row_no].co_invoices[i].co_invoice_no == d[row_no].co_invoices[i].orginal_co_invoice_no)
                  {
                    //Remove rematch for the same com. invoice no. (Unmatch com. invoice)
                    if(btn_rematch_cominvoice_menu == '' && d[row_no].co_invoices[i].lope_no != '-')
                      btn_unmatch_cominvoice = divider + 
                                              '<li>' +
                                                '<a href="javascript:;" class="dropdown-item text-secondary btn-remove-rematch-declaration-cominvoice" title="Unmatch com. invoice" data-invoice_name="com" data-unmatch="1" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-group_lope_no="'+ d[row_no].co_invoices[i].group_lope_no +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-disregard="1" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                                  '<span><i class="bx bx-list-minus"></i> Unmatch com. invoice</span>' +
                                                '</a>' +
                                              '</li>'; 
                  }
                }
              //}
              /*--end Menus --*/                               
                
                co_invoice_html += '<tr class="accordion-button '+ tr_class_name +'">' +
                                    '<td class="cw-1 declaration-th-w20" '+ tr_tooltip +'></td>' +                                     
                                    '<td class="cw-1 text-start declaration-th-w150 '+ same_cominvoice_class +'" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].lope_no + orginal_co_invoice_no + '</td>' + 
                                    '<td class="text-start declaration-th-w150" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].category_desc + '</td>' + 

                                    ((d[row_no].co_invoices[i].country == 'CH') ? '<td class="text-start declaration-th-w150" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].co_invoice_date + '</td>' : '') + 

                                    '<td class="text-start declaration-th-w150 '+ same_cominvoice_class +'" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].co_invoice_no + '<span class="'+ invoice_count_class +' text-end fs-tiny p-1 mx-2">' + (d[row_no].co_invoices[i].invoices.length - filter_disregarded_invoices.length) + '</span>' + '</td>' +
                                    '<td class="text-end declaration-th-w150" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].statistical_value + '</td>' +
                                    //'<td class="text-end declaration-th-w150 '+ net_amount_class +'" '+ ((final_net_amount_tooltip) ? final_net_amount_tooltip : tr_tooltip) +'>' + ((d[row_no].co_invoices[i].category_type == 'RE') ? '-' : d[row_no].co_invoices[i].com_net_amount) + '</td>' +
                                    '<td class="text-end declaration-th-w150 '+ net_amount_class +'" '+ ((final_net_amount_tooltip) ? final_net_amount_tooltip : tr_tooltip) +'>' + d[row_no].co_invoices[i].com_net_amount + '</td>' +
                                    '<td class="text-end declaration-th-w150" '+ tr_tooltip +'>' + parsed_salesinvoice_net_amount_gross_format + '</td>' +

                                    ((d[row_no].co_invoices[i].country == 'CH') ? '<td class="text-end declaration-th-w150">' + ((d[row_no].co_invoices[i].convert_net_amount) ? d[row_no].co_invoices[i].convert_net_amount : '-') + '</td>' : '') + 

                                    '<td class="text-end declaration-th-w150" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].import_vat + '</td>' +  

                                    ((d[row_no].co_invoices[i].country == 'CH') ? '<td class="text-end declaration-th-w150">' + ((d[row_no].co_invoices[i].convert_vat_amount) ? d[row_no].co_invoices[i].convert_vat_amount : '-') + '</td>' : '') + 

                                    '<td class="text-end declaration-th-w150" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].duties + '</td>' +                                    
                                    '<td class="text-end declaration-th-w150" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].vat_on_duties + '</td>' +
                                    '<td class="text-end declaration-th-w150" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].adjustment + '</td>' +
                                    '<td class="text-end declaration-th-w150" '+ tr_tooltip +'>' + d[row_no].co_invoices[i].vat_on_adjustment + '</td>' +
                                    '<td class="text-center">' +  
                                      ((d[row_no].co_invoices[i].id != '-') ?                                     
                                      '<div class="d-inline-block">' +
                                        '<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                                        '<ul class="dropdown-menu dropdown-menu-end m-0">' +
                                          '<li>' +
                                            '<a href="javascript:;" class="dropdown-item btn-disregard-declaration-invoice" data-insert_type="'+ btn_icn +'" data-comment_reason="'+ d[row_no].co_invoices[i].comment_reason +'" data-comment="'+ d[row_no].co_invoices[i].comment +'" data-comment_visiblity="'+ d[row_no].co_invoices[i].comment_visiblity +'" title="'+btn_title+' Comment" data-invoice_name="com" data-invoice_id="'+ d[row_no].co_invoices[i].id +'" data-invoice_no="'+ d[row_no].co_invoices[i].co_invoice_no +'" data-tab_name="'+ which_tab +'" data-disregard="0" data-invoice_date="'+ d[row_no].co_invoices[i].o_invoice_date +'">' +
                                              '<span><i class="bx bx-comment-'+ btn_icn +'"></i> '+ btn_title +' Comment</span>' +
                                            '</a>' +                                          
                                          '</li>' +                                                               
                                          btn_delete_comment +   
                                          btn_refresh_cominvoice +                                                                            
                                          btn_rematch_cominvoice_menu +  
                                          btn_unmatch_cominvoice +
                                          btn_cargo_pdf +                                            
                                          btn_disregard_wrong_cominvoice +
                                          btn_disregard_cominvoice_lopeno +                                          
                                          //btn_disregard_lopeno +
                                          //btn_delete_cominvoice +
                                        '</ul>' +
                                      '</div>' 
                                       : '') + 
                                    '</td>' +
                                  '</tr>'
                ;                
              }//for
          }
        }
            
        co_invoice_html += '</tbody></table>';
        
        return co_invoice_html;
      }//co_invoices
      else if(row_type == 'invoice') 
      {
        var vat_check_percent = '';        

        var invoice_html = '<table class="datatables-declaration-invoices datatables-'+ which_tab +'-declaration-invoices table">' +
                                '<thead class="">' +
                                  '<tr>' +
                                    '<th class="cw-1 declaration-th-w20 dt-chk-select-all">'+
                                      '<input type="checkbox" class="form-check-input">' +
                                    '</th>' +                                         
                                    '<th class="text-start declaration-th-w150 align-top">Invoices</th>' +
                                    '<th class="text-end declaration-th-w150"></th>' + 
                                    '<th class="text-start declaration-th-w150 align-top">Date</th>' + 
                                    '<th class="text-end declaration-th-w150"></th>' +                                     
                                    '<th class="text-end declaration-th-w150 align-top">Net amount</th>' +

                                    ((d[parent_row_no].country == 'CH') ? '<th class="text-end declaration-th-w150"></th>' : '') + 
                                    ((d[parent_row_no].country == 'CH') ? '<th class="text-end declaration-th-w150 align-top">Net amount (CHF)</th>' : '') + 

                                    '<th class="text-end declaration-th-w150 align-top">Adjustment</th>' +
                                    '<th class="text-end declaration-th-w150 align-top">VAT amount</th>' +

                                    ((d[parent_row_no].country == 'CH') ? '<th class="text-end declaration-th-w150 align-top">VAT amount (CHF)</th>' : '') + 

                                    '<th class="text-end declaration-th-w150 align-top head_vat_check_percent_'+ which_tab +'">VAT check '+ vat_check_percent +'%</th>' +
                                    '<th class="text-end declaration-th-w150 align-top">Currency</th>' +                                    
                                    '<th class="text-end declaration-th-w150"></th>' + 
                                    '<th class="text-end declaration-th-w150"></th>' + 
                                    '<th class="text-center align-top">Action</th>' +
                                  '</tr>' +
                                '</thead>' +
                                '<tbody>'
        ;        

        if (typeof parent_row_no !== 'undefined' && typeof row_no !== 'undefined')
        {
          if(d[parent_row_no].co_invoices[row_no].country == 'NO')
            vat_check_percent = '25';
          else if(d[parent_row_no].co_invoices[row_no].country == 'CH')
            vat_check_percent = '8.1';

          $('.head_vat_check_percent_'+ which_tab).html('VAT check '+ vat_check_percent +'%');

          if(d[parent_row_no].co_invoices[row_no].invoices == null || d[parent_row_no].co_invoices[row_no].invoices.length == 0)
            invoice_html += '<tr><td colspan="'+ ((d[parent_row_no].co_invoices[row_no].country == 'CH') ? 16 : 13) +'" style="text-align: center;">No sales invoices</td></tr>';         
          else {            
            for (let j in d[parent_row_no].co_invoices[row_no].invoices) 
            {   
              var tooltip_prefix = 'data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" title="';
              var tooltip_suffix = '"';

              var tr_class_name = '';
              if(d[parent_row_no].co_invoices[row_no].invoices[j].currency != 'NOK')  
              {     
                tr_class_name = 'alert-danger';

                $("#btn-declaration-"+ which_tab +" span").removeClass("alert-primary");
                $("#btn-declaration-"+ which_tab +" span").addClass(tr_class_name);
              }  

              /*Disregarded Sales Invoice*/
              var disregard_invoice_class_name = '';
              var disregard_invoice_tooltip = '';
              if(d[parent_row_no].co_invoices[row_no].invoices[j].disregard_invoice)
              {
                disregard_invoice_class_name = ' disabled';

                disregard_invoice_tooltip = tooltip_prefix + d[parent_row_no].co_invoices[row_no].invoices[j].disregard_reason.toUpperCase() +
                                        d[parent_row_no].co_invoices[row_no].invoices[j].disregard_comment.replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;") + 
                                        tooltip_suffix;
              }
              if($('#chk-declaration-filter-show-disregarded-invoices').prop('checked'))
                disregard_invoice_class_name += ' show';
              else
                disregard_invoice_class_name += ' hide';
              /*Disregarded Sales Invoice*/

              var btn_icn = (d[parent_row_no].co_invoices[row_no].invoices[j].comment_reason) ? 'edit' : 'add';      
              var btn_title = (d[parent_row_no].co_invoices[row_no].invoices[j].comment_reason) ? 'Edit' : 'Add';

              var btn_delete_comment = '';
              if(d[parent_row_no].co_invoices[row_no].invoices[j].comment_reason)
              {
                btn_delete_comment = '<li>' +
                              '<a href="javascript:;" class="dropdown-item text-danger btn-declaration-invoice-delete-comment" title="Delete Comment" data-invoice_name="sales" data-tab_name="'+ which_tab +'" data-invoice_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'">' +
                                '<span class="text-danger"><i class="bx bx-comment-x"></i> Delete Comment</span>' +
                              '</a>' +                                          
                            '</li>';

                if(disregard_invoice_tooltip)
                  disregard_invoice_tooltip = tooltip_prefix + d[parent_row_no].co_invoices[row_no].invoices[j].comment_reason.toUpperCase() +
                                        d[parent_row_no].co_invoices[row_no].invoices[j].comment + "----------<br>" +
                                        d[parent_row_no].co_invoices[row_no].invoices[j].disregard_reason.toUpperCase() +
                                        d[parent_row_no].co_invoices[row_no].invoices[j].disregard_comment.replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;") +
                                        tooltip_suffix;
                else
                  disregard_invoice_tooltip = tooltip_prefix + d[parent_row_no].co_invoices[row_no].invoices[j].comment_reason.toUpperCase() +
                                        d[parent_row_no].co_invoices[row_no].invoices[j].comment + tooltip_suffix;
              }

              var btn_sales_pdf = '';
              if(d[parent_row_no].co_invoices[row_no].invoices[j].pdf)
              {
                btn_sales_pdf = '<li>' +
                                  '<a href="javascript:;" class="dropdown-item btn-declaration-invoice-download-pdf" title="View PDF" data-invoice_name="sales" data-tab_name="'+ which_tab +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-invoice_xml_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].pdf +'">' +
                                    '<span><i class="bx bxs-file-pdf text-danger"></i> View PDF</span>' +
                                  '</a>' +                                          
                                '</li>' +
                                '<div class="dropdown-divider"></div>' +
                                '<li>' +
                                  '<a href="javascript:;" class="dropdown-item btn-declaration-invoice-edit" title="Edit" data-invoice_name="sales" data-tab_name="'+ which_tab +'" data-credit_note="'+ ((d[parent_row_no].co_invoices[row_no].id == '-') ? 1 : 0) +'" data-invoice_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-invoice_date="'+ d[parent_row_no].co_invoices[row_no].invoices[j].o_invoice_date +'" data-invoice_xml_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].pdf +'" data-edit_from="'+ d[parent_row_no].co_invoices[row_no].invoices[j].edit_from +'">' +
                                    '<span><i class="bx bx-edit-alt"></i> Edit</span>' +
                                  '</a>' +                                          
                                '</li>';
              }
              // else
              // {
              //   btn_sales_pdf = '<li>' +
              //                     '<a href="javascript:;" class="dropdown-item btn-declaration-invoice-create" title="Add" data-invoice_name="sales" data-tab_name="'+ which_tab +'"  data-month_year="'+ d[parent_row_no].month_year +'" data-invoice_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-invoice_date="'+ d[parent_row_no].co_invoices[row_no].invoices[j].o_invoice_date +'" data-invoice_xml_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].pdf +'">' +
              //                       '<span><i class="bx bx-plus-medical"></i> Add</span>' +
              //                     '</a>' +                                          
              //                   '</li>';
              // }

              var btn_sales_invoice_move = '';              
              btn_sales_invoice_move = '<li>' +
                                          '<a href="javascript:;" class="dropdown-item btn-move-declaration-salesinvoice" title="Move sales invoice" data-invoice_name="sales" data-invoice_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[parent_row_no].co_invoices[row_no].invoices[j].o_invoice_date +'" data-cominvoice_id="'+ d[parent_row_no].co_invoices[row_no].id +'" data-cominvoice_no="'+ d[parent_row_no].co_invoices[row_no].co_invoice_no +'">' +
                                            '<span><i class="bx bx-move"></i> Move sales invoice</span>' +
                                          '</a>' +
                                        '</li>';

              var btn_refresh_salesinvoice = '';
              if(d[parent_row_no].co_invoices[row_no].invoices[j].is_net_amount_null)          
                btn_refresh_salesinvoice  = '<li>' +
                                            '<a href="javascript:;" class="dropdown-item text-secondary btn-refresh-invoice" title="Refresh Data" data-invoice_name="sales" data-invoice_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-tab_name="'+ which_tab +'" data-invoice_date="'+ d[parent_row_no].co_invoices[row_no].invoices[j].o_invoice_date +'" data-cominvoice_id="'+ d[parent_row_no].co_invoices[row_no].id +'" data-cominvoice_no="'+ d[parent_row_no].co_invoices[row_no].co_invoice_no +'">' +
                                              '<span><i class="bx bx-refresh"></i> Refresh Data</span>' +
                                            '</a>' +
                                          '</li>';
              
              /*-- Rules --*/
              var negative_net_amount_class = '';              

              var net_amount_tooltip = '';  
              if (d[parent_row_no].co_invoices[row_no].invoices[j].net_amount.startsWith("-")) 
              {
                negative_net_amount_class = 'text-danger';

                net_amount_tooltip += tooltip_prefix + 'Negative number.' + tooltip_suffix;
              }

              var negative_vat_amount_class = '';
              var vat_amount_tooltip = 'data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" ';  
              if (d[parent_row_no].co_invoices[row_no].invoices[j].vat_amount.startsWith("-")) 
              {
                negative_vat_amount_class = 'text-danger';

                vat_amount_tooltip += tooltip_prefix + 'Negative number.' + tooltip_suffix;
              }

              //RULE 3 - com. invoice net amount 0
              if(d[parent_row_no].co_invoices[row_no].invoices[j].is_net_amount_null)
              {
                negative_net_amount_class = 'text-danger';

                net_amount_tooltip += tooltip_prefix + 'Missing sales invoice.' + tooltip_suffix;
              }              
              /*--end Rules --*/             

              /*-- Adjustment Amount Calculation --*/   
              var currency_locale = 'da-DK';
              var currency_style = 'NOK';
              if(d[parent_row_no].co_invoices[row_no].country == 'CH')
              {
                currency_locale = 'fr-FR';
                currency_style = 'CHF';
              }
              let sales_invoice_shipping_amount = d[parent_row_no].co_invoices[row_no].invoices[j].shipping;
              let parsed_salesinvoice_shipping_amount = parseAmountValue(sales_invoice_shipping_amount, d[parent_row_no].co_invoices[row_no].invoices[j].currency);

              let sales_invoice_variance_amount = d[parent_row_no].co_invoices[row_no].invoices[j].variance;
              let parsed_salesinvoice_variance_amount = parseAmountValue(sales_invoice_variance_amount, d[parent_row_no].co_invoices[row_no].invoices[j].currency);

              let sales_invoice_adjustment_amount = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parsed_salesinvoice_shipping_amount + parsed_salesinvoice_variance_amount));
              /*--end Adjustment Amount Calculation --*/ 
              
              invoice_html += '<tr class="'+ tr_class_name + disregard_invoice_class_name +'">' +
                                  '<td class="cw-1 declaration-th-w20 dt-chk-cell alert-warning">' +
                                    ((d[parent_row_no].co_invoices[row_no].invoices[j].disregard_invoice) ? '' : 
                                    '<input type="checkbox" class="dt-chk form-check-input" value="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-invoice_date="'+ d[parent_row_no].co_invoices[row_no].invoices[j].o_invoice_date +'">') +                                   
                                  '</td>' +                                      
                                  //'<td class="text-start declaration-th-w150"'+ disregard_invoice_tooltip +'>' + ((d[parent_row_no].co_invoices[row_no].category_type == 'RE') ? '-' : d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no) + '</td>' +
                                  '<td class="text-start declaration-th-w150"'+ disregard_invoice_tooltip +'>' + d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no + '</td>' +
                                  '<td class="text-end declaration-th-w150"'+ disregard_invoice_tooltip +'></td>' +    
                                  '<td class="text-start declaration-th-w150"'+ disregard_invoice_tooltip +'>' + d[parent_row_no].co_invoices[row_no].invoices[j].invoice_date + '</td>' +                                  
                                  '<td class="text-end declaration-th-w150"'+ disregard_invoice_tooltip +'></td>' +
                                  '<td class="text-end declaration-th-w150 '+ negative_net_amount_class +'" '+ ((disregard_invoice_tooltip) ? disregard_invoice_tooltip : net_amount_tooltip) +'>' + d[parent_row_no].co_invoices[row_no].invoices[j].net_amount + '</td>' +

                                  ((d[parent_row_no].co_invoices[row_no].country == 'CH') ? '<td class="text-end declaration-th-w150"'+ disregard_invoice_tooltip +'></td>' : '') + 
                                  ((d[parent_row_no].co_invoices[row_no].country == 'CH') ? '<td class="text-end declaration-th-w150">' + ((d[parent_row_no].co_invoices[row_no].invoices[j].convert_net_amount) ? d[parent_row_no].co_invoices[row_no].invoices[j].convert_net_amount : '-') + '</td>' : '') + 

                                  '<td class="text-end declaration-th-w150"'+ disregard_invoice_tooltip +'>' + sales_invoice_adjustment_amount + '</td>' +
                                  '<td class="text-end declaration-th-w150 '+ negative_vat_amount_class +'" '+ ((disregard_invoice_tooltip) ? disregard_invoice_tooltip : vat_amount_tooltip) +'>' + d[parent_row_no].co_invoices[row_no].invoices[j].vat_amount + '</td>' +

                                  ((d[parent_row_no].co_invoices[row_no].country == 'CH') ? '<td class="text-end declaration-th-w150">' + ((d[parent_row_no].co_invoices[row_no].invoices[j].convert_vat_amount) ? d[parent_row_no].co_invoices[row_no].invoices[j].convert_vat_amount : '-') + '</td>' : '') + 

                                  '<td class="text-end declaration-th-w150"'+ disregard_invoice_tooltip +'>' + d[parent_row_no].co_invoices[row_no].invoices[j].vat_check_25 + '</td>' +
                                  '<td class="text-end declaration-th-w150"'+ disregard_invoice_tooltip +'>' + d[parent_row_no].co_invoices[row_no].invoices[j].currency + '</td>' +                                  
                                  '<td class="text-end declaration-th-w150"'+ disregard_invoice_tooltip +'></td>' + 
                                  '<td class="text-end declaration-th-w150"'+ disregard_invoice_tooltip +'></td>' + 
                                  '<td class="text-center">' +                                                                                                      
                                    '<div class="d-inline-block">' +
                                      '<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                                      '<ul class="dropdown-menu dropdown-menu-end m-0">' +
                                        ((d[parent_row_no].co_invoices[row_no].id != '-') ?    

                                        ((d[parent_row_no].co_invoices[row_no].invoices[j].disregard_invoice) ? 
                                          '<li>' +
                                            '<a href="javascript:;" class="dropdown-item btn-enable-declaration-invoice" title="Enable invoice" data-invoice_name="sales" data-invoice_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-tab_name="'+ which_tab +'" data-enable="1" data-invoice_date="'+ d[parent_row_no].co_invoices[row_no].invoices[j].o_invoice_date +'">' +
                                              '<span><i class="bx bx-list-check"></i> Enable invoice</span>' +
                                            '</a>' +
                                          '</li>'
                                          : 
                                          '<li>' +
                                            '<a href="javascript:;" class="dropdown-item btn-disregard-declaration-invoice" data-insert_type="'+ btn_icn +'" data-comment_reason="'+ d[parent_row_no].co_invoices[row_no].invoices[j].comment_reason +'" data-comment="'+ d[parent_row_no].co_invoices[row_no].invoices[j].comment +'" data-comment_visiblity="'+ d[parent_row_no].co_invoices[row_no].invoices[j].comment_visiblity +'" title="'+btn_title+' Comment" data-invoice_name="sales" data-invoice_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-tab_name="'+ which_tab +'" data-disregard="0" data-invoice_date="'+ d[parent_row_no].co_invoices[row_no].invoices[j].o_invoice_date +'">' +
                                              '<span><i class="bx bx-comment-'+ btn_icn +'"></i> '+ btn_title +' Comment</span>' +
                                            '</a>' +                                          
                                          '</li>' +  
                                          ((btn_delete_comment) ? '<div class="dropdown-divider"></div>' : '') + 
                                          btn_delete_comment +

                                          ((btn_refresh_salesinvoice) ? '<div class="dropdown-divider"></div>' : '') + 
                                          btn_refresh_salesinvoice +

                                          '<div class="dropdown-divider"></div>' +                                       
                                          '<li>' +
                                            '<a href="javascript:;" class="dropdown-item btn-disregard-declaration-invoice" title="Disregard invoice" data-invoice_name="sales" data-invoice_id="'+ d[parent_row_no].co_invoices[row_no].invoices[j].id +'" data-invoice_no="'+ d[parent_row_no].co_invoices[row_no].invoices[j].invoice_no +'" data-tab_name="'+ which_tab +'" data-disregard="1" data-invoice_date="'+ d[parent_row_no].co_invoices[row_no].invoices[j].o_invoice_date +'">' +
                                              '<span><i class="bx bx-list-minus"></i> Disregard invoice</span>' +
                                            '</a>' + 
                                          '</li>' +  
                                          ((btn_sales_pdf) ? '<div class="dropdown-divider"></div>' : '') +
                                          btn_sales_pdf +
                                          ((btn_sales_invoice_move) ? '<div class="dropdown-divider"></div>' : '') +
                                          btn_sales_invoice_move) 

                                        : ((btn_sales_pdf) ? '<div class="dropdown-divider"></div>' : '') +
                                          btn_sales_pdf ) +   
                                      '</ul>' +
                                    '</div>' +                     
                                  '</td>' +
                                '</tr>'
              ;        
            }//for           
          }
        }
            
        invoice_html += '</tbody></table>';
        
        return invoice_html;
      }//invoices
  }
  
  $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {  
    var id = $(e.target).attr("id") // activated tab
   console.log(id);
    if(id == 'btn-declaration-first')
    {            
      $(".dt-declaration-export .first-declaration-export").removeClass('d-none');  
      $(".dt-declaration-export .second-declaration-export").addClass('d-none');     
      $(".dt-declaration-export .third-declaration-export").addClass('d-none');

      $(".dt-search-filter .first-search-filter").removeClass('d-none');    
      $(".dt-search-filter .second-search-filter").addClass('d-none');    
      $(".dt-search-filter .third-search-filter").addClass('d-none');          
    }
    else if(id == 'btn-declaration-second')
    {            
      $(".dt-declaration-export .first-declaration-export").addClass('d-none');  
      $(".dt-declaration-export .second-declaration-export").removeClass('d-none');     
      $(".dt-declaration-export .third-declaration-export").addClass('d-none');

      $(".dt-search-filter .first-search-filter").addClass('d-none');    
      $(".dt-search-filter .second-search-filter").removeClass('d-none');    
      $(".dt-search-filter .third-search-filter").addClass('d-none');       
    } 
    else if(id == 'btn-declaration-third')
    {      
      $(".dt-declaration-export .first-declaration-export").addClass('d-none');  
      $(".dt-declaration-export .second-declaration-export").addClass('d-none');     
      $(".dt-declaration-export .third-declaration-export").removeClass('d-none');

      $(".dt-search-filter .first-search-filter").addClass('d-none');    
      $(".dt-search-filter .second-search-filter").addClass('d-none');    
      $(".dt-search-filter .third-search-filter").removeClass('d-none');      
    }        
  });

  /*NEW - EXPORT - TO - EXCEL*/
  function exportToExcelMissingFilesNew(dt, which_tab) 
  {    
    var clientname = $("#client_name").val();
    var monthyear = moment($("#declaration_"+ which_tab +"_monthyear").val(), "MM-YYYY").format("MMM-YYYY");
    
    let workbook = XLSX.utils.book_new();
    let sheetData = [];

    // Define headers
    const headers = ["Invoice No", "Currency", "VAT Amount", "Total Net Amount", "Total Amount", 
    "Sales Invoices QTY", "On No of Comm. Inv."]; // Adjust based on your columns
    sheetData.push(headers); // Add headers to the first row

    var declaration_datas = [];
    if(which_tab == 'first')
      declaration_datas = declaration_first_datas;
    else if(which_tab == 'second')
      declaration_datas = declaration_second_datas;
    else if(which_tab == 'third')
      declaration_datas = declaration_third_datas;

    if(declaration_datas.length > 0)
    {
      var currency_style = 'NOK';

      var co_invoices = declaration_datas[0]['co_invoices'];
                 
      let total_parsed_vat_amount = 0;  
      let total_parsed_net_amount = 0;    
      let total_parsed_gross_amount = 0;
      let total_parsed_sales_invoice_qty = 0;    
      let total_parsed_no_of_com_invoice = 0;    
      $.each(co_invoices, function (co_invoices_idx, co_invoice) { 

        var category_type = co_invoice['category_type'];  
        if(category_type != 'EB' && category_type != 'RE')
        { 
        //if((co_invoice['co_invoice_no'] != '-' && co_invoice['lope_no'] != '-'))
        //{    
          $.each(co_invoice['invoices'], function (idx, rowData) {                

            var sales_invoice_no = rowData['invoice_no'];

            var currency_code = rowData['currency'];

            if(sales_invoice_no == '' || rowData['is_net_amount_null'] || currency_code != 'NOK')
            {
              let vat_amount = rowData['vat_amount'];
              let parsed_vat_amount =  parseAmountValue(vat_amount, currency_style);
              //total_parsed_vat_amount += parsed_vat_amount;

              let net_amount = rowData['net_amount'];
              let parsed_net_amount =  parseAmountValue(net_amount, currency_style);

              let shipping_amount = rowData['shipping'];
              let parsed_shipping_amount =  parseAmountValue(shipping_amount, currency_style);

              let variance_amount = rowData['variance'];
              let parsed_variance_amount =  parseAmountValue(variance_amount, currency_style);

              let parsed_gross_net_amount = (parsed_net_amount + parsed_shipping_amount + parsed_variance_amount);

              //total_parsed_net_amount += parsed_gross_net_amount;

              let parsed_gross_amount = parsed_vat_amount + parsed_gross_net_amount;
              //total_parsed_gross_amount += parsed_gross_amount;

              //total_parsed_sales_invoice_qty += 1;
              //total_parsed_no_of_com_invoice += 1;                          
                        
              /*-- Rules --*/
              var negative_net_amount_class = 0;
              if (parsed_gross_net_amount.toFixed(2).startsWith("-")) 
                negative_net_amount_class = 1;

              var negative_vat_amount_class = 0;
              if (parsed_vat_amount.toFixed(2).startsWith("-")) 
                negative_vat_amount_class = 1;
              /*-- Rules --*/

              let mainRow = [sales_invoice_no, currency_code, 
              (negative_vat_amount_class) ? { v: parsed_vat_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_vat_amount,
              (negative_net_amount_class) ? { v: parsed_gross_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_gross_net_amount,
              (negative_vat_amount_class || negative_net_amount_class) ? { v: parsed_gross_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_gross_amount,                       
              1, 1]; // Adjust based on your columns
                  
              // Push main row data
              sheetData.push(mainRow);
            }//Missing RULES
          }); 
        } //not EB and RE
        //} // no hypens
      });

      /*TOTAL*/        
      $.each(sheetData, function (index, sheetItem) { 
        if(index > 0)
        {            
          total_parsed_vat_amount += (typeof sheetItem[2] === 'object' && sheetItem[2] !== null) ? sheetItem[2]['v'] : sheetItem[2];         
          total_parsed_net_amount += (typeof sheetItem[3] === 'object' && sheetItem[3] !== null) ? sheetItem[3]['v'] : sheetItem[3];
          total_parsed_gross_amount += (typeof sheetItem[4] === 'object' && sheetItem[4] !== null) ? sheetItem[4]['v'] : sheetItem[4];         
          total_parsed_sales_invoice_qty += (typeof sheetItem[5] === 'object' && sheetItem[5] !== null) ? sheetItem[5]['v'] : sheetItem[5];      
          total_parsed_no_of_com_invoice += (typeof sheetItem[6] === 'object' && sheetItem[6] !== null) ? sheetItem[6]['v'] : sheetItem[6];      
        }       
      });
      /*TOTAL*/

      // Define footer
      const footers = ["Total", "", total_parsed_vat_amount, total_parsed_net_amount, total_parsed_gross_amount, 
      total_parsed_sales_invoice_qty, total_parsed_no_of_com_invoice]; // Adjust based on your columns
      sheetData.push(footers); // Add footers to the last row
    }
    
    // Create the worksheet
    let worksheet = XLSX.utils.aoa_to_sheet(sheetData);     

    // Set header style (background color)    
    const headerFill = {
        fill: {
            fgColor: { rgb: "5a8dee" } // Background color (light blue)
        },
        font: {
            color: { rgb: "FFFFFF" },  // Text color (white)
            bold: true,                // Optional: Make text bold
            size: 12                   // Optional: Set text size
        }
    };

    // Apply the fill to the header cells
    for (let i = 0; i < headers.length; i++) {
        const cellAddress = XLSX.utils.encode_cell({ c: i, r: 0 }); // Header row
    
        if (!worksheet[cellAddress])
            worksheet[cellAddress] = {}; // Ensure the cell exists
       
        worksheet[cellAddress].s = headerFill; // Apply style    
    }

    // Set header style (background color)    
    const footerFill = {                
        font: {           
            bold: true,                // Optional: Make text bold           
        }
    };

    // Apply the footer style to the last row (footer row)
    const lastRowIndex = sheetData.length - 1; // Last row is the footer row    
    for (let col = 0; col < sheetData[lastRowIndex].length; col++) {    
      const cellAddress = XLSX.utils.encode_cell({ c: col, r: lastRowIndex }); // Last row (footer row)

      if (!worksheet[cellAddress])
        worksheet[cellAddress] = {}; // Initialize the cell if it doesn't exist

      worksheet[cellAddress].s = footerFill; // Apply the footer style to the cell
    }

    XLSX.utils.book_append_sheet(workbook, worksheet, monthyear);

    // Export the workbook
    XLSX.writeFile(workbook, clientname + '-MissingFiles-'+ monthyear +'.xlsx');
  }

  function exportToExcelOnlyDeclarationsNew(dt, which_tab) 
  {    
    var clientname = $("#client_name").val();
    var monthyear = moment($("#declaration_"+ which_tab +"_monthyear").val(), "MM-YYYY").format("MMM-YYYY");

    var currency_locale = 'da-DK';
    var currency_style = 'NOK';

    let workbook = XLSX.utils.book_new();
    let sheetData = [];

    // Define headers
    const headers = ["Exp No", "Declaration No", "Duties", "Net Amount", "Adjustment", "Statistical value"]; // Adjust based on your columns
    sheetData.push(headers); // Add headers to the first row

    var declaration_datas = [];
    if(which_tab == 'first')
      declaration_datas = declaration_first_datas;
    else if(which_tab == 'second')
      declaration_datas = declaration_second_datas;
    else if(which_tab == 'third')
      declaration_datas = declaration_third_datas;

    if(declaration_datas.length > 0)
    {
      var import_vat_xml_datas = declaration_datas[0]['import_vat_xml'];
      
      let total_parsed_duties = 0;
      let total_parsed_net_amount = 0;
      let total_parsed_adjustment = 0;
      let total_parsed_statistical_value = 0;

      if(import_vat_xml_datas.length == 0)
      {
        //$.each(import_vat_xml_datas, function (idx, rowData) {
          
        var expo_no = '-';
        var lope_no = '-';

        var currency_code = '-';

        let duties = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0);
        let parsed_duties = parseAmountValue(duties, currency_code);
        //total_parsed_duties += parsed_duties;
        
        let net_amount = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0);
        let parsed_net_amount = parseAmountValue(net_amount, currency_code);
        //total_parsed_net_amount += parsed_net_amount;                    

        let adjustment = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0);
        let parsed_adjustment = parseAmountValue(adjustment, currency_code);
        //total_parsed_adjustment += parsed_adjustment;

        let statistical_value = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0);
        let parsed_statistical_value = parseAmountValue(statistical_value, currency_code);
        //total_parsed_statistical_value += parsed_statistical_value;

        let mainRow = [expo_no, lope_no, parsed_duties
        , parsed_net_amount, parsed_adjustment, parsed_statistical_value]; // Adjust based on your columns
            
        // Push main row data
        sheetData.push(mainRow);      
        //});
      }
      else
      {
        $.each(import_vat_xml_datas, function (idx, rowData) {
          var category_type = rowData['Ekspedisjon']['Kategori']['KategoriType'];
          var category_desc = rowData['Ekspedisjon']['Kategori']['KategoriBeskrivelse'];

          var allow = true;
          var expo_type = rowData['Ekspedisjon']['EkspType']['EkspTypeNavn'];
          if(category_type == 'MA' && (expo_type.toLowerCase().indexOf('utførsel') !== -1))
            allow = false;
            
          //if(category_type != 'MA' && category_type != 'SO')
          if(category_type != 'SO' && allow)
          {          
            var expo_no = rowData['Ekspedisjon']['EkspedisjonsId']['EkspNr'];
            var lope_no = rowData['Ekspedisjon']['EkspedisjonsId']['LopeNr'];

            var currency_code = rowData['FakturaValutaBelop']['Fakturavaluta'];

            let duties = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(rowData['Avgift']);
            let parsed_duties = parseAmountValue(duties, currency_style);//DON'T CHANGE currency_style
            //total_parsed_duties += parsed_duties;

            let parsed_net_amount = 0;
            let parsed_adjustment = 0;
            let parsed_statistical_value = 0;
            if(rowData['FakturaValutaBelop']['OmrKurs'] == 100)
            {
            //   let net_amount = new Intl.NumberFormat(currency_locale, {
            // style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(rowData['FakturaValutaBelop']['Fakturasum']);
              //parsed_net_amount = parseAmountValue(net_amount, currency_code);
              //total_parsed_net_amount += parsed_net_amount;

              let net_amount = rowData['FakturaValutaBelop']['Fakturasum'];

              let adjustment = new Intl.NumberFormat(currency_locale, {
            style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(rowData['Justering']);
              parsed_adjustment = parseAmountValue(adjustment, currency_code);
              //total_parsed_adjustment += parsed_adjustment;

              let statistical_value = new Intl.NumberFormat(currency_locale, {
            style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(rowData['StatistiskVerdi']);
              parsed_statistical_value = parseAmountValue(statistical_value, currency_code);
              //total_parsed_statistical_value += parsed_statistical_value;

              if(category_type == 'RE')
              {
                if (rowData['StatistiskVerdi'].startsWith("-")) 
                {
                  //net_amount = '-' + net_amount;
                  net_amount = -1 * net_amount;
                } //negative statisticalValue
                else
                {
                  if (parsed_statistical_value == 0)  
                    net_amount = 0;
                } //zero statisticalValue
              } //Refund  
              else if(category_type == 'EB')
              {
                net_amount = statistical_value;
              } //Recalculation

              net_amount = new Intl.NumberFormat(currency_locale, {
               style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(net_amount);
              parsed_net_amount = parseAmountValue(net_amount, currency_code);
              //total_parsed_net_amount += parsed_net_amount;
            }
            else
            {
              let only_net_amount = rowData['FakturaValutaBelop']['Fakturasum'];            
              //let omr_kurs_amount = rowData['FakturaValutaBelop']['OmrKurs']; 

              let currency_value = rowData['FakturaValutaBelop']['Fakturavaluta'];  

              let omr_kurs_amount = 1;
              if(currency_value == 'DKK')   
              {  
                omr_kurs_amount = rowData['FakturaValutaBelop']['OmrKurs'].replace(/[,.]/g, ""); 
                omr_kurs_amount = omr_kurs_amount.substr(0, 1) + "." + omr_kurs_amount.substr(1);
              }
              else if(currency_value == 'USD' || currency_value == 'EUR')   
              {
                omr_kurs_amount = rowData['FakturaValutaBelop']['OmrKurs'].replace(/[,.]/g, ""); 
                omr_kurs_amount = omr_kurs_amount.substr(0, 2) + "." + omr_kurs_amount.substr(2);
              }

              let net_amount = only_net_amount * omr_kurs_amount;           
              //parsed_net_amount =  parseAmountValue(net_amount, currency_code);
              //total_parsed_net_amount += parsed_net_amount; 

              let adjustment = rowData['Justering'];
              parsed_adjustment = parseAmountValue(adjustment, currency_code);
              //total_parsed_adjustment += parsed_adjustment;

              let statistical_value = rowData['StatistiskVerdi'];
              parsed_statistical_value = parseAmountValue(statistical_value, currency_code);
              //total_parsed_statistical_value += parsed_statistical_value;  

              if(category_type == 'RE')
              {
                if (rowData['StatistiskVerdi'].startsWith("-")) 
                {
                  //net_amount = '-' + net_amount;
                  net_amount = -1 * net_amount;
                } //negative statisticalValue
                else
                {
                  if (parsed_statistical_value == 0)  
                    net_amount = 0;
                } //zero statisticalValue
              } //Refund  
              else if(category_type == 'EB')
              {
                net_amount = statistical_value;
              } //Recalculation

              //parsed_net_amount =  parseAmountValue(net_amount, currency_code);
              parsed_net_amount = parseFloat(net_amount.toFixed(2));
              //total_parsed_net_amount += parsed_net_amount;       
            }                              

            let mainRow = [expo_no, lope_no, parsed_duties
            , parsed_net_amount, parsed_adjustment, parsed_statistical_value]; // Adjust based on your columns
                
            // Push main row data
            sheetData.push(mainRow); 
          } //not MA and SO
        });
      }//has XML
      
      /*TOTAL*/        
      $.each(sheetData, function (index, sheetItem) { 
        if(index > 0)
        {            
          total_parsed_duties += (typeof sheetItem[2] === 'object' && sheetItem[2] !== null) ? sheetItem[2]['v'] : sheetItem[2];         
          total_parsed_net_amount += (typeof sheetItem[3] === 'object' && sheetItem[3] !== null) ? sheetItem[3]['v'] : sheetItem[3];
          total_parsed_adjustment += (typeof sheetItem[4] === 'object' && sheetItem[4] !== null) ? sheetItem[4]['v'] : sheetItem[4];         
          total_parsed_statistical_value += (typeof sheetItem[5] === 'object' && sheetItem[5] !== null) ? sheetItem[5]['v'] : sheetItem[5];      
        }       
      });
      /*TOTAL*/

      // Define footer
      const footers = ["Total", "", total_parsed_duties, total_parsed_net_amount, total_parsed_adjustment, total_parsed_statistical_value]; // Adjust based on your columns
      sheetData.push(footers); // Add footers to the last row
    }
    
    // Create the worksheet
    let worksheet = XLSX.utils.aoa_to_sheet(sheetData);     

    // Set header style (background color)    
    const headerFill = {
        fill: {
            fgColor: { rgb: "5a8dee" } // Background color (light blue)
        },
        font: {
            color: { rgb: "FFFFFF" },  // Text color (white)
            bold: true,                // Optional: Make text bold
            size: 12                   // Optional: Set text size
        }
    };

    // Apply the fill to the header cells
    for (let i = 0; i < headers.length; i++) {
        const cellAddress = XLSX.utils.encode_cell({ c: i, r: 0 }); // Header row
    
        if (!worksheet[cellAddress])
            worksheet[cellAddress] = {}; // Ensure the cell exists
       
        worksheet[cellAddress].s = headerFill; // Apply style    
    }

    // Set header style (background color)    
    const footerFill = {                
        font: {           
            bold: true,                // Optional: Make text bold           
        }
    };

    // Apply the footer style to the last row (footer row)
    const lastRowIndex = sheetData.length - 1; // Last row is the footer row    
    for (let col = 0; col < sheetData[lastRowIndex].length; col++) {    
      const cellAddress = XLSX.utils.encode_cell({ c: col, r: lastRowIndex }); // Last row (footer row)

      if (!worksheet[cellAddress])
        worksheet[cellAddress] = {}; // Initialize the cell if it doesn't exist

      worksheet[cellAddress].s = footerFill; // Apply the footer style to the cell
    }

    XLSX.utils.book_append_sheet(workbook, worksheet, monthyear);

    // Export the workbook
    XLSX.writeFile(workbook, clientname + '-Declaration-'+ monthyear +'.xlsx');
  }

  function exportToExcelOnlyComInvoicesNew(dt, which_tab) 
  {    
    var clientname = $("#client_name").val();
    var monthyear = moment($("#declaration_"+ which_tab +"_monthyear").val(), "MM-YYYY").format("MMM-YYYY");

    let workbook = XLSX.utils.book_new();
    let sheetData = [];

    // Define headers
    const headers = ["Lope No", "Commercial Invoice No", "Net Amount", "Commercial Invoices QTY", 
    "On No Of Declarations"]; // Adjust based on your columns
    sheetData.push(headers); // Add headers to the first row

    var declaration_datas = [];
    if(which_tab == 'first')
      declaration_datas = declaration_first_datas;
    else if(which_tab == 'second')
      declaration_datas = declaration_second_datas;
    else if(which_tab == 'third')
      declaration_datas = declaration_third_datas;

    if(declaration_datas.length > 0)
    {
      var currency_style = 'NOK';      

      var co_invoices = declaration_datas[0]['co_invoices'];
           
      let total_parsed_net_amount = 0;
      //let total_parsed_vat_amount = 0;      
      let total_parsed_com_invoice_qty = 0;    
      let total_parsed_no_of_declaration = 0;    
      $.each(co_invoices, function (idx, rowData) {              
        var lope_no = rowData['lope_no'];
        var co_invoice_no = rowData['co_invoice_no'];

        var currency_code = rowData['currency'];

        //let net_amount = rowData['net_amount_co_invoice'];
        let net_amount = rowData['com_net_amount'];
        let parsed_net_amount =  parseAmountValue(net_amount, currency_style);
        //total_parsed_net_amount += parsed_net_amount;

        //total_parsed_com_invoice_qty += 1;
        //total_parsed_no_of_declaration += 1;
        // let vat_amount = rowData['import_vat'];
        // let parsed_vat_amount =  parseAmountValue(vat_amount, currency_code);
        // total_parsed_vat_amount += parsed_vat_amount;
        
        let mainRow = [lope_no, co_invoice_no, parsed_net_amount, 1, 1]; // Adjust based on your columns
            
        // Push main row data
        sheetData.push(mainRow);         
      });

      /*TOTAL*/        
      $.each(sheetData, function (index, sheetItem) { 
        if(index > 0)
        {            
          total_parsed_net_amount += (typeof sheetItem[2] === 'object' && sheetItem[2] !== null) ? sheetItem[2]['v'] : sheetItem[2];         
          total_parsed_com_invoice_qty += (typeof sheetItem[3] === 'object' && sheetItem[3] !== null) ? sheetItem[3]['v'] : sheetItem[3];
          total_parsed_no_of_declaration += (typeof sheetItem[4] === 'object' && sheetItem[4] !== null) ? sheetItem[4]['v'] : sheetItem[4];         
        }       
      });
      /*TOTAL*/

      // Define footer
      const footers = ["Total", "", total_parsed_net_amount, total_parsed_com_invoice_qty, 
      total_parsed_no_of_declaration]; // Adjust based on your columns
      sheetData.push(footers); // Add footers to the last row
    }
    
    // Create the worksheet
    let worksheet = XLSX.utils.aoa_to_sheet(sheetData);     

    // Set header style (background color)    
    const headerFill = {
        fill: {
            fgColor: { rgb: "5a8dee" } // Background color (light blue)
        },
        font: {
            color: { rgb: "FFFFFF" },  // Text color (white)
            bold: true,                // Optional: Make text bold
            size: 12                   // Optional: Set text size
        }
    };

    // Apply the fill to the header cells
    for (let i = 0; i < headers.length; i++) {
        const cellAddress = XLSX.utils.encode_cell({ c: i, r: 0 }); // Header row
    
        if (!worksheet[cellAddress])
            worksheet[cellAddress] = {}; // Ensure the cell exists
       
        worksheet[cellAddress].s = headerFill; // Apply style    
    }

    // Set header style (background color)    
    const footerFill = {                
        font: {           
            bold: true,                // Optional: Make text bold           
        }
    };

    // Apply the footer style to the last row (footer row)
    const lastRowIndex = sheetData.length - 1; // Last row is the footer row    
    for (let col = 0; col < sheetData[lastRowIndex].length; col++) {    
      const cellAddress = XLSX.utils.encode_cell({ c: col, r: lastRowIndex }); // Last row (footer row)

      if (!worksheet[cellAddress])
        worksheet[cellAddress] = {}; // Initialize the cell if it doesn't exist

      worksheet[cellAddress].s = footerFill; // Apply the footer style to the cell
    }

    XLSX.utils.book_append_sheet(workbook, worksheet, monthyear);

    // Export the workbook
    XLSX.writeFile(workbook, clientname + '-ComInvoices-'+ monthyear +'.xlsx');
  }

  function exportToExcelOnlySalesInvoicesNew(dt, which_tab) 
  {    
    var clientname = $("#client_name").val();
    var monthyear = moment($("#declaration_"+ which_tab +"_monthyear").val(), "MM-YYYY").format("MMM-YYYY");

    let workbook = XLSX.utils.book_new();
    let sheetData = [];

    // Define headers
    const headers = ["Invoice No", "Currency", "VAT Amount", "Total Net Amount", "Total Amount", 
    "Sales Invoices QTY", "On No of Comm. Inv."]; // Adjust based on your columns
    sheetData.push(headers); // Add headers to the first row

    var declaration_datas = [];
    if(which_tab == 'first')
      declaration_datas = declaration_first_datas;
    else if(which_tab == 'second')
      declaration_datas = declaration_second_datas;
    else if(which_tab == 'third')
      declaration_datas = declaration_third_datas;

    if(declaration_datas.length > 0)
    {
      var currency_style = 'NOK';    
      
      var co_invoices = declaration_datas[0]['co_invoices'];
                 
      let total_parsed_vat_amount = 0;  
      let total_parsed_net_amount = 0;    
      let total_parsed_gross_amount = 0;
      let total_parsed_sales_invoice_qty = 0;    
      let total_parsed_no_of_com_invoice = 0;    
      $.each(co_invoices, function (co_invoices_idx, co_invoice) {  
        if((co_invoice['co_invoice_no'] != '-' && co_invoice['lope_no'] != '-'))
        {  
          var category_type = co_invoice['category_type'];  
          if(category_type != 'EB' && category_type != 'RE')
          {            
            $.each(co_invoice['invoices'], function (idx, rowData) {                

              var sales_invoice_no = rowData['invoice_no'];

              var tr_selected = ($(".datatables-declaration-invoices tr.selected").length == 0) ? true : false;
              $.each($(".datatables-declaration-invoices tr.selected"), function (index, tr) {                
                if($(tr).find('td .form-check-input').attr('data-invoice_no') == sales_invoice_no)
                  tr_selected = true;
              });   

              if(tr_selected)
              {
                var show_disregarded_invoices = ($('#chk-declaration-filter-show-disregarded-invoices').prop('checked')) ? true : false;
                var show_invoice = (show_disregarded_invoices) ? true : ((rowData.disregard_invoice) ? false : true);

                if(show_invoice) 
                {
                  var currency_code = rowData['currency'];

                  let vat_amount = rowData['vat_amount'];
                  let parsed_vat_amount =  parseAmountValue(vat_amount, currency_style);
                  //total_parsed_vat_amount += parsed_vat_amount;

                  let net_amount = rowData['net_amount'];
                  let parsed_net_amount =  parseAmountValue(net_amount, currency_style);

                  let shipping_amount = rowData['shipping'];
                  let parsed_shipping_amount =  parseAmountValue(shipping_amount, currency_style);

                  let variance_amount = rowData['variance'];
                  let parsed_variance_amount =  parseAmountValue(variance_amount, currency_style);

                  let parsed_gross_net_amount = (parsed_net_amount + parsed_shipping_amount + parsed_variance_amount);

                  //total_parsed_net_amount += parsed_gross_net_amount;

                  let parsed_gross_amount = parsed_vat_amount + parsed_gross_net_amount;
                  //total_parsed_gross_amount += parsed_gross_amount;

                  //total_parsed_sales_invoice_qty += 1;
                  //total_parsed_no_of_com_invoice += 1;                          
                
                  // let mainRow = [sales_invoice_no, currency_code, parsed_vat_amount, parsed_gross_net_amount, 
                  // parsed_gross_amount, 1, 1]; // Adjust based on your columns

                  /*-- Rules --*/
                  var negative_net_amount_class = 0;
                  if (parsed_gross_net_amount.toFixed(2).startsWith("-")) 
                    negative_net_amount_class = 1;

                  var negative_vat_amount_class = 0;
                  if (parsed_vat_amount.toFixed(2).startsWith("-")) 
                    negative_vat_amount_class = 1;
                  /*-- Rules --*/

                  let mainRow = [sales_invoice_no, currency_code, 
                  (negative_vat_amount_class) ? { v: parsed_vat_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_vat_amount,
                  (negative_net_amount_class) ? { v: parsed_gross_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_gross_net_amount,
                  (negative_vat_amount_class || negative_net_amount_class) ? { v: parsed_gross_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_gross_amount,                       
                  1, 1]; // Adjust based on your columns
                      
                  // Push main row data
                  sheetData.push(mainRow);
                }
              }//only selected rows
            }); 
          } //not EB and RE
        } // no hypens
      });

      /*TOTAL*/        
      $.each(sheetData, function (index, sheetItem) { 
        if(index > 0)
        {            
          total_parsed_vat_amount += (typeof sheetItem[2] === 'object' && sheetItem[2] !== null) ? sheetItem[2]['v'] : sheetItem[2];         
          total_parsed_net_amount += (typeof sheetItem[3] === 'object' && sheetItem[3] !== null) ? sheetItem[3]['v'] : sheetItem[3];
          total_parsed_gross_amount += (typeof sheetItem[4] === 'object' && sheetItem[4] !== null) ? sheetItem[4]['v'] : sheetItem[4];
          total_parsed_sales_invoice_qty += (typeof sheetItem[5] === 'object' && sheetItem[5] !== null) ? sheetItem[5]['v'] : sheetItem[5];
          total_parsed_no_of_com_invoice += (typeof sheetItem[6] === 'object' && sheetItem[6] !== null) ? sheetItem[6]['v'] : sheetItem[6];          
        }       
      });
      /*TOTAL*/

      // Define footer
      const footers = ["Total", "", total_parsed_vat_amount, total_parsed_net_amount, total_parsed_gross_amount, 
      total_parsed_sales_invoice_qty, total_parsed_no_of_com_invoice]; // Adjust based on your columns
      sheetData.push(footers); // Add footers to the last row
    }
    
    // Create the worksheet
    let worksheet = XLSX.utils.aoa_to_sheet(sheetData);     

    // Set header style (background color)    
    const headerFill = {
        fill: {
            fgColor: { rgb: "5a8dee" } // Background color (light blue)
        },
        font: {
            color: { rgb: "FFFFFF" },  // Text color (white)
            bold: true,                // Optional: Make text bold
            size: 12                   // Optional: Set text size
        }
    };

    // Apply the fill to the header cells
    for (let i = 0; i < headers.length; i++) {
        const cellAddress = XLSX.utils.encode_cell({ c: i, r: 0 }); // Header row
    
        if (!worksheet[cellAddress])
            worksheet[cellAddress] = {}; // Ensure the cell exists
       
        worksheet[cellAddress].s = headerFill; // Apply style    
    }

    // Set header style (background color)    
    const footerFill = {                
        font: {           
            bold: true,                // Optional: Make text bold           
        }
    };

    // Apply the footer style to the last row (footer row)
    const lastRowIndex = sheetData.length - 1; // Last row is the footer row    
    for (let col = 0; col < sheetData[lastRowIndex].length; col++) {    
      const cellAddress = XLSX.utils.encode_cell({ c: col, r: lastRowIndex }); // Last row (footer row)

      if (!worksheet[cellAddress])
        worksheet[cellAddress] = {}; // Initialize the cell if it doesn't exist

      worksheet[cellAddress].s = footerFill; // Apply the footer style to the cell
    }

    XLSX.utils.book_append_sheet(workbook, worksheet, monthyear);

    // Export the workbook
    XLSX.writeFile(workbook, clientname + '-SalesInvoices-'+ monthyear +'.xlsx');
  }

  function exportToExcelPeriodOverviewNew(dt, which_tab) 
  {       
    var clientname = $("#client_name").val();
    var monthyear = moment($("#declaration_"+ which_tab +"_monthyear").val(), "MM-YYYY").format("MMM-YYYY");

    // var currency_locale = 'da-DK';
    // var currency_style = 'NOK';

    let workbook = XLSX.utils.book_new();
    let sheetData = [];

    // Define headers
    let headers = ["Date", "Declaration No", "Duties", "Net Amount", "Adjustment", "Statistical value",
    "Import VAT (NO)", "VAT on Duties (NO)", "VAT on Adjustment", ".", 
    "Commercail Invoice Ref.", "Net Amount Commercial Invoices", "Total Net Amount Sales Invoices",
    "VAT Amount Sales Invoice", "Sales VAT vs Import VAT", "Document QTY", "Stat. Value - Net Amount", 
    "Net Amount - Total Net Amount", "VAT Check", "Expo No. (Temp)"]; // Adjust based on your columns
    sheetData.push(headers); // Add headers to the first row

    var declaration_datas = [];
    if(which_tab == 'first')
      declaration_datas = declaration_first_datas;
    else if(which_tab == 'second')
      declaration_datas = declaration_second_datas;
    else if(which_tab == 'third')
      declaration_datas = declaration_third_datas;

    if(declaration_datas.length > 0)
    {
      let total_parsed_duties = 0;
      let total_parsed_duties_vat = 0;
      let total_parsed_net_amount = 0;
      let total_parsed_adjustment = 0;
      let total_parsed_adjustment_vat = 0;
      let total_parsed_statistical_value = 0;
      let total_parsed_import_vat = 0;
      
      let total_parsed_cominvoice_net_amount = 0;
      let total_parsed_salesinvoice_net_amount = 0;
      let total_parsed_salesinvoice_vat_amount = 0;

      let total_parsed_sales_vat_vs_import_vat = 0;
      let total_document_qty = 0;
      let total_parsed_statistical_value_minus_net_amount_co_invoice = 0;
      let total_parsed_cominvoice_minus_salesinvoice_net_amount = 0;
      let total_parsed_vat_check = 0;

      //Converted Amounts
      let converted_total_parsed_cominvoice_net_amount = 0;
      let converted_total_parsed_salesinvoice_net_amount = 0;
      let converted_total_parsed_salesinvoice_vat_amount = 0;

      let converted_total_parsed_sales_vat_vs_import_vat = 0;    
      let converted_total_parsed_statistical_value_minus_net_amount_co_invoice = 0;
      let converted_total_parsed_cominvoice_minus_salesinvoice_net_amount = 0;
      let converted_total_parsed_vat_check = 0;
      //Converted Amounts

      var co_invoices = declaration_datas[0]['co_invoices']; 
      //co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no)); 
      co_invoices.sort((a, b) => {
        if (a.id === '-') return 1;
        if (b.id === '-') return -1;
        return a.co_invoice_no.localeCompare(b.co_invoice_no);
      });

      if(declaration_datas[0]['country'] == 'NO')
      {
        var currency_locale = 'da-DK';
        var currency_style = 'NOK';

        let vat_percent = 0.25;

        var import_vat_xml_datas = declaration_datas[0]['import_vat_xml'];
                      
        if(import_vat_xml_datas.length == 0)
        {
          var declaration_date = '-';
          var com_invoice_no = '-';

          var expo_no = '-';
          var lope_no = '-';

          var currency_code = '-';

          let duties = new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0);
          let parsed_duties =  parseAmountValue(duties, currency_code);
          //total_parsed_duties += parsed_duties;

          let parsed_duties_vat = parsed_duties * vat_percent;       
          //total_parsed_duties_vat += parsed_duties_vat;

          let net_amount = new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0);
          let parsed_net_amount =  parseAmountValue(net_amount, currency_code);
          //total_parsed_net_amount += parsed_net_amount;

          let adjustment = new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0);
          let parsed_adjustment =  parseAmountValue(adjustment, currency_code);
          //total_parsed_adjustment += parsed_adjustment;

          let parsed_adjustment_vat = parsed_adjustment * vat_percent;       
          //total_parsed_adjustment_vat += parsed_adjustment_vat;

          let statistical_value = new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0);
          let parsed_statistical_value =  parseAmountValue(statistical_value, currency_code);
          //total_parsed_statistical_value += parsed_statistical_value;

          let parsed_import_vat = (parsed_duties + parsed_statistical_value) * vat_percent;       
          //total_parsed_import_vat += parsed_import_vat;

          
          var final_com_invoice_no = '';

          let parsed_cominvoice_net_amount = 0;
          let parsed_salesinvoice_net_amount_gross = 0;
          let parsed_salesinvoice_vat_amount_gross = 0;        
          $.each(co_invoices, function (idx, co_invoice) {         
            
            final_com_invoice_no = co_invoice['co_invoice_no'];

            var currency_code = co_invoice['currency'];
            if(currency_code != 'NOK')
              currency_code = 'NOK';

            //let net_amount_co_invoice = co_invoice['net_amount_co_invoice'];
            let net_amount_co_invoice = co_invoice['com_net_amount'];
            parsed_cominvoice_net_amount =  parseAmountValue(net_amount_co_invoice, currency_code);
            //total_parsed_cominvoice_net_amount += parsed_cominvoice_net_amount;
            
            let parsed_statistical_value_minus_net_amount_co_invoice = parsed_statistical_value - parsed_cominvoice_net_amount;            
            //total_parsed_statistical_value_minus_net_amount_co_invoice += parsed_statistical_value_minus_net_amount_co_invoice;

            //total_document_qty += co_invoice['invoices'].length;
            let salesinvoice_count = 0;
            var category_type = co_invoice['category_type'];  
            if(category_type != 'EB' && category_type != 'RE')
            {
              $.each(co_invoice['invoices'], function (idx, sales_invoice) {
                var show_disregarded_invoices = ($('#chk-declaration-filter-show-disregarded-invoices').prop('checked')) ? true : false;
                var show_invoice = (show_disregarded_invoices) ? true : ((sales_invoice.disregard_invoice) ? false : true);

                if(show_invoice)               
                //if(!sales_invoice.disregard_invoice) 
                {
                  salesinvoice_count++;

                  let net_amount_sales_invoice = sales_invoice['net_amount'];
                  let parsed_salesinvoice_net_amount = parseAmountValue(net_amount_sales_invoice, currency_code);

                  let shipping_amount_sales_invoice = sales_invoice['shipping'];
                  let parsed_salesinvoice_shipping_amount = parseAmountValue(shipping_amount_sales_invoice, currency_code);

                  let variance_amount_sales_invoice = sales_invoice['variance'];
                  let parsed_salesinvoice_variance_amount = parseAmountValue(variance_amount_sales_invoice, currency_code);

                  parsed_salesinvoice_net_amount_gross += (parsed_salesinvoice_net_amount + parsed_salesinvoice_shipping_amount + parsed_salesinvoice_variance_amount);

                  //total_parsed_salesinvoice_net_amount += (parsed_salesinvoice_net_amount + parsed_salesinvoice_shipping_amount);

                  let vat_amount_sales_invoice = sales_invoice['vat_amount'];
                  let parsed_salesinvoice_vat_amount = parseAmountValue(vat_amount_sales_invoice, currency_code);
                  parsed_salesinvoice_vat_amount_gross += parsed_salesinvoice_vat_amount;  
                } //NOT disregarded
              });
            } //not EB and RE
            //total_parsed_salesinvoice_vat_amount += parsed_salesinvoice_vat_amount_gross;

            let parsed_sales_vat_vs_import_vat = parsed_salesinvoice_vat_amount_gross - (parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat);       
            //total_parsed_sales_vat_vs_import_vat += parsed_sales_vat_vs_import_vat;

            let parsed_cominvoice_minus_salesinvoice_net_amount = parsed_cominvoice_net_amount - parsed_salesinvoice_net_amount_gross;       
            //total_parsed_cominvoice_minus_salesinvoice_net_amount += parsed_cominvoice_minus_salesinvoice_net_amount;

            let parsed_vat_check = (parsed_salesinvoice_net_amount_gross * vat_percent) - parsed_salesinvoice_vat_amount_gross;       
            //total_parsed_vat_check += parsed_vat_check;            

            // let mainRow = [declaration_date, lope_no, parsed_duties
            // , parsed_net_amount, parsed_adjustment, parsed_statistical_value,
            // parsed_import_vat, parsed_duties_vat, parsed_adjustment_vat, "",
            // final_com_invoice_no, parsed_cominvoice_net_amount, parsed_salesinvoice_net_amount_gross, 
            // parsed_salesinvoice_vat_amount_gross, parsed_sales_vat_vs_import_vat, co_invoice['invoices'].length, 
            // parsed_statistical_value_minus_net_amount_co_invoice, parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)),
            // parsed_vat_check]; // Adjust based on your columns

            //RULE 1 - TOTAL com. invoice net amount - TOTAL sales invoice net amount
            var row_color = 0;
            if (Math.abs(parsed_cominvoice_minus_salesinvoice_net_amount) > 100)
              row_color = 1;

            //RULE 2 - TOTAL com. invoice net amount - TOTAL ivf/xml net amount                  
            let diff_cominvoice_vs_xml_net_amount = parsed_statistical_value - parsed_cominvoice_net_amount; 
            if (Math.abs(diff_cominvoice_vs_xml_net_amount) > 100)
              row_color = 1;

            /*Check Lope no. already exists*/
            var filter_lope_no_exists = [];
            var sheetData_index = '';
            //if(!co_invoice['no_of_split'])
            //{
              filter_lope_no_exists = sheetData.filter(function(obj, index) {  
                //if(obj[1]['v'] == co_invoice['lope_no'] || obj[1] == co_invoice['lope_no'])
                if( (obj[1]['v'] == co_invoice['lope_no'] || obj[1] == co_invoice['lope_no']) && 
                    (obj[19]['v'] == co_invoice['expo_no'] || obj[19] == co_invoice['expo_no'])
                  )
                {
                  sheetData_index = index;                    
                  return true;  
                }         
                else
                  return false;
              });
            //}
            /*Check Lope no. already exists*/

            if(filter_lope_no_exists.length == 0)
            {
              let mainRow = [ 
                (row_color) ? { v: declaration_date, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : declaration_date,
                (row_color) ? { v: lope_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : lope_no,
                (row_color) ? { v: parsed_duties, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_duties,
                (row_color) ? { v: parsed_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_net_amount,
                (row_color) ? { v: parsed_adjustment, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_adjustment,
                (row_color) ? { v: parsed_statistical_value, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_statistical_value,
                (row_color) ? { v: parsed_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_import_vat,
                (row_color) ? { v: parsed_duties_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_duties_vat,
                (row_color) ? { v: parsed_adjustment_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_adjustment_vat,
                "",
                (row_color) ? { v: final_com_invoice_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : final_com_invoice_no,
                (row_color) ? { v: parsed_cominvoice_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_cominvoice_net_amount,
                (row_color) ? { v: parsed_salesinvoice_net_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_salesinvoice_net_amount_gross,
                (row_color) ? { v: parsed_salesinvoice_vat_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_salesinvoice_vat_amount_gross,
                (row_color) ? { v: parsed_sales_vat_vs_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_sales_vat_vs_import_vat,
                (row_color) ? { v: salesinvoice_count, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : salesinvoice_count,
                (row_color) ? { v: parsed_statistical_value_minus_net_amount_co_invoice, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_statistical_value_minus_net_amount_co_invoice,
                (row_color) ? { v: parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)),
                (row_color) ? { v: parseFloat(parsed_vat_check.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(parsed_vat_check.toFixed(2))                   

                ,co_invoice['expo_no']
              ]; // Adjust based on your columns
                
              // Push main row data
              sheetData.push(mainRow);   
            } //new lope no.                                    
            else  
            {    
              if(co_invoice['no_of_split'])
              {
                if(row_color)
                  sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;                    
                else
                  sheetData[sheetData_index][10] += '.' + final_com_invoice_no;
              }
              else
              {
                //DON'T DELETE this else - for DFI, STOF etc.,
                if(row_color)
                {
                  if (typeof sheetData[sheetData_index][10]['v'] !== 'undefined')
                    sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no; 
                  else
                    sheetData[sheetData_index][10] += '.' + final_com_invoice_no; 

                  if (typeof sheetData[sheetData_index][11]['v'] !== 'undefined')
                    sheetData[sheetData_index][11]['v'] += parsed_cominvoice_net_amount;
                  else
                    sheetData[sheetData_index][11] += parsed_cominvoice_net_amount;

                  if (typeof sheetData[sheetData_index][12]['v'] !== 'undefined')
                    sheetData[sheetData_index][12]['v'] += parsed_salesinvoice_net_amount_gross;
                  else
                    sheetData[sheetData_index][12] += parsed_salesinvoice_net_amount_gross;

                  if (typeof sheetData[sheetData_index][13]['v'] !== 'undefined')
                    sheetData[sheetData_index][13]['v'] += parsed_salesinvoice_vat_amount_gross;
                  else
                    sheetData[sheetData_index][13] += parsed_salesinvoice_vat_amount_gross;

                  if (typeof sheetData[sheetData_index][14]['v'] !== 'undefined')
                  {
                    sheetData[sheetData_index][14]['v'] += parsed_sales_vat_vs_import_vat;                             
                    sheetData[sheetData_index][14]['v'] = ((parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat) + sheetData[sheetData_index][14]['v']);
                  }
                  else
                  {
                    sheetData[sheetData_index][14] += parsed_sales_vat_vs_import_vat;                           
                    sheetData[sheetData_index][14] = ((parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat) + sheetData[sheetData_index][14]);
                  }
                            
                  if (typeof sheetData[sheetData_index][15]['v'] !== 'undefined')
                    sheetData[sheetData_index][15]['v'] += salesinvoice_count;
                  else
                    sheetData[sheetData_index][15] += salesinvoice_count;

                  if (typeof sheetData[sheetData_index][16]['v'] !== 'undefined')
                  {
                    //sheetData[sheetData_index][16]['v'] += parsed_statistical_value_minus_net_amount_co_invoice;
                    //sheetData[sheetData_index][16]['v'] = (parsed_statistical_value - sheetData[sheetData_index][16]['v']);

                    let final_statistical_value_minus_net_amount_co_invoice =  sheetData[sheetData_index][16]['v'] - parsed_cominvoice_net_amount;
                    sheetData[sheetData_index][16]['v'] = final_statistical_value_minus_net_amount_co_invoice; 
                  }
                  else
                  {
                    //sheetData[sheetData_index][16] += parsed_statistical_value_minus_net_amount_co_invoice;
                    //sheetData[sheetData_index][16] = (parsed_statistical_value - sheetData[sheetData_index][16]);

                    let final_statistical_value_minus_net_amount_co_invoice =  sheetData[sheetData_index][16] - parsed_cominvoice_net_amount;
                    sheetData[sheetData_index][16] = final_statistical_value_minus_net_amount_co_invoice; 
                  }
                }
                else
                {
                  if (typeof sheetData[sheetData_index][10]['v'] !== 'undefined')
                    sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;
                  else
                    sheetData[sheetData_index][10] += '.' + final_com_invoice_no;

                  if (typeof sheetData[sheetData_index][11]['v'] !== 'undefined')
                    sheetData[sheetData_index][11]['v'] += parsed_cominvoice_net_amount;
                  else
                    sheetData[sheetData_index][11] += parsed_cominvoice_net_amount;

                  if (typeof sheetData[sheetData_index][12]['v'] !== 'undefined')
                    sheetData[sheetData_index][12]['v'] += parsed_salesinvoice_net_amount_gross;
                  else
                    sheetData[sheetData_index][12] += parsed_salesinvoice_net_amount_gross;

                  if (typeof sheetData[sheetData_index][13]['v'] !== 'undefined')
                    sheetData[sheetData_index][13]['v'] += parsed_salesinvoice_vat_amount_gross;
                  else
                    sheetData[sheetData_index][13] += parsed_salesinvoice_vat_amount_gross;

                  if (typeof sheetData[sheetData_index][14]['v'] !== 'undefined')
                  {
                    sheetData[sheetData_index][14]['v'] += parsed_sales_vat_vs_import_vat;                             
                    sheetData[sheetData_index][14]['v'] = ((parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat) + sheetData[sheetData_index][14]['v']);
                  }
                  else
                  {
                    sheetData[sheetData_index][14] += parsed_sales_vat_vs_import_vat;                           
                    sheetData[sheetData_index][14] = ((parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat) + sheetData[sheetData_index][14]);
                  }

                  if (typeof sheetData[sheetData_index][15]['v'] !== 'undefined')
                    sheetData[sheetData_index][15]['v'] += salesinvoice_count;
                  else
                    sheetData[sheetData_index][15] += salesinvoice_count;

                  if (typeof sheetData[sheetData_index][16]['v'] !== 'undefined')
                  {
                    //sheetData[sheetData_index][16]['v'] += parsed_statistical_value_minus_net_amount_co_invoice;
                    //sheetData[sheetData_index][16]['v'] = (parsed_statistical_value - sheetData[sheetData_index][16]['v']);

                    let final_statistical_value_minus_net_amount_co_invoice =  sheetData[sheetData_index][16]['v'] - parsed_cominvoice_net_amount;
                    sheetData[sheetData_index][16]['v'] = final_statistical_value_minus_net_amount_co_invoice; 
                  }
                  else
                  {
                    //sheetData[sheetData_index][16] += parsed_statistical_value_minus_net_amount_co_invoice;
                    //sheetData[sheetData_index][16] = (parsed_statistical_value - sheetData[sheetData_index][16]);

                    let final_statistical_value_minus_net_amount_co_invoice =  sheetData[sheetData_index][16] - parsed_cominvoice_net_amount;
                    sheetData[sheetData_index][16] = final_statistical_value_minus_net_amount_co_invoice; 
                  }
                }
              } //DON'T DELETE this else - for DFI, STOF etc.,
                        
              // let prev_parsed_salesinvoice_vat_amount_gross = sheetData[sheetData_index][13]['v'];                    
              // parsed_sales_vat_vs_import_vat = (prev_parsed_salesinvoice_vat_amount_gross + parsed_salesinvoice_vat_amount_gross) - (parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat); 
                       
              // let prev_parsed_cominvoice_net_amount = sheetData[sheetData_index][11]['v'];                    
              // parsed_statistical_value_minus_net_amount_co_invoice =  parsed_statistical_value - (prev_parsed_cominvoice_net_amount + parsed_cominvoice_net_amount);
                                              
              // sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;
              
              // sheetData[sheetData_index][11]['v'] += parsed_cominvoice_net_amount;
              // sheetData[sheetData_index][12]['v'] += parsed_salesinvoice_net_amount_gross;
              // sheetData[sheetData_index][13]['v'] += parsed_salesinvoice_vat_amount_gross;
              // sheetData[sheetData_index][14]['v'] = parsed_sales_vat_vs_import_vat;
              // sheetData[sheetData_index][15]['v'] += salesinvoice_count;
              // sheetData[sheetData_index][16]['v'] = parsed_statistical_value_minus_net_amount_co_invoice;
              // sheetData[sheetData_index][17]['v'] += parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2));
              // sheetData[sheetData_index][18]['v'] += parseFloat(parsed_vat_check.toFixed(2));
            } //group already existing lope no.  
          });
        }//XML length 0
        else
        {
          $.each(import_vat_xml_datas, function (idx, rowData) {            
            var category_type = rowData['Ekspedisjon']['Kategori']['KategoriType'];
            var category_desc = rowData['Ekspedisjon']['Kategori']['KategoriBeskrivelse'];

            var eksp_type_no = rowData['Ekspedisjon']['EkspType']['EkspTypeNr'];

            if(eksp_type_no != 1)
            {
              var allow = true;
              var expo_type = rowData['Ekspedisjon']['EkspType']['EkspTypeNavn'];
              if(category_type == 'MA' && (expo_type.toLowerCase().indexOf('utførsel') !== -1))
                allow = false;

              //if(category_type != 'MA' && category_type != 'SO')
              if(category_type != 'SO' && allow)
              {
                var invoice_refs = [];           
                if (Array.isArray(rowData['Fakturareferanser']['Fakturareferanse']))
                  invoice_refs = rowData['Fakturareferanser']['Fakturareferanse'];
                else
                  invoice_refs = [rowData['Fakturareferanser']['Fakturareferanse']];

                $.each(invoice_refs, function (idx, invoice_ref) {
                  
                  //var declaration_date = invoice_ref['Fakturadato'];
                  var declaration_date = rowData['Ekspedisjon']['EkspDato'];
                  var com_invoice_no = invoice_ref['Fakturanummer'];
                 
                  var expo_no = rowData['Ekspedisjon']['EkspedisjonsId']['EkspNr'];
                  var lope_no = rowData['Ekspedisjon']['EkspedisjonsId']['LopeNr'];

                  var currency_code = rowData['FakturaValutaBelop']['Fakturavaluta'];

                  let duties = new Intl.NumberFormat(currency_locale, {
                style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(rowData['Avgift']);
                  let parsed_duties =  parseAmountValue(duties, currency_style);//DON'T CHANGE currency_style
                  //total_parsed_duties += parsed_duties;

                  let parsed_duties_vat = parsed_duties * vat_percent;       
                  //total_parsed_duties_vat += parsed_duties_vat;
                  
                  let parsed_net_amount =  0;
                  let parsed_adjustment =  0;
                  let parsed_statistical_value =  0;
                  if(rowData['FakturaValutaBelop']['OmrKurs'] == 100)
                  {
                  //   let net_amount = new Intl.NumberFormat(currency_locale, {
                  // style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(rowData['FakturaValutaBelop']['Fakturasum']);
                  //   parsed_net_amount =  parseAmountValue(net_amount, currency_code);
                  //   total_parsed_net_amount += parsed_net_amount;
                    let net_amount = rowData['FakturaValutaBelop']['Fakturasum'];

                    let adjustment = new Intl.NumberFormat(currency_locale, {
                  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(rowData['Justering']);
                    parsed_adjustment =  parseAmountValue(adjustment, currency_code);
                    //total_parsed_adjustment += parsed_adjustment;

                    let statistical_value = new Intl.NumberFormat(currency_locale, {
                  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(rowData['StatistiskVerdi']);
                    parsed_statistical_value =  parseAmountValue(statistical_value, currency_code);
                    //total_parsed_statistical_value += parsed_statistical_value;

                    if(category_type == 'RE')
                    {
                      if (rowData['StatistiskVerdi'].startsWith("-")) 
                      {
                        //net_amount = '-' + net_amount;
                        net_amount = -1 * net_amount;
                      } //negative statisticalValue
                      else
                      {
                        if (parsed_statistical_value == 0)  
                          net_amount = 0;
                      } //zero statisticalValue
                    } //Refund  
                    else if(category_type == 'EB')
                    {
                      net_amount = statistical_value;
                    } //Recalculation

                    net_amount = new Intl.NumberFormat(currency_locale, {
                     style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(net_amount);
                    parsed_net_amount =  parseAmountValue(net_amount, currency_code);
                    //total_parsed_net_amount += parsed_net_amount;
                  }
                  else
                  {          
                    let only_net_amount = rowData['FakturaValutaBelop']['Fakturasum'];            
                    //let omr_kurs_amount = rowData['FakturaValutaBelop']['OmrKurs']; 

                    let currency_value = rowData['FakturaValutaBelop']['Fakturavaluta'];  

                    let omr_kurs_amount = 1;
                    if(currency_value == 'DKK')   
                    {  
                      omr_kurs_amount = rowData['FakturaValutaBelop']['OmrKurs'].replace(/[,.]/g, ""); 
                      omr_kurs_amount = omr_kurs_amount.substr(0, 1) + "." + omr_kurs_amount.substr(1);
                    }
                    else if(currency_value == 'USD' || currency_value == 'EUR')   
                    {
                      omr_kurs_amount = rowData['FakturaValutaBelop']['OmrKurs'].replace(/[,.]/g, ""); 
                      omr_kurs_amount = omr_kurs_amount.substr(0, 2) + "." + omr_kurs_amount.substr(2);
                    }                

                    let net_amount = only_net_amount * omr_kurs_amount; 
                    //parsed_net_amount =  parseAmountValue(net_amount, currency_code);
                    //total_parsed_net_amount += parsed_net_amount;  

                    let adjustment = rowData['Justering'];
                    parsed_adjustment =  parseAmountValue(adjustment, currency_code);
                    //total_parsed_adjustment += parsed_adjustment;

                    let statistical_value = rowData['StatistiskVerdi'];
                    parsed_statistical_value =  parseAmountValue(statistical_value, currency_code);
                    //total_parsed_statistical_value += parsed_statistical_value; 

                    if(category_type == 'RE')
                    {
                      if (rowData['StatistiskVerdi'].startsWith("-")) 
                      {
                        //net_amount = '-' + net_amount;
                        net_amount = -1 * net_amount;
                      } //negative statisticalValue
                      else
                      {
                        if (parsed_statistical_value == 0)  
                          net_amount = 0;
                      } //zero statisticalValue
                    } //Refund  
                    else if(category_type == 'EB')
                    {
                      net_amount = statistical_value;
                    } //Recalculation

                    //parsed_net_amount =  parseAmountValue(net_amount, currency_code);
                    parsed_net_amount = parseFloat(net_amount.toFixed(2));
                    //total_parsed_net_amount += parsed_net_amount;           
                  }          

                  let parsed_adjustment_vat = parsed_adjustment * vat_percent;       
                  //total_parsed_adjustment_vat += parsed_adjustment_vat;

                  let parsed_import_vat = (parsed_duties + parsed_statistical_value) * vat_percent;       
                  //total_parsed_import_vat += parsed_import_vat;

                  var final_com_invoice_no = '';

                  let parsed_cominvoice_net_amount = 0;
                  let parsed_salesinvoice_net_amount_gross = 0;
                  let parsed_salesinvoice_vat_amount_gross = 0;                
          
                  $.each(co_invoices, function (idx, co_invoice) {

                    var proceed = false;

                    if(String(co_invoice['group_lope_no']).indexOf('***') != -1)
                    {
                      let arr_com_invoice_no = co_invoice['co_invoice_no'].split(", ");
                      let arr_o_com_invoice_no = co_invoice['orginal_co_invoice_no'].split(", ");

                      if( (arr_com_invoice_no.includes(com_invoice_no) && lope_no == co_invoice['lope_no']) ||
                        (arr_o_com_invoice_no.includes(com_invoice_no) && lope_no == co_invoice['lope_no']) 
                        )
                        proceed = true;                 
                    }
                    else
                    {
                      if((com_invoice_no == co_invoice['co_invoice_no'] && lope_no == co_invoice['lope_no']) || 
                        (com_invoice_no == co_invoice['orginal_co_invoice_no'] && lope_no == co_invoice['lope_no']))
                        proceed = true;
                    }              
//console.log(com_invoice_no + " -- " + proceed);
//console.log(co_invoice);
                    // if((com_invoice_no == co_invoice['co_invoice_no'] && lope_no == co_invoice['lope_no']) || 
                    //   (com_invoice_no == co_invoice['orginal_co_invoice_no'] && lope_no == co_invoice['lope_no']))
                    if(proceed)
                    {     
                      final_com_invoice_no = co_invoice['co_invoice_no'];

                      var currency_code = co_invoice['currency'];
                      if(currency_code != 'NOK')
                        currency_code = 'NOK';
                     
                      //let net_amount_co_invoice = co_invoice['net_amount_co_invoice'];
                      let net_amount_co_invoice = (category_type == 'EB' || category_type == 'RE') ? 0 : co_invoice['com_net_amount'];
                      parsed_cominvoice_net_amount =  parseAmountValue(net_amount_co_invoice, currency_code);
                      //total_parsed_cominvoice_net_amount += parsed_cominvoice_net_amount;

                      // if(co_invoice['co_invoice_no'] == '-')
                      // {
                      //   parsed_statistical_value = 0;
                      //   parsed_import_vat = 0;
                      //   parsed_duties_vat = 0;
                      //   parsed_adjustment_vat = 0;
                      // }
                      let parsed_statistical_value_minus_net_amount_co_invoice = parsed_statistical_value - parsed_cominvoice_net_amount;            
                      //total_parsed_statistical_value_minus_net_amount_co_invoice += parsed_statistical_value_minus_net_amount_co_invoice;

                      //total_document_qty += co_invoice['invoices'].length;
                      let salesinvoice_count = 0;                      
                      if(category_type != 'EB' && category_type != 'RE')
                      {
                        $.each(co_invoice['invoices'], function (idx, sales_invoice) {
                          var show_disregarded_invoices = ($('#chk-declaration-filter-show-disregarded-invoices').prop('checked')) ? true : false;
                          var show_invoice = (show_disregarded_invoices) ? true : ((sales_invoice.disregard_invoice) ? false : true);

                          if(show_invoice)
                          //if(!sales_invoice.disregard_invoice) 
                          {
                            salesinvoice_count++;

                            let net_amount_sales_invoice = sales_invoice['net_amount'];
                            let parsed_salesinvoice_net_amount = parseAmountValue(net_amount_sales_invoice, currency_code);

                            let shipping_amount_sales_invoice = sales_invoice['shipping'];
                            let parsed_salesinvoice_shipping_amount = parseAmountValue(shipping_amount_sales_invoice, currency_code);

                            let variance_amount_sales_invoice = sales_invoice['variance'];
                            let parsed_salesinvoice_variance_amount = parseAmountValue(variance_amount_sales_invoice, currency_code);

                            parsed_salesinvoice_net_amount_gross += (parsed_salesinvoice_net_amount + parsed_salesinvoice_shipping_amount + parsed_salesinvoice_variance_amount);

                            //total_parsed_salesinvoice_net_amount += (parsed_salesinvoice_net_amount + parsed_salesinvoice_shipping_amount);

                            let vat_amount_sales_invoice = sales_invoice['vat_amount'];
                            let parsed_salesinvoice_vat_amount = parseAmountValue(vat_amount_sales_invoice, currency_code);
                            parsed_salesinvoice_vat_amount_gross += parsed_salesinvoice_vat_amount;

                            //total_parsed_salesinvoice_vat_amount += parsed_salesinvoice_vat_amount_gross;
                          }//NOT disregarded
                        });
                      } //not EB and RE
                      //total_parsed_salesinvoice_vat_amount += parsed_salesinvoice_vat_amount_gross;

                      let parsed_sales_vat_vs_import_vat = parsed_salesinvoice_vat_amount_gross - (parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat);       
                      //total_parsed_sales_vat_vs_import_vat += parsed_sales_vat_vs_import_vat;

                      let parsed_cominvoice_minus_salesinvoice_net_amount = parsed_cominvoice_net_amount - parsed_salesinvoice_net_amount_gross;       
                      //total_parsed_cominvoice_minus_salesinvoice_net_amount += parsed_cominvoice_minus_salesinvoice_net_amount;

                      let parsed_vat_check = (parsed_salesinvoice_net_amount_gross * vat_percent) - parsed_salesinvoice_vat_amount_gross;       
                      //total_parsed_vat_check += parsed_vat_check;                              

                      // let mainRow = [declaration_date, lope_no, parsed_duties
                      // , parsed_net_amount, parsed_adjustment, parsed_statistical_value,
                      // parsed_import_vat, parsed_duties_vat, parsed_adjustment_vat, "",
                      // final_com_invoice_no, parsed_cominvoice_net_amount, parsed_salesinvoice_net_amount_gross, 
                      // parsed_salesinvoice_vat_amount_gross, parsed_sales_vat_vs_import_vat, co_invoice['invoices'].length, 
                      // parsed_statistical_value_minus_net_amount_co_invoice, parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)),
                      // parsed_vat_check]; // Adjust based on your columns

                      //RULE 1 - TOTAL com. invoice net amount - TOTAL sales invoice net amount
                      var row_color = 0;
                      if (Math.abs(parsed_cominvoice_minus_salesinvoice_net_amount) > 100)
                        row_color = 1;

                      //RULE 2 - TOTAL com. invoice net amount - TOTAL ivf/xml net amount                  
                      let diff_cominvoice_vs_xml_net_amount = parsed_statistical_value - parsed_cominvoice_net_amount; 
                      if (Math.abs(diff_cominvoice_vs_xml_net_amount) > 100)
                        row_color = 1;

                      /*Check Lope no. already exists*/
                      var filter_lope_no_exists = [];
                      var sheetData_index = '';
                      //if(!co_invoice['no_of_split'])
                      //{
                        filter_lope_no_exists = sheetData.filter(function(obj, index) {  
                          //if(obj[1]['v'] == co_invoice['lope_no'] || obj[1] == co_invoice['lope_no'])
                          if( (obj[1]['v'] == co_invoice['lope_no'] || obj[1] == co_invoice['lope_no']) && 
                              (obj[19]['v'] == co_invoice['expo_no'] || obj[19] == co_invoice['expo_no'])
                            )
                          {
                            sheetData_index = index;                    
                            return true;  
                          }         
                          else
                            return false;
                        });
                      //}
                      /*Check Lope no. already exists*/
  //console.log(final_com_invoice_no);
//   if(co_invoice['lope_no'] == '2500252715')
//   {
//      console.log(co_invoice['lope_no'] + " -- " + row_color);      
//      console.log(final_com_invoice_no);
//      console.log(parsed_salesinvoice_vat_amount_gross);
//      //console.log(parsed_sales_vat_vs_import_vat);
//      console.log("statistical_value -- cominvoice_net_amount");
//      //console.log(parsed_statistical_value);
//      console.log(parsed_cominvoice_net_amount);
//      //console.log(parsed_statistical_value_minus_net_amount_co_invoice);
//      console.log(parsed_cominvoice_minus_salesinvoice_net_amount);
//   console.log(filter_lope_no_exists);
// }
                      if(filter_lope_no_exists.length == 0)
                      {                    
                        let mainRow = [ 
                          (row_color) ? { v: declaration_date, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : declaration_date,
                          (row_color) ? { v: lope_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : lope_no,
                          (row_color) ? { v: parsed_duties, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_duties,
                          (row_color) ? { v: parsed_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_net_amount,
                          (row_color) ? { v: parsed_adjustment, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_adjustment,
                          (row_color) ? { v: parsed_statistical_value, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_statistical_value,
                          (row_color) ? { v: parsed_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_import_vat,
                          (row_color) ? { v: parsed_duties_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_duties_vat,
                          (row_color) ? { v: parsed_adjustment_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_adjustment_vat,
                          "",
                          (row_color) ? { v: final_com_invoice_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : final_com_invoice_no,
                          (row_color) ? { v: parsed_cominvoice_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_cominvoice_net_amount,
                          (row_color) ? { v: parsed_salesinvoice_net_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_salesinvoice_net_amount_gross,
                          (row_color) ? { v: parsed_salesinvoice_vat_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_salesinvoice_vat_amount_gross,
                          (row_color) ? { v: parsed_sales_vat_vs_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_sales_vat_vs_import_vat,
                          (row_color) ? { v: salesinvoice_count, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : salesinvoice_count,
                          (row_color) ? { v: parsed_statistical_value_minus_net_amount_co_invoice, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_statistical_value_minus_net_amount_co_invoice,
                          (row_color) ? { v: parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)),
                          (row_color) ? { v: parseFloat(parsed_vat_check.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(parsed_vat_check.toFixed(2))                   

                          ,co_invoice['expo_no']
                        ]; // Adjust based on your columns
                        
                        // Push main row data
                        sheetData.push(mainRow); 
                      } //new lope no.                                    
                      else  
                      { 
                        if(co_invoice['no_of_split'])
                        {
                          if(row_color)
                            sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;                    
                          else
                            sheetData[sheetData_index][10] += '.' + final_com_invoice_no;
                        }
                        else
                        {
                          //DON'T DELETE this else - for DFI, STOF etc.,
                          if(row_color)
                          {
                            if (typeof sheetData[sheetData_index][10]['v'] !== 'undefined')
                              sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no; 
                            else
                              sheetData[sheetData_index][10] += '.' + final_com_invoice_no; 

                            if (typeof sheetData[sheetData_index][11]['v'] !== 'undefined')
                              sheetData[sheetData_index][11]['v'] += parsed_cominvoice_net_amount;
                            else
                              sheetData[sheetData_index][11] += parsed_cominvoice_net_amount;

                            if (typeof sheetData[sheetData_index][12]['v'] !== 'undefined')
                              sheetData[sheetData_index][12]['v'] += parsed_salesinvoice_net_amount_gross;
                            else
                              sheetData[sheetData_index][12] += parsed_salesinvoice_net_amount_gross;

                            if (typeof sheetData[sheetData_index][13]['v'] !== 'undefined')
                              sheetData[sheetData_index][13]['v'] += parsed_salesinvoice_vat_amount_gross;
                            else
                              sheetData[sheetData_index][13] += parsed_salesinvoice_vat_amount_gross;

                            if (typeof sheetData[sheetData_index][14]['v'] !== 'undefined')
                            {                              
                              sheetData[sheetData_index][14]['v'] += parsed_sales_vat_vs_import_vat;                             
                              sheetData[sheetData_index][14]['v'] = ((parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat) + sheetData[sheetData_index][14]['v']);

                              //let final_sales_vat_vs_import_vat =  sheetData[sheetData_index][14]['v'] - parsed_sales_vat_vs_import_vat;
                              //sheetData[sheetData_index][14]['v'] = final_sales_vat_vs_import_vat;
                            }
                            else
                            {
                              sheetData[sheetData_index][14] += parsed_sales_vat_vs_import_vat;                           
                              sheetData[sheetData_index][14] = ((parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat) + sheetData[sheetData_index][14]);

                              //let final_sales_vat_vs_import_vat =  sheetData[sheetData_index][14] - parsed_sales_vat_vs_import_vat;
                              //sheetData[sheetData_index][14] = final_sales_vat_vs_import_vat;
                            }

                            if (typeof sheetData[sheetData_index][15]['v'] !== 'undefined')
                              sheetData[sheetData_index][15]['v'] += salesinvoice_count;
                            else
                              sheetData[sheetData_index][15] += salesinvoice_count;

                            if (typeof sheetData[sheetData_index][16]['v'] !== 'undefined')
                            {                                                                        
                              //sheetData[sheetData_index][16]['v'] += parsed_statistical_value_minus_net_amount_co_invoice;
                              //sheetData[sheetData_index][16]['v'] = (parsed_statistical_value - sheetData[sheetData_index][16]['v']);

                              let final_statistical_value_minus_net_amount_co_invoice =  sheetData[sheetData_index][16]['v'] - parsed_cominvoice_net_amount;
                              sheetData[sheetData_index][16]['v'] = final_statistical_value_minus_net_amount_co_invoice;                          
                            }
                            else
                            {
                              //sheetData[sheetData_index][16] += parsed_statistical_value_minus_net_amount_co_invoice;
                              //sheetData[sheetData_index][16] = (parsed_statistical_value - sheetData[sheetData_index][16]);

                              let final_statistical_value_minus_net_amount_co_invoice =  sheetData[sheetData_index][16] - parsed_cominvoice_net_amount;
                              sheetData[sheetData_index][16] = final_statistical_value_minus_net_amount_co_invoice;      
                            }
                                                       
                            if (typeof sheetData[sheetData_index][17]['v'] !== 'undefined')
                            {
                              let final_parsed_cominvoice_minus_salesinvoice_net_amount = sheetData[sheetData_index][17]['v'] + parsed_cominvoice_minus_salesinvoice_net_amount;
                              sheetData[sheetData_index][17]['v'] = final_parsed_cominvoice_minus_salesinvoice_net_amount;
                            }
                            else
                            {
                              let final_parsed_cominvoice_minus_salesinvoice_net_amount = sheetData[sheetData_index][17] + parsed_cominvoice_minus_salesinvoice_net_amount;
                              sheetData[sheetData_index][17] = parsed_cominvoice_minus_salesinvoice_net_amount;
                            }
                          }
                          else
                          {
                            if (typeof sheetData[sheetData_index][10]['v'] !== 'undefined')
                              sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;
                            else
                              sheetData[sheetData_index][10] += '.' + final_com_invoice_no;

                            if (typeof sheetData[sheetData_index][11]['v'] !== 'undefined')
                              sheetData[sheetData_index][11]['v'] += parsed_cominvoice_net_amount;
                            else
                              sheetData[sheetData_index][11] += parsed_cominvoice_net_amount;

                            if (typeof sheetData[sheetData_index][12]['v'] !== 'undefined')
                              sheetData[sheetData_index][12]['v'] += parsed_salesinvoice_net_amount_gross;
                            else
                              sheetData[sheetData_index][12] += parsed_salesinvoice_net_amount_gross;

                            if (typeof sheetData[sheetData_index][13]['v'] !== 'undefined')
                              sheetData[sheetData_index][13]['v'] += parsed_salesinvoice_vat_amount_gross;
                            else
                              sheetData[sheetData_index][13] += parsed_salesinvoice_vat_amount_gross;
                           
                            if (typeof sheetData[sheetData_index][14]['v'] !== 'undefined')
                            {
                              sheetData[sheetData_index][14]['v'] += parsed_sales_vat_vs_import_vat;                             
                              sheetData[sheetData_index][14]['v'] = ((parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat) + sheetData[sheetData_index][14]['v']);

                              //let final_sales_vat_vs_import_vat =  sheetData[sheetData_index][14]['v'] - parsed_sales_vat_vs_import_vat;
                              //sheetData[sheetData_index][14]['v'] = final_sales_vat_vs_import_vat;
                            }
                            else
                            {
                              sheetData[sheetData_index][14] += parsed_sales_vat_vs_import_vat;                           
                              sheetData[sheetData_index][14] = ((parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat) + sheetData[sheetData_index][14]);

                              //let final_sales_vat_vs_import_vat =  sheetData[sheetData_index][14] - parsed_sales_vat_vs_import_vat;
                              //sheetData[sheetData_index][14] = final_sales_vat_vs_import_vat;
                            }
                            
                            if (typeof sheetData[sheetData_index][15]['v'] !== 'undefined')
                              sheetData[sheetData_index][15]['v'] += salesinvoice_count;
                            else
                              sheetData[sheetData_index][15] += salesinvoice_count;

                            if (typeof sheetData[sheetData_index][16]['v'] !== 'undefined')
                            {
                              //sheetData[sheetData_index][16]['v'] += parsed_statistical_value_minus_net_amount_co_invoice;
                              //sheetData[sheetData_index][16]['v'] = (parsed_statistical_value - sheetData[sheetData_index][16]['v']);

                              let final_statistical_value_minus_net_amount_co_invoice =  sheetData[sheetData_index][16]['v'] - parsed_cominvoice_net_amount;
                              sheetData[sheetData_index][16]['v'] = final_statistical_value_minus_net_amount_co_invoice; 
                            }
                            else
                            {
                              //sheetData[sheetData_index][16] += parsed_statistical_value_minus_net_amount_co_invoice;
                              //sheetData[sheetData_index][16] = (parsed_statistical_value - sheetData[sheetData_index][16]);

                              let final_statistical_value_minus_net_amount_co_invoice =  sheetData[sheetData_index][16] - parsed_cominvoice_net_amount;
                              sheetData[sheetData_index][16] = final_statistical_value_minus_net_amount_co_invoice; 
                            }

                            if (typeof sheetData[sheetData_index][17]['v'] !== 'undefined')
                            {
                              let final_parsed_cominvoice_minus_salesinvoice_net_amount = sheetData[sheetData_index][17]['v'] + parsed_cominvoice_minus_salesinvoice_net_amount;
                              sheetData[sheetData_index][17]['v'] = final_parsed_cominvoice_minus_salesinvoice_net_amount;
                            }
                            else
                            {
                              let final_parsed_cominvoice_minus_salesinvoice_net_amount = sheetData[sheetData_index][17] + parsed_cominvoice_minus_salesinvoice_net_amount;
                              sheetData[sheetData_index][17] = parsed_cominvoice_minus_salesinvoice_net_amount;
                            }
                          }
                        } //DON'T DELETE this else - for DFI, STOF etc.,

                        // if(String(co_invoice['group_lope_no']).indexOf('***') === -1)
                        // {
                        //   if(row_color)
                        //     sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;                    
                        //   else
                        //     sheetData[sheetData_index][10] += '.' + final_com_invoice_no;

                        //   let prev_parsed_cominvoice_net_amount = sheetData[sheetData_index][11]['v'];   
                        //   if(prev_parsed_cominvoice_net_amount == parsed_cominvoice_net_amount)
                        //   {

                        //   }
                        //   else
                        //   {
                        //     parsed_statistical_value_minus_net_amount_co_invoice =  parsed_statistical_value - (prev_parsed_cominvoice_net_amount + parsed_cominvoice_net_amount);
                        //   }
                        // }

                        // let prev_parsed_salesinvoice_vat_amount_gross = sheetData[sheetData_index][13]['v'];                    
                        // parsed_sales_vat_vs_import_vat = (prev_parsed_salesinvoice_vat_amount_gross + parsed_salesinvoice_vat_amount_gross) - (parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat); 

                        // let prev_parsed_cominvoice_net_amount = sheetData[sheetData_index][11]['v'];                    
                        // parsed_statistical_value_minus_net_amount_co_invoice =  parsed_statistical_value - (prev_parsed_cominvoice_net_amount + parsed_cominvoice_net_amount);
                                       
                        // // if(String(co_invoice['group_lope_no']).indexOf('***') != -1)
                        // // {
                        //    sheetData[sheetData_index][10]['v'] = final_com_invoice_no;
                        //    sheetData[sheetData_index][11]['v'] = parsed_cominvoice_net_amount;
                        // // }
                        // // else
                        // // {
                        //   //sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;
                        
                        //   //sheetData[sheetData_index][11]['v'] += parsed_cominvoice_net_amount;
                        //   sheetData[sheetData_index][12]['v'] += parsed_salesinvoice_net_amount_gross;
                        //   sheetData[sheetData_index][13]['v'] += parsed_salesinvoice_vat_amount_gross;
                        //   sheetData[sheetData_index][14]['v'] = parsed_sales_vat_vs_import_vat;
                        //   sheetData[sheetData_index][15]['v'] += co_invoice['invoices'].length;
                        //   sheetData[sheetData_index][16]['v'] = parsed_statistical_value_minus_net_amount_co_invoice;
                        //   sheetData[sheetData_index][17]['v'] += parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2));
                        //   sheetData[sheetData_index][18]['v'] += parsed_vat_check;
                        //}
                      } //group already existing lope no.
                    } //proceed
                  }); //loop com. invoices
                }); //loop com. invoices from Import VAT XML
              } //not MA and SO
            } // EkspTypeNr not 1
          }); //loop Import VAT XML
        }//has XML

        // $.each(co_invoices, function (idx, co_invoice) {
        //   if(co_invoice['co_invoice_no'] == '-' && co_invoice['id'] != '-')
        //   {          
        //     var declaration_date = co_invoice['o_invoice_date'];
        //     var final_com_invoice_no = co_invoice['co_invoice_no'];
        //     var lope_no = co_invoice['lope_no'];

        //     var currency_code = co_invoice['currency'];
        //     if(currency_code != 'NOK')
        //       currency_code = 'NOK';

        //     //let net_amount_co_invoice = co_invoice['net_amount_co_invoice'];
        //     let net_amount_co_invoice = co_invoice['com_net_amount'];
        //     let parsed_cominvoice_net_amount =  parseAmountValue(net_amount_co_invoice, currency_code);
        //     //total_parsed_cominvoice_net_amount += parsed_cominvoice_net_amount;
            
        //     let parsed_net_amount = 0;
        //     let parsed_statistical_value = 0;
        //     let parsed_import_vat = 0;
        //     let parsed_duties = 0;
        //     let parsed_duties_vat = 0;
        //     let parsed_adjustment = 0;
        //     let parsed_adjustment_vat = 0;
            
        //     let parsed_statistical_value_minus_net_amount_co_invoice = parsed_statistical_value - parsed_cominvoice_net_amount;            
        //     //total_parsed_statistical_value_minus_net_amount_co_invoice += parsed_statistical_value_minus_net_amount_co_invoice;

        //     let salesinvoice_count = 0;
        //     let parsed_salesinvoice_net_amount_gross = 0;
        //     let parsed_salesinvoice_vat_amount_gross = 0; 
            
        //     //total_document_qty += co_invoice['invoices'].length;
        //     var category_type = co_invoice['category_type'];  
        //     if(category_type != 'EB')
        //     {
        //       $.each(co_invoice['invoices'], function (idx, sales_invoice) {
        //         var show_disregarded_invoices = ($('#chk-declaration-filter-show-disregarded-invoices').prop('checked')) ? true : false;
        //         var show_invoice = (show_disregarded_invoices) ? true : ((sales_invoice.disregard_invoice) ? false : true);

        //         if(show_invoice) 
        //         //if(!sales_invoice.disregard_invoice) 
        //         {
        //           salesinvoice_count++;

        //           let net_amount_sales_invoice = sales_invoice['net_amount'];
        //           let parsed_salesinvoice_net_amount = parseAmountValue(net_amount_sales_invoice, currency_code);

        //           let shipping_amount_sales_invoice = sales_invoice['shipping'];
        //           let parsed_salesinvoice_shipping_amount = parseAmountValue(shipping_amount_sales_invoice, currency_code);

        //           parsed_salesinvoice_net_amount_gross += (parsed_salesinvoice_net_amount + parsed_salesinvoice_shipping_amount);

        //           //total_parsed_salesinvoice_net_amount += (parsed_salesinvoice_net_amount + parsed_salesinvoice_shipping_amount);

        //           let vat_amount_sales_invoice = sales_invoice['vat_amount'];
        //           let parsed_salesinvoice_vat_amount = parseAmountValue(vat_amount_sales_invoice, currency_code);
        //           parsed_salesinvoice_vat_amount_gross += parsed_salesinvoice_vat_amount;

        //           //console.log(vat_amount_sales_invoice + ' -- ' + parsed_salesinvoice_vat_amount_gross + ' == ' + total_parsed_salesinvoice_vat_amount);            
        //         } //NOT disregarded
        //       });
        //     } //not EB
        //     //total_parsed_salesinvoice_vat_amount += parsed_salesinvoice_vat_amount_gross;
        //     //console.log(total_parsed_salesinvoice_vat_amount);
        //     let parsed_sales_vat_vs_import_vat = parsed_salesinvoice_vat_amount_gross - (parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat);       
        //     //total_parsed_sales_vat_vs_import_vat += parsed_sales_vat_vs_import_vat;

        //     let parsed_cominvoice_minus_salesinvoice_net_amount = parsed_cominvoice_net_amount - parsed_salesinvoice_net_amount_gross;       
        //     //total_parsed_cominvoice_minus_salesinvoice_net_amount += parsed_cominvoice_minus_salesinvoice_net_amount;

        //     let parsed_vat_check = (parsed_salesinvoice_net_amount_gross * vat_percent) - parsed_salesinvoice_vat_amount_gross;       
        //     //total_parsed_vat_check += parsed_vat_check;            

        //     // let mainRow = [declaration_date, lope_no, parsed_duties
        //     // , parsed_net_amount, parsed_adjustment, parsed_statistical_value,
        //     // parsed_import_vat, parsed_duties_vat, parsed_adjustment_vat, "",
        //     // final_com_invoice_no, parsed_cominvoice_net_amount, parsed_salesinvoice_net_amount_gross, 
        //     // parsed_salesinvoice_vat_amount_gross, parsed_sales_vat_vs_import_vat, co_invoice['invoices'].length, 
        //     // parsed_statistical_value_minus_net_amount_co_invoice, parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)),
        //     // parsed_vat_check]; // Adjust based on your columns

        //     //RULE 1 - TOTAL com. invoice net amount - TOTAL sales invoice net amount
        //     var row_color = 0;
        //     if (Math.abs(parsed_cominvoice_minus_salesinvoice_net_amount) > 100)
        //       row_color = 1;

        //     //RULE 2 - TOTAL com. invoice net amount - TOTAL ivf/xml net amount                  
        //     let diff_cominvoice_vs_xml_net_amount = parsed_statistical_value - parsed_cominvoice_net_amount; 
        //     if (Math.abs(diff_cominvoice_vs_xml_net_amount) > 100)
        //       row_color = 1;

        //     /*Check Lope no. already exists*/
        //     var filter_lope_no_exists = [];
        //     var sheetData_index = '';
        //     //if(!co_invoice['no_of_split'])
        //     //{
        //       filter_lope_no_exists = sheetData.filter(function(obj, index) {  
        //         //if(obj[1]['v'] == co_invoice['lope_no'] || obj[1] == co_invoice['lope_no'])
        //         if( (obj[1]['v'] == co_invoice['lope_no'] || obj[1] == co_invoice['lope_no']) && 
        //             (obj[19]['v'] == co_invoice['expo_no'] || obj[19] == co_invoice['expo_no'])
        //           )
        //         {
        //           sheetData_index = index;                    
        //           return true;  
        //         }         
        //         else
        //           return false;
        //       });
        //     //}
        //     /*Check Lope no. already exists*/

        //     if(filter_lope_no_exists.length == 0)
        //     {
        //       let mainRow = [ 
        //         (row_color) ? { v: declaration_date, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : declaration_date,
        //         (row_color) ? { v: lope_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : lope_no,
        //         (row_color) ? { v: parsed_duties, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_duties,
        //         (row_color) ? { v: parsed_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_net_amount,
        //         (row_color) ? { v: parsed_adjustment, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_adjustment,
        //         (row_color) ? { v: parsed_statistical_value, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_statistical_value,
        //         (row_color) ? { v: parsed_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_import_vat,
        //         (row_color) ? { v: parsed_duties_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_duties_vat,
        //         (row_color) ? { v: parsed_adjustment_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_adjustment_vat,
        //         "",
        //         (row_color) ? { v: final_com_invoice_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : final_com_invoice_no,
        //         (row_color) ? { v: parsed_cominvoice_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_cominvoice_net_amount,
        //         (row_color) ? { v: parsed_salesinvoice_net_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_salesinvoice_net_amount_gross,
        //         (row_color) ? { v: parsed_salesinvoice_vat_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_salesinvoice_vat_amount_gross,
        //         (row_color) ? { v: parsed_sales_vat_vs_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_sales_vat_vs_import_vat,
        //         (row_color) ? { v: salesinvoice_count, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : salesinvoice_count,
        //         (row_color) ? { v: parsed_statistical_value_minus_net_amount_co_invoice, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_statistical_value_minus_net_amount_co_invoice,
        //         (row_color) ? { v: parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)),
        //         (row_color) ? { v: parseFloat(parsed_vat_check.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(parsed_vat_check.toFixed(2))                                

        //         ,co_invoice['expo_no']
        //       ]; // Adjust based on your columns
                        
                
        //       // Push main row data
        //       sheetData.push(mainRow); 
        //     } //new lope no.                                    
        //     else  
        //     {  
        //       if(co_invoice['no_of_split'])
        //       {
        //         if(row_color)
        //           sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;                    
        //         else
        //           sheetData[sheetData_index][10] += '.' + final_com_invoice_no;
        //       }

        //       // let prev_parsed_salesinvoice_vat_amount_gross = sheetData[sheetData_index][13]['v'];               
        //       // parsed_sales_vat_vs_import_vat = (prev_parsed_salesinvoice_vat_amount_gross + parsed_salesinvoice_vat_amount_gross) - (parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat); 

        //       // let prev_parsed_cominvoice_net_amount = sheetData[sheetData_index][11]['v'];                    
        //       // parsed_statistical_value_minus_net_amount_co_invoice =  parsed_statistical_value - (prev_parsed_cominvoice_net_amount + parsed_cominvoice_net_amount);

        //       // sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;
              
        //       // sheetData[sheetData_index][11]['v'] += parsed_cominvoice_net_amount;
        //       // sheetData[sheetData_index][12]['v'] += parsed_salesinvoice_net_amount_gross;
        //       // sheetData[sheetData_index][13]['v'] += parsed_salesinvoice_vat_amount_gross;
        //       // sheetData[sheetData_index][14]['v'] = parsed_sales_vat_vs_import_vat;
        //       // sheetData[sheetData_index][15]['v'] += salesinvoice_count;
        //       // sheetData[sheetData_index][16]['v'] = parsed_statistical_value_minus_net_amount_co_invoice;
        //       // sheetData[sheetData_index][17]['v'] += parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2));
        //       // sheetData[sheetData_index][18]['v'] += parseFloat(parsed_vat_check.toFixed(2));
        //     } //group already existing lope no.           
        //   }
        // });

      }//NO
      else if(declaration_datas[0]['country'] == 'CH')
      {
        sheetData = [];
        // Define headers
        headers = ["Date", "Declaration No", "Duties", "Net Amount", "Adjustment", "Statistical value",
        "Import VAT (NO)", "VAT on Duties (NO)", "VAT on Adjustment", ".", 
        "Commercail Invoice Ref.", "Net Amount Commercial Invoices", "Total Net Amount Sales Invoices",
        "VAT Amount Sales Invoice", "Sales VAT vs Import VAT", "Document QTY", "Stat. Value - Net Amount", 
        "Net Amount - Total Net Amount", "VAT Check", ".", 
        "Commercail Invoice Ref.(CHF)", "Net Amount Commercial Invoices(CHF)", "Total Net Amount Sales Invoices(CHF)",
        "VAT Amount Sales Invoice(CHF)", "Sales VAT vs Import VAT(CHF)", "Stat. Value - Net Amount(CHF)", 
        "Net Amount - Total Net Amount(CHF)", "VAT Check(CHF)", "Expo No. (Temp)"]; // Adjust based on your columns
        sheetData.push(headers); // Add headers to the first row

        var currency_locale = 'fr-FR';
        var currency_style = 'CHF';
        let vat_percent = 0.081;

        $.each(co_invoices, function (idx, co_invoice) {                  
          var declaration_date = co_invoice['o_invoice_date'];
          var final_com_invoice_no = co_invoice['co_invoice_no'];
          var lope_no = co_invoice['lope_no'];

          var exchange_rate = co_invoice['exchange_rate'];

          var currency_code = co_invoice['currency'];
          if(currency_code != 'CHF')
            currency_code = 'CHF';
         
          let net_amount_co_invoice = co_invoice['com_net_amount'];
          let parsed_cominvoice_net_amount = parseAmountValue(net_amount_co_invoice, currency_code);          
          
          let statistical_value = co_invoice['statistical_value'];
          let parsed_statistical_value =  parseAmountValue(statistical_value, currency_code);

          let parsed_net_amount = parsed_statistical_value;          
          
          let parsed_duties = 0;
          let parsed_duties_vat = 0;
          let parsed_adjustment = 0;
          let parsed_adjustment_vat = 0;
          
          let vat_amount = co_invoice['import_vat'];
          let parsed_vat_amount =  parseAmountValue(vat_amount, currency_code);
          let parsed_import_vat = parsed_vat_amount;//(parsed_duties + parsed_statistical_value) * vat_percent;

          let parsed_statistical_value_minus_net_amount_co_invoice = parsed_statistical_value - parsed_cominvoice_net_amount;     

          //Converted Amounts
          let converted_net_amount = co_invoice['convert_net_amount'];
          let converted_parsed_cominvoice_net_amount = parseAmountValue(converted_net_amount, currency_code);

          let converted_parsed_statistical_value_minus_net_amount_co_invoice = parsed_statistical_value - converted_parsed_cominvoice_net_amount;       
          //Converted Amounts

          let salesinvoice_count = 0;
          let parsed_salesinvoice_net_amount_gross = 0;
          let parsed_salesinvoice_vat_amount_gross = 0; 
                 
          let converted_parsed_salesinvoice_net_amount_gross = 0;
          let converted_parsed_salesinvoice_vat_amount_gross = 0; 

          $.each(co_invoice['invoices'], function (idx, sales_invoice) {
            var show_disregarded_invoices = ($('#chk-declaration-filter-show-disregarded-invoices').prop('checked')) ? true : false;
            var show_invoice = (show_disregarded_invoices) ? true : ((sales_invoice.disregard_invoice) ? false : true);

            if(show_invoice) 
            //if(!sales_invoice.disregard_invoice) 
            {
              salesinvoice_count++;

              let net_amount_sales_invoice = sales_invoice['net_amount'];
              let parsed_salesinvoice_net_amount = parseAmountValue(net_amount_sales_invoice, currency_code);              

              let shipping_amount_sales_invoice = sales_invoice['shipping'];
              let parsed_salesinvoice_shipping_amount = parseAmountValue(shipping_amount_sales_invoice, currency_code);

              parsed_salesinvoice_net_amount_gross += (parsed_salesinvoice_net_amount + parsed_salesinvoice_shipping_amount);              
      
              let vat_amount_sales_invoice = sales_invoice['vat_amount'];
              let parsed_salesinvoice_vat_amount = parseAmountValue(vat_amount_sales_invoice, currency_code);
              parsed_salesinvoice_vat_amount_gross += parsed_salesinvoice_vat_amount;    

              //Converted Amounts
              let converted_net_amount_sales_invoice = sales_invoice['convert_net_amount'];
              let converted_parsed_salesinvoice_net_amount = parseAmountValue(converted_net_amount_sales_invoice, currency_code);

              let converted_parsed_salesinvoice_shipping_amount = 0;
              if(sales_invoice['exchange_rate'])
              {
                let converted_shipping_amount_sales_invoice = sales_invoice['shipping'];                
                converted_parsed_salesinvoice_shipping_amount = parseAmountValue(converted_shipping_amount_sales_invoice, currency_code);

                converted_parsed_salesinvoice_shipping_amount = converted_parsed_salesinvoice_shipping_amount * sales_invoice['exchange_rate'];
              }

              converted_parsed_salesinvoice_net_amount_gross += (converted_parsed_salesinvoice_net_amount + converted_parsed_salesinvoice_shipping_amount);

              let converted_vat_amount_sales_invoice = sales_invoice['convert_vat_amount'];
              let converted_parsed_salesinvoice_vat_amount = parseAmountValue(converted_vat_amount_sales_invoice, currency_code);
              converted_parsed_salesinvoice_vat_amount_gross += converted_parsed_salesinvoice_vat_amount;              
              //Converted Amounts             
            } //NOT disregarded
          });
         
          let parsed_sales_vat_vs_import_vat = parsed_salesinvoice_vat_amount_gross - (parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat);       
          
          let parsed_cominvoice_minus_salesinvoice_net_amount = parsed_cominvoice_net_amount - parsed_salesinvoice_net_amount_gross;       
          
          let parsed_vat_check = (parsed_salesinvoice_net_amount_gross * vat_percent) - parsed_salesinvoice_vat_amount_gross;  
         
          //Converted Amounts
          let converted_parsed_sales_vat_vs_import_vat = converted_parsed_salesinvoice_vat_amount_gross - (parsed_import_vat - parsed_duties_vat - parsed_adjustment_vat);       
          
          let converted_parsed_cominvoice_minus_salesinvoice_net_amount = converted_parsed_cominvoice_net_amount - converted_parsed_salesinvoice_net_amount_gross;       
          
          let converted_parsed_vat_check = (converted_parsed_salesinvoice_net_amount_gross * vat_percent) - converted_parsed_salesinvoice_vat_amount_gross;  
          //Converted Amounts

          //RULE 1 - TOTAL com. invoice net amount - TOTAL sales invoice net amount
          var row_color = 0;
          if (salesinvoice_count == 0)
            row_color = 1;

          //RULE 2 - TOTAL com. invoice net amount - TOTAL ivf/xml net amount                  
          // let diff_cominvoice_vs_xml_net_amount = parsed_statistical_value - parsed_cominvoice_net_amount; 
          // if (Math.abs(diff_cominvoice_vs_xml_net_amount) > 100)
          //   row_color = 1;

          /*Check Lope no. already exists*/
          var filter_lope_no_exists = [];
          var sheetData_index = '';
          //if(!co_invoice['no_of_split'])
          //{
            filter_lope_no_exists = sheetData.filter(function(obj, index) {  
              //if(obj[1]['v'] == co_invoice['lope_no'] || obj[1] == co_invoice['lope_no'])
              // if( (obj[1]['v'] == co_invoice['lope_no'] || obj[1] == co_invoice['lope_no']) && 
              //     (obj[19]['v'] == co_invoice['expo_no'] || obj[19] == co_invoice['expo_no'])
              //   )
              if(obj[1]['v'] == co_invoice['lope_no'])
              {
                sheetData_index = index;                    
                return true;  
              }         
              else
                return false;
            });
          //}
          /*Check Lope no. already exists*/

          if(filter_lope_no_exists.length == 0)
          {
            let mainRow = [ 
              (row_color) ? { v: declaration_date, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : declaration_date,
              (row_color) ? { v: lope_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : lope_no,
              (row_color) ? { v: parsed_duties, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_duties,
              (row_color) ? { v: parsed_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_net_amount,
              (row_color) ? { v: parsed_adjustment, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_adjustment,
              (row_color) ? { v: parsed_statistical_value, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_statistical_value,
              (row_color) ? { v: parsed_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_import_vat,
              (row_color) ? { v: parsed_duties_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_duties_vat,
              (row_color) ? { v: parsed_adjustment_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_adjustment_vat,
              "",
              (row_color) ? { v: final_com_invoice_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : final_com_invoice_no,
              (row_color) ? { v: parsed_cominvoice_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_cominvoice_net_amount,
              (row_color) ? { v: parsed_salesinvoice_net_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_salesinvoice_net_amount_gross,
              (row_color) ? { v: parsed_salesinvoice_vat_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_salesinvoice_vat_amount_gross,
              (row_color) ? { v: parsed_sales_vat_vs_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_sales_vat_vs_import_vat,
              (row_color) ? { v: salesinvoice_count, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : salesinvoice_count,
              (row_color) ? { v: parsed_statistical_value_minus_net_amount_co_invoice, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parsed_statistical_value_minus_net_amount_co_invoice,
              (row_color) ? { v: parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)),
              (row_color) ? { v: parseFloat(parsed_vat_check.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(parsed_vat_check.toFixed(2)),

              "",
              (row_color) ? { v: final_com_invoice_no, s: { font: { color: { rgb: "FF0000" }, bold: false } } } : final_com_invoice_no,
              (row_color) ? { v: converted_parsed_cominvoice_net_amount, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : converted_parsed_cominvoice_net_amount,
              (row_color) ? { v: converted_parsed_salesinvoice_net_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : converted_parsed_salesinvoice_net_amount_gross,
              (row_color) ? { v: converted_parsed_salesinvoice_vat_amount_gross, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : converted_parsed_salesinvoice_vat_amount_gross,
              (row_color) ? { v: converted_parsed_sales_vat_vs_import_vat, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : converted_parsed_sales_vat_vs_import_vat,             
              (row_color) ? { v: converted_parsed_statistical_value_minus_net_amount_co_invoice, t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : converted_parsed_statistical_value_minus_net_amount_co_invoice,
              (row_color) ? { v: parseFloat(converted_parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(converted_parsed_cominvoice_minus_salesinvoice_net_amount.toFixed(2)),
              (row_color) ? { v: parseFloat(converted_parsed_vat_check.toFixed(2)), t: 'n', s: { font: { color: { rgb: "FF0000" }, bold: false } } } : parseFloat(converted_parsed_vat_check.toFixed(2))

              ,co_invoice['lope_no']
            ]; // Adjust based on your columns
                      
              
            // Push main row data
            sheetData.push(mainRow); 
          } //new lope no.                                    
          else  
          {  
            if(co_invoice['no_of_split'])
            {
              if(row_color)
                sheetData[sheetData_index][10]['v'] += '.' + final_com_invoice_no;                    
              else
                sheetData[sheetData_index][10] += '.' + final_com_invoice_no;
            }            
          } //group already existing lope no.                  
        });
      }//other than NO
      //let parsed_cominvoice_net_amount = 0;
            
      /*TOTAL*/        
      $.each(sheetData, function (index, sheetItem) { 
        if(index > 0)
        {            
          total_parsed_duties += (typeof sheetItem[2] === 'object' && sheetItem[2] !== null) ? sheetItem[2]['v'] : sheetItem[2];         
          total_parsed_net_amount += (typeof sheetItem[3] === 'object' && sheetItem[3] !== null) ? sheetItem[3]['v'] : sheetItem[3];
          total_parsed_adjustment += (typeof sheetItem[4] === 'object' && sheetItem[4] !== null) ? sheetItem[4]['v'] : sheetItem[4];
          total_parsed_statistical_value += (typeof sheetItem[5] === 'object' && sheetItem[5] !== null) ? sheetItem[5]['v'] : sheetItem[5];
          total_parsed_import_vat += (typeof sheetItem[6] === 'object' && sheetItem[6] !== null) ? sheetItem[6]['v'] : sheetItem[6];
          total_parsed_duties_vat += (typeof sheetItem[7] === 'object' && sheetItem[7] !== null) ? sheetItem[7]['v'] : sheetItem[7];
          total_parsed_adjustment_vat += (typeof sheetItem[8] === 'object' && sheetItem[8] !== null) ? sheetItem[8]['v'] : sheetItem[8];

          total_parsed_cominvoice_net_amount += (typeof sheetItem[11] === 'object' && sheetItem[11] !== null) ? sheetItem[11]['v'] : sheetItem[11];
          total_parsed_salesinvoice_net_amount += (typeof sheetItem[12] === 'object' && sheetItem[12] !== null) ? sheetItem[12]['v'] : sheetItem[12];
          total_parsed_salesinvoice_vat_amount += (typeof sheetItem[13] === 'object' && sheetItem[13] !== null) ? sheetItem[13]['v'] : sheetItem[13];
          total_parsed_sales_vat_vs_import_vat += (typeof sheetItem[14] === 'object' && sheetItem[14] !== null) ? sheetItem[14]['v'] : sheetItem[14];
          total_document_qty += (typeof sheetItem[15] === 'object' && sheetItem[15] !== null) ? sheetItem[15]['v'] : sheetItem[15];
          total_parsed_statistical_value_minus_net_amount_co_invoice += (typeof sheetItem[16] === 'object' && sheetItem[16] !== null) ? sheetItem[16]['v'] : sheetItem[16];
          total_parsed_cominvoice_minus_salesinvoice_net_amount += (typeof sheetItem[17] === 'object' && sheetItem[17] !== null) ? sheetItem[17]['v'] : sheetItem[17];
          total_parsed_vat_check += (typeof sheetItem[18] === 'object' && sheetItem[18] !== null) ? sheetItem[18]['v'] : sheetItem[18];          

          if(declaration_datas[0]['country'] == 'CH')
          {
            converted_total_parsed_cominvoice_net_amount += (typeof sheetItem[21] === 'object' && sheetItem[21] !== null) ? sheetItem[21]['v'] : sheetItem[21];
            converted_total_parsed_salesinvoice_net_amount += (typeof sheetItem[22] === 'object' && sheetItem[22] !== null) ? sheetItem[22]['v'] : sheetItem[22];
            converted_total_parsed_salesinvoice_vat_amount += (typeof sheetItem[23] === 'object' && sheetItem[23] !== null) ? sheetItem[23]['v'] : sheetItem[23];
            converted_total_parsed_sales_vat_vs_import_vat += (typeof sheetItem[24] === 'object' && sheetItem[24] !== null) ? sheetItem[24]['v'] : sheetItem[24];            
            converted_total_parsed_statistical_value_minus_net_amount_co_invoice += (typeof sheetItem[25] === 'object' && sheetItem[25] !== null) ? sheetItem[25]['v'] : sheetItem[25];
            converted_total_parsed_cominvoice_minus_salesinvoice_net_amount += (typeof sheetItem[26] === 'object' && sheetItem[26] !== null) ? sheetItem[26]['v'] : sheetItem[26];
            converted_total_parsed_vat_check += (typeof sheetItem[27] === 'object' && sheetItem[27] !== null) ? sheetItem[27]['v'] : sheetItem[27];               
          }
        }       
      });
      /*TOTAL*/

      // Define footer
      let footers = ["Total", "", total_parsed_duties
            , total_parsed_net_amount, total_parsed_adjustment, total_parsed_statistical_value,
            total_parsed_import_vat, total_parsed_duties_vat, total_parsed_adjustment_vat, "",
            "", total_parsed_cominvoice_net_amount, total_parsed_salesinvoice_net_amount, 
            total_parsed_salesinvoice_vat_amount, total_parsed_sales_vat_vs_import_vat, total_document_qty, 
            total_parsed_statistical_value_minus_net_amount_co_invoice, total_parsed_cominvoice_minus_salesinvoice_net_amount,
            total_parsed_vat_check, "Expo No. (Temp)"]; // Adjust based on your columns

     if(declaration_datas[0]['country'] == 'CH')
     {
        footers = ["Total", "", total_parsed_duties
            , total_parsed_net_amount, total_parsed_adjustment, total_parsed_statistical_value,
            total_parsed_import_vat, total_parsed_duties_vat, total_parsed_adjustment_vat, "",
            "", total_parsed_cominvoice_net_amount, total_parsed_salesinvoice_net_amount, 
            total_parsed_salesinvoice_vat_amount, total_parsed_sales_vat_vs_import_vat, total_document_qty, 
            total_parsed_statistical_value_minus_net_amount_co_invoice, total_parsed_cominvoice_minus_salesinvoice_net_amount,
            total_parsed_vat_check, "", "", converted_total_parsed_cominvoice_net_amount, converted_total_parsed_salesinvoice_net_amount, 
            converted_total_parsed_salesinvoice_vat_amount, converted_total_parsed_sales_vat_vs_import_vat, 
            converted_total_parsed_statistical_value_minus_net_amount_co_invoice, converted_total_parsed_cominvoice_minus_salesinvoice_net_amount,
            parseFloat(converted_total_parsed_vat_check.toFixed(2)), "Expo No. (Temp)"]; // Adjust based on your columns
     }

      sheetData.push(footers); // Add footers to the last row
    }
    
    sheetData.forEach(row => row.pop());

    // Create the worksheet
    let worksheet = XLSX.utils.aoa_to_sheet(sheetData);         

    // Set header style (background color)    
    const headerFill = {
        fill: {
            fgColor: { rgb: "5a8dee" } // Background color (light blue)
        },
        font: {
            color: { rgb: "FFFFFF" },  // Text color (white)
            bold: true,                // Optional: Make text bold
            size: 12                   // Optional: Set text size
        }
    };

    // Apply the fill to the header cells
    for (let i = 0; i < headers.length; i++) {
        const cellAddress = XLSX.utils.encode_cell({ c: i, r: 0 }); // Header row
    
        if (!worksheet[cellAddress])
            worksheet[cellAddress] = {}; // Ensure the cell exists
       
        worksheet[cellAddress].s = headerFill; // Apply style    
    }

    // Set header style (background color)    
    const footerFill = {                
        font: {           
            bold: true,                // Optional: Make text bold           
        }
    };

    // Apply the footer style to the last row (footer row)
    const lastRowIndex = sheetData.length - 1; // Last row is the footer row    
    for (let col = 0; col < sheetData[lastRowIndex].length; col++) {    
      const cellAddress = XLSX.utils.encode_cell({ c: col, r: lastRowIndex }); // Last row (footer row)

      if (!worksheet[cellAddress])
        worksheet[cellAddress] = {}; // Initialize the cell if it doesn't exist

      worksheet[cellAddress].s = footerFill; // Apply the footer style to the cell
    }

    XLSX.utils.book_append_sheet(workbook, worksheet, monthyear);

    // Export the workbook
    XLSX.writeFile(workbook, clientname + '-PeriodOverview-'+ monthyear +'.xlsx');
  }
  /*NEW - EXPORT - TO - EXCEL*/

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300); 

  $(document).on('click', '#btn-control', function() {
    if($(this).parent('div').hasClass('first-declaration-export'))
    {
      $("#offcanvasDeclarationControl ul.first").show();
      $("#offcanvasDeclarationControl ul.second").hide();
    }
    else
    {
      $("#offcanvasDeclarationControl ul.second").show();
      $("#offcanvasDeclarationControl ul.first").hide();
    }
  });

  $(document).ready(function() {
    addTooltip($('.datatables-declarations > tbody > tr.accordion-button'));

    reInitializeTooltips();
  });

  function reInitializeTooltips()  
  {
    // Initialize tooltips for all elements with the data-bs-toggle attribute
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

    if (tooltipTriggerList.length > 0) 
    {
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
          var tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl); // Get the tooltip instance
          if (tooltip) {
              tooltip.dispose(); // Destroy the tooltip
          }
      });
      
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {      
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });    
    }
  }

  function addTooltip(tr)  
  {      
    // if(!trtype)
    // {
      var declaration_no = tr.find('td:nth-child(2)');
      if(declaration_no.html() == '')
      {
        var message = 'Missing Declarations';

        tr.addClass('alert-danger');
        tr.find('td').each(function (i) 
        {            
          $(this).attr('data-bs-toggle', 'tooltip');
          $(this).attr('data-bs-offset', '0,4');
          $(this).attr('data-bs-placement', 'top');
          $(this).attr('data-bs-html', 'true');
          $(this).attr('title', '<i class="bx bx-bell bx-xs"></i> <span>'+ message +'</span>');
        });
      }
    //}

    // if(trtype == 'lopeno')
    // {
    //   console.log(tr);
    //   var lope_no = tr.find('td:nth-child(2)');    
    //   if(lope_no.html() == '-')
    //   {
    //     var message = 'File Missing';

    //     tr.addClass('alert-danger');
    //     tr.find('td').each(function (i) 
    //     {            
    //       $(this).attr('data-bs-toggle', 'tooltip');
    //       $(this).attr('data-bs-offset', '0,4');
    //       $(this).attr('data-bs-placement', 'top');
    //       $(this).attr('data-bs-html', 'true');
    //       $(this).attr('title', '<i class="bx bx-bell bx-xs"></i> <span>'+ message +'</span>');
    //     });
    //   }
    // }
  }

  // $(document).on('mouseover', '.datatables-declaration-co-invoices tr.accordion-button', function() {
  //   console.log($(this));
  //   addTooltip($(this));
  // });

  function addRedRemarks(which_tab)  
  {  
    //var declaration_datas = (which_tab == 'first') ? declaration_first_datas : declaration_second_datas;
    var declaration_datas = [];
    if(which_tab === 'first')
      declaration_datas = declaration_first_datas;
    else if(which_tab === 'second')
      declaration_datas = declaration_second_datas;
    else if(which_tab === 'third')
      declaration_datas = declaration_third_datas;
    var has_red_mark = 0;

    $.each(declaration_datas, function (idx, declaration) {
      if(declaration['declaration_no'] == '')
      {
        has_red_mark++;
        return false;
      }
      else
      {
        $.each(declaration['co_invoices'], function (idx, com_invoice) {
          if(com_invoice['currency'] != 'NOK')
          {
            has_red_mark++;
            return false;
          }
          else if(com_invoice['doc_status'].toLowerCase() != 'validated')
          {
            has_red_mark++;
            return false;
          }
          else if(com_invoice['lope_no'] == '-')
          {
            has_red_mark++;
            return false;
          }
          else
          {
            $.each(com_invoice['invoices'], function (idx, invoice) {
              if(invoice['currency'] != 'NOK')
              {
                has_red_mark++;
                return false;
              }
            });
          }      
        });
      }
    });

    if(has_red_mark > 0)
    {
      $("#btn-declaration-"+ which_tab +" span").removeClass("alert-primary");
      $("#btn-declaration-"+ which_tab +" span").addClass("alert-danger");
    }    
  }

  //Filter    
  $(document).on('click', '#chk-declaration-filter-show-err-lines', function() {    
    filterErrorOnly();
  });

  function filterErrorOnly()
  {    
    if($('#chk-declaration-filter-show-err-lines').prop('checked'))   
    { 
      $('.datatables-declaration-invoices tbody tr:not(.alert-danger)').hide();

      var rows = $('.datatables-declaration-co-invoices tr.p-0.cw-1');
      console.log(rows);
      
      rows.each(function () {  
        if($(this).find('.datatables-declaration-invoices tbody tr.alert-danger').length == 0)
        {
          if($(this).find('.datatables-declaration-invoices thead tr.no-error-lines').length == 0)
            $(this).find('.datatables-declaration-invoices thead').append('<tr class="no-error-lines"><th></th><th colspan="14">No error line!!!</th></tr>');
        }
      });      
    }
    else  
    {
      $('.datatables-declaration-invoices tbody tr:not(.alert-danger)').show(); 

      $('.datatables-declaration-invoices thead tr.no-error-lines').remove();      
    }    
  }

  //Filter    
  $(document).on('click', '#chk-declaration-filter-show-disregarded-invoices', function() {    
    filterShowDisregardedInvoices();
  });

  function filterShowDisregardedInvoices()
  {    
    if($('#chk-declaration-filter-show-disregarded-invoices').prop('checked'))   
    { 
      $('.datatables-declaration-invoices tbody tr.disabled').removeClass('hide');
      $('.datatables-declaration-invoices tbody tr.disabled').addClass('show');      
    }
    else  
    {
      $('.datatables-declaration-invoices tbody tr.disabled').removeClass('show');
      $('.datatables-declaration-invoices tbody tr.disabled').addClass('hide');   
    }    
  }

  //Checkbox Select    
  $(document).on('click', 'th.dt-chk-select-all', function() {
    var chk_all = $(this).find(".form-check-input");
    var dt = $(this).closest(".datatables-declaration-invoices");
    
    dt.find(".dt-chk").prop('checked', chk_all.prop('checked')); 
    
    var table = $(this).closest("table"); 
    var rows = table.find('tbody tr');    
    if(chk_all.prop('checked'))   
      rows.addClass('selected');      
    else    
      rows.removeClass('selected');          

    checkSelectAll(dt);
  });

  $(document).on('click', '.datatables-declaration-invoices tbody tr td:first-child', function (e) {
    const $td = $(this);
    const $target = $(e.target);

    if ($target.is('input[type="checkbox"].dt-chk')) {
      // Checkbox was clicked
      console.log('Checkbox clicked!');

      var tr = $td.closest("tr");  

      if($td.prop('checked'))
        tr.addClass('selected');
      else
        tr.removeClass('selected');
    } else {
      // First td (but not checkbox) was clicked
      console.log('First td clicked!');
      
      var chk = $td.find(".form-check-input.dt-chk");
      console.log(chk);
      var tr = $td.parent("tr");
      if(tr.hasClass('selected'))
      {
        chk.prop('checked', '');   
        tr.removeClass('selected');        
      }
      else
      {
        chk.prop('checked', 'checked');   
        tr.addClass('selected');
      }  
    }

    var dt = $td.closest(".datatables-declaration-invoices");
    checkSelectAll(dt);    
  });

  // $(document).on('click', 'td.dt-chk-cell', function() {console.log("td.dt-chk-cell click");
  //   var chk = $(this).find(".form-check-input");
  //   var dt = $(this).closest(".datatables-declaration-invoices");

  //   var tr = $(this).parent("tr");  
  //   console.log(chk.prop('checked'));
  //   console.log(tr);    
  //   if(chk.prop('checked'))
  //     tr.addClass('selected');
  //   else
  //     tr.removeClass('selected');

  //   checkSelectAll(dt);    
  // });

  // $(document).on('click', '.dt-chk', function() {console.log("checkbox click");
  //   var chk = $(this);
  //   var dt = $(this).closest(".datatables-declaration-invoices");

  //   var tr = $(this).closest("tr");     
  //   if(chk.prop('checked'))
  //     tr.addClass('selected');
  //   else
  //     tr.removeClass('selected');

  //   checkSelectAll(dt);    
  // });

  function checkSelectAll(dt)
  {
    var total_chk = dt.find(".dt-chk");

    var chk_all = dt.find("th.dt-chk-select-all .form-check-input");  
    var remaining_chk = dt.find(".dt-chk:checked");

    if(remaining_chk.length == 0)
    {
      chk_all.removeClass('indeterminate');
      chk_all.prop('checked', false);   

      $(".btn-disregard-invoice").attr('disabled', 'disabled');
      $(".btn-disregard-invoice").addClass('bg-label-secondary');
      $(".btn-disregard-invoice").removeClass('bg-label-primary');
    }
    else
    {
      if(total_chk.length == remaining_chk.length)
      {
        chk_all.removeClass('indeterminate');
        $(".btn-disregard-invoice").removeAttr('disabled');
        $(".btn-disregard-invoice").addClass('bg-label-primary');
        $(".btn-disregard-invoice").removeClass('bg-label-secondary');
      }
      else
      {  
        chk_all.addClass('indeterminate');      

        $(".btn-disregard-invoice").removeAttr('disabled');
        $(".btn-disregard-invoice").addClass('bg-label-primary');
        $(".btn-disregard-invoice").removeClass('bg-label-secondary');
      }
    }
  }

  $(document).on('click', '.datatables-declaration-invoices tbody tr td:last-child', function() {  
  console.log("last child"); 
  });

  // $(document).on('click', '.datatables-declaration-invoices tbody tr td:not(:last)', function() {console.log("not last child");
  //   var tr = $(this).parent('tr');

  //   var chk = tr.find("td .form-check-input");   
  //   if(chk.prop('checked'))   
  //   {
  //     chk.prop('checked', '');
  //     chk.removeAttr('checked'); 

  //     tr.removeClass('selected');
  //   }
  //   else
  //   {
  //     chk.prop('checked', 'checked');   

  //     tr.addClass('selected');        
  //   }

  //   var dt = $(this).closest(".datatables-declaration-invoices");
  //   checkSelectAll(dt);   
  // });

  //Disregard Invoice - other    
  // $(document).on('click', '.declaration-invoice-disregard-switch', function() {
  //   var id = $(this).attr('id');
  //   if(id == 'declaration-invoice-disregard-switch-other')    
  //     $('.declaration-invoice-disregard-compose-message').show();    
  //   else  
  //     $('.declaration-invoice-disregard-compose-message').hide(); 
  // });
  
  // $(document).on('click', '#declaration-invoice-disregard-reason', function() {    
  //   if($(this).val() == 'other')    
  //     $('.declaration-invoice-disregard-compose-message').show();    
  //   else  
  //     $('.declaration-invoice-disregard-compose-message').hide(); 
  // });
  

  //Cargo Declaration Files Click    
  $(document).on('click', '.btn-cargo-declaration-files', function () {    
    var data = $(this).data();
    var import_vat_id = data['import_vat_id'];
   
    var cargo_declaration_files_url = `${baseUrl}cargo-declaration-files/${import_vat_id}`;
         
    window.open(cargo_declaration_files_url, '_blank');//, 'noreferrer'
  });

  // Disregard invoice
  $(document).on('click', '.btn-disregard-invoice', function () {    
    var btn_disregard_invoice = $(this);
    var data = btn_disregard_invoice.data();

    var disregard_invoice_text = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'disregard' : 'enable';
    var disregard_invoice_suffix = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'ed' : 'd';
    var disregard_invoice_text_capitalize = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'Disregard' : 'Enable';
    var disregard_invoice_text_after = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'Enable' : 'Disregard';
    var disregard_invoice_text_loading = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'Disregarding' : 'Enabling';
   
    var vat_reg_id = $("#vat_reg_id").val();    
    //var vat_reg_period = data['vat_reg_period'];  

    // var product_type = data['product_type'];
    // var product_type_text = (product_type == 1) ? 'VAT Returns' : 'Import Reconciliation';
    // var accordion_name = (product_type == 1) ? 'All' : 'ImportReconciliation';
    
    var selected_invoices_id = $.map($('#navs-declaration-'+ data['tab_name'] +' .form-check-input.dt-chk:checked'), function(c){
                                  return c.value; 
                              });
    var selected_invoices = $.map($('#navs-declaration-'+ data['tab_name'] +' .form-check-input.dt-chk:checked'), function(c){      
                              //return {id: c.value, invoice_no: $(c).data('invoice_no'), invoice_date: $(c).data('invoice_date')};       
                              return $(c).data('invoice_no');       
                            });
console.log(selected_invoices);
console.log(selected_invoices_id);
    Swal.fire({
      title: 'Are you sure?',
      //text: "You want to "+ disregard_invoice_text +" the "+ vat_reg_period +" period for " + product_type_text + "!",
      text: "You want to "+ disregard_invoice_text +" the selected invoices!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ disregard_invoice_text_capitalize +'!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
    
      if (result.value) {
        
        btn_disregard_invoice.attr('disabled', 'disabled');
        btn_disregard_invoice.html(//'<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' + 
            '<span><i class="bx bx-list-minus"></i> ' +
            disregard_invoice_text_loading + '...</span>');
        
        var filldata = {         
          "invoice_id": selected_invoices_id,
          "invoice_no": selected_invoices,
          "invoice_date": selected_invoices[0]['invoice_date'],
          //"invoice_name": 'sales',
          "invoice_name": data['invoice_name'],
          "tab_name": data['tab_name'],
          "disregard": data['is_disregard']
        };
        fillDisregardModal(filldata);        
            
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled ' + disregard_invoice_text_capitalize + ' Invoice :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
     
    }); 
  });

  // Enable invoice - Single
  $(document).on('click', '.btn-enable-declaration-invoice', function () {    
    var btn_enable_invoice = $(this);
    var data = btn_enable_invoice.data();

    var invoice_id = data['invoice_id'];
    var invoice_no = data['invoice_no'];
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];
    var is_enable = data['enable'];

    Swal.fire({
      title: 'Are you sure?',
      text: "You want to enable the invoice!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, enable!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_enable_invoice.attr('disabled', 'disabled');
        btn_enable_invoice.html('<i class="spinner-border me-1"></i>');

        $.ajax({
            url: `${declarationInvoiceUrl}${invoice_id}/disregard`,
            type: 'POST',        
            data: {invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : $("#vat_reg_id").val(), 
              tab_name: which_tab, is_enable: is_enable},        
            success: function (result) {
             
              if(result)    
              {                 
                btn_enable_invoice.removeAttr('disabled');
                btn_enable_invoice.html('<i class="bx bx-list-check"></i>');
              
                var declaration_datas = drawDtTable(result, 'declaration');
                reloadDeclarations(declaration_datas);
              
                var swal_text = 'Declaration';          
                if(invoice_name == 'com')
                  swal_text = 'Commercial invoice';
                else if(invoice_name == 'sales')
                  swal_text = 'Sales invoice';
               
                Swal.fire({
                  icon: 'success',
                  title: 'Invoice enabled!',
                  text: swal_text + ' has been enabled.',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
              }
            },
            error: function (error) {
              console.log(error);
            }
          });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled enable invoice :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // Disregard invoice/ Comment invoice - Single
  $(document).on('click', '.btn-disregard-declaration-invoice', function () {    
    var invoice_line = $(this);
    var data = invoice_line.data();

    fillDisregardModal(data);
  });

  function fillDisregardModal(data)
  {    
    $("#invoice_vat_reg_id").val($("#vat_reg_id").val());
    $("#invoice_id").val(data['invoice_id']);
    $("#invoice_no").val(data['invoice_no']);
    $("#invoice_name").val(data['invoice_name']);
    $("#month_year").val(moment(data['invoice_date']).format('MM-YYYY'));    
    $("#tab_name").val(data['tab_name']);
    $("#is_disregard").val(data['disregard']);

    if(data['disregard_type'])
    {
      $("#disregard_type").val(data['disregard_type']);

      if(data['disregard_type'] == 'ivf')
      {
        var invoice_nos = data['invoice_no'].split(", ");
        var invoice_ids = data['group_invoice_id'].split("***");

        var checkbox_div = '';
        $.each(invoice_nos, function(index, value) {
          checkbox_div += '<div class="form-check mt-3 col-3">' +
                      '<input class="form-check-input" type="checkbox" value="'+ invoice_ids[index] +'" name="chk_invoice_no[]" id="chk_invoice_no" />' +
                      '<label class="form-check-label" for="chk_invoice_no">' +
                        value +
                      '</label>' +
                    '</div>';
        });

        $(".declaration-invoice-disregard-invoices").html(checkbox_div);
        $(".declaration-invoice-disregard-invoices-select").show();
      }
      else
        $(".declaration-invoice-disregard-invoices-select").hide();
    }
    else
    {
      $("#disregard_type").val('');
      $(".declaration-invoice-disregard-invoices-select").hide();
    }

    var sub_headline = 'Note: ' + moment(data['invoice_date']).format('MMM') +  ' ' + $("#org_no").val();
    $("#modalDeclarationInvoiceDisregard .onboarding-info").html(sub_headline);

    var prefix = (data['disregard'] == "1") ? "Disregard " : "Add ";
    var suffix = (data['disregard'] == "1") ? "" : " Comment";
    var heading = 'Declaration';
    if(data['invoice_name'] == 'com')
    {
      if(data['disregard_type'] == 'ivf')
        heading = 'Wrong Commercial Invoice (XML)';
      else if(data['disregard_type'] == 'lopeno')
        heading = 'Lope no.';
      else
        heading = 'Commercial Invoice';
    }
    else if(data['invoice_name'] == 'sales')
      heading = 'Sales Invoice';   

    $("#modalDeclarationInvoiceDisregard .onboarding-title").html(prefix + heading + suffix);

    if(data['invoice_name'] == 'com')
    {
      if(data['disregard_type'])
        $(".declaration-com-invoice-visible-switch").css('visibility', 'hidden');
      else
      {
        if(prefix == 'Add')
          $(".declaration-com-invoice-visible-switch").css('visibility', 'visible');
        else
          $(".declaration-com-invoice-visible-switch").css('visibility', 'hidden');
      }

      $("#declaration-invoice-disregard-reason optgroup[label='Sales Invoice']").attr('disabled', 'disabled');
      $("#declaration-invoice-disregard-reason optgroup[label='Commercial Invoice']").removeAttr('disabled');
    }
    else if(data['invoice_name'] == 'sales')
    {
      $(".declaration-com-invoice-visible-switch").css('visibility', 'hidden');

      $("#declaration-invoice-disregard-reason optgroup[label='Sales Invoice']").removeAttr('disabled');
      $("#declaration-invoice-disregard-reason optgroup[label='Commercial Invoice']").attr('disabled', 'disabled');
    }
    else
    {
      $(".declaration-com-invoice-visible-switch").css('visibility', 'visible');

      $("#declaration-invoice-disregard-reason optgroup[label='Sales Invoice']").removeAttr('disabled');
      $("#declaration-invoice-disregard-reason optgroup[label='Commercial Invoice']").removeAttr('disabled');
    }

    $("#declaration-invoice-disregard-reason").val('');
    $("#declaration-invoice-disregard-comment-editor .ql-editor").html('');    

    $("#declaration-invoice-visible-switch").attr('checked', 'checked');
    $("#declaration-invoice-visible-switch").parent('label.switch').find('span.switch-label').html('Public');
    $("#comment_visiblity").val('Public');
    if(data['insert_type'] == 'edit')
    {
      $("#declaration-invoice-disregard-reason").val(data['comment_reason']);
      $("#declaration-invoice-disregard-comment-editor .ql-editor").html(data['comment']);   

      if(data['comment_visiblity'] == 0 || data['comment_visiblity'] == 1)
      {      
        $("#declaration-invoice-visible-switch").attr('checked', 'checked');
        $("#declaration-invoice-visible-switch").parent('label.switch').find('span.switch-label').html('Public');

        $("#comment_visiblity").val('Public');
      }
      else
      { 
        $("#declaration-invoice-visible-switch").removeAttr('checked');       
        $("#declaration-invoice-visible-switch").parent('label.switch').find('span.switch-label').html('Team');   
        $("#comment_visiblity").val('Team');
      }

      //$("#declaration-invoice-visible-switch").val(data['comment_visiblity']);
    }   

    $('#modalDeclarationInvoiceDisregard').modal('show');
  }

  // Disregard invoice - Save  
  $(document).on("submit", ".frm-declaration-invoice-disregard-comment", function(event) {  
    event.preventDefault();

    var selected_reason = $("#declaration-invoice-disregard-reason").val();

    if($(this).find(".ql-editor").html().replace( /(<([^>]+)>)/ig, '') == "")
    {      
      Swal.fire({
        title: 'Error',
        text: 'Please type reason for disregarding invoice',
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
      $(this).find(".ql-editor").focus();
      return false;
    }
    else
    {
      var formId = $(this).attr('id');
      //var data = $(this).data();     
      //var client_id = data['client_id']; 
      var invoice_id = (String($('#invoice_id').val()).indexOf(',') != -1) ? 0 : $('#invoice_id').val();      
       
      $("#declaration-invoice-disregard-comment-quill").val($(this).find(".ql-editor").html());

      var formData = new FormData(this);         
                   
      var btn_comment_save = $("#" + formId + " #btn-declaration-invoice-disregard-comment-save");
      btn_comment_save.attr('disabled', 'disabled');
      btn_comment_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Saving...');
      
      $.ajax({
        url: `${declarationInvoiceUrl}${invoice_id}/disregard`,
        type: 'POST',
        dataType: "JSON",
        data: formData,
        processData: false,
        contentType: false,
        success: function (result) {
         
          if(result)    
          {                 
            btn_comment_save.removeAttr('disabled');
            btn_comment_save.html('Saved');
            btn_comment_save.removeClass('disabled');

            var which_tab = result['tab_name'];
            $("#btn_"+ which_tab +"_disregard_invoice").html();

            if(invoice_id == 0)
              $('#btn_'+ which_tab +'_disregard_invoice').html(//'<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' + 
              '<span><i class="bx bx-list-minus"></i> Disregard invoice</span>');
            
                        
            var declaration_datas = drawDtTable(result, 'declaration');
            reloadDeclarations(declaration_datas);
           
            var swal_title = 'Comments saved';      
            var swal_text = 'Declaration comment';          
            if($('#invoice_name').val() == 'com')
            {
              if($("#is_disregard").val() == "1")
              {
                swal_title = 'Disregarded and reason saved';  
                if($("#disregard_type").val() == "ivf")                
                  swal_text = 'XML Commercial invoice has been disregarded and the reason ';                
                else if($("#disregard_type").val() == "lopeno")                
                  swal_text = 'Lope no. has been disregarded and the reason ';  
                else
                  swal_text = 'Commercial invoice has been disregarded and the reason ';
              }
              else
                swal_text = 'Commercial invoice comment';
            }
            else if($('#invoice_name').val() == 'sales')
            {
              if($("#is_disregard").val() == "1")
              {
                swal_title = 'Disregarded and reason saved';  
                swal_text = 'Sales invoice has been disregarded and the reason ';
              }
              else
                swal_text = 'Sales invoice comment';
            }
           
            //Clear Modal Values
            $("#declaration-invoice-disregard-reason").val('');
            $("#declaration-invoice-disregard-comment-editor").find(".ql-editor").html("");

            if($("#declaration-invoice-visible-switch").length > 0)
              $("#declaration-invoice-visible-switch").val(1);

            $('#modalDeclarationInvoiceDisregard').modal('hide');

            Swal.fire({
              icon: 'success',
              title: swal_title +'!',
              text: swal_text + ' has been saved.',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        },
        error: function (error) {
          console.log(error);
        }
      });
    }  
  });

  // Invoice - Delete
  $(document).on('click', '.btn-declaration-invoice-delete-comment', function () {    
    var btn_delete_comment = $(this);
    var data = btn_delete_comment.data();

    var invoice_id = data['invoice_id'];
    var invoice_no = data['invoice_no'];
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];

    Swal.fire({
      title: 'Are you sure?',
      text: "You want to delete the comment!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_delete_comment.attr('disabled', 'disabled');
        btn_delete_comment.html('<i class="spinner-border me-1"></i>');

        $.ajax({
            url: `${declarationInvoiceUrl}${invoice_id}/deletecomment`,
            type: 'DELETE',        
            data: {invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : $("#vat_reg_id").val(), tab_name: which_tab},        
            success: function (result) {
             
              if(result)    
              {                 
                btn_delete_comment.removeAttr('disabled');
                btn_delete_comment.html('<i class="bx bx-comment-minus"></i>');
                        
                
                var declaration_datas = drawDtTable(result, 'declaration');
                reloadDeclarations(declaration_datas);

                var swal_text = 'Declaration comment';          
                if($('#invoice_name').val() == 'com')
                  swal_text = 'Commercial invoice comment';
                else if($('#invoice_name').val() == 'sales')
                  swal_text = 'Sales invoice comment ';
               
                Swal.fire({
                  icon: 'success',
                  title: 'Comment deleted!',
                  text: swal_text + ' has been deleted.',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
              }
            },
            error: function (error) {
              console.log(error);
            }
          });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled delete comment :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });   
  });

  // Re-match com. invoice - open modal
  $(document).on('click', '.btn-rematch-declaration-cominvoice', function () {    
    var btn_rematch_cominvoice = $(this);
    var data = btn_rematch_cominvoice.data();

    var rematch_cominvoice_text = (btn_rematch_cominvoice.attr('title') == 'Rematch com. invoice') ? 'rematch' : 'enable';
    var rematch_cominvoice_suffix = (btn_rematch_cominvoice.attr('title') == 'Rematch com. invoice') ? 'ed' : 'd';
    var rematch_cominvoice_text_capitalize = (btn_rematch_cominvoice.attr('title') == 'Rematch com. invoice') ? 'Rematch' : 'Enable';
    var rematch_cominvoice_text_after = (btn_rematch_cominvoice.attr('title') == 'Rematch com. invoice') ? 'Enable' : 'Rematch';
    var rematch_cominvoice_text_loading = (btn_rematch_cominvoice.attr('title') == 'Rematch com. invoice') ? 'Rematching' : 'Enabling';
   
    var vat_reg_id = $("#vat_reg_id").val();    
   
    var selected_invoice_id = data['invoice_id'];
    var selected_invoice = data['invoice_no'];
    var selected_invoice_date = data['invoice_date'];
   
    Swal.fire({
      title: 'Are you sure?',   
      text: "You want to "+ rematch_cominvoice_text +" the selected invoice!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ rematch_cominvoice_text_capitalize +'!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
    
      if (result.value) {
        
        btn_rematch_cominvoice.attr('disabled', 'disabled');
        btn_rematch_cominvoice.html(
            '<span><i class="bx bx-list-plus"></i> ' +
            rematch_cominvoice_text_loading + '...</span>');
        
        var filldata = {         
          "invoice_id": selected_invoice_id,
          "invoice_no": selected_invoice,
          "invoice_date": selected_invoice_date,
          "invoice_name": 'com',
          "tab_name": data['tab_name'],
          "no_of_split": data['no_of_split']          
        };
        fillRematchModal(filldata);        
            
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        btn_rematch_cominvoice.html(
            '<span><i class="bx bx-list-plus"></i> Rematch com. invoice</span>');

        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled ' + rematch_cominvoice_text_capitalize + ' Invoice :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
     
    }); 
  });

  // Remove Re-match com. invoice
  $(document).on('click', '.btn-remove-rematch-declaration-cominvoice', function () {    
    var btn_remove_rematch_cominvoice = $(this);
    var data = btn_remove_rematch_cominvoice.data();

    var invoice_id = data['invoice_id'];
    var invoice_no = data['invoice_no'];
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];   
    var group_invoice_ids = data['group_lope_no'];
    
    Swal.fire({
      title: 'Are you sure?',
      text: (data['unmatch']) ? "You want to unmatch for the selected com. invoice!" : "You want to remove the re-match com. invoice!",       
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: (data['unmatch']) ? 'Yes, Unmatch!' : 'Yes, remove!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_remove_rematch_cominvoice.attr('disabled', 'disabled');
        btn_remove_rematch_cominvoice.html('<i class="spinner-border me-1"></i>');

        btn_remove_rematch_cominvoice.closest('tr.accordion-button').addClass('disabled');

        $.ajax({
            url: `${declarationInvoiceUrl}${invoice_id}/rematch`,
            type: 'DELETE',        
            data: {group_invoice_ids: group_invoice_ids, invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : $("#vat_reg_id").val(), tab_name: which_tab
              , unmatch: ((data['unmatch']) ? data['unmatch'] : null )},        
            success: function (result) {
             
              if(result)    
              {                 
                btn_remove_rematch_cominvoice.removeAttr('disabled');
                if(data['unmatch'])
                  btn_remove_rematch_cominvoice.html('<i class="bx bx-list-minus"></i>');
                else
                  btn_remove_rematch_cominvoice.html('<i class="bx bx-folder-minus"></i>');
                               
                var declaration_datas = drawDtTable(result, 'declaration');
                reloadDeclarations(declaration_datas);               
               
                btn_remove_rematch_cominvoice.closest('tr.accordion-button').removeClass('disabled');

                var swal_text = (data['unmatch']) ? 'Commercial invoice unmatch' : 'Commercial invoice re-match';          
               
                if(!data['unmatch'])
                  Swal.fire({
                    icon: 'success',
                    title: 'Commercial invoice re-match removed!',
                    text: swal_text + ' has been removed.',
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  });
              }
            },
            error: function (error) {
              console.log(error);
            }
          });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: (data['unmatch']) ? 'Cancelled unmatch commercial invoice :)' : 'Cancelled re-match remove :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });   
  });  

  // Retain Lope no.
  $(document).on('click', '.btn-retain-lopeno', function () {    
    var btn_retain_lopeno = $(this);
    var data = btn_retain_lopeno.data();

    var invoice_id = data['invoice_id'];
    var invoice_no = data['invoice_no'];
    var lope_no = data['lope_no'];
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];
    var disregard_type = data['disregard_type'];
    var is_retain = data['retain'];    
    
    var tr = $(this).closest("tr")
    tr.addClass("loading");
    tr.find("td").attr('disabled', 'disabled');

    Swal.fire({
      title: 'Are you sure?',
      text: "You want to retain the lope no.!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, retain!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_retain_lopeno.attr('disabled', 'disabled');
        btn_retain_lopeno.html('<i class="spinner-border me-1"></i>');

        $.ajax({                
            url: `${declarationInvoiceUrl}${invoice_id}/disregard`,
            type: 'POST',        
            data: {invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : $("#vat_reg_id").val(), 
              tab_name: which_tab, is_enable: is_retain, disregard_type: disregard_type, lope_no: lope_no},        
            success: function (result) {
             
              if(result)    
              {                 
                btn_retain_lopeno.removeAttr('disabled');
                btn_retain_lopeno.html('<i class="bx bx-add-to-queue"></i>');               
                
                var declaration_datas = drawDtTable(result, 'declaration');
                reloadDeclarations(declaration_datas);

                var swal_text = 'Lope No.';          
               
                Swal.fire({
                  icon: 'success',
                  title: 'Lope No. retained!',
                  text: swal_text + ' has been retained.',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then(function (result) {
                  if (result.value)
                  {
                    tr.removeClass("loading");
                    tr.find("td").removeAttr('disabled');
                  }
                });
              }
            },
            error: function (error) {
              console.log(error);
            }
          });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled lope no. retain :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });   
  });

  // Retain Com. Invoice
  $(document).on('click', '.btn-retain-cominvoice', function () {    
    var btn_retain_cominvoice = $(this);
    var data = btn_retain_cominvoice.data();

    var invoice_id = data['invoice_id'];
    var invoice_no = data['invoice_no'];
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name']; 
    var is_retain = data['retain'];       

    var tr = $(this).closest("tr")
    tr.addClass("loading");
    tr.find("td").attr('disabled', 'disabled');

    Swal.fire({
      title: 'Are you sure?',
      text: "You want to retain the commercial invoice!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, retain!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_retain_cominvoice.attr('disabled', 'disabled');
        btn_retain_cominvoice.html('<i class="spinner-border me-1"></i>');

        $.ajax({                
            url: `${declarationInvoiceUrl}${invoice_id}/disregard`,
            type: 'POST',        
            data: {invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : $("#vat_reg_id").val(), 
              tab_name: which_tab, is_enable: is_retain}, 
            success: function (result) {
             
              if(result)    
              {                 
                btn_retain_cominvoice.removeAttr('disabled');
                btn_retain_cominvoice.html('<i class="bx bx-add-to-queue"></i>');               
                
                var declaration_datas = drawDtTable(result, 'declaration');
                reloadDeclarations(declaration_datas);

                var swal_text = 'Commercial invoice';          
               
                Swal.fire({
                  icon: 'success',
                  title: 'Commercial invoice retained!',
                  text: swal_text + ' has been retained.',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then(function (result) {
                  if (result.value)
                  {
                    tr.removeClass("loading");
                    tr.find("td").removeAttr('disabled');
                  }  
                });
              }
            },
            error: function (error) {
              console.log(error);
            }
          });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled commercial invoice retain :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });   
  });  

  function fillRematchModal(data)
  {    
    $("#rematch_invoice_vat_reg_id").val($("#vat_reg_id").val());
    $("#rematch_invoice_id").val(data['invoice_id']);
    $("#rematch_invoice_no").val(data['invoice_no']);
    $("#rematch_invoice_name").val(data['invoice_name']);
    $("#rematch_month_year").val(moment(data['invoice_date']).format('MM-YYYY'));    
    $("#rematch_tab_name").val(data['tab_name']);
  
    var sub_headline = 'Note: ' + moment(data['invoice_date']).format('MMM') +  ' ' + $("#org_no").val() + 
                        ' - Com. Invoice No: ' + data['invoice_no'];
    $("#modalDeclarationComInvoiceRematch .onboarding-info").html(sub_headline);
  
    var prefix = "Re-match ";
    var suffix = "";
    var heading = 'Declaration';
    if(data['invoice_name'] == 'com')
      heading = 'Commercial Invoice';
    else if(data['invoice_name'] == 'sales')
      heading = 'Sales Invoice';   

    $("#modalDeclarationComInvoiceRematch .onboarding-title").html(prefix + heading + suffix);

    var com_invoices = [];
    if(data['tab_name'] == 'first')
      com_invoices = declaration_first_datas[0]['modal_co_invoices'];
    else if(data['tab_name'] == 'second')
      com_invoices = declaration_second_datas[0]['modal_co_invoices'];
    else if(data['tab_name'] == 'third')
      com_invoices = declaration_third_datas[0]['modal_co_invoices'];

    var options = '<option value="" selected="selected">--Select Com. Invoices--</option>'; 

    var option_current_period_group = false;
    var option_other_period_group = false;

    var options_current_period_group = '<optgroup label="--- Current Period ---">';
    var options_other_period_group = '<optgroup label="--- Other Periods ---">';

    var option_count = 0;           
    $.each(com_invoices, function (index, com_invoice) { 
      
      if(!com_invoice['disabled'])
      {   
        if(data['invoice_id'] == com_invoice['id'])
        {

        }
        else
        {    
          if(com_invoice['other_period'])          
            options_other_period_group += '<option value="'+com_invoice['id']+'">'+com_invoice['co_invoice_no']+'</option>';           
          else 
            options_current_period_group += '<option value="'+com_invoice['id']+'">'+com_invoice['co_invoice_no']+'</option>';
         
          option_count = option_count + 1;
        }            
      }
    });
    options_current_period_group += '</optgroup>';
    options_other_period_group += '</optgroup>';

    options += options_current_period_group + options_other_period_group;
    

    $('#declaration-cominvoice-rematch').html(options); 
    if(option_count == 0)
    {
      $('#declaration-cominvoice-rematch').attr('disabled', 'disabled'); 
      $('#btn-declaration-cominvoice-rematch-save').attr('disabled', 'disabled'); 
    }
    else
    {
      $('#declaration-cominvoice-rematch').removeAttr('disabled'); 
      $('#btn-declaration-cominvoice-rematch-save').removeAttr('disabled'); 
    }
    
    var no_of_split = data['no_of_split'];
    if(no_of_split)
    {
      $('.declaration-cominvoice-rematch-split #no_of_split').prop('required', false);
      $('.declaration-cominvoice-rematch-split #no_of_split').val('');
      //$('.declaration-cominvoice-rematch-split').hide();
    }
    else
    {
      if((String(data['invoice_no']).indexOf('+') !== -1) 
        || (String(data['invoice_no']).indexOf('/') !== -1) 
        || ((String(data['invoice_no']).indexOf('-') !== -1) && (String(data['invoice_no']).indexOf('SPG') === -1))                
      )
      { 
        if(String(data['invoice_no']).indexOf('SF') !== -1)
        {
          $('.declaration-cominvoice-rematch-split #no_of_split').prop('required', false);
          $('.declaration-cominvoice-rematch-split #no_of_split').val('');
          //$('.declaration-cominvoice-rematch-split').hide();
        }
        else
        {
          if(data['invoice_no'] == '-')         
          {
            $('.declaration-cominvoice-rematch-split #no_of_split').prop('required', false);
            $('.declaration-cominvoice-rematch-split #no_of_split').val('');
            //$('.declaration-cominvoice-rematch-split').hide();
          }
          else
          {
            var occurrences = (String(data['invoice_no']).indexOf('-') !== -1) ? (String(data['invoice_no']).match(/-/g) || []).length : 0;

            if(occurrences == 0 || occurrences == 1)       
            {
              $('.declaration-cominvoice-rematch-split #no_of_split').prop('required', true);
              $('.declaration-cominvoice-rematch-split #no_of_split').val('');
              //$('.declaration-cominvoice-rematch-split').show();
            }
            else
            {console.log("NOOO oocurance ");
              $('.declaration-cominvoice-rematch-split #no_of_split').prop('required', false);
              $('.declaration-cominvoice-rematch-split #no_of_split').val('');
              //$('.declaration-cominvoice-rematch-split').hide();
            }
          }
        }
      }
      else
      {
        $('.declaration-cominvoice-rematch-split #no_of_split').prop('required', false);
        $('.declaration-cominvoice-rematch-split #no_of_split').val('');
        //$('.declaration-cominvoice-rematch-split').hide();
      }
    }

    $('.declaration-cominvoice-rematch-split').show();
    $('#modalDeclarationComInvoiceRematch').modal('show');
  }

  // Re-match com. invoice MODAL - close
  $(document).on("hide.bs.modal", "#modalDeclarationComInvoiceRematch", function(event) {
    console.log("modal close");
    $(".btn-rematch-declaration-cominvoice").html(            
      '<span><i class="bx bx-list-plus"></i> Rematch com. invoice</span>'
    );
  });

  // Re-match com. invoice - save
  $(document).on("submit", ".frm-declaration-cominvoice-rematch", function(event) {  
    event.preventDefault();

    //var selected_reason = $("#declaration-invoice-disregard-reason").val();

    var formId = $(this).attr('id');   
    var invoice_id = (String($('#rematch_invoice_id').val()).indexOf(',') != -1) ? 0 : $('#rematch_invoice_id').val();      
     
    //$("#declaration-invoice-disregard-comment-quill").val($(this).find(".ql-editor").html());

    var formData = new FormData(this);         
                 
    var btn_rematch_save = $("#" + formId + " #btn-declaration-cominvoice-rematch-save");
    btn_rematch_save.attr('disabled', 'disabled');
    btn_rematch_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
            'Re-matching...');
    
    $.ajax({
      url: `${declarationInvoiceUrl}${invoice_id}/rematch`,
      type: 'POST',
      dataType: "JSON",
      data: formData,
      processData: false,
      contentType: false,
      success: function (result) {
       
        if(result)    
        {                 
          btn_rematch_save.removeAttr('disabled');
          btn_rematch_save.html('Re-match');
          btn_rematch_save.removeClass('disabled');

          var which_tab = result['tab_name'];
          $(".btn-rematch-declaration-cominvoice").html(            
            '<span><i class="bx bx-list-plus"></i> Rematch com. invoice</span>'
          );
          
          
          var declaration_datas = drawDtTable(result, 'declaration');
          reloadDeclarations(declaration_datas);          
        
          var swal_text = 'Commercial invoice';          
         
          //Clear Modal Values          
          var com_invoices = declaration_datas['declaration_'+ which_tab +'_datas'][0]['modal_co_invoices'];          
          $("#declaration-cominvoice-rematch").html('');

          var options = '<option value="" selected="selected">--Select Com. Invoices--</option>';            
          $.each(com_invoices, function (index, com_invoice) { 
            var com_invoice_disabled = (com_invoice['disabled']) ? 'disabled="disabled"' : '';
            options += '<option value="'+com_invoice['id']+'" '+ com_invoice_disabled +'>'+com_invoice['co_invoice_no']+'</option>';
          });
          $('#declaration-cominvoice-rematch').html(options); 
          
          $('#modalDeclarationComInvoiceRematch').modal('hide');

          Swal.fire({
            icon: 'success',
            title: 'Com. Invoice rematched!',
            text: swal_text + ' has been rematched.',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      },
      error: function (error) {
        console.log(error);
      }
    });  
  });

  //Download/View sales invoice PDF
  $(document).on('click', '.btn-declaration-invoice-download-pdf', function () {
      var btn_download_sales_invoice_pdf = $(this);
      var data = btn_download_sales_invoice_pdf.data();

      var file_type = 'importreconciliationfiles'; 
      var file_id = data['invoice_xml_id'];
      var invoice_no = data['invoice_no'];
          
      btn_download_sales_invoice_pdf.html('<span><i class="bx bxs-file-pdf text-danger"></i> Downloading...</span>');

      $.ajax({      
        url: `${fileUrl}${file_id}/download`,
        type: 'GET',       
        data: {file_type: file_type},  
        xhrFields: {
          responseType: 'blob'      
        },
        success: function (data) {  
          //console.log(data);
          //if(data)        
          btn_download_sales_invoice_pdf.html('<span><i class="bx bxs-file-pdf text-danger"></i> View PDF</span>');

          //window.open(data, '_blank');
          var blob=new Blob([data]);      
          var link=document.createElement('a');
          link.href=window.URL.createObjectURL(blob);
          link.download= invoice_no + ".pdf";
          link.click();
        },
        error: function (err) {
          console.log(err);     
        }
      });
  });

  // Add FTP Sales invoice XML - open modal
  $(document).on('click', '.btn-declaration-invoice-create', function () {    
    var btn_create_salesinvoice = $(this);
    var data = btn_create_salesinvoice.data();
    
    var vat_reg_id = $("#vat_reg_id").val();    
   
    var selected_invoice_id = data['invoice_id'];
    var selected_invoice = data['invoice_no'];
    var selected_invoice_date = data['invoice_date'];
    var month_year = data['month_year'];
    //var selected_invoice_xml_id = data['invoice_xml_id'];
    //var edit_from = data['edit_from'];    
     
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];

    Swal.fire({
      title: 'Are you sure?',   
      text: "You want to create the invoice!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Create!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
    
      if (result.value) {
        
        btn_create_salesinvoice.attr('disabled', 'disabled');
        btn_create_salesinvoice.html(
            '<span><i class="bx bx-plus-medical"></i> Creating...</span>');
                
        $('#modalDeclarationFtpSalesInvoiceEdit .onboarding-title').html('Create FTP Sales Invoice');
                
        $('#modalDeclarationFtpSalesInvoiceEdit #declaration_ftpsalesinvoice').hide();        
        $('#modalDeclarationFtpSalesInvoiceEdit #declaration_ftpsalesinvoice').html('');
        $("#modalDeclarationFtpSalesInvoiceEdit .sk-bounce").show();
        $('#modalDeclarationFtpSalesInvoiceEdit').modal('show');
        
        $.ajax({          
          url: `${declarationInvoiceUrl}${selected_invoice_id}/edit`,
          data: { vat_reg_id: vat_reg_id, invoice_no: selected_invoice, invoice_date: selected_invoice_date
            , invoice_name: invoice_name, tab_name: which_tab, month_year: month_year},  
          type: 'GET',
          success: function (result) {
            //console.log(result);

            $("#modalDeclarationFtpSalesInvoiceEdit .sk-bounce").hide();
            $('#modalDeclarationFtpSalesInvoiceEdit #declaration_ftpsalesinvoice').html(result.sales_invoice_datas);
            $('#modalDeclarationFtpSalesInvoiceEdit #declaration_ftpsalesinvoice').show();
            
            btn_create_salesinvoice.html(
            '<span><i class="bx bx-plus-medical"></i> Add</span>');
          },
          error: function(jqXHR, textStatus, errorThrown){
            console.log('error: ' + textStatus);
          }
        });

      } else if (result.dismiss === Swal.DismissReason.cancel) {
        btn_create_salesinvoice.html(
            '<span><i class="bx bx-plus-medical"></i> Add</span>');

        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled Create Invoice :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
     
    }); 
  });

  // Edit FTP Sales invoice XML - open modal
  $(document).on('click', '.btn-declaration-invoice-edit', function () {    
    var btn_edit_salesinvoice = $(this);
    var data = btn_edit_salesinvoice.data();
    
    var vat_reg_id = $("#vat_reg_id").val();    
   
    var selected_invoice_id = data['invoice_id'];
    var selected_invoice = data['invoice_no'];
    var selected_invoice_date = data['invoice_date'];
    var selected_invoice_xml_id = data['invoice_xml_id'];
    var edit_from = data['edit_from'];    
     
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];
    var credit_note = data['credit_note'];

    Swal.fire({
      title: 'Are you sure?',   
      text: "You want to edit the selected invoice!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Edit!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
    
      if (result.value) {
        
        btn_edit_salesinvoice.attr('disabled', 'disabled');
        btn_edit_salesinvoice.html(
            '<span><i class="bx bx-edit-alt"></i> Editing...</span>');
                
        if(credit_note)
          $('#modalDeclarationFtpSalesInvoiceEdit .onboarding-title').html('Edit FTP Credit Note');
        else
          $('#modalDeclarationFtpSalesInvoiceEdit .onboarding-title').html('Edit FTP Sales Invoice');

        $('#modalDeclarationFtpSalesInvoiceEdit #declaration_ftpsalesinvoice').hide();        
        $('#modalDeclarationFtpSalesInvoiceEdit #declaration_ftpsalesinvoice').html('');
        $("#modalDeclarationFtpSalesInvoiceEdit .sk-bounce").show();
        $('#modalDeclarationFtpSalesInvoiceEdit').modal('show');
       
        $.ajax({          
          url: `${declarationInvoiceUrl}${selected_invoice_id}/edit`,
          data: { vat_reg_id: vat_reg_id, invoice_no: selected_invoice, invoice_date: selected_invoice_date
            , invoice_xml_id: selected_invoice_xml_id, invoice_name: invoice_name, tab_name: which_tab, edit_from: edit_from},  
          type: 'GET',
          success: function (result) {
            //console.log(result);

            $("#modalDeclarationFtpSalesInvoiceEdit .sk-bounce").hide();
            $('#modalDeclarationFtpSalesInvoiceEdit #declaration_ftpsalesinvoice').html(result.sales_invoice_datas);
            $('#modalDeclarationFtpSalesInvoiceEdit #declaration_ftpsalesinvoice').show();
            
            btn_edit_salesinvoice.html(
            '<span><i class="bx bx-edit-alt"></i> Edit</span>');
          },
          error: function(jqXHR, textStatus, errorThrown){
            console.log('error: ' + textStatus);
          }
        });

      } else if (result.dismiss === Swal.DismissReason.cancel) {
        btn_edit_salesinvoice.html(
            '<span><i class="bx bx-edit-alt"></i> Edit</span>');

        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled Edit Invoice :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
     
    }); 
  });

  // Edit FTP Sales invoice XML - save
  $(document).on("submit", ".frm-declaration-ftpsalesinvoice-edit", function(event) {  
    event.preventDefault();
   
    var formId = $(this).attr('id');   
    var ftp_file_id = $("#ftp_file_id").val();
    var invoice_id = (String($('#sales_invoice_id').val()).indexOf(',') != -1) ? 0 : $('#sales_invoice_id').val();  

    var formData = new FormData(this);         
                 
    var btn_save = $("#" + formId + " #btn-declaration-ftpsalesinvoice-edit-save");
    btn_save.attr('disabled', 'disabled');
    btn_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
            'Saving...');
    
    $.ajax({
      url: `${declarationInvoiceUrl}${invoice_id}/edit`,
      type: 'POST',
      dataType: "JSON",
      data: formData,
      processData: false,
      contentType: false,
      success: function (result) {
       
        if(result)    
        {                 
          btn_save.removeAttr('disabled');
          btn_save.html('Save');
          btn_save.removeClass('disabled');

          var which_tab = result['tab_name'];
          
          $(".btn-declaration-invoice-edit").html(            
            '<span><i class="bx bx-edit-alt"></i> Edit</span>'
          );          
          
          var declaration_datas = drawDtTable(result, 'declaration');
          reloadDeclarations(declaration_datas);         
          
          $('#modalDeclarationFtpSalesInvoiceEdit').modal('hide');

          Swal.fire({
            icon: 'success',
            title: 'Sales Invoice ' + ((ftp_file_id == '') ? 'created!' : 'edited!'),
            text: 'Sales Invoice has been ' + ((ftp_file_id == '') ? 'created.' : 'edited.'),
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      },
      error: function (error) {
        console.log(error);
      }
    });  
  });

  //Download/View Cargo PDF
  $(document).on('click', '.btn-declaration-cargo-download-pdf', function () {
      var btn_download_cargo_pdf = $(this);
      var data = btn_download_cargo_pdf.data();

      var file_type = data['cargo_type'];
      var file_id = data['cargo_file_id'];
      var invoice_no = data['invoice_no'];
      
      btn_download_cargo_pdf.html('<span><i class="bx bxs-download text-danger"></i> Downloading...</span>');

      $.ajax({      
        url: `${fileUrl}${file_id}/download`,
        type: 'GET',       
        data: {file_type: file_type},         
        success: function (data) {                
          btn_download_cargo_pdf.html('<span><i class="bx bxs-file-pdf text-danger"></i> View Cargo PDF</span>');

          window.open(data, '_blank');          
        },
        error: function (err) {
          console.log(err);     
        }
      });
  });

  // Delete com. invoice/sales invoice (for only com. invoices)
  $(document).on('click', '.btn-delete-declaration-invoice', function () {    
    var btn_delete_invoice = $(this);
    var data = btn_delete_invoice.data();

    var vat_reg_id = $("#vat_reg_id").val();

    var invoice_id = data['invoice_id'];
    var invoice_no = data['invoice_no'];
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];

    Swal.fire({
      title: 'Are you sure?',
      text: "You want to delete the " + ((invoice_name == 'com') ? "com. invoice along with lope no. permanently" : "sales invoice") + "!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_delete_invoice.attr('disabled', 'disabled');
        btn_delete_invoice.html('<i class="spinner-border me-1"></i>');

        $.ajax({
            url: `${declarationInvoiceUrl}${invoice_id}`,
            type: 'DELETE',        
            data: {invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : vat_reg_id, tab_name: which_tab},        
            success: function (result) {
             
              if(result['status'] == 200)    
              {                 
                btn_delete_invoice.removeAttr('disabled');
                btn_delete_invoice.html('<i class="bx bx-folder-minus"></i>');
               
                
                var declaration_datas = drawDtTable(result, 'declaration');
                reloadDeclarations(declaration_datas);
                              
                var swal_text = 'Invoice';          
                if($('#invoice_name').val() == 'com')
                  swal_text = 'Commercial invoice';
                else if($('#invoice_name').val() == 'sales')
                  swal_text = 'Sales invoice ';
               
                Swal.fire({
                  icon: 'success',
                  title: swal_text +' deleted!',
                  text: swal_text + ' has been deleted.',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
              }
            },
            error: function (error) {
              console.log(error);
            }
          });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled delete :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });   
  });  

  // Move sales invoice - open modal
  $(document).on('click', '.btn-move-declaration-salesinvoice', function () {    
    var btn_move_salesinvoice = $(this);
    var data = btn_move_salesinvoice.data();

    var move_salesinvoice_text = (btn_move_salesinvoice.attr('title') == 'Move sales invoice') ? 'move' : 'enable';
    var move_salesinvoice_suffix = (btn_move_salesinvoice.attr('title') == 'Move sales invoice') ? 'ed' : 'd';
    var move_salesinvoice_text_capitalize = (btn_move_salesinvoice.attr('title') == 'Move sales invoice') ? 'Move' : 'Enable';
    var move_salesinvoice_text_after = (btn_move_salesinvoice.attr('title') == 'Move sales invoice') ? 'Enable' : 'Move';
    var move_salesinvoice_text_loading = (btn_move_salesinvoice.attr('title') == 'Move sales invoice') ? 'Moving' : 'Enabling';
   
    var vat_reg_id = $("#vat_reg_id").val();    
   
    var com_invoice_id = data['cominvoice_id'];
    var com_invoice_no = data['cominvoice_no'];

    var selected_invoice_id = data['invoice_id'];
    var selected_invoice = data['invoice_no'];
    var selected_invoice_date = data['invoice_date'];
   
    Swal.fire({
      title: 'Are you sure?',   
      text: "You want to "+ move_salesinvoice_text +" the selected invoice!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ move_salesinvoice_text_capitalize +'!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
    
      if (result.value) {
        
        btn_move_salesinvoice.attr('disabled', 'disabled');
        btn_move_salesinvoice.html(
            '<span><i class="bx bx-move"></i> ' +
            move_salesinvoice_text_loading + '...</span>');
        
        var filldata = {       
          "cominvoice_id": com_invoice_id,
          "cominvoice_no": com_invoice_no,  
          "invoice_id": selected_invoice_id,
          "invoice_no": selected_invoice,
          "invoice_date": selected_invoice_date,
          "invoice_name": 'sales',
          "tab_name": data['tab_name']
        };
        fillMoveSalesInvoiceModal(filldata);        
            
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        btn_move_salesinvoice.html(
            '<span><i class="bx bx-move"></i> Move sales invoice</span>');

        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled ' + move_salesinvoice_text_capitalize + ' Invoice :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
     
    }); 
  });

  function fillMoveSalesInvoiceModal(data)
  {    
    $("#move_invoice_vat_reg_id").val($("#vat_reg_id").val());
    $("#move_invoice_id").val(data['invoice_id']);
    $("#move_invoice_no").val(data['invoice_no']);
    $("#move_invoice_name").val(data['invoice_name']);
    $("#move_month_year").val(moment(data['invoice_date']).format('MM-YYYY'));    
    $("#move_tab_name").val(data['tab_name']);

    $("#move_cominvoice_id").val(data['cominvoice_id']);
    $("#move_cominvoice_no").val(data['cominvoice_no']);
  
    var sub_headline = 'Note: ' + moment(data['invoice_date']).format('MMM') +  ' ' + $("#org_no").val() +                         
                        ' - Com. Invoice No: ' + data['cominvoice_no'] +
                        ' - Sales Invoice No: ' + data['invoice_no'];
    $("#modalDeclarationSalesInvoiceMove .onboarding-info").html(sub_headline);
  
    var prefix = "Move ";
    var suffix = "";
    var heading = 'Declaration';
    if(data['invoice_name'] == 'com')
      heading = 'Commercial Invoice';
    else if(data['invoice_name'] == 'sales')
      heading = 'Sales Invoice';   

    $("#modalDeclarationSalesInvoiceMove .onboarding-title").html(prefix + heading + suffix);

    var com_invoices = [];
    if(data['tab_name'] == 'first')
      com_invoices = declaration_first_datas[0]['modal_co_invoices'];
    else
      com_invoices = declaration_second_datas[0]['modal_co_invoices'];

    var options = '<option value="" selected="selected">--Select Com. Invoices--</option>'; 

    var option_current_period_group = false;
    var option_other_period_group = false;

    var options_current_period_group = '<optgroup label="--- Current Period ---">';
    var options_other_period_group = '<optgroup label="--- Other Periods ---">';

    var option_count = 0;           
    $.each(com_invoices, function (index, com_invoice) {       
      if(!com_invoice['disabled'])
      {   
        if(data['cominvoice_id'] == com_invoice['id'])
        {

        }
        else
        {    
          if(com_invoice['other_period'])         
            options_other_period_group += '<option value="'+com_invoice['id']+'">'+com_invoice['co_invoice_no']+'</option>';           
          else 
            options_current_period_group += '<option value="'+com_invoice['id']+'">'+com_invoice['co_invoice_no']+'</option>';
         
          option_count = option_count + 1;
        }             
      }
    });
    options_current_period_group += '</optgroup>';
    options_other_period_group += '</optgroup>';

    options += options_current_period_group + options_other_period_group;
    

    $('#declaration-cominvoice-move').html(options); 
    if(option_count == 0)
    {
      $('#declaration-cominvoice-move').attr('disabled', 'disabled'); 
      $('#btn-declaration-cominvoice-move-save').attr('disabled', 'disabled'); 
    }
    else
    {
      $('#declaration-cominvoice-move').removeAttr('disabled'); 
      $('#btn-declaration-cominvoice-move-save').removeAttr('disabled'); 
    }
    
    $('#modalDeclarationSalesInvoiceMove').modal('show');
  }

  // Move sales invoice - save
  $(document).on("submit", ".frm-declaration-salesinvoice-move", function(event) {  
    event.preventDefault();

    var formId = $(this).attr('id');   
    var invoice_id = (String($('#move_invoice_id').val()).indexOf(',') != -1) ? 0 : $('#move_invoice_id').val();      
         
    var formData = new FormData(this);         
                 
    var btn_move_save = $("#" + formId + " #btn-declaration-salesinvoice-move-save");
    btn_move_save.attr('disabled', 'disabled');
    btn_move_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
            'Moving...');
    
    $.ajax({
      url: `${declarationInvoiceUrl}${invoice_id}/move`,
      type: 'POST',
      dataType: "JSON",
      data: formData,
      processData: false,
      contentType: false,
      success: function (result) {
       
        if(result)    
        {                 
          btn_move_save.removeAttr('disabled');
          btn_move_save.html('Move');
          btn_move_save.removeClass('disabled');

          var which_tab = result['tab_name'];
          $(".btn-move-declaration-salesinvoice").html(            
            '<span><i class="bx bx-move"></i> Move Sales invoice</span>'
          );
          
         
          var declaration_datas = drawDtTable(result, 'declaration');
          reloadDeclarations(declaration_datas);          
        
          var swal_text = 'Sales invoice';          
         
          //Clear Modal Values          
          var com_invoices = declaration_datas['declaration_'+ which_tab +'_datas'][0]['modal_co_invoices'];          
          $("#declaration-salesinvoice-move").html('');

          var options = '<option value="" selected="selected">--Select Com. Invoices--</option>';            
          $.each(com_invoices, function (index, com_invoice) { 
            var com_invoice_disabled = (com_invoice['disabled']) ? 'disabled="disabled"' : '';
            options += '<option value="'+com_invoice['id']+'" '+ com_invoice_disabled +'>'+com_invoice['co_invoice_no']+'</option>';
          });
          $('#declaration-salesinvoice-move').html(options); 
          
          $('#modalDeclarationSalesInvoiceMove').modal('hide');

          Swal.fire({
            icon: 'success',
            title: 'Sales Invoice moveed!',
            text: swal_text + ' has been moveed.',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      },
      error: function (error) {
        $(".btn-move-declaration-salesinvoice").html(            
            '<span><i class="bx bx-move"></i> Move Sales invoice</span>'
          );

        console.log(error);
      }
    });  
  });

  // Dispose toast when open another
  function toastDispose(toast) {
    if (toast && toast._element !== null) {
      if (toastPlacementDiv) {
        toastPlacementHeader.classList.remove(selectedToastType);
        DOMTokenList.prototype.remove.apply(toastPlacementDiv.classList, selectedToastPlacement);
      }
      
      toast.dispose();
    }
  }

  ///function reloadDeclarations(result) {
  function reloadDeclarations(declaration_datas) {  
     // Delay execution by 3 seconds (3000 milliseconds)
    //setTimeout(function() {        

      var dt_declarations_tables = $('.datatables-declarations');

      for (var i = 0; i < dt_declarations_tables.length; i++) 
      {      
        var declaration_name = '';        
        if(i === 0)        
          declaration_name = 'first';          
        else if(i === 1)        
          declaration_name = 'second';          
        else if(i === 2)       
          declaration_name = 'third';     

        if($('.datatables-'+ declaration_name +'-declarations').length > 0)
        {
          if ($.fn.DataTable.isDataTable('.datatables-'+ declaration_name +'-declarations'))
          {
            $("#navs-declaration-" + declaration_name).css({
              'pointer-events': 'none',
              'opacity': '0.5',
              'cursor': 'not-allowed'
            });

            var dt_declarations = $('.datatables-'+ declaration_name +'-declarations').DataTable(); // safely get it
          
            if (dt_declarations.rows().any())
            {
              dt_declarations.clear().rows.add(declaration_datas['declaration_'+ declaration_name +'_datas']).draw();
              $("#btn-declaration-"+ declaration_name +" span").html(declaration_datas['declaration_'+ declaration_name +'_datas'].length);

              // Add class to all data rows
              $('.datatables-'+ declaration_name +'-declarations tbody tr').addClass('accordion-button');
              clickAccordionButton(dt_declarations, declaration_name, $('.datatables-'+ declaration_name +'-declarations tbody tr.accordion-button:first-child'));

              addRedRemarks(declaration_name);
            }

            $("#navs-declaration-" + declaration_name).removeAttr('style');
          }
        }       
      }

      reInitializeTooltips();

      return true;
    //}, 3000);
  }

  // Function to start the interval to check the batch job status
  function startInterval(batchId) {
      if (intervalId === null) {
          intervalId = setInterval(function() {
              checkBatchStatus(batchId);
          }, 2000); // Check status every 2 seconds
          console.log("Started checking batch status every 2 seconds");
      }
  }

  // Function to stop the interval
  function stopInterval() {
      if (intervalId !== null) {
          clearInterval(intervalId);
          console.log("Stopped checking batch status");
          intervalId = null;
      }
      if (xhr !== null) {
          xhr.abort(); // Abort the ongoing AJAX request if any
          console.log("AJAX request aborted");
          xhr = null;
      }
  }

  // AJAX function to create the batch job
  function createBatchJob() {
    var vat_reg_id = $("#vat_reg_id").val();

    var btn_refresh = $('#btn-gs-refresh');
    btn_refresh.attr('disabled','disabled');
    btn_refresh.addClass('disabled');
    btn_refresh.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Refreshing...');

    finshedRefresh = false;

      xhr = $.ajax({
          url: `${declarationInvoiceUrl}${vat_reg_id}/global-search-refresh`,
          method: 'GET',
          // data: {
          //     _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token for security
          // },
          success: function(result) {
              //console.log("Batch job created, ID:", response.batch_id);
console.log(result);
              let totalBatches = result['batchIds'].length;       

              if(totalBatches > 0)
              {                
                console.log(" totalBatches : " + totalBatches);

                $.each(result['batchIds'], function (index, batchId) { 
                  console.log(batchId)    ;

                  var batch_id = batchId['batchId'];
                  
                  // After the batch job is created, start checking the status
                  startInterval(batch_id); // Start checking the status of the job

                  console.log("intervalId: " + intervalId + " batch_id: " + batch_id)    ;
                }); 
              }
              else
              { 
                btn_refresh.removeAttr('disabled');
                btn_refresh.removeClass('disabled');
                btn_refresh.html('Refresh'); 

                if (toastPlacement)
                  toastDispose(toastPlacement);

                $(toastPlacementDiv).find('small').html(moment().format('DD-MM-YYYY hh:mm:ss A'));
                $(toastPlacementDiv).find('.toast-body').html("No new data's found in Global search.");

                toastPlacementHeader.classList.add(selectedToastType);
                DOMTokenList.prototype.add.apply(toastPlacementDiv.classList, selectedToastPlacement);
                toastPlacement = new bootstrap.Toast(toastPlacementDiv);
                toastPlacement.show(); 
              }
              
          },
          error: function(xhr, status, error) {
              console.log("Error creating batch job:", status, error);

              if (toastPlacement)
                toastDispose(toastPlacement);

              $(toastPlacementDiv).find('small').html(moment().format('DD-MM-YYYY hh:mm:ss A'));
              $(toastPlacementDiv).find('.toast-body').html("Error in fetching datas from Global search.");

              toastPlacementHeader.classList.add(errorToastType);
              DOMTokenList.prototype.add.apply(toastPlacementDiv.classList, selectedToastPlacement);
              toastPlacement = new bootstrap.Toast(toastPlacementDiv);
              toastPlacement.show();      
          }
      });
  }

  // AJAX function to check the batch job status
  function checkBatchStatus(batchId) {
      var vat_reg_id = $("#vat_reg_id").val();

      var btn_refresh = $('#btn-gs-refresh');

      // Check if xhr is already active. If so, prevent sending a new request
      if (xhr !== null && xhr.readyState !== 4) {
          console.log("Previous request still in progress, skipping...");
          return; // Prevent sending a new request if the previous one is not complete
      }

      xhr = $.ajax({
          url: `${declarationInvoiceUrl}${vat_reg_id}/batch-status/` + batchId,  
          method: 'GET',          
          success: function(response) {
              console.log(response);
              // console.log("Batch Status:", response.status);
              // if (response.status === 'completed') {
              //     // If the batch job is complete, stop checking status
              //     stopInterval();
              //     alert('Batch job completed!');
              // }

              if(response['status'] == 'processing')
              {  
                console.log("processing.....");   
                if(response['pending_jobs'] == 0)
                {            
                  console.log("pending_jobs 0.....");                            
                } //pending_jobs == 0
                else
                {            
                  console.log("totalJobs: " + totalJobs);
                  console.log("completedJobs: " + completedJobs);

                  if(totalJobs == 0)
                    totalJobs = response['pending_jobs'];

                  if(response['pending_jobs'] > 0)
                  {
                    completedJobs = totalJobs - response['pending_jobs'];
                    console.log("ssss " + completedJobs);

                    if (toastPlacement)
                      toastDispose(toastPlacement);

                    $(toastPlacementDiv).find('small').html(moment().format('DD-MM-YYYY hh:mm:ss A'));                    
                    $(toastPlacementDiv).find('.toast-body').html("Refreshing in progress...");

                    toastPlacementHeader.classList.add(selectedToastType);
                    DOMTokenList.prototype.add.apply(toastPlacementDiv.classList, selectedToastPlacement);
                    toastPlacement = new bootstrap.Toast(toastPlacementDiv);
                    toastPlacement.show();  
                  }           
                } //pending_jobs > 0
              } //processing
              else if(response['status'] == 'unknown')  
              {  
                console.log("finshedRefresh ...." + finshedRefresh);   

                if(!finshedRefresh)
                {
                  if(totalJobs == 0)
                  {        
                    console.log("totalJobs 0.....");   

                    btn_refresh.removeAttr('disabled');
                    btn_refresh.removeClass('disabled');
                    btn_refresh.html('Refresh');  

                    finshedRefresh = true;

                    if (toastPlacement)
                      toastDispose(toastPlacement);

                    $(toastPlacementDiv).find('small').html(moment().format('DD-MM-YYYY hh:mm:ss A'));
                    $(toastPlacementDiv).find('.toast-body').html("Global search datas refreshed successfully.");

                    toastPlacementHeader.classList.add(selectedToastType);
                    DOMTokenList.prototype.add.apply(toastPlacementDiv.classList, selectedToastPlacement);
                    toastPlacement = new bootstrap.Toast(toastPlacementDiv);
                    toastPlacement.show();                           
                  }
                  else
                  {
                    if(completedJobs === (totalJobs - 1))
                    {    
                      console.log("completedJobs ....");                                              
                      totalJobs = 0;
                      completedJobs = 0;

                      finshedRefresh = true;

                      var result = response['result'];                      
                      var declaration_datas = drawDtTable(result, 'declaration');
                      reloadDeclarations(declaration_datas);

                      btn_refresh.removeAttr('disabled');
                      btn_refresh.removeClass('disabled');
                      btn_refresh.html('Refresh');  

                      if (toastPlacement)
                        toastDispose(toastPlacement);

                      $(toastPlacementDiv).find('small').html(moment().format('DD-MM-YYYY hh:mm:ss A'));
                      $(toastPlacementDiv).find('.toast-body').html("Global search datas refreshed successfully.");

                      toastPlacementHeader.classList.add(selectedToastType);
                      DOMTokenList.prototype.add.apply(toastPlacementDiv.classList, selectedToastPlacement);
                      toastPlacement = new bootstrap.Toast(toastPlacementDiv);
                      toastPlacement.show();               
                    }  
                  }
                }//  finshedRefresh

                stopInterval();
              } //completed
          },
          error: function(xhr, status, error) {
              console.log("Error checking batch status:", status, error);
              // Log the error message
             
              // if (toastPlacement)
              //   toastDispose(toastPlacement);

              // $(toastPlacementDiv).find('small').html(moment().format('DD-MM-YYYY hh:mm:ss A'));
              // $(toastPlacementDiv).find('.toast-body').html("Error in fetching datas from Global search.");

              // toastPlacementHeader.classList.add(errorToastType);
              // DOMTokenList.prototype.add.apply(toastPlacementDiv.classList, selectedToastPlacement);
              // toastPlacement = new bootstrap.Toast(toastPlacementDiv);
              // toastPlacement.show();                
          }
      });
  }

  // Refresh button click event to create batch job  
  $(document).on('click', '#btn-gs-refresh', function() {  
      createBatchJob();  // Trigger the batch job creation process
  });

  // Refresh specific invoice (com./sales)
  $(document).on('click', '.btn-refresh-invoice', function() { 
    var btn_refresh_invoice = $(this);
    var data = btn_refresh_invoice.data();

    var invoice_id = data['invoice_id'];
    var invoice_no = data['invoice_no'];
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];
    
    btn_refresh_invoice.attr('disabled', 'disabled');
    btn_refresh_invoice.html('<i class="bx bx-loader-alt me-1"></i> Refreshing...');

    btn_refresh_invoice.closest('tr.accordion-button').addClass('disabled');
    
    $.ajax({
        url: `${declarationInvoiceUrl}${invoice_id}/refresh`,
        type: 'POST',        
        data: {invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : $("#vat_reg_id").val(), 
          tab_name: which_tab},        
        success: function (result) {        
          if(result)    
          {                             
            var declaration_datas = drawDtTable(result, 'declaration');
            reloadDeclarations(declaration_datas);
          
            btn_refresh_invoice.removeAttr('disabled');
            btn_refresh_invoice.html('<i class="bx bx-refresh"></i> Refresh Data');
          
            btn_refresh_invoice.closest('tr.accordion-button').removeClass('disabled');

            /*
            var swal_text = '';          
            if(invoice_name == 'com')
              swal_text = 'Commercial invoice';
            else if(invoice_name == 'sales')
              swal_text = 'Sales invoice';
           
            Swal.fire({
              icon: 'success',
              title: 'Invoice refreshed!',
              text: swal_text + ' has been refreshed.',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
            */
          }
        },
        error: function (error) {
          console.log(error);
        }
      });
  });

   //Switch Currency convert options
  $(".switch-input.currency-convert-date").each(function () {
    switchCurrenctRateTypes($(this));   
  });

  $(document).on('click', '.switch-input.currency-convert-date', function () { 
    switchCurrenctRateTypes($(this));       
  });

  function switchCurrenctRateTypes(element)
  {
    var formId = element.closest('.formCurrencyConvert').attr('id');    
          
    if(!element.prop('checked'))   
    {
      $("#" + formId + " .btn-convert").addClass('disabled');                     
      $("#" + formId + " .btn-convert").removeClass('btn-success');                
      $("#" + formId + " .btn-convert").addClass('btn-danger'); 
    }
    else     
    {
      $("#" + formId + " .btn-convert").removeClass('disabled');        
      $("#" + formId + " .btn-convert").addClass('btn-success');                
      $("#" + formId + " .btn-convert").removeClass('btn-danger');  
    }
  }

  document.querySelectorAll('[id^="currencyConvertModal-"]').forEach(modal => {
    modal.addEventListener('show.bs.modal', function (event) {
      var which_tab = $('.nav-link.active').attr('id').replace('btn-declaration-', '');
      var selected_month = $("#declaration_"+ which_tab +"_monthyear").val();
     
      // Hide all exchange-rate inputs first
      modal.querySelectorAll('.last-exchange-rate').forEach(el => el.style.display = 'none');

      // Show the one matching data-x
      const inputToShow = modal.querySelector('.last-exchange-rate[data-last_exchange_monthyear="'+ selected_month +'"]');     
      if (inputToShow)
        inputToShow.style.display = 'block';      
    });
  });

  //Convert currency 
  $(document).on('click', '.btn-convert', function () {
    
    var formId = $(this).closest('.formCurrencyConvert').attr('id');    
    var modalId = $(this).closest('.modal-file').attr('id'); 
    
    var btn_convert = $(this);   

    var data = btn_convert.data();
   
    var vat_reg_id = data['vat_reg_id'];  

    var which_tab = $(".nav-tabs .nav-item .nav-link.active").attr("id").replace('btn-declaration-', '');
    var selected_month = $("#declaration_"+ which_tab +"_monthyear").val();
    var selected_month_text = moment(selected_month, "MM-YYYY").format("MMM-YYYY");
 
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to convert the currency for the "+ selected_month_text +" month invoices!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Convert!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_convert.attr('disabled', 'disabled');
        btn_convert.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
            'Converting...');
        
        $.ajax({
          data: $('#' + formId).serialize() + '&tab_name=' + which_tab + '&selected_month=' + selected_month,
          type: 'POST',         
          url: `${declarationInvoiceUrl}${vat_reg_id}/convert`,
          success: function (result) {            
            checkJobStatus(result, modalId, btn_convert);            
          },
          error: function (error) {
            console.log(error);
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled currency conversion :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });   
    
  });

  function checkJobStatus(result, modalId, btn_convert) {
    var logId = result.joblog_id;
    $.get(`/job-status/${logId}`, function(data) {
      if (data.job_status === 'completed') {
        result = data;

        var declaration_datas = drawDtTable(result, 'declaration');
        reloadDeclarations(declaration_datas);

        var vat_reg_id = result.declarations.id;
        $("#currencyConvertModal-" + vat_reg_id + " #accordionStyleCurrencyConvert-" + vat_reg_id).replaceWith(result.modal_currency_convert);
        
        if(data['message'] == 'success')    
          Swal.fire({
            icon: 'success',
            title: `Successfully converted!`,
            text: `Currency converted Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }  
          }).then(function (r) {               
            btn_convert.removeAttr('disabled');               
            btn_convert.html("Convert");   

            $("#"+ modalId).modal('hide');              
          });
        else
          Swal.fire({
            icon: 'error',
            title: `Error in conversion!`,
            text: `Error while converting`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
         
      } else if (data.job_status === 'failed') {
        console.log('Job failed. Please try again.');
      } else {
        setTimeout(() => checkJobStatus(result, modalId, btn_convert), 2000); // retry in 2s
      }
    }).fail(function() {
      // Optional: handle error (e.g. log not found)
      console.error('Error fetching job status.');
    });
  }

  // Unmatch com. invoice
  $(document).on('click', '.btn-unmatch-invoice', function() { 
    var btn_unmatch_invoice = $(this);
    var data = btn_unmatch_invoice.data();

    var invoice_id = data['invoice_id'];
    var invoice_no = data['invoice_no'];
    var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];
    
    btn_unmatch_invoice.attr('disabled', 'disabled');
    btn_unmatch_invoice.html('<i class="bx bx-loader-alt me-1"></i> Unmatching...');

    btn_unmatch_invoice.closest('tr.accordion-button').addClass('disabled');
    
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to unmatch for the selected com. invoice!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Unmatch!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        $.ajax({
            url: `${declarationInvoiceUrl}${invoice_id}/unmatch`,
            type: 'POST',        
            data: {invoice_no: invoice_no, invoice_name: invoice_name, vat_reg_id : $("#vat_reg_id").val(), 
              tab_name: which_tab},        
            success: function (result) {        
              if(result)    
              {                             
                var declaration_datas = drawDtTable(result, 'declaration');
                reloadDeclarations(declaration_datas);
              
                btn_unmatch_invoice.removeAttr('disabled');
                btn_unmatch_invoice.html('<i class="bx bx-list-minus"></i> Unmatch com. invoice');
              
                btn_unmatch_invoice.closest('tr.accordion-button').removeClass('disabled');

                /*
                var swal_text = '';          
                if(invoice_name == 'com')
                  swal_text = 'Commercial invoice';
                else if(invoice_name == 'sales')
                  swal_text = 'Sales invoice';
               
                Swal.fire({
                  icon: 'success',
                  title: 'Invoice refreshed!',
                  text: swal_text + ' has been refreshed.',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
                */
              }
            },
            error: function (error) {
              console.log(error);
            }
          });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled unmatch commercial invoice :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });   
  });

  // Bootstrap Datepicker
  // --------------------------------------------------------------------
  var bsDatepickerInvoiceDate = $('#invoice_date'),
    bsDatepickerDeliveryDate = $('#delivery_date'),
    bsDatepickerDueDate = $('#due_date'),
    bsDatepickerSettlementDate = $('#settlement_date'),
    bsDatepickerPenaltyDate = $('#penalty_date');

  // Basic
  if (bsDatepickerInvoiceDate.length) {
    bsDatepickerInvoiceDate.datepicker({
      format: "dd/mm/yyyy",      
      autoclose: true,
    });
  }

  // Basic
  if (bsDatepickerDeliveryDate.length) {
    bsDatepickerDeliveryDate.datepicker({
      format: "dd/mm/yyyy",      
      autoclose: true,
    });
  }

  // Basic
  if (bsDatepickerDueDate.length) {
    bsDatepickerDueDate.datepicker({
      format: "dd/mm/yyyy",      
      autoclose: true,
    });
  }

  // Basic
  if (bsDatepickerSettlementDate.length) {
    bsDatepickerSettlementDate.datepicker({
      format: "dd/mm/yyyy",      
      autoclose: true,
    });
  }

  // Basic
  if (bsDatepickerPenaltyDate.length) {
    bsDatepickerPenaltyDate.datepicker({
      format: "dd/mm/yyyy",      
      autoclose: true,
    });
  }

  // window.Echo.channel('com-sales-invoices-channel').listen('.ImportReconciliationComSalesInvoicesEvent', (event) => {    
  //       console.log('Import Reconciliation Com./Sales Invoices CURRENCY Event:', event);       
  //       // Handle the event
  //       var vat_reg_id = event.vat_reg_id;
        
  //       $.ajax({      
  //         url: baseUrl + 'declarations/'+ vat_reg_id,
  //         type: 'GET',                 
  //         success: function (result) {  
  //           if(result)    
  //           {                              
  //             var declaration_datas = drawDtTable(result, 'declaration');
  //             reloadDeclarations(declaration_datas);
  //           }
  //         },
  //         error: function (err) {
  //           console.log(err);     
  //         }
  //       });
  // });
});
